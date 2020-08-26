<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Laravie\QueryFilter\Tests\Models\Note;
use Laravie\QueryFilter\Tests\Models\User;

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Note::class, function (Faker $faker) {
    static $password;

    return [
        'title' => $faker->text(),
        'content' => $faker->paragraph(),
    ];
});
