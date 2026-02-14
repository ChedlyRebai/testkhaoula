<?php

namespace App\Controller\admin;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/admin/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'admin_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        return $this->render('admin/quiz/index.html.twig', [
            'quizzes' => $quizRepository->findAll(),
        ]);
    }


#[Route('/new', name: 'admin_quiz_new')]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $quiz = new Quiz();
    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $entityManager->persist($quiz);
    $entityManager->flush();

    return $this->redirectToRoute('admin_quiz_index');
}



    return $this->render('admin/quiz/new.html.twig', [
        'form' => $form,
    ]);
}


    #[Route('/{id_quiz}', name: 'admin_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('admin/quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id_quiz}/edit', name: 'admin_quiz_edit')]
public function edit(
    Request $request,
    #[MapEntity(mapping: ['id_quiz' => 'id_quiz'])] Quiz $quiz,
    EntityManagerInterface $entityManager
): Response {
    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $entityManager->persist($quiz);
    $entityManager->flush();

    return $this->redirectToRoute('admin_quiz_index');
}



    return $this->render('admin/quiz/modifier.html.twig', [
        'form' => $form,
        'quiz' => $quiz,
    ]);
}

    #[Route('/{id_quiz}/delete', name: 'admin_quiz_delete', methods: ['POST'])]
public function delete(
    #[MapEntity(mapping: ['id_quiz' => 'id_quiz'])] Quiz $quiz,
    EntityManagerInterface $entityManager
): Response {
    $entityManager->remove($quiz);
    $entityManager->flush();

    return $this->redirectToRoute('admin_quiz_index');
}
}
