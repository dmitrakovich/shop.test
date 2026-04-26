<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\TagGroupResource;
use App\Models\Tag;
use App\Models\TagGroup;
use Filament\Resources\Pages\CreateRecord;

class CreateTagGroup extends CreateRecord
{
    protected static string $resource = TagGroupResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['tag_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var TagGroup $group */
        $group = $this->getRecord();
        $tagIds = $this->form->getState()['tag_ids'] ?? [];
        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        $this->syncTagsToGroup($group, array_map('intval', $tagIds));
    }

    /**
     * @param  array<int>  $tagIds
     */
    private function syncTagsToGroup(TagGroup $group, array $tagIds): void
    {
        if ($tagIds === []) {
            return;
        }

        Tag::query()->whereIn('id', $tagIds)->update(['tag_group_id' => $group->getKey()]);
    }
}
