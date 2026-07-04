<?php

namespace App\Filament\Resources\Seo\SeoPages\Schemas;

use App\Enums\Seo\SeoPageType;
use App\Models\Seo\SeoPage;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class SeoPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->schema([
                        Select::make('page_type')
                            ->label('Тип страницы')
                            ->options(SeoPageType::class)
                            ->default(SeoPageType::Catalog)
                            ->native(false)
                            ->required()
                            ->live(),
                        TextInput::make('url')
                            ->label('Точный URL')
                            ->required()
                            ->maxLength(512)
                            ->unique(
                                table: SeoPage::class,
                                column: 'url',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule): Unique => $rule,
                            )
                            ->helperText(function (Get $get): string {
                                $pageType = $get('page_type');

                                if ($pageType instanceof SeoPageType) {
                                    $prefix = $pageType->value;
                                } else {
                                    $resolved = SeoPageType::tryFrom((string)$pageType);
                                    $prefix = $resolved !== null ? $resolved->value : SeoPageType::Catalog->value;
                                }

                                return 'Будет сохранено как ' . $prefix . '/...';
                            }),
                        TextInput::make('tag_name')
                            ->label('Название тега')
                            ->maxLength(255),
                    ])
                    ->columns(1),
                Section::make('Meta')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                        TextInput::make('h1')
                            ->label('H1')
                            ->maxLength(255),
                        Textarea::make('keywords')
                            ->label('Keywords')
                            ->rows(2)
                            ->helperText('Через запятую'),
                    ])
                    ->columns(1),
                Section::make('Контент')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('seo_text_title')
                            ->label('Title SEO-текста')
                            ->maxLength(255),
                        RichEditor::make('seo_text')
                            ->label('SEO-текст')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
