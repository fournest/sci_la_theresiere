<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    /**
     * Test de succès pour la référence du dossier
     */
    public function testSetGetDossierResaSuccess(): void
    {
        $reservation = new Reservation();
        $dossier = 'ARINFO-SUCCESS-001';

        $reservation->setDossierResa($dossier);

        $this->assertSame($dossier, $reservation->getDossierResa(), 
            "La valeur retournée par getDossierResa() doit correspondre à la valeur définie par setDossierResa()."
        );
    }

    /**
     * 💰 Test du nouveau champ : Prix Total de la location (Attendu en String par Doctrine)
     */
    public function testSetGetPrixTotalSuccess(): void
    {
        $reservation = new Reservation();
        $prixTest = '1500.5'; // Stocké en string pour la précision décimale

        $reservation->setPrixTotal($prixTest);

        $this->assertSame($prixTest, $reservation->getPrixTotal(), 
            "Le getter/setter du Prix Total doit manipuler une String pour préserver la précision Doctrine."
        );
    }

    /**
     * 💰 Test du nouveau champ : Montant de l'acompte (Attendu en String par Doctrine)
     */
    public function testSetGetMontantAcompteSuccess(): void
    {
        $reservation = new Reservation();
        $acompteTest = '450.15'; // Stocké en string pour la précision décimale

        $reservation->setMontantAcompte($acompteTest);

        $this->assertSame($acompteTest, $reservation->getMontantAcompte(), 
            "Le getter/setter du Montant de l'acompte doit manipuler une String pour préserver la précision Doctrine."
        );
    }
}