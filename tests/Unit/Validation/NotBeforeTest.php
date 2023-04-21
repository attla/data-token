<?php

use Attla\Support\Envir;

$validations = Envir::get('TEST_VALIDATIONS');
$value = Envir::get('TEST_VALUE');

$nbf = fn($date) => $validations['generic']($date, 'nbf');
$validAt = fn($date, $now) => $validations['validAt']($date, $now, 'nbf');
$leeway = fn($leeway, $date) => $validations['leeway']($leeway, $date, 'nbf');

// it(
//     '"nbf" date is valid',
//     fn ($date) =>  $this->assertEquals($nbf($date), $value)
// )->with('before-dates')
// ->skip(!$validations || !$value);

// it(
//     '"nbf" at date is valid',
//     fn ($date, $now) => $this->assertEquals($validAt($date, $now), $value)
// )->with('after-at-dates')
// ->skip(!$validations || !$value);

// it(
//     '"nbf" with leeway is valid',
//     fn ($leewayTime, $date) => $this->assertEquals($leeway($leewayTime, $date), $value)
// )->with('after-leeways')
// ->skip(!$validations || !$value);

// it(
//     'invalid "nbf"',
//     fn ($date) => $this->assertFalse($nbf($date))
// )->with('after-dates')
// ->skip(!$validations);

// it(
//     'invalid "nbf" at date',
//     fn ($date, $now) => $this->assertFalse($validAt($date, $now))
// )->with('before-at-dates')
// ->skip(!$validations);

// it(
//     'invalid "nbf" leeway',
//     fn ($leewayTime, $date) => $this->assertFalse($leeway($leewayTime, $date))
// )->with('before-leeways')
// ->skip(!$validations);
