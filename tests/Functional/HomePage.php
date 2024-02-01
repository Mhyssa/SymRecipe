<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $recipes = $crawler->filter('.card .border-info .mb-3');
        $this->assertEquals(4, count($recipes));

        $this->assertSelectorTextContains('h1', 'Bienvenue sur SymRecipe');
    }
}
