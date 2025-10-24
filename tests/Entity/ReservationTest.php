<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;


class ReservationTest extends TestCase
{
    
    public function testSetGetDossierResaSuccess(): void
    {
        
        $reservation = new Reservation();
        $dossier = 'ARINFO-SUCCESS-001';

        
        $reservation->setDossierResa($dossier);

        
        $this->assertSame($dossier, $reservation->getDossierResa(), 
            "La valeur retournée par getDossierResa() doit correspondre à la valeur définie par setDossierResa()."
        );
    }
}
