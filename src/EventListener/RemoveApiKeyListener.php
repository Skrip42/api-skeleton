<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class RemoveApiKeyListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $request->query->remove('api_key');
    }
}
