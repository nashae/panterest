<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $flashbag;

    public function __construct(UrlGeneratorInterface $urlGenerator, FlashBagInterface $flashbag)
    {
        $this->urlGenerator = $urlGenerator;
        $this->flashbag = $flashbag;
    }

    public function onLogoutEvent(LogoutEvent $event)
    {
        /** @var User */
        $user = $event->getToken()->getUser();

        $this->flashbag->add(
            'success',
            'logged out, bye '. $user->getFullName() . ' !!'
        );
        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_home')));
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
