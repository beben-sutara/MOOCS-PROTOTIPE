<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'thumbnail_path' => null,
            'instructor_id' => User::factory()->state([
                'role' => 'instructor',
            ]),
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => [
            'status' => 'pending_approval',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
        ]);
    }
}
