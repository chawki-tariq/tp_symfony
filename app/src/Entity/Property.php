<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
#[Vich\Uploadable]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $availability_start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $availability_end = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'properties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(PropertyType::class)]
    private ?PropertyType $type = null;

    #[ORM\OneToMany(mappedBy: 'property', targetEntity: Booking::class, cascade: ['remove'])]
    private Collection $bookings;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_name = null;

    #[Vich\UploadableField(mapping: 'property_images', fileNameProperty: 'image_name')]
    private ?File $image_file = null;

    #[ORM\Column(nullable: true, type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'properties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?int $adultRate = null;

    #[ORM\Column(options: [
        'default' => 0
    ])]
    private ?int $childRate = 0;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->bookings = new ArrayCollection();
    }

    public function setImageFile(?File $image_file = null): void
    {
        $this->image_file = $image_file;

        if (null !== $image_file) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    public function getImageFile(): ?File
    {
        return $this->image_file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageName(): ?string
    {
        return $this->image_name;
    }

    public function setImageName(?string $image_name): self
    {
        $this->image_name = $image_name;

        return $this;
    }

    public function getAvailabilityStart(): ?\DateTimeInterface
    {
        return $this->availability_start;
    }

    public function setAvailabilityStart(?\DateTimeInterface $availability_start): self
    {
        $this->availability_start = $availability_start;

        return $this;
    }

    public function getAvailabilityEnd(): ?\DateTimeInterface
    {
        return $this->availability_end;
    }

    public function setAvailabilityEnd(?\DateTimeInterface $availability_end): self
    {
        $this->availability_end = $availability_end;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getType(): ?PropertyType
    {
        return $this->type;
    }

    public function setType(?PropertyType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setProperty($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getProperty() === $this) {
                $booking->setProperty(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    #[Assert\Callback]
    public function validateDate(ExecutionContextInterface $context, $payload)
    {
        if ($this->availability_start >= $this->availability_end) {
            $context->buildViolation("La date de début de disponibilité doit être inférieur à la date de fin de disponibilité!")
                ->atPath('availability_start')
                ->addViolation();
        }
    }

    public function getAdultRate(): ?int
    {
        return $this->adultRate;
    }

    public function setAdultRate(int $adultRate): self
    {
        $this->adultRate = $adultRate;

        return $this;
    }

    public function getChildRate(): ?int
    {
        return $this->childRate;
    }

    public function setChildRate(int $childRate): self
    {
        $this->childRate = $childRate;

        return $this;
    }

    public function getFormatedPrice(): string
    {
        return number_format($this->adultRate / 100, 0, '', ' ');
    }
}