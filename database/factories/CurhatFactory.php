<?php

namespace Database\Factories;

use App\Models\Curhat;
use App\Models\CurhatCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curhat>
 */
class CurhatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'category_id' => CurhatCategory::factory(),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'privacy_scope' => 'counselor',
            'counseling_request' => 'no',
            'status' => 'submitted',
        ];
    }

    public function assignedTo(User $counselor): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $counselor->id,
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
