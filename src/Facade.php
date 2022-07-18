<?php

namespace Attla\DataToken;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @method static string encode()
 * @method static mixed decode(string $data, bool $assoc = false)
 * @method static mixed fromString(string $data, bool $assoc = false)
 * @method static mixed parseString(string $data, bool $assoc = false)
 * @method static mixed parse(string $data, bool $assoc = false)
 * @method static self payload($value)
 * @method static self body($value)
 * @method static self secret(string $secret)
 * @method static self same(string $entropy)
 * @method static self exp(int|\Carbon\CarbonInterface $exp = 30)
 * @method static self iss(string $value = '')
 * @method static self bwr()
 * @method static self ip()
 * @method static self sign(int|\Carbon\CarbonInterface $exp = 30)
 * @method static string id($value)
 * @method static string sid($value)
 *
 * @see \Attla\DataToken\Manager
 */
class Facade extends BaseFacade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
