<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ModifierUtilisateurType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{File\Exception\FileException, File\UploadedFile, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{

    #[Route('/list', name: 'list')]
    public function list(UserRepository $userRepository): Response{

        $users = $userRepository->findAll();
        dump($users);

        return $this->render('/user/list.html.twig', [
            'users' => $users
        ]);

    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException("Oops ! Utilisateur non trouvé !");
        }

        return $this->render('/user/show.html.twig', [
            'user' => $user
        ]);
   }

//    #[Route('/update', name: 'update')]
//    public function updateProfile(Request $request, UserRepository $userRepository): Response
//    {
//        $user = new User();
//
//        $userForm = $this->createForm(ModifierUtilisateurType::class, $user);
//
//        if ($request->isMethod('POST')) {
//            $userForm->handleRequest($request);
//
//            if ($userForm->isSubmitted() && $userForm->isValid()) {
//
//
//
//                $userRepository->save($user, true);
//
//                $this->addFlash('success', 'Ton profil a été mis à jour !');
//
//                return $this->redirectToRoute('sortie_list');
//            }
//        }
//
//        return $this->render('user/profil.html.twig', [
//            'userForm' => $userForm->createView(),
//            'user' => $user,
//        ]);
//
//    }


    #[Route('/update', name: 'update')]
    public function updateProfile(Request $request,
                                  UserRepository $userRepository,
                                  UserInterface $user,
                                  UserPasswordHasherInterface $userPasswordHasher,
                                  ): Response
    {

        $userForm = $this->createForm(ModifierUtilisateurType::class, $user);

        if ($request->isMethod('POST')) {
            $userForm->handleRequest($request);

            if ($userForm->isSubmitted() && $userForm->isValid()) {

                $avatarFile = $userForm->get('avatar')->getData();

                if ($avatarFile) {

                    $newFilename = uniqid().'.'.$avatarFile->guessExtension();

                    try {
                        $avatarFile->move(
                            $this->getParameter('upload_avatar'),
                            $newFilename
                        );
                    } catch (FileException $e) {

                    }

                    $user->setAvatar($newFilename);
                }

                if ($userForm -> get('plainPassword')->getData()) {
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $userForm->get('plainPassword')->getData()
                        )
                    );
                }

                $userRepository->save($user, true);

                $this->addFlash('success', 'Ton profil a été mis à jour !');

                return $this->redirectToRoute('sortie_list');
            }
        }

        return $this->render('user/profil.html.twig', [
            'userForm' => $userForm->createView(),
            'user' => $user,
        ]);

    }



}


