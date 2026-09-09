<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    /**
     * Enregistre la langue choisie en session puis renvoie l'utilisateur
     * sur la page d'où il vient (fallback : la liste des évènements si
     * aucun referer n'est disponible, par exemple accès direct à l'URL).
     */
    #[Route(
        '/changer-langue/{_locale}',
        name: 'app_change_locale',
        requirements: ['_locale' => 'fr|en|nl|es'],
        methods: ['GET'],
    )]
    public function change(string $_locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $_locale);

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('app_event_index'));
    }
}
