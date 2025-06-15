<?php

namespace Tourze\HotelProfileBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\HotelProfileBundle\Repository\RoomTypeRepository;

#[ORM\Entity(repositoryClass: RoomTypeRepository::class)]
#[ORM\Table(name: 'ims_hotel_room_type', options: ['comment' => '酒店房型信息表'])]
#[ORM\Index(name: 'room_type_idx_hotel_name', columns: ['hotel_id', 'name'])]
class RoomType implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'roomTypes')]
    #[ORM\JoinColumn(name: 'hotel_id', nullable: false, onDelete: 'CASCADE')]
    private ?Hotel $hotel = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '房型名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '房型代码'])]
    #[Assert\Length(max: 20)]
    private ?string $code = null;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => '房间面积(平方米)'])]
    #[Assert\Positive]
    private float $area = 0.0;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '床型描述'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $bedType = '';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最大入住人数'])]
    #[Assert\Positive]
    private int $maxGuests = 2;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '含早餐数量'])]
    #[Assert\PositiveOrZero]
    private int $breakfastCount = 0;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '房型照片URL数组'])]
    private array $photos = [];

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '房型描述'])]
    private ?string $description = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateTime = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: RoomTypeStatusEnum::class, options: ['comment' => '状态'])]
    private RoomTypeStatusEnum $status = RoomTypeStatusEnum::ACTIVE;

    public function __toString(): string
    {
        return isset($this->hotel) ? sprintf('%s - %s', $this->hotel->getName(), $this->name) : $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): self
    {
        $this->hotel = $hotel;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getArea(): float
    {
        return $this->area;
    }

    public function setArea(float $area): self
    {
        $this->area = $area;
        return $this;
    }

    public function getBedType(): string
    {
        return $this->bedType;
    }

    public function setBedType(string $bedType): self
    {
        $this->bedType = $bedType;
        return $this;
    }

    public function getMaxGuests(): int
    {
        return $this->maxGuests;
    }

    public function setMaxGuests(int $maxGuests): self
    {
        $this->maxGuests = $maxGuests;
        return $this;
    }

    public function getBreakfastCount(): int
    {
        return $this->breakfastCount;
    }

    public function setBreakfastCount(int $breakfastCount): self
    {
        $this->breakfastCount = $breakfastCount;
        return $this;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function setPhotos(array $photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function getStatus(): RoomTypeStatusEnum
    {
        return $this->status;
    }

    public function setStatus(RoomTypeStatusEnum $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }
}
