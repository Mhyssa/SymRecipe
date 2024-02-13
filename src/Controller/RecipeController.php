<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;

use App\Repository\RecipeRepository;

use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

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

    #[Route('/recette/publique', name: 'recipe.index.public', methods:['GET'])]
    public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $cache = new FilesystemAdapter();
        $data = $cache->get('recipes', function(ItemInterface $item) use ($repository){
            $item->expiresAfter(15);
            return $repository->findPublicRecipe(null);

        });

        $recipes = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),10
        );

        return $this->render('pages/recipe/index_public.html.twig',[
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
        if (!$form->isValid()) {
            // Traitez les erreurs de validation si nécessaire
            // ...
    
            return $this->render('pages/recipe/new.html.twig', ['form' => $form->createView()]);
        }
        $manager->persist($recipe);

        $manager->flush();

        $this->addFlash('success', 'Recette ajoutée avec succès');
       
        return $this->redirectToRoute('recipe.index');
       }


      return $this->render('pages/recipe/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * This controller allow us an recipe public
     *
     * @param Recipe $recipe
     * @return Response
     */ 
    #[Route('/recette/{id}', name: 'recipe.show', methods:['GET', 'POST'])]
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") and subject.isIsPublic() === true'),
        subject: 'recipe',
    )]
    public function show(
        Recipe $recipe,
        Request $request,
        MarkRepository $markRepository,
        EntityManagerInterface $manager
    ): Response {
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existingMark) {
                $manager->persist($mark);
            } else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Votre note a bien été prise en compte.'
            );

            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
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
