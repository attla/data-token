<?php

namespace Attla\DataToken;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Attla\Support\Str as AttlaStr;

class Parser
{
    /**
     * The token instance
     *
     * @var \Attla\DataToken\Token
     */
    private Token $token;

    /**
     * The token string
     *
     * @var string
     */
    private $tokenString = '';

    /**
     * The token instance
     *
     * @var array<string, string>
     */
    private $methodAliases = [
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
     * Create a new Token instance
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = new Token();
        $this->tokenString = (string) $token;

        foreach ($this->methodAliases as $method => $aliases) {
            $aliases = (array) $aliases;
            array_walk($aliases, fn($value) => $this->methodAliases[$value] = $method);

            unset($this->methodAliases[$method]);
        }
    }





    /**
     * Set token expiration date.
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function validAt(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->claims->set(Claims::NOW, Util::timestamp($date));
        // $this->token->header[Claims::EXPIRATION_TIME] = Util::timestamp($date);
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
        $this->token->claims->set(Claims::ID, $id);
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
        $this->token->claims->set(Claims::SUBJECT, $subject);
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
        $this->token->claims->set(Claims::AUDIENCE, array_merge(
            $this->token->claims->getArray(Claims::AUDIENCE),
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
        $this->token->claims->set(Claims::ISSUER, array_merge(
            $this->token->claims->getArray(Claims::ISSUER),
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


    private function parseToken()
    {
        if (
            !$this->tokenString
            || !is_string($this->tokenString)
            || count($token = explode('.', $this->tokenString)) != 3
        ) {
            return false;
        }

        [$header, $payload, $signature] = $token;

        $this->token
            ->header($header)
            ->body($payload)
            ->signature($signature);
    }

    /**
     * Returns the value of token
     *
     * @return mixed
     */
    public function get()
    {
        // var_dump($this->token->header->all());
        $this->parseToken();

        // var_dump($this->token->header->all());
        // die;

        if ($this->token->isInvalid()) {
            // var_dump(11111);
            // var_dump('invalid: ' . join('.', $data));
            return false;
        }

        return $this->token->body;
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
        $method = $this->methodAliases[$name]
            ?? $this->methodAliases[$name = Str::camel(AttlaStr::removePrefix($name, 'is'))]
            ?? null;
        if (!is_null($method)) {
            return $this->{$method}(...$arguments);
        }

        if ($this->token->isset($name) || $this->token->hasMethod($name)) {
            $this->token->{$name}(...$arguments);

            return $this;
        }

        throw new \BadMethodCallException('Method "' . $name . '" not exists on ' . __NAMESPACE__);
    }
}
