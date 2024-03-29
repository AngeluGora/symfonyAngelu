<?php

namespace App\Controller;

use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UsuarioType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UsuariosController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/usuarios', name: 'app_usuarios')]
    public function index(): Response
    {
        $usuarios = $this->entityManager->getRepository(Usuario::class)->findAll();

        return $this->render('usuarios/index.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/usuarios/new', name: 'app_usuario_new')]
    public function new(Request $request): Response
    {
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Guardar el usuario en la base de datos
            $this->entityManager->persist($usuario);
            $this->entityManager->flush();

            // Redirigir a la página de detalles del usuario recién creado
            return $this->redirectToRoute('app_usuarios');
        }

        return $this->render('usuarios/form.html.twig', [
            'formularioUsuario' => $form->createView(),
        ]);
    }

    
    #[IsGranted('ROLE_USER')]
    #[Route('/usuarios/{id}/edit', name: 'app_usuario_edit')]
    public function edit(Request $request, Usuario $usuario, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UsuarioType::class, $usuario);
    
        // Guardar la contraseña actual del usuario antes de modificarla
        $contraseñaActual = $usuario->getPassword();
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $nuevaContraseña = $usuario->getPassword();
            if ($nuevaContraseña !== $contraseñaActual) {
                // Codificar la nueva contraseña antes de guardarla
                $usuario->setPassword(
                    $passwordHasher->hashPassword(
                        $usuario,
                        $nuevaContraseña
                    )
                );
            } else {
                // Mantener la contraseña actual si no se ha modificado
                $usuario->setPassword($contraseñaActual);
            }
    
            // Manejar la foto si se ha cargado un nuevo archivo
            $fotoFile = $form['foto']->getData();
            if ($fotoFile) {
                // Generar un nombre único para el archivo
                $nombreArchivo = md5(uniqid()) . '.' . $fotoFile->guessExtension();

                // Mover el archivo a un directorio donde se almacenarán las fotos
                $fotoFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/images',
                    $nombreArchivo
                );

                // Guardar el nombre del archivo en la entidad Usuario
                $usuario->setFoto($nombreArchivo);
            }
    
            // Guardar los cambios del usuario en la base de datos
            $this->entityManager->flush();
    
            // Redirigir a la página de detalles del usuario
            return $this->redirectToRoute('app_usuarios');
        }
    
        return $this->render('usuarios/form.html.twig', [
            'formularioUsuario' => $form->createView(),
        ]);
    }
    

    


    
    #[IsGranted('ROLE_USER')]
    #[Route('/usuarios/{id}', name: 'app_usuario_show')]
    public function show(int $id): Response
    {
        // Obtener el usuario por su ID
        $usuario = $this->entityManager->getRepository(Usuario::class)->find($id);
    
        // Verificar si el usuario existe
        if (!$usuario) {
            throw $this->createNotFoundException('No se encontró el usuario con el ID proporcionado.');
        }
    
        // Renderizar la plantilla con el usuario
        return $this->render('usuarios/show.html.twig', [
            'usuario' => $usuario, // Pasar el usuario a la vista
        ]);
    }
    #[IsGranted('ROLE_USER', statusCode: 403, exceptionCode: 10010)]
    #[Route('/usuarios/{id}/delete', name: 'app_usuario_delete')]
    public function delete(Request $request, Usuario $usuario): Response
    {
        // Eliminar el usuario de la base de datos
        $this->entityManager->remove($usuario);
        $this->entityManager->flush();

        // Redirigir a la página de listado de usuarios
        return $this->redirectToRoute('app_usuarios');
    }

}
