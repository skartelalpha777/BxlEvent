<?php

namespace App\Enum;

/**
 * Definit les différent roles 
 */

enum UserRole: string
{
    case ADMIN = 'admin';
    case CONTRIBUTEUR = 'contributeur';
    case MEMBRE = 'membre';

    /**
     * cette focntion permet juste de convertir le premier
     * carractere de du role afin de d'avoir Membre au lieu
     * de membre par exemple lors de l'affichage
     * @return string le role courant.
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::CONTRIBUTEUR => 'Contributeur',
            self::MEMBRE => 'Membre',
        };
    }
}