<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $host = null;
    private $ip   = null;

    public function __construct($data)
    {
        $this->host = $data['host'];
        $this->ip   = $data['ip'];
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->host;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function eraseCredentials()
    {
        return null;
    }
}
