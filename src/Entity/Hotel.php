<?php

namespace Tourze\HotelProfileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Repository\HotelRepository;

#[ORM\Entity(repositoryClass: HotelRepository::class)]
#[ORM\Table(name: 'ims_hotel_profile', options: ['comment' => '酒店基础信息表'])]
class Hotel implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '酒店名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '详细地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $address = '';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '星级(1-5)'])]
    #[Assert\Range(min: 1, max: 5)]
    private int $starLevel = 3;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '联系人姓名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $contactPerson = '';

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '联系电话'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(pattern: '/^[\d\-\+\(\)\s]+$/', message: '请输入有效的电话号码')]
    private string $phone = '';

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '联系邮箱'])]
    #[Assert\Email]
    #[Assert\Length(max: 100)]
    private ?string $email = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '酒店照片URL数组'])]
    #[Assert\Type(type: 'array')]
    private array $photos = [];

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '设施与服务信息'])]
    #[Assert\Type(type: 'array')]
    private array $facilities = [];

    #[ORM\Column(type: Types::STRING, length: 20, enumType: HotelStatusEnum::class, options: ['comment' => '酒店状态'])]
    #[Assert\Choice(callback: [HotelStatusEnum::class, 'cases'])]
    private HotelStatusEnum $status = HotelStatusEnum::OPERATING;

    /**
     * @var Collection<int, RoomType>
     */
    #[ORM\OneToMany(targetEntity: RoomType::class, mappedBy: 'hotel', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $roomTypes;

    public function __construct()
    {
        $this->roomTypes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getStarLevel(): int
    {
        return $this->starLevel;
    }

    public function setStarLevel(int $starLevel): void
    {
        $this->starLevel = $starLevel;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return array<string>
     */
    public function getPhotos(): array
    {
        return $this->photos;
    }

    /**
     * @param list<string> $photos
     */
    public function setPhotos(array $photos): void
    {
        $this->photos = $photos;
    }

    /**
     * @return array<string>
     */
    public function getFacilities(): array
    {
        return $this->facilities;
    }

    /**
     * @param list<string> $facilities
     */
    public function setFacilities(array $facilities): void
    {
        $this->facilities = $facilities;
    }

    public function getStatus(): HotelStatusEnum
    {
        return $this->status;
    }

    public function setStatus(HotelStatusEnum $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection<int, RoomType>
     */
    public function getRoomTypes(): Collection
    {
        return $this->roomTypes;
    }

    public function addRoomType(RoomType $roomType): self
    {
        if (!$this->roomTypes->contains($roomType)) {
            $this->roomTypes->add($roomType);
            $roomType->setHotel($this);
        }

        return $this;
    }

    public function removeRoomType(RoomType $roomType): self
    {
        if ($this->roomTypes->removeElement($roomType)) {
            if ($roomType->getHotel() === $this) {
                $roomType->setHotel(null);
            }
        }

        return $this;
    }
}
