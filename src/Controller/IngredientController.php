<?php

namespace App\Controller;


use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IngredientController extends AbstractController
{

    /**
     * This function display all ingredient(s)
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }


    /**
     * This function create an ingredient
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager ) : Response 
    {
       
       $ingredient = new Ingredient();
       $form = $this->createForm(IngredientType::class, $ingredient);
       
       $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
           // $form->getData() holds the submitted values
           // but, the original `$task` variable has also been updated
        $ingredient = $form->getData();
        $ingredient->setUser($this->getUser());
        
        $manager->persist($ingredient);
        $manager->flush();

        $this->addFlash(
           'success',
           'c bi1 ajoute'
        );
        return $this->redirectToRoute('ingredient.index');
       }

       return $this->render('pages/ingredient/new.html.twig',[
        'form' => $form->createView()
       ]);
    }

   
    /**
     * This Controller update an ingredient
     *
     * @param Ingredient $ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */   
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") and user === subject.getUser()'),
        subject: 'ingredient',
    )]
    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request,
    EntityManagerInterface $manager) : Response 
    {

        // $ingredient = $repository->findOneBy(['id' => $id]);;
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
         $ingredient = $form->getData();
         
         $manager->persist($ingredient);
         $manager->flush();
 
         $this->addFlash(
            'success',
            'c bi1 modif'
         );
        return $this->redirectToRoute('ingredient.index');

        }
        return $this->render('pages/ingredient/edit.html.twig',[
            'form' => $form->createView()
        ]);
    }

   
    /**
     * This Controller delete an ingredient
     *
     * @param Ingredient $ingredient
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/suprression/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(Ingredient $ingredient, EntityManagerInterface $manager) : Response {
        
        if (!$ingredient) {
            $this->addFlash(
                'danger',
                'pas trouvé'
            );
        }
        
        
        $manager->remove($ingredient);
        $manager->flush();
 
        $this->addFlash(
            'danger',
            'c bi1 delete'
        );
        return $this->redirectToRoute('ingredient.index');

    }

}
