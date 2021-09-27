<?php
namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ApiException extends Exception
{
    private $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct($message, $statusCode = null)
    {
        parent::__construct($message);
        if (!empty($statusCode)) {
            $this->statusCode = $statusCode;
        }
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
