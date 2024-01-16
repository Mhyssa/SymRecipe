<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * This controller allow us to edit user's profile
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if(!$this->getUser() || $this->getUser() !== $user){
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $user = $form->getData();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre profil a bien été modifié'
            );

            return $this->redirectToRoute('recipe.index');
        }
        
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/utilisateur', name: 'user.index', methods:['GET'])]
    public function index(): Response
    {       
        return $this->render('pages/user/index.html.twig');
    }
}
