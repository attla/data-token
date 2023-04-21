<?php

namespace Attla\DataToken;

use Carbon\CarbonInterface;
use Attla\Support\Arr as AttlaArr;
use Attla\Pincryp\Factory as Pincryp;
use hisorange\BrowserDetect\Facade as BrowserDetect;

class Manager
{
    /**
     * The token instance
     *
     * @var \Attla\DataToken\Token
     */
    private Token $token;

    /**
     * Create a new Manager instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->token = new Token();
    }

    /**
     * Encode the token
     *
     * @return string
     */
    public function encode(): string
    {
        return $this->token->encodedHeader()
            . '.' . $this->token->encodedBody()
            . '.' . $this->token->signature();
    }

    /**
     * Decode the token if is valid
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data)
    {
        if (
            !$data
            || !is_string($data)
            || count($data = explode('.', $data)) != 3
        ) {
            return false;
        }

        [$header, $payload, $signature] = $data;

        $this->token->header($header)
            ->body($payload)
            ->signature($signature);

        if ($this->token->isInvalid()) {
            // var_dump(11111);
            // var_dump('invalid: ' . join('.', $data));
            return false;
        }

        // TODO: retornar o manager e se quiser dps pega o body
        return $this->token->body();
    }

    /**
     * Alias of decode
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function fromString($data)
    {
        return $this->decode($data);
    }

    /**
     * Alias of decode
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function parseString($data)
    {
        return $this->decode($data);
    }

    /**
     * Alias of decode
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function parse($data)
    {
        return $this->decode($data);
    }

    /**
     * Set data token payload
     *
     * @param mixed $value
     * @return $this
     */
    public function payload($value): self
    {
        $this->token->payload = $value;
        return $this;
    }

    /**
     * Alias of payload
     *
     * @param mixed $value
     * @return $this
     */
    public function body($value): self
    {
        return $this->payload($value);
    }

    /**
     * Set data token secret
     *
     * @param string $secret
     * @return $this
     */
    public function secret(string $secret): self
    {
        $this->token->secret($secret);
        return $this;
    }

    /**
     * Set token expiration date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function expiration(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claims::EXPIRATION_TIME, Util::timestamp($date));
        // $this->token->header[Claims::EXPIRATION_TIME] = Util::timestamp($date);
        return $this;
    }

    /**
     * Set token expiration date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function exp(int|CarbonInterface|\DateTimeInterface $date): self
    {
        return $this->expiration($date);
    }

    /**
     * Set token not before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function notBefore(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claims::NOT_BEFORE, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token not before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function nbf(int|CarbonInterface|\DateTimeInterface $date): self
    {
        return $this->notBefore($date);
    }

    /**
     * Set token issued before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function issuedBefore(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claims::ISSUED_AT, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token issued before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function iat(int|CarbonInterface|\DateTimeInterface $date): self
    {
        return $this->issuedBefore($date);
    }

    /**
     * Set token audience validation.
     *
     * @param mixed $aud
     * @return $this
     */
    public function audience($aud): self
    {
        $this->token->header->set(Claims::AUDIENCE, array_merge(
            (array) $this->token->header->get(Claims::AUDIENCE, []),
            is_array($aud) ? $aud : [$aud]
        ));

        return $this;
    }

    /**
     * Set token audience validation.
     *
     * @param mixed $aud
     * @return $this
     */
    public function aud($aud): self
    {
        return $this->audience($aud);
    }





    /**
     * Set token body as associative when it can be converted.
     *
     * @return $this
     */
    public function associative(): self
    {
        $this->token->associative = true;
        return $this;
    }

    /**
     * Set token body as object when it can be converted.
     *
     * @return $this
     */
    public function asObject(): self
    {
        $this->token->associative = false;
        return $this;
    }

    /**
     * Encode the token if it is casted as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->encode();
    }

    /**
     * Dynamically call any token method
     *
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $this->token->{$name}(...$arguments);

        return $this;
    }




























    /**
     * Set JWT iss validation
     *
     * @param string $value
     * @return $this
     */
    public function iss(string $value = ''): self
    {
        $this->header['iss'] = $value ?: $_SERVER['HTTP_HOST'];
        return $this;
    }

    /**
     * Set JWT browser validation
     *
     * @return $this
     */
    public function bwr(): self
    {
        $this->header['bwr'] = $this->getBrowser();
        return $this;
    }

    /**
     * Set JWT IP validation
     *
     * @return $this
     */
    public function ip(): self
    {
        $this->header['ip'] = $this->getIp();
        return $this;
    }

    /**
     * Signs a JWT with security validations
     *
     * @param int|\Carbon\CarbonInterface $epx
     * @return $this
     */
    public function sign(int|CarbonInterface $exp = 30): self
    {
        $this->exp($exp);
        $this->iss();
        $this->bwr();
        $this->ip();
        return $this;
    }

    /**
     * Generate a unique identifier
     *
     * @param mixed $value
     * @return string
     */
    public function id($value): string
    {
        return $this->payload($value)->encode();
    }

    /**
     * Always generate the same identifier
     *
     * @param mixed $value
     * @return string
     */
    public function sid($value): string
    {
        return $this->same()->id($value);
    }

    /**
     * Get entropy token
     *
     * @return string
     */
    public function getEntropy()
    {
        if ($this->same) {
            return $this->header['e'];
        }

        return $this->header['e'] = Pincryp::generateKey(6);
    }

    /**
     * Set JWT to same mode
     *
     * @param string $entropy
     * @return $this
     */
    public function same(string $entropy = ''): self
    {
        $this->same = true;
        $this->header['e'] = Pincryp::md5($entropy ?: $this->secret);
        return $this;
    }

    /**
     * Get the client browser
     *
     * @return string
     */
    protected function getBrowser(): string
    {
        return BrowserDetect::browserFamily() . BrowserDetect::browserVersionMajor();
    }

    /**
     * Get the client IP address
     *
     * @return string|null
     */
    protected function getIp()
    {
        return request()->getClientIp();
    }

    /**
     * Get a primitive value or array
     *
     * @param mixed $value
     * @return mixed
     */
    protected function primitiveOrArray($value)
    {
        if (
            is_numeric($value)
            || is_string($value)
            || is_array($value)
        ) {
            return $value;
        }

        return AttlaArr::toArray($value);
    }
}
