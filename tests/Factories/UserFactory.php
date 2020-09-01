<?php

namespace Laravie\QueryFilter\Tests\Factories;

use Illuminate\Support\Str;
use Laravie\QueryFilter\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the password.
     *
     * @var string
     */
    protected static $password;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => static::$password ?: static::$password = bcrypt('secret'),
            'remember_token' => Str::random(10),
            'address' => null,
        ];
    }
}
