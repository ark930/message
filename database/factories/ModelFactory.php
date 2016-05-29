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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'user_name' => $faker->lastName,
        'nick_name' => $faker->firstName,
        'email' => $faker->safeEmail,
        'remember_token' => str_random(10),
        'tel' => mt_rand(13000000000,13999999999),
        'api_token' => str_random('60'),
    ];
});
