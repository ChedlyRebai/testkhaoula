<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projets')]
class ProjetController extends AbstractController
{
    /**
     * READ - Get all Projets (list) with search, sort, filter
     */
    #[Route('', name: 'projet_list', methods: ['GET'])]
    public function list(ProjetRepository $repository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        $projets = $repository->search($search, $sort, $order);

        return $this->render('front/projets/list_edubin.html.twig', [
            'projets' => $projets,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    /**
     * AJAX - Get projets with search/sort/filter as JSON
     */
    #[Route('/api/search', name: 'projet_api_search', methods: ['GET'])]
    public function apiSearch(ProjetRepository $repository, Request $request): JsonResponse
    {
        try {
            $search = $request->query->get('search', '');
            $sort = $request->query->get('sort', 'date');
            $order = $request->query->get('order', 'DESC'); // Now accepts order from frontend
            $dateFrom = $request->query->get('dateFrom', '');
            $dateTo = $request->query->get('dateTo', '');
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = 50; // 50 results per page

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Pass dates to repository search method
            $projets = $repository->search($search, $sort, $order, true, $limit, $offset, $dateFrom ?: null, $dateTo ?: null);
            $total = $repository->countBySearch($search);

            $data = [];
            foreach ($projets as $projet) {
                $taches = [];
                foreach ($projet->getTaches() as $tache) {
                    $taches[] = [
                        'id' => $tache->getId(),
                        'titre' => $tache->getTitre(),
                        'statut' => $tache->getStatut(),
                        'priorite' => $tache->getPriorite(),
                    ];
                }
                $data[] = [
                    'id' => $projet->getId(),
                    'nom' => $projet->getNom(),
                    'description' => substr($projet->getDescription(), 0, 60) . (strlen($projet->getDescription()) > 60 ? '...' : ''),
                    'dateCreation' => $projet->getDateCreation()->format('d/m/Y'),
                    'enabled' => $projet->isEnabled(),
                    'taches' => $taches,
                ];
            }

            return $this->json([
                'success' => true,
                'total' => $total,
                'count' => count($projets),
                'page' => $page,
                'limit' => $limit,
                'projets' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * CREATE - Display form to create a new Projet
     */
    #[Route('/new', name: 'projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès!');
            return $this->redirectToRoute('projet_list');
        }

        return $this->render('front/projets/form_edubin.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
            'projet' => $projet,
        ]);
    }

    /**
     * UPDATE - Display form to edit a Projet
     */
    #[Route('/{id}/edit', name: 'projet_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, ProjetRepository $repository, EntityManagerInterface $em): Response
    {
        $projet = $repository->find($id);
        if (!$projet) {
            throw $this->createNotFoundException('Projet non trouvé');
        }

        $form = $this->createForm(ProjetType::class, $projet, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Projet mis à jour avec succès!');
            return $this->redirectToRoute('projet_show', ['id' => $projet->getId()]);
        }

        return $this->render('front/projets/form_edubin.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
            'projet' => $projet,
            'edit' => true,
        ]);
    }

    /**
     * DELETE - Remove a Projet
     */
    #[Route('/{id}/delete', name: 'projet_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, ProjetRepository $repository, EntityManagerInterface $em): Response
    {
        $projet = $repository->find($id);
        if (!$projet) {
            throw $this->createNotFoundException('Projet non trouvé');
        }

        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->request->get('_token'))) {
            $em->remove($projet);
            $em->flush();
            $this->addFlash('success', 'Projet supprimé avec succès!');
        }

        return $this->redirectToRoute('projet_list');
    }

    /**
     * READ - Get a single Projet by ID
     */
    #[Route('/{id}', name: 'projet_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, ProjetRepository $repository): Response
    {
        $projet = $repository->find($id);
        if (!$projet) {
            throw $this->createNotFoundException('Projet non trouvé');
        }

        return $this->render('front/projets/show_edubin.html.twig', [
            'projet' => $projet,
        ]);
    }
}

