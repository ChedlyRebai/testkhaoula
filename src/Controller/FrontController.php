<?php

namespace App\Controller;

use App\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'front_index')]
    public function index(): Response
    {
        return $this->render('front/edubin_index.html.twig');
    }

    #[Route('/home', name: 'front_home')]
    public function home(): Response
    {
        return $this->render('front/edubin_index.html.twig');
    }

    #[Route('/about', name: 'front_about')]
    public function about(): Response
    {
        return $this->render('about/index.html.twig');
    }

    #[Route('/courses', name: 'front_courses')]
    public function courses(): Response
    {
        return $this->render('courses/index.html.twig');
    }

    #[Route('/courses/{id}', name: 'front_courses_single')]
    public function coursesSingle(int $id): Response
    {
        return $this->render('courses/courses-singel.html.twig', [
            'courseId' => $id
        ]);
    }

    #[Route('/events', name: 'front_events')]
    public function events(): Response
    {
        return $this->render('events/index.html.twig');
    }

    #[Route('/events/{id}', name: 'front_events_single')]
    public function eventsSingle(int $id): Response
    {
        return $this->render('events/events-singel.html.twig', [
            'eventId' => $id
        ]);
    }

    #[Route('/teachers', name: 'front_teachers')]
    public function teachers(): Response
    {
        return $this->render('our_teachers/teachers.html.twig');
    }

    #[Route('/teachers/{id}', name: 'front_teachers_single')]
    public function teachersSingle(int $id): Response
    {
        return $this->render('our_teachers/teachers-singel.html.twig', [
            'teacherId' => $id
        ]);
    }

    #[Route('/blog', name: 'front_blog')]
    public function blog(): Response
    {
        return $this->render('blog/index.html.twig');
    }

    #[Route('/blog/{id}', name: 'front_blog_single')]
    public function blogSingle(int $id): Response
    {
        return $this->render('blog/blog-singel.html.twig', [
            'blogId' => $id
        ]);
    }

    #[Route('/contact', name: 'front_contact')]
    public function contact(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/shop', name: 'front_shop')]
    public function shop(): Response
    {
        return $this->render('shop/index.html.twig');
    }

    #[Route('/shop/{id}', name: 'front_shop_single')]
    public function shopSingle(int $id): Response
    {
        return $this->render('shop/shop-singel.html.twig', [
            'shopId' => $id
        ]);
    }

    #[Route('/forum', name: 'front_forum_index')]
    public function forum(ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAllOrderedByDate();
        return $this->render('forum_admin/index.html.twig', [
            'forums' => $forums,
        ]);
    }

    #[Route('/projet', name: 'front_project')]
    public function project(): Response
    {
        return $this->render('projet/index.html.twig');
    }

    #[Route('/reclamation', name: 'front_complaint')]
    public function complaint(): Response
    {
        return $this->render('reclamation/index.html.twig');
    }
}
