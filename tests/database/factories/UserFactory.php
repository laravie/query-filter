<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Laravie\QueryFilter\Tests\Models\User;

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => Str::random(10),
        'address' => null,
    ];
});
