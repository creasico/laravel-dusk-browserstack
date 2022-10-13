<?php

namespace Creasi\Laravel\Factories;

use Creasi\Laravel\Files\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = File::class;

    public function definition()
    {
        $now = now();

        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->unique()->safeEmail(),
            'size' => now(),
            'url' => now(),
            'path' => now(),
            'mime' => now(),
            'type' => now(),
            'meta' => now(),
            'disk' => now(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
