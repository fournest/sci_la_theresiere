<?php

namespace App\Enum;

enum ReservationStatus: string
{
    // Statut initial lorsque l'utilisateur crée la demande
    case PENDING = 'en_attente';
    
    // Étape 1 : L'admin a envoyé le contrat
    case CONTRACT_SENT = 'contrat envoyé';
    
    // Étape 2 : L'admin a reçu le contrat signé (via validateReturn)
    case SIGNED = 'contrat_signe';
    
    // Étape 3 : La réservation est confirmée (via confirm)
    case CONFIRMED = 'confirmée'; 
    
    // Statut si l'utilisateur annule ou l'admin refuse
    case CANCELLED = 'annulée';
    
    // Statut si la réservation a eu lieu
    case COMPLETED = 'terminée';
    
    // Statut de modification
    case MODIFIED = 'modifiée';
    
    /**
     * @return string[] Retourne les statuts qui BLOQUENT une salle.
     */
    public static function getBlockingStatuses(): array
    {
        return [
            self::PENDING->value,
            self::CONTRACT_SENT->value,
            self::SIGNED->value,
            self::CONFIRMED->value,
            self::MODIFIED->value,
        ];
    }
}