<?php

use Hashids\Hashids;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Attla\{
    DataToken\Util,
    DataToken\Factory as DataToken,
    Pincryp\Factory as Pincryp,
    Support\Envir,
    Support\Str as AttlaStr
};
use Illuminate\Support\Stringable;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// function something()
// {
//     // ..
// }

/*
setup test
*/

$value = 'Now I am become Death, the destroyer of worlds.';

Envir::set('APP_KEY', AttlaStr::randHex());

Envir::set('TEST_SECRET', $secret = str_shuffle($value));
Envir::set('TEST_VALUE', $value);

$apply = fn($token, $method, ...$args) => $token->{$method}(...$args);
$parse = fn($token) => DataToken::parse($token)->secret($secret);
$get = fn($token) => $token->get();
$getToken = fn() => Envir::get(
    'TEST_TOKEN',
    DataToken::create()
        ->secret($secret)
        ->body($value)
);

$validation = fn($date, $method) => $get($parse($get($apply($getToken(), $method, $date))));
$validAt = fn($date, $now, $method) => $get($apply($parse(
    $get($apply($getToken(), $method, $date))
), 'validAt', $now));
$leewayDate = fn($leeway, $date, $method) => $get($apply($parse(
    $get($apply($getToken(), $method, $date))
), 'leeway', $leeway));

$compareValidation = fn($value, $method) => $get($parse(
    $apply($getToken(), $method, $value),
    $get($apply($getToken(), $method, $value))
));

Envir::set(
    'TEST_VALIDATIONS',
    [
        'generic' => fn($date, $method) => $get($parse($get($apply($getToken(), $method, $date)))),

        'leeway' => fn($leeway, $date, $method) => $get($apply($parse(
            $get($apply($getToken(), $method, $date))
        ), 'leeway', $leeway)),

        'validAt' => fn($date, $now, $method) => $get($apply($parse(
            $get($apply($getToken(), $method, $date))
        ), 'validAt', $now)),

        // OLD
        // TODO: talvez mandar essas funções pra os test
        // 'leewayExp' => fn($leeway, $date) => $leewayDate($leeway, $date, 'exp'),
        // 'leewayNbf' => fn($leeway, $date) => $leewayDate($leeway, $date, 'nbf'),
        // 'leewayIat' => fn($leeway, $date) => $leewayDate($leeway, $date, 'iat'),
        // 'exp' => fn($date) => $validation($date, 'exp'),
        // 'validAtExp' => fn($date, $now) => $validAt($date, $now, 'exp'),
        // 'nbf' => fn($date) => $validation($date, 'nbf'),
        // 'validAtNbf' => fn($date, $now) => $validAt($date, $now, 'nbf'),
        // 'iat' => fn($date) => $validation($date, 'iat'),
        // 'validAtIat' => fn($date, $now) => $validAt($date, $now, 'iat'),
        // 'aud' => fn($value) => $compareValidation($value, 'aud'),
    ]
);

uses()
->beforeAll(function () use ($value) {
    //before all things
})->beforeEach(function () use ($value, $secret) {
    Envir::set(
        'TEST_TOKEN',
        DataToken::create()
            ->secret($secret)
            ->body($value)
    );
})->in('Unit', 'Unit/Validation');

dataset('string', $charTypes = [
    'alfa' => $value,
    'alfanum' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'special chars' => '`~!@#$%^&*()\\][+={}/|:;"\'<>,.?-_',
    'acents' => 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
]);

dataset('passphrases', $charTypes);

dataset('value', [$value]);
dataset('passphrase', [$value]);

// methods
// dataset('encode-methods', $encodeMethods = [
//     'encode',
// ]);

// dataset('decode-methods', $decodeMethods = [
//     'decode',
//     'fromString',
//     'parseString',
//     'parse',
// ]);

// dataset('build-methods', $buildMethods = [
//     'payload',
//     'body',
//     'secret',
//     'same',
// ]);

// dataset('validation-methods', $validationMethods = [
//     'exp',
//     'iss',
//     'bwr',
//     'ip',
// ]);

// dataset('aliases-methods', $aliasMethods = [
//     'sign',
//     'id',
//     'sid',
// ]);

// dataset('public-methods', array_merge(
//     $encodeMethods,
//     $decodeMethods,
//     $buildMethods,
//     $validationMethods,
//     $aliasMethods,
//     [
//         'getEntropy',
//         '__toString',
//     ],
// ));

