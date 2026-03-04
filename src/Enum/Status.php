<?php

namespace App\Enum;

/**
 * definit les différents type statut possible pour un évènement
 */
enum Status: string
{
    case VALIDATED = 'validated';
    case REFUSED = 'refused';
    case NOTCHECKED = 'notchecked';
}