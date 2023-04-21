<?php

use Attla\Support\Envir;

// $validations = Envir::get('TEST_VALIDATIONS');
// $value = Envir::get('TEST_VALUE');

// $iat = fn($date) => $validations['generic']($date, 'iat');
// $validAt = fn($date, $now) => $validations['validAt']($date, $now, 'iat');
// $leeway = fn($leeway, $date) => $validations['leeway']($leeway, $date, 'iat');

// it(
//     '"iat" date is valid',
//     fn ($date) =>  $this->assertEquals($iat($date), $value)
// )->with('before-dates')
// ->skip(!$validations || !$value);

// it(
//     '"iat" at date is valid',
//     fn ($date, $now) => $this->assertEquals($validAt($date, $now), $value)
// )->with('after-at-dates')
// ->skip(!$validations || !$value);

// it(
//     '"iat" with leeway is valid',
//     fn ($leewayTime, $date) => $this->assertEquals($leeway($leewayTime, $date), $value)
// )->with('after-leeways')
// ->skip(!$validations || !$value);

// it(
//     'invalid "iat"',
//     fn ($date) => $this->assertFalse($iat($date))
// )->with('after-dates')
// ->skip(!$validations);

// it(
//     'invalid "iat" at date',
//     fn ($date, $now) => $this->assertFalse($validAt($date, $now))
// )->with('before-at-dates')
// ->skip(!$validations);

// it(
//     'invalid "iat" leeway',
//     fn ($leewayTime, $date) => $this->assertFalse($leeway($leewayTime, $date))
// )->with('before-leeways')
// ->skip(!$validations);
