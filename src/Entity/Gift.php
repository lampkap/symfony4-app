<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GiftRepository")
 */
class Gift
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gift;

    public function getId()
    {
        return $this->id;
    }

    public function getGift(): ?string
    {
        return $this->gift;
    }

    public function setGift(string $gift): self
    {
        $this->gift = $gift;

        return $this;
    }
}
