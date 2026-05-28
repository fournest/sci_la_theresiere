<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppFunctionalTest extends WebTestCase
{
    /**
     * 🌐 Test 1 : Vérifie que la page d'accueil répond correctement (HTTP 200)
     */
    public function testHomePageSuccess(): void
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful(
            "La requête GET sur la page d'accueil (/) devrait retourner un statut HTTP 200."
        );
    }

    /**
     * 🔒 Test 2 : Vérifie qu'un utilisateur ANONYME est bloqué s'il tente de réserver
     */
    public function testReservationNewIsSecureForAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reservation/new');

        // Symfony doit intercepter la requête et renvoyer un code 302 (Redirection vers /login)
        $this->assertResponseStatusCodeSame(302, 
            "Un utilisateur non connecté devrait être redirigé (Code 302) en tentant d'accéder à /reservation/new."
        );
    }

    /**
     * 🔑 Test 3 : Vérifie qu'un utilisateur CONNECTÉ accède au formulaire sans plantage Twig
     */
    public function testReservationNewSuccessForAuthenticatedUser(): void
    {
        $client = static::createClient();
        
        // On récupère le conteneur de Symfony pour extraire le UserRepository
        $userRepository = static::getContainer()->get(UserRepository::class);
        
        // On cherche le premier utilisateur disponible dans ta base de données de test
        $testUser = $userRepository->findOneBy([]);

        if (!$testUser) {
            $this->markTestSkipped("Aucun utilisateur trouvé en base de données pour tester la connexion.");
        }

        // On simule la connexion de cet utilisateur sur le navigateur virtuel
        $client->loginUser($testUser);
        
        // On tente d'accéder au formulaire de création
        $client->request('GET', '/reservation/new');

        $this->assertResponseIsSuccessful(
            "Un utilisateur connecté doit pouvoir afficher le formulaire de réservation (HTTP 200) sans erreur Twig."
        );
    }
}