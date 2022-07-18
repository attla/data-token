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
     * DataToken manager instance
     *
     * @var \Attla\DataToken\Manager
     */
    protected Manager $factory;

    public function __construct()
    {
        $this->factory = new Manager();
    }

    public function __toString(): string
    {
        return $this->factory->encode();
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->factory, $name)) {
            throw new \BadMethodCallException(
                sprintf("The method '%s' doesn't exists in DataToken Class", $name)
            );
        }

        $result = $this->factory->{$name}(...$arguments);
        return $result instanceof Manager ? $this : $result;
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static())->{$name}(...$arguments);
    }
}
