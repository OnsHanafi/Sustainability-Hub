<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Service::class)]
    private Collection $Category;

    public function __construct()
    {
        $this->Category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
      public function __toString()
    {
        return $this->getType() ;
    }

      /**
       * @return Collection<int, Service>
       */
      public function getCategory(): Collection
      {
          return $this->Category;
      }

      public function addCategory(Service $category): self
      {
          if (!$this->Category->contains($category)) {
              $this->Category->add($category);
              $category->setCategory($this);
          }

          return $this;
      }

      public function removeCategory(Service $category): self
      {
          if ($this->Category->removeElement($category)) {
              // set the owning side to null (unless already changed)
              if ($category->getCategory() === $this) {
                  $category->setCategory(null);
              }
          }

          return $this;
      }
}
