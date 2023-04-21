<?php

namespace Attla\DataToken;

use Attla\Pincryp\Factory as Pincryp;
use Attla\Support\{
    Arr as AttlaArr,
    Str as AttlaStr,
    AbstractData
};
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class Token extends AbstractData
{
    /**
     * The header of the token
     *
     * @var \Attla\Support\AbstractData
     */
    public $header = [];

    /**
     * The claims of token
     *
     * @var \Attla\Support\AbstractData
     */
    public $claims = [];

    /**
     * The body of the token
     *
     * @var mixed
     */
    public $body;

    /**
     * The signature of the token
     *
     * @var string
     */
    public $signature = '';

    // /**
    //  * The secret passphrase
    //  *
    //  * @var string
    //  */
    // public $secret = '';

    /**
     * Determine if the token is on same mode
     *
     * @var bool
     */
    public $same = false;

    /**
     * When checking nbf, iat or expiration times,
     * we want to provide some extra leeway time to
     * account for clock skew.
     *
     * @var int
     */
    public $leeway = 0;

    /**
     * Determine if the body has an associative array.
     *
     * @var bool
     */
    public $associative = false;

    /**
     * Undefined identification.
     *
     * @var string
     */
    public $undefined = '!@#undefined#@!';

    /**
     * Pincryp instance
     *
     * @var Attla\Pincryp\Factory
     */
    public $pincryp;

    /**
     * Create a new Token instance
     *
     * @param object|array $source
     * @return void
     */
    public function __construct(object|array $source = [])
    {
        parent::__construct($source);
        $this->pincryp = new Pincryp();
        // $this->set('pincryp', new Pincryp());
    }

    /**
     * Set secret key
     *
     * @param string $secret
     * @return void
     */
    public function secret(string $key)
    {
        $this->pincryp->config->key = $key;
        // $this->set('pincryp', new Pincryp());
    }

    /**
     * Set header value
     *
     * @param object|array $header
     * @return AbstractData
     */
    public function setHeader(object|array $header = []): AbstractData
    {
        return new AbstractData($header);
    }

    /**
     * Set claims value
     *
     * @param object|array $claims
     * @return AbstractData
     */
    public function setClaims(object|array $claims = []): AbstractData
    {
        return new AbstractData($claims);
    }

    /**
     * Alias to set header value
     *
     * @param string $header
     * @return $this
     */
    public function header(string $header = null): self
    {
        if (($header = $this->pincryp->decode($header)) instanceof \stdClass) {
        // if (($header = Pincryp::decode($header, $this->secret)) instanceof \stdClass) {
            $this->set('header', $header);
        }

        return $this;
    }

    /**
     * Get encode header value
     *
     * @return string
     */
    public function encodedHeader()
    {
        $this->newEntropy();

        return $this->pincryp->encode(
            $this->same ? $this->header : AttlaArr::randomized($this->header)
        );
    }

    /**
     * Alias to set body value
     *
     * @param mixed $body
     * @return $this
     */
    public function body($body = null)
    {

        if ($body = $this->pincryp->onceKey($this->header->e)->decode($body, $this->associative)) {
        // if ($body = $this->pincryp->decode($body, $this->header->e ?? '', $this->associative)) {
            $this->set('body', $body);
        }

        return $this;
    }

    /**
     * Get encode body value
     *
     * @return string
     */
    public function encodedBody()
    {
        return $this->pincryp
            ->onceKey($this->entropy())
            ->encode($this->body);
        // return Pincryp::encode($this->body, $this->entropy());
    }

    /**
     * Get token signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->hash(
            $this->resolvekWithSameData(fn() => $this->encodedHeader()) . $this->encodedBody(),
            $this->secret . $this->entropy()
        );
    }

    /**
     * Get callable result on same config
     *
     * @param callable $callable
     * @return mixed
     */
    private function resolvekWithSameData(callable $callable)
    {
        $old = $this->same;
        $this->same = true;

        $result = $callable();

        $this->same = $old;

        return $result;
    }

    /**
     * Check if the signature is valid
     *
     * @return bool
     */
    public function signed()
    {
        if (!$signature = $this->signature) {
            return false;
        }

        // var_dump('body 1: ' . $this->encodedBody());
        // var_dump('sign 1: ' . $signature);
        // var_dump('entropy 1: ' . $this->entropy());
        // $liveSignature = $this->getSignature();
        // var_dump('body 2: ' . $this->encodedBody());
        // var_dump('sign 2: ' . $liveSignature);
        // var_dump('entropy 2: ' . $this->entropy());

        return $signature === $this->getSignature();
    }

    /**
     * Check if the token is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->get('body') || !$this->signed()) {
            // var_dump((string)spl_object_id($this) . ': ' .$this->get('payload'));
            // var_dump($this->signed());
            // var_dump(222);
            return false;
        }

        return $this->validateClaims();
    }

    /**
     * Check if the token is invalid
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return !$this->isValid();
    }

    /**
     * Check all validations of the token
     *
     * @return bool
     */
    public function validateClaims(): bool
    {
        // var_dump($this->header->all());
        // var_dump($this->claims->getInt(Claims::NOW, time()));
        // var_dump($this->isExpired($now = $this->claims->getInt(Claims::NOW, time())));
        // die;
        if (
            $this->isExpired($now = $this->claims->getInt(Claims::NOW, time()))
            || !$this->notBefore($now)
            || !$this->issuedBefore($now)
            // || $this->invalidAudience()
            //checks
        ) {
            return false;
        }

        return true;
    }

    private function leeway()
    {
        return $this->getInt('leeway');
    }

    // problema ta aqui
    // private function setLeeway(int $leeway)
    // {
    //     // var_dump($leeway);
    //     return $leeway;
    // }

    /**
     * Check if the token is expired
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    public function isExpired(int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        if (!$this->header->has($claim = Claims::EXPIRATION_TIME)) {
            return false;
        }

        if ($this->leeway()) {
            // var_dump($this->leeway());
            // die;

            // // Check if this token has expired.
            // if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
            //     throw new ExpiredException('Expired token');
            // }


            // Check the nbf if it is defined. This is the time that the
            // token can actually be used. If it's not yet that time, abort.
            // if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
            //     throw new BeforeValidException(
            //         'Cannot handle token prior to ' . \date(DateTime::ISO8601, $payload->nbf)
            //     );
            // }

            // // Check that this token has been created before 'now'. This prevents
            // // using tokens that have been created for later use (and haven't
            // // correctly used the nbf claim).
            // if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
            //     throw new BeforeValidException(
            //         'Cannot handle token prior to ' . \date(DateTime::ISO8601, $payload->iat)
            //     );
            // }
        }
        return Util::timestamp($date) - $this->leeway() >= $this->header->getInt($claim);
    }

    /**
     * Check if the token has valid before a date
     *
     * @param string $claim
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    private function validAfter(string $claim, int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        if (!$this->header->has($claim)) {
            return true;
        }

        // var_dump(1 + 1 >= 3);

        $id = spl_object_id($this);
        // var_dump(
        //     $id . ' - date:::' . $date,
        //     $id . ' - leeway:::' . $this->leeway(),
        //     $id . ' - claim:::' . $this->header->getInt($claim),
        //     $id . ' - return:::' . (
        //     Util::timestamp($date) + $this->leeway() >= $this->header->getInt($claim) ? 'true' : 'false'
        // ),
        // );
        // var_dump(Util::timestamp($date) + $this->leeway() >= (int) $this->header->get($claim));
        // var_dump($date, Util::timestamp($date), $claim, $this->leeway(), (int) $this->header->get($claim));

        // if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
        //     throw new BeforeValidException(
        //         'Cannot handle token prior to ' . \date(DateTime::ISO8601, $payload->iat)
        //     );
        // }

        // public function isMinimumTimeBefore(DateTimeInterface $now): bool
        // {
        //     return $now >= $this->claims->get(RegisteredClaims::NOT_BEFORE);
        // }
        return Util::timestamp($date) + $this->leeway() >= $this->header->getInt($claim);
    }

    /**
     * Check if the token has not before a date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    public function notBefore(int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        return $this->validAfter(Claims::NOT_BEFORE, $date);
    }

    /**
     * Check if the token has issued before a date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    public function issuedBefore(int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        return $this->validAfter(Claims::ISSUED_AT, $date);
    }


    // não testado
    /**
     * Check if the token is permitted to audience
     *
     * @param string $audience
     * @return bool
     */
    public function isPermittedFor(string $audience): bool
    {
        return in_array($audience, $this->header->getArray(Claims::AUDIENCE), true);
    }

    /**
     * Check the identity token
     *
     * @param string $id
     * @return bool
     */
    public function isIdentifiedBy(string $id): bool
    {
        return $this->header->get(Claims::ID) === $id;
    }

    /**
     * Check the sbject of token
     *
     * @param string $subject
     * @return bool
     */
    public function isRelatedTo(string $subject): bool
    {
        return $this->header->get(Claims::SUBJECT) === $subject;
    }

    // ver como verificar isso
    public function hasBeenIssuedBy(string ...$issuers): bool
    {
        return in_array($this->header->getArray(Claims::ISSUER), $issuers, true);
    }

    /**
     * Transform the date to a timestamp
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return int
     */
    // public function timestamp(int|CarbonInterface|\DateTimeInterface $date = null): int
    // {
    //     if (is_null($date)) {
    //     } elseif (is_int($date)) {
    //         return $date;
    //     } elseif ($date instanceof CarbonInterface) {
    //         return $date->timestamp;
    //     } elseif ($date instanceof \DateTimeInterface) {
    //         return $date instanceof \DateTimeImmutable
    //             ? CarbonImmutable::instance($date)->timestamp
    //             : Carbon::instance($date)->timestamp;
    //     }

    //     return time();
    // }

    /**
     * Get entropy token
     *
     * @return string
     */
    private function entropy()
    {
        // var_dump((string)spl_object_id($this) . ': ' . $this->header->e ?? 'iiiiiiiiiiiiiiiiii');
        return $this->header->e ?? $this->newEntropy();
    }

    /**
     * Generate new entropy token
     *
     * @return string
     */
    private function newEntropy()
    {
        if ($this->same && !empty($this->header->e)) {
            return $this->header->e;
        }

        // error_log('xxxx newEntropy backtrace ' . var_export(debug_backtrace(2, 10), true));
        // error_log('xxxx newEntropy header: ' . gettype($this->header));
        return $this->header->e = AttlaStr::randHex(6);


        $this->header->set('e', AttlaStr::randHex(6));
        return $this->header->e;
    }

    /**
     * Set token to same mode
     *
     * @param string $entropy
     * @return $this
     */
    public function same(string $entropy = ''): self
    {
        $this->same = true;
        $this->header->e = $this->hash($entropy ?: $this->secret);

        return $this;
    }

    private function hash($data, string $secret = null)
    {
        $pincryp = is_null($secret) ? $this->pincryp : $this->pincryp->onceKey($secret);

        return $pincryp->encode(md5((string) $data, true));
    }

    // /**
    //  * Return token as string.
    //  *
    //  * @return string
    //  */
    // public function __toString(): string
    // {
    //     return $this->encodedHeader()
    //         . '.' . $this->encodedBody()
    //         . '.' . $this->signature();
    // }
}
