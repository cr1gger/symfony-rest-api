<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TokenRepository::class)
 */
class Token
{
    const
        TOKEN_DIE = 0,
        TOKEN_LIVE = 1;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="integer")
     */
    private $expires;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private $live;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpires(): ?int
    {
        return $this->expires;
    }

    public function setExpires(int $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function isValid(): bool
    {
        if ($this->live === self::TOKEN_DIE) return false;
        if ($this->expires === 0) return true; # Infinite token
        return (time() < $this->expires);
    }

    public function getLive(): ?int
    {
        return $this->live;
    }

    public function setLive(int $live): self
    {
        $this->live = $live;

        return $this;
    }

    public function generateToken($length = 40)
    {
        try
        {
            $this->token = sha1(random_bytes(20));

        } catch (\Throwable $e)
        {
            $AZ = range('A', 'Z');
            $az = range('a','z');
            $num = range(0, 9);
            $result = array_merge($AZ, $az, $num);
            $result = shuffle($result);
            $string = implode('', $result);
            $this->token = substr($string, 0 , $length);
        }
    }
}
