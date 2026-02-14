<?php

namespace App\Controller\Admin;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Form\ProjetType;
use App\Form\TacheType;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    // ==================== PROJETS ====================

    /**
     * Admin - List all Projets
     */
    #[Route('/projets', name: 'admin_projets_list', methods: ['GET'])]
    public function projetsList(ProjetRepository $repository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        $projets = $repository->search($search, $sort, $order);

        return $this->render('admin/projets/list.html.twig', [
            'projets' => $projets,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    /**
     * Admin - Show a single Projet
     */
    #[Route('/projets/{id}', name: 'admin_projet_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function projetShow(int $id, ProjetRepository $repository): Response
    {
        $projet = $repository->find($id);
        if (!$projet) {
            throw $this->createNotFoundException('Projet non trouvé');
        }

        return $this->render('admin/projets/show.html.twig', [
            'projet' => $projet,
        ]);
    }

    /**
     * Admin - Create a new Projet
     */
    #[Route('/projets/new', name: 'admin_projet_new', methods: ['GET', 'POST'])]
    public function projetNew(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès!');
            return $this->redirectToRoute('admin_projets_list');
        }

        return $this->render('admin/projets/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
            'projet' => $projet,
        ]);
    }

    /**
     * Admin - Edit a Projet
     */
    #[Route('/projets/{id}/edit', name: 'admin_projet_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function projetEdit(int $id, Request $request, ProjetRepository $repository, EntityManagerInterface $em): Response
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
            return $this->redirectToRoute('admin_projet_show', ['id' => $projet->getId()]);
        }

        return $this->render('admin/projets/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
            'projet' => $projet,
        ]);
    }

    /**
     * Admin - Delete a Projet
     */
    #[Route('/projets/{id}/delete', name: 'admin_projet_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function projetDelete(int $id, Request $request, ProjetRepository $repository, EntityManagerInterface $em): Response
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

        return $this->redirectToRoute('admin_projets_list');
    }

    // ==================== TACHES ====================

    /**
     * Admin - List all Taches
     */
    #[Route('/taches', name: 'admin_taches_list', methods: ['GET'])]
    public function tachesList(TacheRepository $repository, ProjetRepository $projetRepo, Request $request): Response
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

        return $this->render('admin/taches/list.html.twig', [
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
     * Admin - Show a single Tache
     */
    #[Route('/taches/{id}', name: 'admin_tache_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function tacheShow(int $id, TacheRepository $repository): Response
    {
        $tache = $repository->find($id);
        if (!$tache) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        return $this->render('admin/taches/show.html.twig', [
            'tache' => $tache,
        ]);
    }

    /**
     * Admin - Create a new Tache
     */
    #[Route('/taches/new', name: 'admin_tache_new', methods: ['GET', 'POST'])]
    public function tacheNew(Request $request, EntityManagerInterface $em, ProjetRepository $projetRepo): Response
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
                return $this->redirectToRoute('admin_tache_show', ['id' => $tache->getId()]);
            }
        }

        return $this->render('admin/taches/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
            'tache' => $tache,
        ]);
    }

    /**
     * Admin - Edit a Tache
     */
    #[Route('/taches/{id}/edit', name: 'admin_tache_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function tacheEdit(int $id, Request $request, EntityManagerInterface $em, TacheRepository $repository, ProjetRepository $projetRepo): Response
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
            return $this->redirectToRoute('admin_tache_show', ['id' => $tache->getId()]);
        }

        return $this->render('admin/taches/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
            'tache' => $tache,
        ]);
    }

    /**
     * Admin - Delete a Tache
     */
    #[Route('/taches/{id}/delete', name: 'admin_tache_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function tacheDelete(int $id, Request $request, EntityManagerInterface $em, TacheRepository $repository): Response
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

        return $this->redirectToRoute('admin_taches_list', $projetId ? ['projet' => $projetId] : []);
    }

    // ==================== DASHBOARD ====================

    /**
     * Admin - Dashboard
     */
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(ProjetRepository $projetRepo, TacheRepository $tacheRepo): Response
    {
        $totalProjets = count($projetRepo->findAll());
        $totalTaches = count($tacheRepo->findAll());
        $projetsEnabled = count($projetRepo->findBy(['enabled' => true]));
        $tachesEnabled = count($tacheRepo->findBy(['enabled' => true]));

        return $this->render('admin/dashboard.html.twig', [
            'totalProjets' => $totalProjets,
            'totalTaches' => $totalTaches,
            'projetsEnabled' => $projetsEnabled,
            'tachesEnabled' => $tachesEnabled,
        ]);
    }

    // ==================== API AJAX ====================

    /**
     * API - Search/Filter/Sort Projets (AJAX)
     */
    #[Route('/api/projets/search', name: 'api_projets_search', methods: ['GET'])]
    public function apiProjetsSearch(ProjetRepository $repository, Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        $projets = $repository->search($search, $sort, $order);

        $data = array_map(function ($projet) {
            return [
                'id' => $projet->getId(),
                'nom' => $projet->getNom(),
                'description' => $projet->getDescription(),
                'dateCreation' => $projet->getDateCreation()->format('d/m/Y'),
                'tachesCount' => count($projet->getTaches()),
                'enabled' => $projet->isEnabled(),
            ];
        }, $projets);

        return $this->json([
            'success' => true,
            'count' => count($projets),
            'projets' => $data,
        ]);
    }

    /**
     * API - Search/Filter/Sort Taches (AJAX)
     */
    #[Route('/api/taches/search', name: 'api_taches_search', methods: ['GET'])]
    public function apiTachesSearch(TacheRepository $repository, Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $projetId = $request->query->get('projet');
        $statut = $request->query->get('statut');
        $priorite = $request->query->get('priorite');
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        $taches = $repository->search(
            $search,
            $projetId ? (int)$projetId : null,
            $statut,
            $priorite ? (int)$priorite : null,
            $sort,
            $order
        );

        $data = array_map(function ($tache) {
            $prioriteLabels = [1 => 'Basse', 2 => 'Normale', 3 => 'Haute'];
            return [
                'id' => $tache->getId(),
                'titre' => $tache->getTitre(),
                'projet' => $tache->getProjet() ? [
                    'id' => $tache->getProjet()->getId(),
                    'nom' => $tache->getProjet()->getNom(),
                ] : null,
                'statut' => $tache->getStatut(),
                'priorite' => $tache->getPriorite(),
                'priorite_label' => $prioriteLabels[$tache->getPriorite()] ?? 'Inconnue',
                'dateCreation' => $tache->getDateCreation()->format('d/m/Y'),
            ];
        }, $taches);

        return $this->json([
            'success' => true,
            'count' => count($taches),
            'taches' => $data,
        ]);
    }
}
