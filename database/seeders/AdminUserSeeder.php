<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get all permissions
        $allPermissions = [];
        if (\Schema::hasTable('permissions')) {
            $allPermissions = Permission::all()->pluck('id');
        }

        // Give permissions to employee ID 1 (current user)
        $employee1 = Employee::find(1);
        if ($employee1 && $allPermissions->count() > 0) {
            $employee1->permissions()->sync($allPermissions);
            $this->command->info('✅ تم إعطاء جميع الصلاحيات للموظف ID=1');
            $this->command->info('📧 الإيميل: ' . $employee1->email);
            $this->command->info('✅ عدد الصلاحيات: ' . $allPermissions->count());
        }

        // Find or create admin employee
        $admin = Employee::where('email', 'admin@teejan.com')->first();
        
        if ($admin) {
            $this->command->info('🔄 المستخدم Admin موجود، سيتم تحديث البيانات...');
            $admin->update([
                'name' => 'Admin',
                'password' => Hash::make('teejanadmin7379'),
                'phone_number' => '0912345678',
            ]);
        } else {
            $admin = Employee::create([
                'name' => 'Admin',
                'email' => 'admin@teejan.com',
                'password' => Hash::make('teejanadmin7379'),
                'phone_number' => '0912345678',
                'salary' => 0,
            ]);
        }

        $this->command->info('✅ تم تحديث حساب الأدمن بنجاح!');
        $this->command->info('📧 الإيميل: admin@teejan.com');
        $this->command->info('🔑 كلمة المرور: teejanadmin7379');
        $this->command->info('🆔 ID: ' . $admin->id);

        // Try to attach permissions if table exists
        try {
            if (\Schema::hasTable('permissions')) {
                if ($allPermissions->count() > 0) {
                    // Detach all permissions first, then attach all
                    $admin->permissions()->sync($allPermissions);
                    $this->command->info('✅ عدد الصلاحيات Admin: ' . $allPermissions->count());
                } else {
                    $this->command->warn('⚠️ لا توجد صلاحيات في قاعدة البيانات');
                }
            } else {
                $this->command->warn('⚠️ جدول الصلاحيات غير موجود');
            }
        } catch (\Exception $e) {
            $this->command->warn('⚠️ خطأ في إضافة الصلاحيات: ' . $e->getMessage());
        }
    }
}
