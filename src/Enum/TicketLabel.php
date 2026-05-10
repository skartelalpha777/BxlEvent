<?php

namespace App\Enum;

/**
 * definit les différents type de prix possible pour ticket 
 */
enum TicketLabel: string
{
    case STANDART = 'Standart';
    case ENFANT = 'Enfant';
    case VIP = 'Vip';
    case PROMO = 'Promo';
    case TABLE= 'Table';
}
