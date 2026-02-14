<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class CourseController extends AbstractController
{
    #[Route('/cours', name: 'course_list', methods: ['GET'])]
    public function list(CourseRepository $repository, RouterInterface $router): Response
    {
        $courses = $repository->findAllEnabled();
        $hasCourseNew = (bool) $router->getRouteCollection()->get('course_new');

        return $this->render('front/courses/list.html.twig', [
            'courses' => $courses,
            'hasCourseNew' => $hasCourseNew,
        ]);
    }
}
