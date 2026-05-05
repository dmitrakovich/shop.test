<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\TagGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * @var class-string<Tag>
     */
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'slug' => (string)Str::uuid(),
            'seo' => fake()->optional()->sentence(),
            'tag_group_id' => TagGroup::factory(),
        ];
    }

    /**
     * Attach tag to existing group id.
     */
    public function forGroup(int $tagGroupId): static
    {
        return $this->state(fn (array $attributes): array => [
            'tag_group_id' => $tagGroupId,
        ]);
    }
}
