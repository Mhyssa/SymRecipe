<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailService;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.index')]
    public function index(Request $request, EntityManagerInterface $manager, MailService $mailService): Response
    {
        
        $contact = new Contact();
        
        if ($user = $this->getUser()) {

        /**
        * @var UserEntity
        */
        $user = $this->getUser();
        
            $contact->setFullName($user->getFullName())
                ->setEmail($user->getEmail());
        }

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $contact = $form->getData();

            $manager->persist($contact);
            $manager->flush();

            //Email
            $mailService->sendEmail(
                $contact->getEmail(),
                $contact->getSubject(),
                'emails/contact.html.twig',
                ['contact' => $contact]
            );


            $this->addFlash(
                'success',
                'Votre demande à bien été prise en compte');

            return $this->redirectToRoute('contact.index');
        }

        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
