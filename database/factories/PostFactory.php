<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake('vi_VN')->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'excerpt' => fake('vi_VN')->sentence(),
            'body' => fake('vi_VN')->paragraphs(4, asText: true),
            'image_path' => null,
            'seo_title' => null,
            'seo_description' => null,
            'is_published' => true,
            'published_at' => now(),
        ];
    }
}
