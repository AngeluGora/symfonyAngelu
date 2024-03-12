<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Incidencia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\IncidenciaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class IncidenciasController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/incidencias', name: 'app_incidencias')]
    public function index(): Response
    {
        $incidencias = $this->entityManager->getRepository(Incidencia::class)->findAll();

        return $this->render('incidencias/index.html.twig', [
            'incidencias' => $incidencias,
        ]);
    }
   
    #[IsGranted('ROLE_USER')]
    #[Route('/incidencias/{id}', name: 'app_incidencia_show')]
    public function show(int $id): Response
    {
        // Obtener la incidencia por su ID
        $incidencia = $this->entityManager->getRepository(Incidencia::class)->find($id);

        // Verificar si la incidencia existe
        if (!$incidencia) {
            throw $this->createNotFoundException('No se encontró la incidencia con el ID proporcionado.');
        }

        // Renderizar la plantilla con la incidencia
        return $this->render('incidencias/show.html.twig', [
            'incidencia' => $incidencia, // Pasar la incidencia a la vista
        ]);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/incidencia/new', name: 'app_incidencia_new')]
    public function new(Request $request): Response
    {
        $incidencia = new Incidencia();
        $form = $this->createForm(IncidenciaType::class, $incidencia);

        // Manejar el envío del formulario y procesar los datos
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Añadir la fecha actual a la incidencia
            $incidencia->setFechaCreacion(new \DateTime());

            // Guardar la incidencia en la base de datos u otras operaciones necesarias
            $this->entityManager->persist($incidencia);
            $this->entityManager->flush();

            // Redirigir a la página de inicio u otra página deseada después de guardar la incidencia
            return $this->redirectToRoute('app_incidencias'); // Cambiar 'app_home' por la ruta deseada
        }

        return $this->render('incidencias/addIncidencia.html.twig', [
            'formularioIncidencia' => $form->createView(),
        ]);
    }

    


    
    #[IsGranted('ROLE_USER')]
    #[Route('/incidencias/{id}/delete', name: 'app_incidencia_delete')]
    public function delete(Request $request, Incidencia $incidencia, EntityManagerInterface $entityManager): Response
    {
        // Obtiene el EntityManager

        // Elimina la incidencia
        $entityManager->remove($incidencia);
        $entityManager->flush();

        // Redirige a la página de listado de incidencias 
        return $this->redirectToRoute('app_incidencias');
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/incidencias/{id}/edit', name: 'app_incidencia_edit')]
    public function edit(Request $request, Incidencia $incidencia): Response
    {
        $form = $this->createForm(IncidenciaType::class, $incidencia);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Obtener el EntityManager
            $entityManager = $this->entityManager;

            // Guardar los cambios en la incidencia editada
            $entityManager->flush();

            // Redirigir a la página de detalles de la incidencia
            return $this->redirectToRoute('app_incidencias');
        }

        return $this->render('incidencias/addIncidencia.html.twig', [
            'formularioIncidencia' => $form->createView(),
        ]);
    }

}
