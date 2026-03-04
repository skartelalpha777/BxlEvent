<?php

namespace App\Enum;
/**
 * definit les différents type de prix possible pour ticket 
 */
enum TicketLabel: string
{
    case ADULTE = 'adulte';
    case ENFANT = 'enfant';
    case VIP = 'vip';
    case ETUDIANT = 'etudiant';
    case PROMO = 'promo';
}