<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("event")]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"choose name ")]
    #[Groups("event")]
    private ?string $name = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"phone number should only containt numbers")]
    #[Assert\Regex(array('pattern' => '/^[0-9]\d*$/'))]
    #[Groups("event")]
    private ?int $phone_number = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"email should be valid")]
    private ?string $email = null;


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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }



}
