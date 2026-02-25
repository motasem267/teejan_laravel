<?php

namespace App\Filament\Resources\Installments;

use App\Filament\Resources\Installments\Pages\CreateInstallment;
use App\Filament\Resources\Installments\Pages\EditInstallment;
use App\Filament\Resources\Installments\Pages\ListInstallments;
use App\Filament\Resources\Installments\Pages\ViewInstallment;
use App\Filament\Resources\Installments\Schemas\InstallmentForm;
use App\Filament\Resources\Installments\Schemas\InstallmentInfolist;
use App\Filament\Resources\Installments\Tables\InstallmentsTable;
use App\Models\Installment;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class InstallmentResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Installment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'الأقساط';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $pluralModelLabel = 'الأقساط';
    protected static ?string $modelLabel = 'قسط';

    protected static function getResourcePermissionName(): string
    {
        return 'installments';
    }

    public static function form(Schema $schema): Schema
    {
        return InstallmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstallmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstallmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstallments::route('/'),
            'create' => CreateInstallment::route('/create'),
            'view' => ViewInstallment::route('/{record}'),
            'edit' => EditInstallment::route('/{record}/edit'),
        ];
    }
}
