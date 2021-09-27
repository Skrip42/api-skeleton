<?php
namespace App\EventListener;

use App\Exceptions\ApiException;
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
        if ($exception instanceof ApiException) {
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setStatusCode(Response::HTTP_OK);
        }
        $response->headers->set('Content-Type', 'text/json');

        $event->setResponse($response);
    }
}
