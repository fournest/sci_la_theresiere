<?php

namespace App\Enum;

enum ReservationStatus: string
{
    // Statut initial lorsque l'utilisateur crée la demande
    case PENDING = 'en_attente';
    
    // NOUVEAU STATUT : L'admin a accepté, le contrat est envoyé, on attend la signature/l'acompte.
    case CONTRACT_SENT = 'contrat_envoyé';
    
    // Statut après signature/acompte (anciennement 'validée')
    case CONFIRMED = 'confirmée'; 
    
    // Statut si l'utilisateur annule ou l'admin refuse
    case CANCELLED = 'annulée';
    
    // Statut si la réservation a eu lieu
    case COMPLETED = 'terminée';
    
    // Optionnel : si l'admin modifie une réservation validée
    case MODIFIED = 'modifiée';
    
    /**
     * @return string[] Retourne les statuts qui BLOQUENT une salle (i.e., ne sont pas annulés ou terminés).
     */
    public static function getBlockingStatuses(): array
    {
        return [
            self::PENDING->value,
            self::CONTRACT_SENT->value,
            self::CONFIRMED->value,
            self::MODIFIED->value,
        ];
    }
}