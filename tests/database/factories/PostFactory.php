<?php

use Faker\Generator as Faker;
use Laravie\QueryFilter\Tests\Models\Post;
use Laravie\QueryFilter\Tests\Models\User;

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Post::class, function (Faker $faker) {
    static $password;

    return [
        'user_id' => \factory(User::class)->create(),
        'title' => $faker->text(),
        'content' => $faker->paragraph(),
    ];
});
