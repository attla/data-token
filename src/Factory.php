<?php

namespace Attla\DataToken;

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
class Factory
{
    /**
     * Dynamically create a token manager calling any method
     *
     * @param string $name
     * @param array $arguments
     * @return \Attla\DataToken\Manager|mixed
     */
    public function __call($name, $arguments)
    {
        return (new Manager())->{$name}(...$arguments);
    }

    /**
     * Dynamically create a token manager by calling statically any method
     *
     * @param string $name
     * @param array $arguments
     * @return \Attla\DataToken\Manager|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return (new Manager())->{$name}(...$arguments);
    }
}
