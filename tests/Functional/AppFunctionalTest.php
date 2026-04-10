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
        $reservation->setStatut($statutAttendue);

        // Vérifie que le statut retourné correspond à celui défini
        $this->assertSame($statutAttendue, $reservation->getStatut(),
            "La valeur 'statut' doit être 'EN COURS'."
        );
    }

    
    public function testHomePageSuccess(): void
    {
        $client = static::createClient();
        // suivre les redirections pour atteindre la page finale
        $client->followRedirects(true);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful(
            "La requête GET sur la page d'accueil (/) devrait retourner un statut HTTP 200."
        );
        
    }
}