// validations


$dateLabels = ['now', '10 sec', '30 min', '1 hour', '1 day', '1 week'];
$dateTransform = [
    fn() => time(),
    fn($str) => strtotime($str),
    fn($str) => Util::strToCarbon($str),
    fn($str) => Util::strToCarbonImmutable($str),
    fn($str) => Util::strToDateTime($str),
    fn($str) => Util::strToDateTimeImmutable($str),
];

// $afterDates = array_combine(
//     array_slice($dateLabels, 1),
//     array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now,
//     ]], $afterDates, $beforeDates)
// )

dataset('before-dates', $beforeDates = array_combine(
    $dateLabels,
    array_map(
        fn($label, $transform) => $transform(($label != 'now' ? '-' : '') . $label),
        $dateLabels,
        $dateTransform
    )
));

dataset('after-dates', $afterDates = array_combine(
    $keys = array_slice($dateLabels, 1),
    array_map(
        fn($label, $transform) => $transform('+' . $label),
        $keys,
        array_slice($dateTransform, 1)
    )
));

// var_dump(
//     count(array_slice($dateLabels, 1)),
//     count(array_values($afterDates)),
//     count(array_values($beforeDates = array_slice($beforeDates, 1))),
//     count(Arr::crossJoin(
//         array_values($afterDates),
//         array_values($beforeDates = array_slice($beforeDates, 1))
//     )),
//     array_map(fn($date, $now) => [$date, $now], [1,2], [3,4])
// );
// die;
dataset('before-at-dates', array_combine(
    $keys,
    array_map(
        fn($date, $now) => [$date, $now],
        $afterDatesValues = array_values($afterDates),
        $beforeDatesValues = array_values(array_slice($beforeDates, 1))
    )
));
// $dsd = array_combine(
//     $keys = array_keys($beforeDates = array_slice($beforeDates, 1)),
//     array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now,
//     ]], $afterDates, $beforeDates)
// )
// var_dump($dsd);
// die;
dataset('after-at-dates', array_combine(
    $keys,
    array_map(
        fn($date, $now) => [$date, $now],
        $beforeDatesValues,
        $afterDatesValues
    )
));

// array_combine(
//     $keys = array_keys($afterDates),
//     $vcvc = array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now,
//     ]], $beforeDates, $afterDates)
// )




dataset('before-leeways', array_combine(
    $keys,
    array_map(
        fn($key, $value) => [abs(strtotime($key, 0)) / 2, $value],
        $keys,
        $afterDatesValues
    )
));

dataset('after-leeways', array_combine(
    $dateLabels,
    array_map(
        fn($key, $value) => [abs(strtotime($key, 0)), $value],
        $dateLabels,
        $beforeDates
    )
));




// dataset('after-dates', $afterDates = [
//     $desc = 'now'     => [$now = time(), $desc, $now, 0],
//     $desc = '+10 sec' => [$value = $now + 10, $desc, $value, 1],
//     $desc = '+30 min' => [$value = Carbon::now()->addMinutes(30), $desc, Util::timestamp($value), 2],
//     $desc = '+1 hour' => [$value = strtotime($desc), $desc, Util::timestamp($value), 3],
//     $desc = '+1 day'  => [
//         $value = date_create('@' . strtotime($desc)),
//         $desc,
//         Util::timestamp($value),
//         4
//     ],
//     $desc = '+1 week' => [
//         $value = date_create_immutable('@' . strtotime($desc)),
//         $desc,
//         Util::timestamp($value),
//         5
//     ],
// ]);

// dataset('after-dates-with-keys', $xxxx = Arr::crossJoin(
//     array_values($afterDates),
//     array_keys($afterDates)
// ));

// dataset('after-leeways', []);
// array_combine(
//     $keys = array_keys($afterDates),
//     array_map(fn($key, $value) => ['v' => [
//         'leeway' => abs(strtotime($key, 0)),
//         'date' => $value,
//     ]], $keys, $afterDates)
// )


