<?php

use Attla\DataToken\Factory as DataToken;

$passphrase =   '12312';
$value =   'Now I am become Death, the destroyer of worlds.';
var_dump('encoded: ' . $encoded = DataToken::create()
                ->secret($passphrase)
                ->body($value)
                ->get());

var_dump('decoded: ' . DataToken::parse($encoded)->secret($passphrase)
->get());
die;

it('is valid token type', function ($value, $passphrase) {
    // expect(DataToken::secret($passphrase)
    //     ->decode(
    //         DataToken::secret($passphrase)
    //         ->payload($value)
    //         ->encode()
    //     ))->toEqual($value);

    $this->assertEquals(
        DataToken::parse(
            DataToken::create()
                ->secret($passphrase)
                ->body($value)
                ->get()
        )->secret($passphrase)
        ->get(),
        $value
    );
})->with('string')
->with('passphrases');

return;

it('each time generate a unique token?', function ($value, $passphrase) {
    $token = DataToken::new()
        ->secret($passphrase)
        ->body($value);

    $this->assertTrue($token->get() !== $token->get());
    // expect($token->encode() !== $token->encode())->toBeTrue();
})->with('string')
->with('passphrases');

it('have the correct value type?', function ($value, $passphrase) {

    // var_dump('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
    // var_dump($value, $passphrase);
    // $decoder = DataToken::secret($passphrase);

    // if (is_array($value)) {
    //     $decoder->associative();
    // }
    // $vv = $decoder->decode(
    //     DataToken::secret($passphrase)
    //         ->payload($value)
    //         ->encode()
    // );

    // var_dump('vvvvvvv: ');
    // var_dump($vv);

    $token = DataToken::new()
        ->secret($passphrase)
        ->content($value)
        ->get();
    $decoder = DataToken::decode($token)->secret($passphrase);
    $type = gettype($value);

    if ($type == 'array') {
        // TODO: talves add o tipo de retorno esperado no header
        // TODO: adicionar tbm a forma que o token foi feito
        $decoder->associative();
    }

    $this->assertTrue(gettype($decoder->get()) === $type);
})->with('varTypes')
->with('passphrase');
