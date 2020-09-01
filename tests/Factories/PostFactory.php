<?php

namespace Laravie\QueryFilter\Tests\Factories;

use Laravie\QueryFilter\Tests\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => UserFactory::new()->create(),
            'title' => $this->faker->text(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
