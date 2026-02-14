<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Commentaire;
use App\Repository\PostRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPostController extends AbstractController
{
    #[Route('/admin/posts', name: 'app_admin_posts')]
    public function listPosts(Request $request, PostRepository $postRepository): Response
    {
        // Recherche par nom d'auteur (backend)
        $author = trim((string) $request->query->get('author'));
        if ($author !== '') {
            $posts = $postRepository->findByFilters(['author' => $author]);
            // Keep only admin-created posts for this admin list
            $posts = array_values(array_filter($posts, fn($p) => $p->isCreatedByAdmin()));
        } else {
            // Afficher tous les posts créés par les admins
            $posts = $postRepository->findBy(['createdByAdmin' => true]);
        }
        
        return $this->render('admin/posts/index.html.twig', [
            'posts' => $posts,
            'author' => $author,
        ]);
    }

    #[Route('/admin/posts/create', name: 'app_admin_post_create', methods: ['GET', 'POST'])]
    public function createPost(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $content = $request->request->get('content');
            $author = $request->request->get('author');
            $image = $request->request->get('image');
            $tags = $request->request->get('tags');

            $post = new Post();
            $post->setTitle($title);
            $post->setContent($content);
            $post->setAuthor($author);
            $post->setImage($image);
            $post->setTags($tags);
            $post->setCreatedByAdmin(true);
            $post->setViews(0);

            // Validate entity
            $errors = [];
            $violations = $validator->validate($post);
            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[$v->getPropertyPath()][] = $v->getMessage();
                }

                return $this->render('admin/posts/create.html.twig', [
                    'old' => ['title' => $title, 'content' => $content, 'author' => $author, 'image' => $image, 'tags' => $tags],
                    'errors' => $errors,
                ]);
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post créé avec succès!');
            return $this->redirectToRoute('app_admin_posts');
        }

        return $this->render('admin/posts/create.html.twig');
    }

    #[Route('/admin/posts/{id}/edit', name: 'app_admin_post_edit', methods: ['GET', 'POST'])]
    public function editPost(Post $post, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        // Vérifier que le post est bien un post admin
        if (!$post->isCreatedByAdmin()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier ce post.');
            return $this->redirectToRoute('app_admin_posts');
        }

        if ($request->isMethod('POST')) {
            $post->setTitle($request->request->get('title'));
            $post->setContent($request->request->get('content'));
            $post->setAuthor($request->request->get('author'));
            $post->setImage($request->request->get('image'));
            $post->setTags($request->request->get('tags'));
            $post->setUpdatedAt(new \DateTimeImmutable());

            // Validate entity
            $errors = [];
            $violations = $validator->validate($post);
            if (count($violations) > 0 || count($errors) > 0) {
                foreach ($violations as $v) {
                    $errors[$v->getPropertyPath()][] = $v->getMessage();
                }

                return $this->render('admin/posts/edit.html.twig', [
                    'post' => $post,
                    'errors' => $errors,
                ]);
            }

            $em->flush();

            $this->addFlash('success', 'Post modifié avec succès!');
            return $this->redirectToRoute('app_admin_posts');
        }

        return $this->render('admin/posts/edit.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/admin/posts/{id}/delete', name: 'app_admin_post_delete', methods: ['POST'])]
    public function deletePost(Post $post, EntityManagerInterface $em): Response
    {
        // Vérifier que le post est bien un post admin
        if (!$post->isCreatedByAdmin()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer ce post.');
            return $this->redirectToRoute('app_admin_posts');
        }

        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post supprimé avec succès!');
        return $this->redirectToRoute('app_admin_posts');
    }

    #[Route('/admin/posts/{id}/view', name: 'app_admin_post_view', methods: ['GET'])]
    public function viewPost(Post $post): Response
    {
        // Render the frontend-style post view for preview in admin
        return $this->render('forum/forum.html.twig', [
            'forum' => $post,
        ]);
    }

    #[Route('/api/admin/posts/{id}/comments/{commentId}/delete', name: 'app_admin_comment_delete', methods: ['DELETE'])]
    public function deleteComment(Post $post, int $commentId, EntityManagerInterface $em, CommentaireRepository $commentRepo): JsonResponse
    {
        // Vérifier que le post est un post admin
        if (!$post->isCreatedByAdmin()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $comment = $commentRepo->find($commentId);
        if (!$comment || $comment->getPost()->getId() !== $post->getId()) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/admin/posts/{id}/comments/{commentId}/edit', name: 'app_admin_comment_edit', methods: ['POST'])]
    public function editComment(Post $post, int $commentId, Request $request, EntityManagerInterface $em, CommentaireRepository $commentRepo): JsonResponse
    {
        // Vérifier que le post est un post admin
        if (!$post->isCreatedByAdmin()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $comment = $commentRepo->find($commentId);
        if (!$comment || $comment->getPost()->getId() !== $post->getId()) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['content'])) {
            $comment->setContent($data['content']);
            $comment->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
        }

        return new JsonResponse(['success' => true, 'comment' => [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'author' => $comment->getAuthor(),
            'updatedAt' => $comment->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]]);
    }
}
