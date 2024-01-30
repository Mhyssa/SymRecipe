<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Listes des recettes')
            ->setEntityLabelInSingular('La recette')
            ->setPageTitle("index", "Symrecipe - Administration des recettes")
            ->setPaginatorPageSize(10)

        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            IntegerField::new('time'),
            IntegerField::new('nbPeople')
                ->hideOnIndex(),
            IntegerField::new('average')
                ->hideOnIndex()
                ->hideOnForm()
                ->setFormTypeOption('disabled','disabled'),
            BooleanField::new('isPublic')
                ->renderAsSwitch(false),
            MoneyField::new('price')
                ->setCurrency('CAD'),
            TextEditorField::new('description'),
            TextareaField::new('description')
                ->hideOnForm(),
            DateTimeField::new('updatedAt')
                ->setFormTypeOption('disabled','disabled'),

        ];
    }
    
}
