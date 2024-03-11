<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IncidenciasContollerController extends AbstractController
{
    #[Route('/incidencias/contoller', name: 'app_incidencias_contoller')]
    public function index(): Response
    {
        return $this->render('incidencias_contoller/index.html.twig', [
            'controller_name' => 'IncidenciasContollerController',
        ]);
    }
}
