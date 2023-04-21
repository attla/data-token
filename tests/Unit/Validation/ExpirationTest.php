<?php

use Attla\Support\Envir;

$validations = Envir::get('TEST_VALIDATIONS');
$value = Envir::get('TEST_VALUE');

$exp = fn($date) => $validations['generic']($date, 'exp');
$validAt = fn($date, $now) => $validations['validAt']($date, $now, 'exp');
$leeway = fn($leeway, $date) => $validations['leeway']($leeway, $date, 'exp');

return;
it(
    '"exp" is valid',
    fn ($date) => $this->assertEquals(
        $exp($date),
        $value
    )
)->with('after-dates')
->skip(!$validations || !$value);

it(
    '"exp" "at date" is valid',
    fn ($date, $now) => $this->assertEquals($validAt($date, $now), $value)
)->with('before-at-dates')
->skip(!$validations || !$value);
// it(
//     '"exp" "at date" is valid',
//     // function (
//     //     $before,
//     //     $descBefore,
//     //     $timeBefore,
//     //     $indexBefore,
//     //     $after,
//     //     $descAfter,
//     //     $timeAfter,
//     //     $indexAfter,
//     // ) {
//     //     var_dump('$before');
//     //     var_dump($before);
//     //     var_dump('$after');
//     //     var_dump($after);
//     //     var_dump('$descBefore');
//     //     var_dump($descBefore);
//     //     var_dump('$descAfter');
//     //     var_dump($descAfter);
//     //     var_dump('$timeBefore');
//     //     var_dump($timeBefore);
//     //     var_dump('$timeAfter');
//     //     var_dump($timeAfter);
//     //     var_dump('$indexBefore');
//     //     var_dump($indexBefore);
//     //     var_dump('$indexAfter');
//     //     var_dump($indexAfter);
//     //     if (
//     //         in_array('+10 sec', [$before,
//     //         $after,
//     //         $descBefore,
//     //         $descAfter,
//     //         $timeBefore,
//     //         $timeAfter,
//     //         $indexBefore,
//     //         $indexAfter])
//     //     ) {
//     //         die;
//     //     }
//     // }
//     // xxxxxxxxxxxxxxxxx
//     fn (
//         $before, $descBefore, $timeBefore, $indexBefore,
//         $after, $descAfter, $timeAfter, $indexAfter,
//     ) => $this->assertEquals(
//         $isLower = $timeAfter >= $timeBefore
//             ? 1
//             : $validAt($after, $before),
//         $isLower ? 2 : $value
//     )
// )->with('before-dates')
// ->with('after-dates')
// ->skip(!$validations || !$value);

it(
    '"exp" with leeway is valid',
    fn ($leewayTime, $date) => $this->assertEquals($leeway($leewayTime, $date), $value)
    // fn ($date, $desc) => $this->assertEquals(
    //     $leeway(abs(strtotime($desc, 0)) + 10, $date),
    //     $value
    // )
)->with('before-leeways')
->skip(!$validations || !$value);

it(
    'invalid "exp"',
    fn ($date) => $this->assertFalse($exp($date))
)->with('before-dates')
->skip(!$validations);

it(
    'invalid "exp" "at date"',
    fn ($date, $now) => $this->assertFalse($validAt($date, $now))
)->with('after-at-dates')
->skip(!$validations);

it(
    'invalid "exp" leeway',
    fn ($leewayTime, $date) => $this->assertFalse($leeway($leewayTime, $date))
)->with('after-leeways')
->skip(!$validations);
