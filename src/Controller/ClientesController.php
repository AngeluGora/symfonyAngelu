<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Form\ClienteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ClientesController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[IsGranted('ROLE_USER', statusCode: 403, exceptionCode: 10010)]
    #[Route('/clientes', name: 'app_clientes')]
    public function index(): Response
    {
        $clientes = $this->entityManager->getRepository(Cliente::class)->findAll();

        return $this->render('clientes/index.html.twig', [
            'clientes' => $clientes,
        ]);
    }
    #[IsGranted('ROLE_USER', statusCode: 403, exceptionCode: 10010)]
    #[Route('/cliente/new', name: 'app_cliente_new')]
    public function new(Request $request): Response
    {
        $cliente = new Cliente();
        $form = $this->createForm(ClienteType::class, $cliente);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Guardar el cliente en la base de datos
            $this->entityManager->persist($cliente);
            $this->entityManager->flush();

            // Redirigir a la página de detalles del cliente recién creado
            return $this->redirectToRoute('app_clientes');
        }

        return $this->render('clientes/form.html.twig', [
            'formularioCliente' => $form->createView(),
        ]);
    }
    #[IsGranted('ROLE_USER', statusCode: 403, exceptionCode: 10010)]
    #[Route('/clientes/{id}/edit', name: 'app_cliente_edit')]
    public function edit(Request $request, Cliente $cliente): Response
    {
        $form = $this->createForm(ClienteType::class, $cliente);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Guardar los cambios del cliente en la base de datos
            $this->entityManager->flush();

            // Redirigir a la página de detalles del cliente
            return $this->redirectToRoute('app_clientes');
        }

        return $this->render('clientes/form.html.twig', [
            'formularioCliente' => $form->createView(),
        ]);
    }
    #[IsGranted('ROLE_USER', statusCode: 403, exceptionCode: 10010)]
    #[Route('/clientes/{id}', name: 'app_cliente_show')]
    public function show(int $id): Response
    {
        // Obtener el cliente por su ID
        $cliente = $this->entityManager->getRepository(Cliente::class)->find($id);
    
        // Verificar si el cliente existe
        if (!$cliente) {
            throw $this->createNotFoundException('No se encontró el cliente con el ID proporcionado.');
        }
    
        // Renderizar la plantilla con el cliente
        return $this->render('clientes/show.html.twig', [
            'cliente' => $cliente, // Pasar el cliente a la vista
        ]);
    }
    #[IsGranted('ROLE_USER', statusCode: 403, exceptionCode: 10010)]
    #[Route('/clientes/{id}/delete', name: 'app_cliente_delete')]
    public function delete(Request $request, Cliente $cliente): Response
    {
        // Eliminar el cliente de la base de datos
        $this->entityManager->remove($cliente);
        $this->entityManager->flush();

        // Redirigir a la página de listado de clientes
        return $this->redirectToRoute('app_clientes');
    }
}
