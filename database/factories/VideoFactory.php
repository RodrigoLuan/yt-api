<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition()
    {
        return [
            'youtube_id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'embed_url' => $this->faker->url,
            'views' => $this->faker->numberBetween(100, 1000),
            'likes' => $this->faker->numberBetween(10, 100),
            'user_id' => User::factory(),  
        ];
    }
}
