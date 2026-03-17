<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'order' => fake()->numberBetween(1, 20),
            'is_locked' => false,
            'prerequisite_module_id' => null,
        ];
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'is_locked' => true,
        ]);
    }
}
