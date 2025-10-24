<?php

namespace App\Tests\Functional;

use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AppFunctionalTest extends WebTestCase
{
    
    public function testSetGetStatutFailureDemo(): void
    {
        $reservation = new Reservation();
        $statutAttendue = 'EN COURS';
        $statutIncorrecte = 'TERMINE'; 
        $reservation->setStatut($statutAttendue);

        
        $this->assertSame($statutIncorrecte, $reservation->getStatut(),
            "*** CE TEST DOIT ÉCHOUER POUR LA DÉMO ! *** La valeur 'statut' devrait être 'TERMINE' mais est 'EN COURS'."
        );
    }

    
    public function testHomePageSuccess(): void
    {
        
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful(
            "La requête GET sur la page d'accueil (/) devrait retourner un statut HTTP 200."
        );
        
    }
}
