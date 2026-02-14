<?php

namespace App\Controller\admin;

use App\Entity\Cours;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/admin/cours')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'admin_cours_index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository): Response
    {
        return $this->render('admin/cours/index.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_cours_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $cours = new Cours();
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cours->setDateCreation(new \DateTime());
            $em->persist($cours);
            $em->flush();

            return $this->redirectToRoute('admin_cours_index');
        }

        return $this->render('admin/cours/new.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/{id_cours}/edit', name: 'admin_cours_edit')]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id_cours' => 'id_cours'])] Cours $cours,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_cours_index');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', '❌ Veuillez vérifier les champs du formulaire');
        }

        return $this->render('admin/cours/modifier.html.twig', [
            'form' => $form,
            'cours' => $cours,
        ]);
    }


    #[Route('/{id_cours}/delete', name: 'admin_cours_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity(mapping: ['id_cours' => 'id_cours'])] Cours $cours,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$cours->getIdCours(), $request->request->get('_token'))) {
            $em->remove($cours);
            $em->flush();
        }

        return $this->redirectToRoute('admin_cours_index');
    }
}

