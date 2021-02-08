<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;

class ApiExceptionListener
{

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if (strpos($route, 'api_') !== 0
            && strpos($route, 'oauth_server_gate') !== 0
        ) {
            return;
        }
        $responseData = $this->serializer->serialize(
            [],
            'api',
            [
                'success'         => false,
                'error_message'   => $exception->getMessage(),
                'error_code'      => $exception->getCode(),
                'error_traceback' => $exception->getTrace(),
                'error_file'      => $exception->getFile(),
                'error_line'      => $exception->getLine()
            ]
        );

        $response = new Response();
        $response->setContent($responseData);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/json');

        $event->setResponse($response);
    }
}
