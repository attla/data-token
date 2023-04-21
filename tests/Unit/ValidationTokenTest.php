<?php

// use Attla\Support\Envir;
// use Attla\DataToken\Util;
// use Attla\DataToken\Factory as DataToken;

// beforeEach(function () {
//     $this->token = DataToken::create()
//         ->secret($secret = Envir::get('TEST_SECRET'))
//         ->body($this->value = Envir::get('TEST_VALUE'));

//     // $validation = fn($date, $fn) => $this->token->decode($this->token->{$fn}($date)->encode());
//     // $apply = fn($method, ...$args) => $this->token->{$method}(...$args);
//     // $decode = fn($value) => $this->token->decode($value);
//     // $encode = fn() => $this->token->encode();

//     // antigo
//     // $apply = fn($token, $method, ...$args) => $token->{$method}(...$args);
//     // $decode = fn($token, $value) => $token->decode($value);
//     // $encode = fn($token) => $token->encode();
//     // novo
//     $apply = fn($token, $method, ...$args) => $token->{$method}(...$args);
//     $parse = fn($token) => DataToken::parse($token)->secret($secret);
//     $get = fn($token) => $token->get();

//     $validation = fn($date, $method) => $get($parse($get($apply($this->token, $method, $date))));
//     $validAt = fn($date, $now, $method) => $get($apply($parse(
//         $get($apply($this->token, $method, $date))
//     ), 'validAt', $now));
//     $leewayDate = fn($leeway, $date, $method) => $get($apply($parse(
//         $get($apply($this->token, $method, $date))
//     ), 'leeway', $leeway));

//     $compareValidation = fn($value, $method) => $get($parse(
//         $apply($this->token, $method, $value),
//         $get($apply($this->token, $method, $value))
//     ));

//     $this->validations = [
//         'leewayExp' => fn($leeway, $date) => $leewayDate($leeway, $date, 'exp'),
//         'leewayNbf' => fn($leeway, $date) => $leewayDate($leeway, $date, 'nbf'),
//         'leewayIat' => fn($leeway, $date) => $leewayDate($leeway, $date, 'iat'),
//         'exp' => fn($date) => $validation($date, 'exp'),
//         'validAtExp' => fn($date, $now) => $validAt($date, $now, 'exp'),
//         'nbf' => fn($date) => $validation($date, 'nbf'),
//         'validAtNbf' => fn($date, $now) => $validAt($date, $now, 'nbf'),
//         'iat' => fn($date) => $validation($date, 'iat'),
//         'validAtIat' => fn($date, $now) => $validAt($date, $now, 'iat'),
//         'aud' => fn($value) => $compareValidation($value, 'aud'),
//     ];
// });

// // expiration
// it('expiration is valid', function ($date) {
//     $this->assertEquals(
//         $this->validations['exp']($date),
//         $this->value
//     );
// })->with('after-dates');

// it('expiration at date is valid', function ($data) {
//     // var_dump(
//     //     date('d/m/Y H:i:s', Util::timestamp($data['date'])),
//     //     date('d/m/Y H:i:s', Util::timestamp($data['now']))
//     // );
//     $this->assertEquals(
//         $this->validations['validAtExp']($data['date'], $data['now']),
//         $this->value
//     );
// })->with('before-at-dates');

// it('expiration with leeway is valid?', function ($data) {
//     $this->assertEquals(
//         $this->validations['leewayExp']($data['leeway'], $data['date']),
//         $this->value
//     );
// })->with('before-leeways');

// it('expiration is invalid', function ($date) {
//     $this->assertFalse($this->validations['exp']($date));
// })->with('before-dates');

// it('invalid expiration at date', function ($data) {
//     $this->assertFalse($this->validations['validAtExp']($data['date'], $data['now']));
// })->with('after-at-dates');

// // not before
// it('not before date is valid', function ($date) {
//     $this->assertEquals(
//         $this->validations['nbf']($date),
//         $this->value
//     );
// })->with('before-dates');

// it('not before at date is valid', function ($data) {
//     $this->assertEquals(
//         $this->validations['validAtNbf']($data['date'], $data['now']),
//         $this->value
//     );
// })->with('after-at-dates');

// it('not before with leeway is valid?', function ($data) {
//     $this->assertEquals(
//         $this->validations['leewayNbf']($data['leeway'], $data['date']),
//         $this->value
//     );
// })->with('after-leeways');

// it('not before date is invalid', function ($date) {
//     $this->assertFalse($this->validations['nbf']($date));
// })->with('after-dates');

// it('invalid not before at date', function ($data) {
//     $this->assertFalse($this->validations['leewayNbf']($data['date'], $data['now']));
// })->with('before-at-dates');

// // issued before
// it('issued before date is valid', function ($date) {
//     $this->assertEquals(
//         $this->validations['iat']($date),
//         $this->value
//     );
// })->with('before-dates');

// it('issued before with leeway is valid?', function ($data) {
//     $this->assertEquals(
//         $this->validations['leewayIat']($data['leeway'], $data['date']),
//         $this->value
//     );
// })->with('after-leeways');

// it('issued before date is invalid', function ($date) {
//     $this->assertFalse($this->validations['iat']($date));
// })->with('after-dates');

// // audience
// // it('audience is valid', function ($info) {
// //     $this->assertEquals(
// //         $this->validations['aud']($info),
// //         $this->value
// //     );
// // })->with('varTypes');
