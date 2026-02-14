<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Commentaire;
use App\Repository\PostRepository;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, PostRepository $postRepository, CommentaireRepository $commentaireRepository): Response
    {
        // Read search parameters from navbar (q for query, optional author)
        $q = trim((string) $request->query->get('q', ''));
        $author = trim((string) $request->query->get('author', ''));

        // Compute global stats from all posts (keep dashboard stats global)
        $allPosts = $postRepository->findAll();
        $totalViews = 0;
        foreach ($allPosts as $p) {
            $totalViews += $p->getViews();
        }
        $totalPosts = count($allPosts);

        // Prepare filtered lists for display (apply search if provided)
        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if ($author !== '') {
            $filters['author'] = $author;
        }

        if (!empty($filters)) {
            // Use repository flexible search for posts
            $posts = $postRepository->findByFilters($filters);

            // If the user searched with `q`, also try matching it against author name
            if (!empty($filters['q'])) {
                $postsByAuthor = $postRepository->findByFilters(['author' => $filters['q']]);
                $existingIds = array_map(fn($p) => $p->getId(), $posts);
                foreach ($postsByAuthor as $pba) {
                    if (!in_array($pba->getId(), $existingIds, true)) {
                        $posts[] = $pba;
                    }
                }
            }

            // For commentaires, use repository filters (author or q)
            $commentFilters = [];
            if (!empty($filters['author'])) $commentFilters['author'] = $filters['author'];
            if (!empty($filters['q'])) $commentFilters['q'] = $filters['q'];
            $commentaires = $commentaireRepository->findByFilters($commentFilters);
        } else {
            $posts = $postRepository->findAll();
            $commentaires = $commentaireRepository->findAll();
        }

        $totalCommentaires = count($commentaires);
        $avgComments = $totalPosts > 0 ? round($totalCommentaires / $totalPosts, 2) : 0;

        return $this->render('admin/dashboard.html.twig', [
            'posts' => $posts,
            'commentaires' => $commentaires,
            'totalPosts' => $totalPosts,
            'totalCommentaires' => $totalCommentaires,
            'totalViews' => $totalViews,
            'avgComments' => $avgComments,
        ]);
    }

    #[Route('/admin/posts/all/{id}/edit', name: 'app_admin_edit_any_post', methods: ['GET', 'POST'])]
    public function editAnyPost(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $content = $request->request->get('content');
            $tags = $request->request->get('tags', '');
            
            // Validation minimale
            if (!$title || !$content) {
                $this->addFlash('error', 'Le titre et le contenu sont obligatoires.');
                return $this->redirectToRoute('app_admin_edit_any_post', ['id' => $post->getId()]);
            }
            
            $post->setTitle($title);
            $post->setContent($content);
            $post->setTags($tags);
            $post->setUpdatedAt(new \DateTimeImmutable());
            
            $em->persist($post);
            $em->flush();
            
            $this->addFlash('success', 'Post modifié avec succès!');
            return $this->redirectToRoute('app_admin');
        }
        
        return $this->render('admin/posts/edit_any.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/admin/posts/all/{id}/delete', name: 'app_admin_delete_any_post', methods: ['POST'])]
    public function deleteAnyPost(Post $post, EntityManagerInterface $em): Response
    {
        $em->remove($post);
        $em->flush();
        
        $this->addFlash('success', 'Post supprimé avec succès!');
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/comments/{id}/edit', name: 'app_admin_edit_any_comment', methods: ['GET', 'POST'])]
    public function editAnyComment(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            
            if (!$content || trim($content) === '') {
                $this->addFlash('error', 'Le contenu du commentaire ne peut pas être vide.');
                return $this->redirectToRoute('app_admin_edit_any_comment', ['id' => $commentaire->getId()]);
            }
            
            $commentaire->setContent($content);
            $commentaire->setUpdatedAt(new \DateTimeImmutable());
            
            $em->persist($commentaire);
            $em->flush();
            
            $this->addFlash('success', 'Commentaire modifié avec succès!');
            return $this->redirectToRoute('app_admin');
        }
        
        return $this->render('admin/comments/edit.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/admin/comments/{id}/delete', name: 'app_admin_delete_any_comment', methods: ['POST'])]
    public function deleteAnyComment(Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        $em->remove($commentaire);
        $em->flush();
        
        $this->addFlash('success', 'Commentaire supprimé avec succès!');
        return $this->redirectToRoute('app_admin');
    }
}