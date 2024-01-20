<?php

namespace App\Controller;

use App\Entity\User;

use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * This controller allow us to edit user's profile
     *
     * @param User $chosenUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") and user === subject'),
        subject: 'chosenUser',
    )]
    public function edit(
        User $chosenUser, 
        Request $request, 
        EntityManagerInterface $manager, 
        UserPasswordHasherInterface $passwordHasher): Response
    {
        // if(!$this->getUser()){
        //     return $this->redirectToRoute('security.login');
        // }

        // if(!$this->getUser() || $this->getUser() !== $user){
        //     return $this->redirectToRoute('recipe.index');
        // }

        $form = $this->createForm(UserType::class, $chosenUser);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $submittedPassword = $form->getData()->getPlainPassword();

            if($passwordHasher->isPasswordValid($chosenUser, $submittedPassword)){

                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
                
                $this->addFlash(
                    'success',
                    'Votre profil a bien été modifié'
                );
                
                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'mot de passe incorect'
                 );
            }
        }
        
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This controller allow us to edit user's password
     *
     * @param User $choosenUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/utilisateur/edition-mot-de-passe/{id}', 'user.edit_password', methods: ['GET', 'POST'])]
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") and user === subject'),
        subject: 'chosenUser',
    )]
    public function editPassword(
        User $chosenUser,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
    ): Response {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($chosenUser, $form->getData()['plainPassword'])) {
                $chosenUser->setUpdatedAt(new \DateTimeImmutable());
                $chosenUser->setPlainPassword(
                    $form->getData()['newPassword']
                );

                $this->addFlash(
                    'success',
                    'Le mot de passe a été modifié.'
                );

                $manager->persist($chosenUser);
                $manager->flush();

                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/utilisateur', name: 'user.index', methods:['GET'])]
    public function index(): Response
    {       
        return $this->render('pages/user/index.html.twig');
    }
}

