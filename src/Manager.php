<?php

namespace Attla\DataToken;

use Attla\Pincryp\Facade as Pincryp;
use Attla\Support\Arr as AttlaArr;
use Carbon\CarbonInterface;
use hisorange\BrowserDetect\Facade as BrowserDetect;

class Manager
{
    /**
     * The header of the JWT
     *
     * @var array
     */
    private array $header = [];

    /**
     * The payload of the JWT
     *
     * @var mixed
     */
    private $payload;

    /**
     * The secret passphrase of the JWT
     *
     * @var string
     */
    private string $secret = '';

    /**
     * Determine if the JWT is on same mode
     *
     * @var bool
     */
    private bool $same = false;

    /**
     * Encode a JWT
     *
     * @return string
     */
    public function encode(): string
    {
        if (!$this->payload) {
            throw new \Exception('DataToken require a not empty payload.');
        }

        $payload = Pincryp::encode($this->payload, $this->getEntropy());
        $header = Pincryp::encode(
            $this->same ? $this->header : AttlaArr::randomized($this->header),
            $this->secret
        );

        return $header . '_'
            . $payload . '_'
            . Pincryp::md5($header . $payload, $this->secret);
    }

    /**
     * Decode the JWT if is valid
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function decode($data, bool $assoc = false)
    {
        if (!$data || !is_string($data)) {
            return false;
        }

        $data = explode('_', $data);
        if (count($data) != 3) {
            return false;
        }

        [$header, $payload, $signature] = $data;

        if ($signature != Pincryp::md5($header . $payload, $this->secret)) {
            return false;
        }

        $header = Pincryp::decode($header, $this->secret);
        if (!$header instanceof \StdClass) {
            return false;
        }

        $payload = Pincryp::decode($payload, $header->e ?? '', $assoc);
        if (!$payload) {
            return false;
        }

        // exp validation
        if (isset($header->exp) && time() > $header->exp) {
            return false;
        }

        // iss validation
        if (isset($header->iss) && $_SERVER['HTTP_HOST'] != $header->iss) {
            return false;
        }

        // bwr validation
        if (isset($header->bwr) && $this->getBrowser() != $header->bwr) {
            return false;
        }

        // ip validation
        if (isset($header->ip) && $this->getIp() != $header->ip) {
            return false;
        }

        $this->header = AttlaArr::toArray($header);
        $this->payload = $this->primitiveOrArray($payload);

        return $payload;
    }

    /**
     * Alias of decode
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function fromString(string $data, bool $assoc = false)
    {
        return $this->decode($data, $assoc);
    }

    /**
     * Alias of decode
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function parseString(string $data, bool $assoc = false)
    {
        return $this->decode($data, $assoc);
    }

    /**
     * Alias of decode
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    public function parse(string $data, bool $assoc = false)
    {
        return $this->decode($data, $assoc);
    }

    /**
     * Set JWT payload
     *
     * @param mixed $value
     * @return mixed
     */
    public function payload($value): self
    {
        $this->payload = $this->primitiveOrArray($value);
        return $this;
    }

    /**
     * Alias of payload
     *
     * @param mixed $value
     * @return mixed
     */
    public function body($value): self
    {
        return $this->payload($value);
    }

    /**
     * Set JWT secret
     *
     * @param string $secret
     * @return self
     */
    public function secret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Set JWT expiration time
     *
     * @param int|\Carbon\CarbonInterface $exp
     * @return self
     */
    public function exp(int|CarbonInterface $exp = 30): self
    {
        if ($exp instanceof CarbonInterface) {
            $exp = $exp->timestamp;
        }

        $this->header['exp'] = time() > $exp ? time() + ($exp * 60) : $exp;
        return $this;
    }

    /**
     * Set JWT iss validation
     *
     * @return self
     */
    public function iss(string $value = ''): self
    {
        $this->header['iss'] = $value ?: $_SERVER['HTTP_HOST'];
        return $this;
    }

    /**
     * Set JWT browser validation
     *
     * @return self
     */
    public function bwr(): self
    {
        $this->header['bwr'] = $this->getBrowser();
        return $this;
    }

    /**
     * Set JWT IP validation
     *
     * @return self
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
     * @return self
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
     * @return self
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
