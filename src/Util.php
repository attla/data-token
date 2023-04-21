<?php

namespace Attla\DataToken;

use Carbon\{
    Carbon,
    CarbonInterface,
    CarbonImmutable
};

class Util
{
    /**
     * Transform the date to a timestamp
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return int
     */
    public static function timestamp(int|CarbonInterface|\DateTimeInterface $date = null): int
    {
        if (is_null($date) || !$date) {
            return 0;
        } elseif (is_int($date)) {
            return $date;
        } elseif ($date instanceof CarbonInterface) {
            return $date->timestamp;
        } elseif ($date instanceof \DateTimeInterface) {
            return $date instanceof \DateTimeImmutable
                ? CarbonImmutable::instance($date)->timestamp
                : Carbon::instance($date)->timestamp;
        }

        return 0;
    }

    /**
     * Transform the date to a timestamp or retrieve a current timestamp
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return int
     */
    public static function minuteToSecond(int|CarbonInterface|\DateTimeInterface $date = null): int
    {
        return static::timestamp($date) ?: time();
    }

    // Carbon::now()->addMinutes(30), $desc, Util::timestamp($value), 2],
    // $desc = '+1 hour' => [$value = strtotime($desc), $desc, Util::timestamp($value), 3],
    // $desc = '+1 day'  => [
    //     $value = date_create('@' . strtotime($desc)),
    //     $desc,
    //     Util::timestamp($value),
    //     4
    // ],
    // $desc = '+1 week' => [
    //     $value = date_create_immutable('@' . strtotime($desc)),
    //     $desc,
    //     Util::timestamp($value),
    //     5
    // ],
    public static function strToCarbon(string $str): Carbon
    {
        return Carbon::createFromTimestamp(strtotime($str));
    }

    public static function strToCarbonImmutable(string $str): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp(strtotime($str));
    }

    public static function strToDateTime(string $str): \DateTime
    {
        return (new \DateTime())->setTimeStamp(strtotime($str));
    }

    public static function strToDateTimeImmutable(string $str): \DateTimeImmutable
    {
        return date_create_immutable('@' . strtotime($str));
    }
}
