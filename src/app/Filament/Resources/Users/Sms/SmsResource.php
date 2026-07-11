<?php

namespace App\Filament\Resources\Users\Sms;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Users\Sms\Pages\ListSms;
use App\Filament\Resources\Users\Sms\Tables\SmsTable;
use App\Models\Logs\SmsLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SmsResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $slug = 'sms';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Users;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $navigationLabel = 'SMS';

    protected static ?string $modelLabel = 'SMS сообщение';

    protected static ?string $pluralModelLabel = 'SMS';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return SmsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSms::route('/'),
        ];
    }
}
