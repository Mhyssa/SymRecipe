<?php

namespace App\DataFixtures;
use Faker\Factory;
use Faker\Generator;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;


class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= 50; $i++){
        
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word());
            $ingredient->setPrice(mt_rand(0,200));
            
            $manager->persist($ingredient);
        }
        
        $manager->flush();

        for ($i=1; $i <= 50; $i++){
        
            $recipe = new Recipe();
            $recipe->setName($this->faker->word());
            $recipe->setPrice(mt_rand(0,1000));
            $recipe->setDescription($this->faker->word());

            
            $manager->persist($recipe);
        }
        


        $manager->flush();
    }
}
