<?php

namespace App\Entity\Catalogue;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Livre extends Article
{
    #[ORM\Column(length: 255, name: 'auteur')]
    private ?string $auteur = null;

    #[Assert\Isbn(message: 'ISBN invalide')]
    #[Assert\NotBlank(message: 'ISBN est requis')]
    #[ORM\Column(length: 255, name: 'isbn')]
    private ?string $ISBN = null;

    #[Assert\PositiveOrZero(message: 'nbPages doit être un nombre positif ou 0')]
    #[Assert\NotBlank(message: 'nbPages est requis')]
    #[ORM\Column(name: 'nb_pages')]
    private ?int $nbPages = null;

    
    #[ORM\Column(length: 255, name: 'date_de_parution')]
    private ?string $dateDeParution = null;

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getISBN(): ?string
    {
        return $this->ISBN;
    }

    public function setISBN(string $ISBN): static
    {
        $this->ISBN = $ISBN;

        return $this;
    }

    public function getNbPages(): ?int
    {
        return $this->nbPages;
    }

    public function setNbPages(int $nbPages): static
    {
        $this->nbPages = $nbPages;

        return $this;
    }

    public function getDateDeParution(): ?string
    {
        return $this->dateDeParution;
    }

    public function setDateDeParution(string $dateDeParution): static
    {
        $this->dateDeParution = $dateDeParution;

        return $this;
    }
}

