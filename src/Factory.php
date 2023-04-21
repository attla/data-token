<?php

namespace Attla\DataToken;

// * @method static string encode()
// * @method static mixed decode(string $data, bool $assoc = false)
// * @method static mixed fromString(string $data, bool $assoc = false)
// * @method static mixed parseString(string $data, bool $assoc = false)
// * @method static mixed parse(string $data, bool $assoc = false)
// * @method static self payload($value)
// * @method static self body($value)
// * @method static self secret(string $secret)
// * @method static self same(string $entropy)
// * @method static self exp(int|\Carbon\CarbonInterface $exp = 30)
// * @method static self iss(string $value = '')
// * @method static self bwr()
// * @method static self ip()
// * @method static self sign(int|\Carbon\CarbonInterface $exp = 30)
// * @method static string id($value)
// * @method static string sid($value)
/**
 * @method static Creator create()
 * @method static Parser parse(string $token)
 *
 * @see \Attla\DataToken\Creator
 * @see \Attla\DataToken\Parser
 */
class Factory
{
    /**
     * Returns a token creation manager.
     *
     * @return \Attla\DataToken\Creator
     */
    public static function create(): Creator
    {
        return new Creator();
    }

    /**
     * Returns a token parse manager.
     *
     * @param string $token
     * @return \Attla\DataToken\Parser
     */
    public static function parse(string $token): Parser
    {
        return new Parser($token);
    }
}
