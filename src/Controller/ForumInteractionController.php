<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum-interaction')]
class ForumInteractionController extends AbstractController
{
    private const VALID_REACTION_TYPES = [
        'like',
        'love',
        'haha',
        'wow',
        'sad',
        'angry',
    ];

    #[Route('/react/{id}', name: 'forum_react', methods: ['POST'])]
    public function reactToForum(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $post = $entityManager->getRepository(Post::class)->find($id);
        
        if (!$post) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $type = $request->request->get('type');
        $author = $request->request->get('author');

        // Validate reaction type
        if (!in_array($type, self::VALID_REACTION_TYPES)) {
            return $this->json(['error' => 'Invalid reaction type'], Response::HTTP_BAD_REQUEST);
        }

        if (!$author) {
            return $this->json(['error' => 'Author is required'], Response::HTTP_BAD_REQUEST);
        }

        // Get current reactions or initialize empty array
        $reactions = $post->getReactions() ?? [];

        // Check if user already reacted
        $userReactionIndex = null;
        foreach ($reactions as $index => $reaction) {
            if ($reaction['author'] === $author) {
                $userReactionIndex = $index;
                break;
            }
        }

        if ($userReactionIndex !== null) {
            // If same type, remove it (toggle); otherwise change type
            if ($reactions[$userReactionIndex]['type'] === $type) {
                unset($reactions[$userReactionIndex]);
                $reactions = array_values($reactions); // Re-index array
            } else {
                $reactions[$userReactionIndex]['type'] = $type;
            }
        } else {
            // Create new reaction
            $reactions[] = [
                'type' => $type,
                'author' => $author,
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ];
        }

        $post->setReactions($reactions);
        $entityManager->flush();

        // Return updated reaction counts
        $counts = $this->getReactionCounts($reactions);
        $total = count($reactions);

        return $this->json([
            'success' => true,
            'counts' => $counts,
            'total' => $total,
        ]);
    }

    #[Route('/comment-react/{id}', name: 'comment_react', methods: ['POST'])]
    public function reactToComment(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);
        
        if (!$commentaire) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $type = $request->request->get('type');
        $author = $request->request->get('author');

        // Validate reaction type
        if (!in_array($type, self::VALID_REACTION_TYPES)) {
            return $this->json(['error' => 'Invalid reaction type'], Response::HTTP_BAD_REQUEST);
        }

        if (!$author) {
            return $this->json(['error' => 'Author is required'], Response::HTTP_BAD_REQUEST);
        }

        // Get current reactions or initialize empty array
        $reactions = $commentaire->getReactions() ?? [];

        // Check if user already reacted
        $userReactionIndex = null;
        foreach ($reactions as $index => $reaction) {
            if ($reaction['author'] === $author) {
                $userReactionIndex = $index;
                break;
            }
        }

        if ($userReactionIndex !== null) {
            // If same type, remove it (toggle); otherwise change type
            if ($reactions[$userReactionIndex]['type'] === $type) {
                unset($reactions[$userReactionIndex]);
                $reactions = array_values($reactions); // Re-index array
            } else {
                $reactions[$userReactionIndex]['type'] = $type;
            }
        } else {
            // Create new reaction
            $reactions[] = [
                'type' => $type,
                'author' => $author,
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ];
        }

        $commentaire->setReactions($reactions);
        $entityManager->flush();

        // Return updated reaction counts
        $counts = $this->getReactionCounts($reactions);
        $total = count($reactions);

        return $this->json([
            'success' => true,
            'counts' => $counts,
            'total' => $total,
        ]);
    }

    #[Route('/comment/{id}', name: 'forum_add_comment', methods: ['POST'])]
    public function addComment(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        
        if (!$post) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $content = $request->request->get('content');
        $author = $request->request->get('author');

        if (!$content || !$author) {
            return $this->json(['error' => 'Missing fields'], Response::HTTP_BAD_REQUEST);
        }

        $commentaire = new Commentaire();
        $commentaire->setContent($content);
        $commentaire->setAuthor($author);
        $commentaire->setPost($post);
        $commentaire->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($commentaire);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $commentaire->getId(),
                'content' => $commentaire->getContent(),
                'author' => $commentaire->getAuthor(),
                'createdAt' => $commentaire->getCreatedAt()->format('d/m/Y H:i'),
                'reactions' => []
            ]
        ]);
    }

    #[Route('/comment-delete/{id}', name: 'forum_delete_comment', methods: ['DELETE', 'POST'])]
    public function deleteComment(int $id, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);
        
        if (!$commentaire) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($commentaire);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/comment-edit/{id}', name: 'forum_edit_comment', methods: ['POST'])]
    public function editComment(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);
        
        if (!$commentaire) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $content = $request->request->get('content');

        if (!$content) {
            return $this->json(['error' => 'Content is required'], Response::HTTP_BAD_REQUEST);
        }

        $commentaire->setContent($content);
        $commentaire->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $commentaire->getId(),
                'content' => $commentaire->getContent(),
                'author' => $commentaire->getAuthor(),
                'createdAt' => $commentaire->getCreatedAt()->format('d/m/Y H:i'),
                'updatedAt' => $commentaire->getUpdatedAt() ? $commentaire->getUpdatedAt()->format('d/m/Y H:i') : null,
                'reactions' => $commentaire->getReactions() ?? []
            ]
        ]);
    }

    /**
     * Get reaction counts from reactions array
     */
    private function getReactionCounts(array $reactions): array
    {
        $counts = [
            'like' => 0,
            'love' => 0,
            'haha' => 0,
            'wow' => 0,
            'sad' => 0,
            'angry' => 0,
        ];

        foreach ($reactions as $reaction) {
            if (isset($counts[$reaction['type']])) {
                $counts[$reaction['type']]++;
            }
        }

        return $counts;
    }
}
