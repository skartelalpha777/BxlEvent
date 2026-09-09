<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applique à chaque requête la langue choisie par l'utilisateur et stockée
 * en session (via LocaleController::change), au lieu de toujours retomber
 * sur le default_locale de la configuration.
 * Doit s'exécuter après LocaleListener::setDefaultLocale() (priorité 100)
 * qui positionne la langue par défaut en tout premier lieu.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly string $defaultLocale = 'fr') {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $locale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 15]],
        ];
    }
}
