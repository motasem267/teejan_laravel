<?php

namespace App\Filament\Resources\AnnualSubscriptionFees;

use App\Filament\Resources\AnnualSubscriptionFees\Pages\CreateAnnualSubscriptionFee;
use App\Filament\Resources\AnnualSubscriptionFees\Pages\EditAnnualSubscriptionFee;
use App\Filament\Resources\AnnualSubscriptionFees\Pages\ListAnnualSubscriptionFees;
use App\Filament\Resources\AnnualSubscriptionFees\Pages\ViewAnnualSubscriptionFee;
use App\Filament\Resources\AnnualSubscriptionFees\Schemas\AnnualSubscriptionFeeForm;
use App\Filament\Resources\AnnualSubscriptionFees\Schemas\AnnualSubscriptionFeeInfolist;
use App\Filament\Resources\AnnualSubscriptionFees\Tables\AnnualSubscriptionFeesTable;
use App\Models\AnnualSubscriptionFee;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class AnnualSubscriptionFeeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = AnnualSubscriptionFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'الاشتراكات السنوية';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $pluralModelLabel = 'الاشتراكات السنوية';
    protected static ?string $modelLabel = 'اشتراك سنوي';

    protected static function getResourcePermissionName(): string
    {
        return 'annual_subscription_fees';
    }

    public static function form(Schema $schema): Schema
    {
        return AnnualSubscriptionFeeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AnnualSubscriptionFeeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnnualSubscriptionFeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnualSubscriptionFees::route('/'),
            'create' => CreateAnnualSubscriptionFee::route('/create'),
            'view' => ViewAnnualSubscriptionFee::route('/{record}'),
            'edit' => EditAnnualSubscriptionFee::route('/{record}/edit'),
        ];
    }
}
