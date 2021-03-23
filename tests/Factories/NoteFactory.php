<?php

namespace Laravie\QueryFilter\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravie\QueryFilter\Tests\Models\Note;

class NoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->text(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