// dataset('before-dates', $beforeDates = [
//     $desc = 'now'     => [$now, $desc, $now, 0],
//     $desc = '-10 sec' => [$value = $now - 10, $desc, $value, 1],
//     $desc = '-30 min' => [$value = Carbon::now()->subMinutes(30), $desc, Util::timestamp($value), 2],
//     $desc = '-1 hour' => [$value = strtotime($desc), $desc, Util::timestamp($value), 3],
//     $desc = '-1 day'  => [
//         $value = date_create('@' . strtotime($desc)),
//         $desc,
//         Util::timestamp($value),
//         4
//     ],
//     $desc = '-1 week' => [
//         $value = date_create_immutable('@' . strtotime($desc)),
//         $desc,
//         Util::timestamp($value),
//         5
//     ],
// ]);

// dataset('before-leeways', []);
// array_combine(
//     $keys = array_keys($beforeDates),
//     $beforeLeeways = array_map(fn($key, $value) => ['v' => [
//         'leeway' => abs(strtotime($key, 0)) + 10,
//         'date' => $value,
//     ]], $keys, $beforeDates)
// )


// dataset('before-at-date', array_combine(
//     $keys = array_keys($afterDates),
//     array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now,
//     ]], array_slice($beforeDates, 1), $afterDates)
// ));



// $afterDates = [
//     $desc = 'now'     => $now = time(),
//     $desc = '+10 sec' => $now + 10,
//     $desc = '+30 min' => Carbon::now()->addMinutes(30),
//     $desc = '+1 hour' => strtotime($desc),
//     $desc = '+1 day'  => date_create('@' . strtotime($desc)),
//     $desc = '+1 week' => date_create_immutable('@' . strtotime($desc)),
// ];

// $beforeDates = [
//     $desc = 'now'     => $now,
//     $desc = '-10 sec' => $now - 10,
//     $desc = '-30 min' => Carbon::now()->subMinutes(30),
//     $desc = '-1 hour' => strtotime($desc),
//     $desc = '-1 day'  => date_create('@' . strtotime($desc)),
//     $desc = '-1 week' => date_create_immutable('@' . strtotime($desc)),
// ];

// dataset('before-at-dates', []);
// $dsd = array_combine(
//     $keys = array_keys($beforeDates = array_slice($beforeDates, 1)),
//     array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now,
//     ]], $afterDates, $beforeDates)
// )
// var_dump($dsd);
// die;
// dataset('after-at-dates', []);
// array_combine(
//     $keys = array_keys($afterDates),
//     $vcvc = array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now,
//     ]], $beforeDates, $afterDates)
// )

// invalid
// dataset('before-at-date', array_combine(
//     $keys = array_keys($beforeDates),
//     array_map(fn($date, $now) => ['v' => [
//         'date' => $date,
//         'now' => $now['v']['date'],
//     ]], $beforeDates, $beforeLeeways)
// ));

dataset('varTypes', [
    'string' => $value,
    'integer' => 42,
    'float' => 4.2,
    'array (sequential)' => ['v' => [4,2]],
    'array (associative)' => $assoc = ['v' => ['four' => 4,'two' => 2]],
    'object (stdClass)' => (object) $assoc['v'],
    // 'null' => null,
    // 'boolean (FALSE)' => false,
    // 'boolean (TRUE)' => true,
]);

// dataset('valid-not-before-dates', $beforeDates);
// dataset('invalid-not-before-dates', $afterDates);




// // ENCODE / CRIAÇÂO
// DataToken::create()
//     ->secret('your secret phrase')
//     ->body($value)
//     ->validAt($date)
//     ->identifiedBy('123123')
//     ->get(); // obtem a string do token

// // DECODE / PARSE
// DataToken::parse(
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.'
//     . 'eyJzdWIiOiIxMjM0NTY3ODkwIn0.'
//     . '2gSBz9EOsQRN9I-3iSxJoFt7NtgV6Rm0IL6a8CAwl3Q'
// )->isValidAt($date)
// ->isIdentifiedBy('123123')
// ->get(); // retorna o valor do token


// // dates
// //verifies the claims iat, nbf, and exp, when present (supports leeway configuration)
// ->validAt($date)
// ->looseValidAt($date)

// // verifies if the claim jti matches the expected value
// ->identifiedBy('123123')

// // verifies if the claim aud contains the expected value
// ->permittedFor('123123')
// ->permittedFor(['123123', 312321, 4.3])


// // verifies if the claim iss is listed as expected values
// ->issuedBy('123123')
// ->issuedBy(['123123', 312321, 4.3])

// // verifies if the claim sub matches the expected value
// ->relatedTo('123123')

