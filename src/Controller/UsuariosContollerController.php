<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UsuariosContollerController extends AbstractController
{
    #[Route('/usuarios/contoller', name: 'app_usuarios_contoller')]
    public function index(): Response
    {
        return $this->render('usuarios_contoller/index.html.twig', [
            'controller_name' => 'UsuariosContollerController',
        ]);
    }
}
