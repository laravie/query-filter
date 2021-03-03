<?php

namespace Laravie\QueryFilter\Tests\Factories;

use Laravie\QueryFilter\Tests\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

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
