<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Authenticator extends AbstractGuardAuthenticator
{
    private $api_key = null;

    public function __construct(ContainerInterface $container)
    {
        $this->api_key= $container->getParameter('api_key');
    }

    public function supports(Request $request)
    {
        return $request->query->has('api_key')
            || $request->request->has('api_key');
    }

    public function getCredentials(Request $request)
    {
        if ($request->query->has('api_key')) {
            return $request->query->get('api_key');
        } else {
            return $request->request->get('api_key');
        }
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (is_null($credentials)) {
            throw new CustomUserMessageAuthenticationException(
                'empty token'
            );
        }
        if ($credentials !== $this->api_key) {
            throw new CustomUserMessageAuthenticationException(
                'token not valid'
            );
        }
        /** @var App\Security\UserProvider $userProvider */
        $user = $userProvider->getUser();
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($credentials !== $this->api_key) {
            return false;
        }
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'success' => false,
            'message' => 'token is not valid'
        ];
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        if (!$request->query->has('api_key')
            && !$request->request->has('api_key')
        ) {
            $data = [
                'success' => false,
                'message' => 'api_key required'
            ];
            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }
    }
}
