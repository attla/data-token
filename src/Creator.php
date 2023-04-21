<?php

namespace Attla\DataToken;

use Carbon\CarbonInterface;

class Creator
{
    /**
     * The token instance
     *
     * @var \Attla\DataToken\Token
     */
    private Token $token;

    /**
     * The token instance
     *
     * @var array<string, string>
     */
    private $methodAliases = [
        'setBody' => [
            'body',
            'payload',
            'content',
        ],
        'secret' => [
            'phrase',
            'passphrase',
        ],
        'expiresAt' => [
            'expiration',
            'exp',
        ],
        'canOnlyBeUsedAfter' => [
            'notBefore',
            'nbf',
        ],
        'issuedAt' => [
            'issuedBefore',
            'iat',
        ],
        'issuedBy' => 'iss',
        'identifiedBy' => 'jti',
        'relatedTo' => 'sub',
        'permittedFor' => [
            'audience',
            'aud',
        ],
    ];

    /**
     * Create a new Manager instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->token = new Token();

        foreach ($this->methodAliases as $method => $aliases) {
            $aliases = (array) $aliases;
            array_walk($aliases, fn($value) => $this->methodAliases[$value] = $method);

            unset($this->methodAliases[$method]);
        }
    }





    /**
     * Set token expiration date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function expiresAt(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claims::EXPIRATION_TIME, Util::timestamp($date));
        // $this->token->header[Claims::EXPIRATION_TIME] = Util::timestamp($date);
        return $this;
    }

    /**
     * Set token not before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function canOnlyBeUsedAfter(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claims::NOT_BEFORE, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token issued before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function issuedAt(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claims::ISSUED_AT, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token "jti" validation.
     *
     * @param string $id
     * @return $this
     */
    public function identifiedBy(string $id): self
    {
        $this->token->header->set(Claims::ID, $id);
        return $this;
    }

    /**
     * Set token "sub" validation.
     *
     * @param string $subject
     * @return $this
     */
    public function relatedTo(string $subject): self
    {
        $this->token->header->set(Claims::SUBJECT, $subject);
        return $this;
    }

    /**
     * Set token audience validation.
     *
     * @param mixed ...$aud
     * @return $this
     */
    public function permittedFor(...$aud): self
    {
        $this->token->header->set(Claims::AUDIENCE, array_merge(
            $this->token->header->getArray(Claims::AUDIENCE),
            $aud
            // is_array($aud) ? $aud : [$aud]
        ));

        return $this;
    }

    /**
     * Set token "iss" validation.
     *
     * @param string ...$issuers
     * @return $this
     */
    public function issuedBy(string ...$issuers): self
    {
        $this->token->header->set(Claims::ISSUER, array_merge(
            $this->token->header->getArray(Claims::ISSUER),
            $issuers
        ));

        return $this;
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
     * Encode the token
     *
     * @return string
     */
    public function get(): string
    {
        // var_dump($this->token);
        return $this->token->encodedHeader()
            . '.' . $this->token->encodedBody()
            . '.' . $this->token->signature();
    }

    /**
     * Encode the token if it is casted as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * Dynamically call method aliases
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|$this
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if (!is_null($method = $this->methodAliases[$name] ?? null)) {
            return $this->{$method}(...$arguments);
        }

        if ($this->token->isset($name) || $this->token->hasMethod($name)) {
            $this->token->{$name}(...$arguments);

            return $this;
        }

        throw new \BadMethodCallException('Method "' . $name . '" not exists on ' . __NAMESPACE__);
    }
}
