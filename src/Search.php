<?php

namespace Xlr8;

use DomainException;

class Search
{
    private static array $sources = [];
    private static array $hotels = [];

    /**
     * @param string[] $sources
     * @return void
     */
    public static function setSources(array $sources): void
    {
        self::$sources = $sources;
    }

    public static function getSources(): array
    {
        return self::$sources;
    }

    private static function getHotelsFromSource(string $source): array
    {
        $content = file_get_contents($source);
        $parsed = json_decode($content, true);
        if (!$parsed || !$parsed['success']) {
            throw new DomainException('Error at getting hotels from source');
        }
        return array_map(function ($hotel) {
            return [
                'name' => $hotel[0],
                'latitude' => (float)$hotel[1],
                'longitude' => (float)$hotel[2],
                'price_per_night' => (float)$hotel[3],
            ];
        }, $parsed['message']);
    }

    private static function getHotels(): array
    {
        if (self::$hotels) {
            return self::$hotels;
        }

        array_map(function ($source) {
            self::$hotels = array_merge(self::$hotels, self::getHotelsFromSource($source));
        }, self::getSources());

        return self::$hotels;
    }

    public static function getNearbyHotels(
        float    $latitude,
        float    $longitude,
        Sortable $orderBy = Sortable::PROXIMITY
    ): array
    {
        $hotels = array_map(function ($hotel) use ($latitude, $longitude) {
            return [
                ...$hotel,
                'proximity' => self::getDistance($latitude, $longitude, $hotel['latitude'], $hotel['longitude']),
            ];
        }, self::getHotels());

        return self::orderBy($hotels, $orderBy);
    }

    private static function getDistance(
        float $originLatitude,
        float $originLongitude,
        mixed $destinationLatitude,
        mixed $destinationLongitude
    ): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($destinationLatitude - $originLatitude);
        $dLon = deg2rad($destinationLongitude - $originLongitude);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($originLatitude)) * cos(deg2rad($destinationLatitude)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c;
    }

    private static function orderBy(array $hotels, Sortable $orderBy): array
    {
        usort($hotels, fn($a, $b) => $a[$orderBy->value] <=> $b[$orderBy->value]);
        return $hotels;
    }
}
