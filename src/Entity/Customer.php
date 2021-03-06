<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @Serializer\ExclusionPolicy("ALL")
 *
 *
 * @Hateoas\Relation(
 *     "show",
 *     href=@Hateoas\Route(
 *     "customer.show",
 *     parameters={"id" = "expr(object.getId())"},
 *     absolute=true
 *     )
 *)
 *
 * @Hateoas\Relation(
 *     "create",
 *     href=@Hateoas\Route(
 *     "customer.create",
 *     absolute= true
 *      )
 * )
 *
 * @Hateoas\Relation(
 *     "delete",
 *     href=@Hateoas\Route(
 *     "customer.delete",
 *     parameters={"id" = "expr(object.getId())"},
 *     absolute=true
 *     )
 *)
 */
class Customer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *     min="10",
     *     minMessage="The address should not be under {{ limit }} characters."
     * )
     * @Serializer\Expose()
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $postal;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Serializer\Expose()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="customers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Customer constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function setPostal(string $postal): self
    {
        $this->postal = $postal;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
