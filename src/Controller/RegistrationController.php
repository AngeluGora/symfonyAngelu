<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        
    $user = new Usuario();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // encode the plain password
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        // Manejo de la foto
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
            $user->setFoto($nombreArchivo);
        }

        // Persistir el usuario
        $entityManager->persist($user);
        $entityManager->flush();

        // Hacer cualquier otra cosa que necesites aquí, como enviar un correo electrónico

        return $this->redirectToRoute('home');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form,
    ]);
}
}
