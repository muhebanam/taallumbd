<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'certificate_no' => 'CERT-'.strtoupper(fake()->unique()->bothify('##??##')),
            'issued_at' => now(),
            'file_path' => null,
        ];
    }
}
