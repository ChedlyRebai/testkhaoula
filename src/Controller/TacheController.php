<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Entity\Projet;
use App\Repository\TacheRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/taches')]
class TacheController extends AbstractController
{
    /**
     * READ - Get all Taches (list) - optionally filtered by projet
     */
    #[Route('', name: 'tache_list', methods: ['GET'])]
    public function list(TacheRepository $repository, ProjetRepository $projetRepo, Request $request): Response
    {
        $projetId = $request->query->get('projet');
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut');
        $priorite = $request->query->get('priorite');
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        $projet = null;
        if ($projetId) {
            $projet = $projetRepo->find($projetId);
            if (!$projet) {
                throw $this->createNotFoundException('Projet non trouvé');
            }
        }

        $taches = $repository->search(
            $search,
            $projetId ? (int)$projetId : null,
            $statut,
            $priorite ? (int)$priorite : null,
            $sort,
            $order
        );

        $projets = $projetRepo->findAll();

        return $this->render('front/taches/list_edubin.html.twig', [
            'taches' => $taches,
            'projet' => $projet,
            'projets' => $projets,
            'search' => $search,
            'statut' => $statut,
            'priorite' => $priorite,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    /**
     * AJAX - Get taches with search/sort/filter as JSON
     */
    #[Route('/api/search', name: 'tache_api_search', methods: ['GET'])]
    public function apiSearch(TacheRepository $repository, Request $request): JsonResponse
    {
        try {
            $projetId = $request->query->get('projet');
            $search = $request->query->get('search', '');
            $statut = $request->query->get('statut');
            $priorite = $request->query->get('priorite');
            $sort = $request->query->get('sort', 'date');
            $order = strtoupper($request->query->get('order', 'DESC'));
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = 50; // 50 results per page

            // Validate order
            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'DESC';
            }

            // Validate and convert projetId
            $projetId = $projetId ? (int)$projetId : null;

            // Validate and convert priorite
            $priorite = $priorite ? (int)$priorite : null;
            if ($priorite && !in_array($priorite, [1, 2, 3])) {
                $priorite = null;
            }

            // Validate statut (empty string is allowed)
            $validStatuts = ['À faire', 'En cours', 'Terminée'];
            if ($statut && !in_array($statut, $validStatuts)) {
                $statut = null;
            }

            // Calculate offset
            $offset = ($page - 1) * $limit;

            $taches = $repository->search(
                $search,
                $projetId,
                $statut,
                $priorite,
                $sort,
                $order,
                true,
                $limit,
                $offset
            );

            $total = $repository->countBySearch(
                $search,
                $projetId,
                $statut,
                $priorite
            );

            $data = [];
            foreach ($taches as $tache) {
                $data[] = [
                    'id' => $tache->getId(),
                    'titre' => $tache->getTitre(),
                    'description' => substr($tache->getDescription(), 0, 60) . (strlen($tache->getDescription()) > 60 ? '...' : ''),
                    'statut' => $tache->getStatut(),
                    'priorite' => $tache->getPriorite(),
                    'priorite_label' => $this->getPrioriteLabel($tache->getPriorite()),
                    'projet' => $tache->getProjet() ? ['id' => $tache->getProjet()->getId(), 'nom' => $tache->getProjet()->getNom()] : null,
                    'enabled' => $tache->isEnabled(),
                ];
            }

            return $this->json([
                'success' => true,
                'total' => $total,
                'count' => count($taches),
                'page' => $page,
                'limit' => $limit,
                'taches' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Helper to get priorite label
     */
    private function getPrioriteLabel(int $priorite): string
    {
        return match ($priorite) {
            1 => 'Basse',
            2 => 'Normale',
            3 => 'Haute',
            default => 'Inconnue',
        };
    }

    /**
     * READ - Get a single Tache by ID
     */
    #[Route('/{id}', name: 'tache_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, TacheRepository $repository): Response
    {
        $tache = $repository->find($id);
        if (!$tache) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }
        return $this->render('front/taches/show_edubin.html.twig', [
            'tache' => $tache,
        ]);
    }

    /**
     * CREATE - Display form to create a new Tache
     */
    #[Route('/new', name: 'tache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ProjetRepository $projetRepo): Response
    {
        $tache = new Tache();
        $projetId = $request->query->get('projet');

        if ($projetId) {
            $projet = $projetRepo->find($projetId);
            if ($projet) {
                $tache->setProjet($projet);
            }
        }

        $form = $this->createForm(TacheType::class, $tache, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$tache->getProjet()) {
                $this->addFlash('error', 'Veuillez sélectionner un projet');
            } else {
                $em->persist($tache);
                $em->flush();

                $this->addFlash('success', 'Tâche créée avec succès!');
                return $this->redirectToRoute('tache_list', ['projet' => $tache->getProjet()->getId()]);
            }
        }

        return $this->render('front/taches/form_edubin.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
            'tache' => $tache,
        ]);
    }

    /**
     * UPDATE - Display form to edit a Tache
     */
    #[Route('/{id}/edit', name: 'tache_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, TacheRepository $repository, ProjetRepository $projetRepo): Response
    {
        $tache = $repository->find($id);
        if (!$tache) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        $form = $this->createForm(TacheType::class, $tache, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Tâche mise à jour avec succès!');
            return $this->redirectToRoute('tache_show', ['id' => $tache->getId()]);
        }

        return $this->render('front/taches/form_edubin.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
            'tache' => $tache,
            'edit' => true,
        ]);
    }

    /**
     * DELETE - Remove a Tache
     */
    #[Route('/{id}/delete', name: 'tache_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, TacheRepository $repository): Response
    {
        $tache = $repository->find($id);
        if (!$tache) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;

        if ($this->isCsrfTokenValid('delete' . $tache->getId(), $request->request->get('_token'))) {
            $em->remove($tache);
            $em->flush();
            $this->addFlash('success', 'Tâche supprimée avec succès!');
        }

        if ($projetId) {
            return $this->redirectToRoute('tache_list', ['projet' => $projetId]);
        }
        return $this->redirectToRoute('tache_list');
    }
}
