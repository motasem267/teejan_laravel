<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_enrollments')) {
            Schema::create('employee_enrollments', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id', 50);
                $table->unsignedBigInteger('academic_year_id');
                $table->unsignedBigInteger('emp_type_id')->nullable();
                $table->timestamps();

                $table->unique(['employee_id', 'academic_year_id'], 'employee_year_unique');

                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
                $table->foreign('emp_type_id')->references('id')->on('employee_types')->nullOnDelete();
            });
        }

        // قيد الموظفين المستمرين في السنة الفعالة حتى لا يُمنع أحد من الدخول بعد التحديث
        $activeYearId = DB::table('academic_years')->where('is_active', true)->value('id');
        $activeStatusId = DB::table('employee_statuses')->where('status_name', 'مستمر')->value('id');

        if ($activeYearId) {
            DB::table('employees')
                ->when($activeStatusId, fn ($q) => $q->where('status_id', $activeStatusId))
                ->get(['id', 'emp_type_id'])
                ->each(fn ($employee) => DB::table('employee_enrollments')->insertOrIgnore([
                    'employee_id' => $employee->id,
                    'academic_year_id' => $activeYearId,
                    'emp_type_id' => $employee->emp_type_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
        }

        $this->addPermissions('employee_enrollments', 'قيد الموظفين', [
            'employee_enrollments.view' => 'عرض قيد الموظفين',
            'employee_enrollments.create' => 'إضافة قيد الموظفين',
            'employee_enrollments.edit' => 'تعديل قيد الموظفين',
            'employee_enrollments.delete' => 'حذف قيد الموظفين',
        ]);

        $this->addPermissions('employee-promotion', 'ترحيل الموظفين', [
            'employee-promotion.view' => 'ترحيل الموظفين للسنة الجديدة',
        ]);
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->where('name', 'like', 'employee_enrollments%')
            ->orWhere('name', 'like', 'employee-promotion%')
            ->pluck('id');

        DB::table('employee_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        Schema::dropIfExists('employee_enrollments');
    }

    private function addPermissions(string $parent, string $label, array $children): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => $parent],
            ['label' => $label, 'parent_id' => null, 'created_at' => now(), 'updated_at' => now()],
        );

        $parentId = DB::table('permissions')->where('name', $parent)->value('id');

        foreach ($children as $name => $childLabel) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['label' => $childLabel, 'parent_id' => $parentId, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }
};
