<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{
    #[Route('/', name: 'app_forum_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $forums = $postRepository->findAllOrderedByDate();

        return $this->render('forum/forum.html.twig', [
            'forums' => $forums,
        ]);
    }

    #[Route('/search', name: 'app_forum_search', methods: ['GET'])]
    public function search(Request $request, PostRepository $postRepository, \App\Repository\CommentaireRepository $commentaireRepository): JsonResponse
    {
        $filters = [];
        $q = $request->query->get('q');
        if ($q) {
            $filters['q'] = $q;
        }
        if ($request->query->has('tags')) {
            $filters['tags'] = $request->query->get('tags');
        }
        if ($request->query->has('author')) {
            $filters['author'] = $request->query->get('author');
        }
        if ($request->query->has('fromDate')) {
            $filters['fromDate'] = $request->query->get('fromDate');
        }
        if ($request->query->has('toDate')) {
            $filters['toDate'] = $request->query->get('toDate');
        }
        if ($request->query->has('sortField')) {
            $filters['sortField'] = $request->query->get('sortField');
        }
        if ($request->query->has('sortDir')) {
            $filters['sortDir'] = $request->query->get('sortDir');
        }
        // Backward compatibility with legacy 'sort' param
        if ($request->query->has('sort')) {
            $filters['sort'] = $request->query->get('sort');
        }
        if ($request->query->has('page')) {
            $filters['page'] = (int) $request->query->get('page');
        }
        if ($request->query->has('limit')) {
            $filters['limit'] = (int) $request->query->get('limit');
        }

        // If author filter supplied, we want posts matching post.author OR comment author
        $posts = $postRepository->findByFilters($filters);

        // If author is provided, include posts that have comments by that author
        if (!empty($filters['author'])) {
            $postIdsFromComments = $commentaireRepository->findPostIdsByAuthor($filters['author']);
                if (!empty($postIdsFromComments)) {
                    // fetch posts by these ids using the same sorting rules as above
                    $allowedSortFields = ['createdAt', 'views', 'title', 'author'];
                    $sortField = $filters['sortField'] ?? 'createdAt';
                    if (!in_array($sortField, $allowedSortFields, true)) {
                        $sortField = 'createdAt';
                    }

                    $sortDir = strtoupper($filters['sortDir'] ?? ($filters['sort'] ?? 'DESC'));
                    if (!in_array($sortDir, ['ASC', 'DESC'], true)) {
                        $sortDir = 'DESC';
                    }

                    // fetch posts by these ids with same ordering
                    $qb = $postRepository->createQueryBuilder('p')
                        ->where('p.id IN (:ids)')
                        ->setParameter('ids', $postIdsFromComments)
                        ->orderBy('p.' . $sortField, $sortDir);

                    $extraPosts = $qb->getQuery()->getResult();
                // merge unique posts
                $existingIds = array_map(fn($p) => $p->getId(), $posts);
                foreach ($extraPosts as $ep) {
                    if (!in_array($ep->getId(), $existingIds, true)) {
                        $posts[] = $ep;
                    }
                }
            }
        }

        $data = array_map(function($p) use ($commentaireRepository) {
            $comments = $commentaireRepository->findByPostOrderedByDate($p->getId());
            $commentData = array_map(function($c) {
                return [
                    'id' => $c->getId(),
                    'author' => $c->getAuthor(),
                    'content' => $c->getContent(),
                    'createdAt' => $c->getCreatedAt() ? $c->getCreatedAt()->format('c') : null,
                ];
            }, $comments);

            return [
                'id' => $p->getId(),
                'title' => $p->getTitle(),
                'content' => $p->getContent(),
                'author' => $p->getAuthor(),
                'createdAt' => $p->getCreatedAt() ? $p->getCreatedAt()->format('c') : null,
                'image' => $p->getImage(),
                'tags' => $p->getTags(),
                'views' => $p->getViews(),
                'commentsCount' => count($comments),
                'createdByAdmin' => $p->isCreatedByAdmin(),
                'comments' => $commentData,
            ];
        }, $posts);

        return new JsonResponse(['data' => $data]);
    }

    #[Route('/new', name: 'app_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, ValidatorInterface $validator): Response
    {
        $post = new Post();

        if ($request->isMethod('POST')) {
            $post->setTitle($request->request->get('title'));
            $post->setContent($request->request->get('content'));
            $post->setTags($request->request->get('tags'));
            $author = $request->request->get('author') ?: 'Anonyme';
            $post->setAuthor($author);
            $post->setCreatedAt(new \DateTimeImmutable());

            // Handle uploaded file and external link (validate before moving/saving file)
            $uploadedFile = $request->files->get('post_image');
            $link = trim((string) $request->request->get('link'));

            // Validate uploaded file and link with Validator
            $extraErrors = [];
            if ($uploadedFile) {
                $fileConstraint = new Assert\File([
                    'maxSize' => '5M',
                    'mimeTypes' => ['image/jpeg','image/png','image/gif','image/webp'],
                ]);
                $fileViolations = $validator->validate($uploadedFile, $fileConstraint);
                foreach ($fileViolations as $v) {
                    $extraErrors['post_image'][] = $v->getMessage();
                }
            }
            if ($link !== '') {
                $urlConstraint = new Assert\Url(['message' => 'Veuillez saisir une URL valide.']);
                $urlViolations = $validator->validate($link, $urlConstraint);
                foreach ($urlViolations as $v) {
                    $extraErrors['link'][] = $v->getMessage();
                }
            }

            // Validate entity using Symfony Validator
            $violations = $validator->validate($post);
            if (count($violations) > 0 || count($extraErrors) > 0) {
                $errors = [];
                foreach ($violations as $v) {
                    $errors[$v->getPropertyPath()][] = $v->getMessage();
                }

                // merge extraErrors
                foreach ($extraErrors as $k => $arr) {
                    foreach ($arr as $m) $errors[$k][] = $m;
                }

                $forums = $postRepository->findAllOrderedByDate();

                return $this->render('forum/forum.html.twig', [
                    'forums' => $forums,
                    'modal_old' => [
                        'title' => $post->getTitle(),
                        'content' => $post->getContent(),
                        'tags' => $post->getTags(),
                        'link' => $request->request->get('link'),
                    ],
                    'modal_errors' => $errors,
                    'show_modal' => true,
                ]);
            }

            // If validation passed, handle file move / external link assignment
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
                $newFilename = $safeFilename . '_' . uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/forum',
                        $newFilename
                    );
                    $post->setImage('/uploads/forum/' . $newFilename);
                } catch (\Exception $e) {
                    // If upload fails, continue without image
                }
            } elseif ($link !== '') {
                // If a link is provided, store it in the image field so it can be displayed as external media
                $post->setImage($link);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/new.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_show', methods: ['GET'])]
    public function show(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->incrementViews();
        $entityManager->flush();

        return $this->render('forum/forum.html.twig', [
            'forum' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $post->setTitle($request->request->get('title'));
            $post->setContent($request->request->get('content'));
            $post->setTags($request->request->get('tags'));
            $author = $request->request->get('author') ?: $post->getAuthor() ?: 'Anonyme';
            $post->setAuthor($author);
            $post->setUpdatedAt(new \DateTimeImmutable());

            // Handle replacement image or external link (validate before moving)
            $uploadedFile = $request->files->get('post_image');
            $link = trim((string) $request->request->get('link'));

            $extraErrors = [];
            if ($uploadedFile) {
                $fileConstraint = new Assert\File([
                    'maxSize' => '5M',
                    'mimeTypes' => ['image/jpeg','image/png','image/gif','image/webp'],
                ]);
                $fileViolations = $validator->validate($uploadedFile, $fileConstraint);
                foreach ($fileViolations as $v) {
                    $extraErrors['post_image'][] = $v->getMessage();
                }
            }
            if ($link !== '') {
                $urlConstraint = new Assert\Url(['message' => 'Veuillez saisir une URL valide.']);
                $urlViolations = $validator->validate($link, $urlConstraint);
                foreach ($urlViolations as $v) {
                    $extraErrors['link'][] = $v->getMessage();
                }
            }

            // Validate entity
            $violations = $validator->validate($post);
            if (count($violations) > 0 || count($extraErrors) > 0) {
                $errors = [];
                foreach ($violations as $v) {
                    $errors[$v->getPropertyPath()][] = $v->getMessage();
                }

                foreach ($extraErrors as $k => $arr) {
                    foreach ($arr as $m) $errors[$k][] = $m;
                }

                return $this->render('forum/forum.html.twig', [
                    'forum' => $post,
                    'is_edit' => true,
                    'errors' => $errors,
                ]);
            }

            // If validation passed, handle file move / external link assignment
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
                $newFilename = $safeFilename . '_' . uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/forum',
                        $newFilename
                    );
                    $post->setImage('/uploads/forum/' . $newFilename);
                } catch (\Exception $e) {
                    // ignore upload errors
                }
            } elseif ($link !== '') {
                $post->setImage($link);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_forum_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('forum/forum.html.twig', [
            'forum' => $post,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/comments/search', name: 'app_forum_comments_search', methods: ['GET'])]
    public function commentsSearch(int $id, Request $request, \App\Repository\CommentaireRepository $commentaireRepository): JsonResponse
    {
        $filters = ['postId' => $id];
        if ($request->query->has('q')) $filters['q'] = $request->query->get('q');
        if ($request->query->has('author')) $filters['author'] = $request->query->get('author');
        if ($request->query->has('fromDate')) $filters['fromDate'] = $request->query->get('fromDate');
        if ($request->query->has('toDate')) $filters['toDate'] = $request->query->get('toDate');
        if ($request->query->has('sortField')) $filters['sortField'] = $request->query->get('sortField');
        if ($request->query->has('sortDir')) $filters['sortDir'] = $request->query->get('sortDir');
        // Backward compatibility with legacy 'sort' param
        if ($request->query->has('sort')) $filters['sort'] = $request->query->get('sort');
        if ($request->query->has('page')) $filters['page'] = (int)$request->query->get('page');
        if ($request->query->has('limit')) $filters['limit'] = (int)$request->query->get('limit');

        $comments = $commentaireRepository->findByFilters($filters);

        $data = array_map(function($c) {
            return [
                'id' => $c->getId(),
                'author' => $c->getAuthor(),
                'content' => $c->getContent(),
                'createdAt' => $c->getCreatedAt() ? $c->getCreatedAt()->format('c') : null,
            ];
        }, $comments);

        return new JsonResponse(['data' => $data]);
    }
}
