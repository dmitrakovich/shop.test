<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\TagGroupResource;
use App\Models\Tag;
use App\Models\TagGroup;
use Filament\Resources\Pages\EditRecord;

class EditTagGroup extends EditRecord
{
    protected static string $resource = TagGroupResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TagGroup $record */
        $record = $this->getRecord();
        $data['tag_ids'] = $record->tags()->pluck('id')->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['tag_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var TagGroup $group */
        $group = $this->getRecord();
        $tagIdsRaw = $this->form->getState()['tag_ids'] ?? [];
        $tagIds = is_array($tagIdsRaw) ? array_map('intval', $tagIdsRaw) : [];

        Tag::query()->where('tag_group_id', $group->getKey())->whereNotIn('id', $tagIds)->update(['tag_group_id' => null]);

        if ($tagIds !== []) {
            Tag::query()->whereIn('id', $tagIds)->update(['tag_group_id' => $group->getKey()]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
