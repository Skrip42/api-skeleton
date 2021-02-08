<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/api", name="api")
 * @IsGranted("ROLE_USER")
 */
abstract class AbstractApiController extends AbstractController
{
    private $serializer;

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function formatResponse($data)
    {
        $responseData = $this->serializer->serialize(
            $data,
            'api',
            [
                'success' => true
            ]
        );
        $response = new Response();
        $response->setContent($responseData);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/json');
        return $response;
    }
}
