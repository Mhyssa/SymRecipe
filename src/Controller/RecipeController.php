<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;

use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe.index', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
            $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

     /**
     * This controller create an recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/creation', name: 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager ) : Response 
    {
       

    $recipe = new Recipe();
    $form = $this->createForm(RecipeType::class, $recipe);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) { 
        $recipe = $form->getData();
        $recipe->setUser($this->getUser());
       
        $manager->persist($recipe);

        $manager->flush();

        $this->addFlash(
            'success',
            'recette ajouter avec succes'
         );
       
        return $this->redirectToRoute('recipe.index');
       }


      return $this->render('pages/recipe/new.html.twig', ['form' => $form->createView()]);
    }

  

    /**
     * his controller edit an recipe
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */    
        #[Route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST'])]
        #[IsGranted(
            new Expression('is_granted("ROLE_USER") and user === subject.getUser()'),
            subject: 'recipe',
        )]
        public function edit(Recipe $recipe, Request $request,
        EntityManagerInterface $manager) : Response 
    {

   
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
         $recipe = $form->getData();
         
         $manager->persist($recipe);
         $manager->flush();
 
         $this->addFlash(
            'success',
            'votre recette à été modifié avec success'
         );
        return $this->redirectToRoute('recipe.index');

        }
        return $this->render('pages/recipe/edit.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * his controller delete an recipe
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @return Response
     */
        #[Route('/recette/suprression/{id}', name: 'recipe.delete', methods: ['GET'])]
        public function delete(Recipe $recipe, EntityManagerInterface $manager) : Response {
        
        if (!$recipe) {
            $this->addFlash(
                'danger',
                'pas trouvé'
            );
        }
        
        
        $manager->remove($recipe);
        $manager->flush();
 
        $this->addFlash(
            'danger',
            'votre recette à été supprimer avec success'
        );
        return $this->redirectToRoute('recipe.index');

    }

}
