<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AccessControlAllowOriginHeaderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $appEnv,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if ($this->appEnv === 'dev') {
            $event->getResponse()->headers->set('Access-Control-Allow-Origin', '*');
        }
    }
}
