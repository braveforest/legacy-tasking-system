<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\Tasks::class, function (Faker\Generator $faker) {
    $status = [0, 2];
    return [
        'title'  => $faker->word,
        'status' => $status[array_rand($status)]
    ];
});
