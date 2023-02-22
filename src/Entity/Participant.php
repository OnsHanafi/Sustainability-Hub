<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"choose name ")]
    private ?string $name = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"phone number should only containt numbers")]
    #[Assert\Regex('/^\d+$/')]
    private ?int $phone_number = null;


    #[ORM\ManyToOne(inversedBy: 'participants')]
    private ?Events $Events = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(int $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getEvents(): ?Events
    {
        return $this->Events;
    }

    public function setEvents(?Events $Events): self
    {
        $this->Events = $Events;

        return $this;
    }



}
