<?php

namespace App\Telegram\Core\Types;

class Location
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
        );
    }
}