// // Configures a new claim, called "uid"
// ->withClaim('uid', 1)

// // HasClaimWithValue


// Envir::set('TEST_SECRET', Pincryp::generateKey());
// Envir::set('TEST_VALUE', Pincryp::generateKey());
// DataToken::parse(
// )->secret($passphrase)
// ->get(),

// var_dump(DataToken::create()
// ->secret($passphrase = '11111111111111')
// ->payload('2222222222222')->leeway(10));
// die;

// $token =
// DataToken::create()
//     ->secret($passphrase = '11111111111111')
//     ->payload('2222222222222')
//     ->leeway(10)
//     ->get();


// var_dump(
//     DataToken::parse($token)
//         ->secret($passphrase)
//         ->get()
// );
// die;




// $token = DataToken::create()
//     ->phrase($secret = 1111111)
//     ->body($value = 2222222);

// $apply = fn($token, $method, ...$args) => $token->{$method}(...$args);
// $parse = fn($token) => DataToken::parse($token)->phrase($secret);
// $get = fn($token) => $token->get();

// $validation = fn($date, $method) => $get($parse($get($apply($token, $method, $date))));


// var_dump($apply($token, 'leeway', 10000));


// var_dump($validation(time() - 10, 'exp'));
// die;


// leeway
// $leewayDate = fn($leeway, $date, $method) => $get($apply($parse(
//     $get($apply($getToken(), $method, $date))
// ), 'leeway', $leeway));


// var_dump($apply($getToken(), 'iat', 10000));
// die;


// var_dump($leewayDate(210, time() + 200, 'iat'));
// die;


// order seed
// $arr = array(0,1,2,3,4,5,6);
// $assoc = [
//     'user' => [
//         'name' => 'nicolau',
//         'id' => 123,
//         'city' => 22
//     ],
//     'postscc' => 'um',
//     'drama' => 'dois',
//     'pane' => 'trez',
//     'faz' => 'quartp',
//     'isso' => 'cinco',
//     'ai' => 'seis',
// ];
// $seed = 123456;


// function sortBySeed(array|string $data, int|null $seed = null): array|string
// {
//     if (is_null($seed)) {
//         return $data;
//     }

//     mt_srand($seed);

//     $isArray = is_array($data);
//     $sorted = !$isArray ? str_split($data) : $data;

//     $size = count($sorted);
//     array_multisort(array_map(fn () => mt_rand(), range(1, $size)), $sorted);

//     return $isArray == 'array' ? $sorted : implode('', $sorted);
// }


// (new Attla\Support\ServiceProvider())->register();
// var_dump((new Stringable($value))->sortBySeed($seed));
// die;
// var_dump(sortBySeed($arr, $seed));
// var_dump(sortBySeed($value, $seed));
// var_dump(sortBySeed($assoc, $seed));
// die;

// function seedsort(array|string $value, int $seed = null): array|string
// {
//     $isArray = is_array($value);
//     if (is_null($seed) || $isArray && Arr::isAssoc($value)) {
//         return $value;
//     }

//     mt_srand($seed);

//     if (!$isArray) {
//         $value = str_split($value);
//     }

//     $size = count($value);
//     array_multisort(array_map(fn () => mt_rand(), range(1, $size)), $value);

//     return $isArray == 'array' ? $value : implode('', $value);
// }

// mt_srand('42');
// $order = array_map(fn () => mt_rand(), range(1, count($arr)));
// // $order = array_map(create_function('$val', 'return mt_rand();'), range(1, count($arr)));
// array_multisort($order, $arr);

// var_dump($arr);
// die;



// $hashids = new Hashids('', 12, '102@#$%!¨&*()_-+=.>,<;:"');

// foreach (
//     [42,123,2233,0,1,

//     'teste'] as $value
// ) {
//     $encoded = $hashids->encode($value); // o2fXhV
//     $decoded = $hashids->decode($encoded); // [1, 2, 3]

//     var_dump("value::: " . $value);
//     var_dump("value::: " . $encoded);
//     var_dump("value::: " . var_export($decoded, 1));
// }

// var_dump($hashids->decode('NOY7P1hNhj'));

// $encoded = $hashids->encode(19272763, 2, 3); // o2fXhV
// $decoded = $hashids->decode($encoded); // [1, 2, 3]


// var_dump($value);
// var_dump($encoded);

// var_dump($decoded);

// die;
