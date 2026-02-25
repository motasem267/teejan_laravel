<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\CreateActivityLog;
use App\Filament\Resources\ActivityLogs\Pages\EditActivityLog;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Pages\ViewActivityLog;
use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogForm;
use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use App\Models\ActivityLog;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class ActivityLogResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ActivityLog';
    protected static ?string $navigationLabel = 'سجل الأنشطة';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة المستخدمين والصلاحيات';
    
    protected static ?string $modelLabel = 'نشاط';
    
    protected static ?string $pluralModelLabel = 'سجل الأنشطة';

    
    protected static function getResourcePermissionName(): string
    {
        return 'activity-logs';
    }
    
    public static function canCreate(): bool
    {
        return false; // منع الإضافة
    }
    
    public static function canEdit($record): bool
    {
        return false; // منع التعديل
    }
    
    public static function canDelete($record): bool
    {
        return false; // منع الحذف
    }
    
    public static function canDeleteAny(): bool
    {
        return false; // منع الحذف الجماعي
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Check if table exists and current user has permission before showing in navigation
        if (!\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            return false;
        }

        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (! method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('activity-logs.view');
    }

    public static function canViewAny(): bool
    {
        // Check if table exists first
        if (!\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            return false;
        }
        
        // Then check permissions
        return parent::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }
}
