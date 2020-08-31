<?php

use Faker\Generator as Faker;
use Laravie\QueryFilter\Tests\Models\Note;

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Note::class, function (Faker $faker) {
    static $password;

    return [
        'title' => $faker->text(),
        'content' => $faker->paragraph(),
    ];
});
