<?php
namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ApiLoggerListener
{
    private $logger;

    public function __construct(LoggerInterface $apiLogger)
    {
        $this->logger = $apiLogger;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        $message = 'request: ' . $request->getRequestUri()
            . ' response:' . $response->getContent();

        $this->logger->info($message);
    }
}
