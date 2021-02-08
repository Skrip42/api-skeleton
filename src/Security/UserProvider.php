<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Security\User;

class UserProvider implements UserProviderInterface
{
    private $request;

    public function __construct(
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getUser() : UserInterface
    {
        return new User(
            [
                'ip'   => $this->request->getClientIp(),
                'host' => $this->request->getHost()
            ]
        );
    }

    public function loadUserByUsername(string $username)
    {
        return $this->getUser();
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass(string $class)
    {
        return User::class === $class;
    }
}
