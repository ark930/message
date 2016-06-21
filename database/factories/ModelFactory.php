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

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'user_name' => $faker->lastName,
        'display_name' => $faker->firstName,
        'email' => $faker->safeEmail,
        'tel' => mt_rand(13000000000,13999999999),
        'type' => 'person',
    ];
});
