<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/base_admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/admin/add_user', name: 'app_admin_add_user')]
public function addUser(): Response {
    return $this->render('admin/add_user.html.twig');
}
#[Route('/admin/buttons', name: 'app_buttons')]
 public function buttons(): Response { 
    return $this->render('admin/components/buttons.html.twig');
     }
#[Route('/admin/cards', name: 'app_cards')]
 public function cards(): Response {
     return $this->render('admin/components/cards.html.twig');     
}
#[Route('/admin/utilities/color', name: 'app_utilities_color')]
public function utilitiesColor(): Response {
    return $this->render('admin/utilities/color.html.twig');
}

#[Route('/admin/utilities/border', name: 'app_utilities_border')]
public function utilitiesBorder(): Response {
    return $this->render('admin/utilities/border.html.twig');
}

#[Route('/admin/utilities/animation', name: 'app_utilities_animation')]
public function utilitiesAnimation(): Response {
    return $this->render('admin/utilities/animation.html.twig');
}

#[Route('/admin/utilities/other', name: 'app_utilities_other')]
public function utilitiesOther(): Response {
    return $this->render('admin/utilities/other.html.twig');
}
#[Route('/admin/tables', name: 'app_admin_tables')]
public function tables(): Response {
    return $this->render('admin/tables.html.twig');
}
#[Route('/admin/login', name: 'app_login')]
public function login(): Response {
    return $this->render('admin/pages/login.html.twig');
}

#[Route('/admin/register', name: 'app_register')]
public function register(): Response {
    return $this->render('admin/pages/register.html.twig');
}

#[Route('/admin/forgot-password', name: 'app_forgot_password')]
public function forgotPassword(): Response {
    return $this->render('admin/pages/forgot_password.html.twig');
}

#[Route('/admin/404', name: 'app_404')]
public function page404(): Response {
    return $this->render('admin/pages/404.html.twig');
}

#[Route('/admin/blank', name: 'app_blank')]
public function blank(): Response {
    return $this->render('admin/pages/blank.html.twig');
}
#[Route('/admin/charts', name: 'app_admin_charts')]
public function charts(): Response {
    return $this->render('admin/charts.html.twig');
}



}