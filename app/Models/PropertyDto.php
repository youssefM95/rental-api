<?php
namespace App\Models;
class PropertyDto
{
     public int $id;
     public int $owner_id;
     public $title;
     public string $description;
     public string $location;
     public float $price_per_day;
     public string $category;
     public array $images;
     public ?User $owner = null;

    // Constructor to initialize the DTO
    public function __construct(
        int $id,
        int $owner_id,
        string $title,
        string $description,
        string $location,
        float $price_per_day,
        string $category,
        array $images,
        User $owner = null
    ) {
        $this->id = $id;
        $this->owner_id = $owner_id;
        $this->title = $title;
        $this->description = $description;
        $this->location = $location;
        $this->price_per_day = $price_per_day;
        $this->category = $category;
        $this->images = $images;
        $this->owner = $owner;
    }

    // Getters
    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getPricePerDay(): float
    {
        return $this->price_per_day;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    // Optionally, you can include setters if needed
    public function setOwnerId(int $owner_id): void
    {
        $this->owner_id = $owner_id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setPricePerDay(float $price_per_day): void
    {
        $this->price_per_day = $price_per_day;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }
}
