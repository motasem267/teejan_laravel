<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Installment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected ?array $installmentData = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        // إذا اختار المستخدم "برسوم اشتراك"، نحفظ بيانات القسط
        if (isset($data['has_subscription_fee']) && $data['has_subscription_fee'] === true) {
            if (isset($data['amount']) && $data['amount'] > 0) {
                $this->installmentData = [
                    'parent_id' => $data['parent_id'],
                    'academic_year' => $data['academic_year'] ?? null,
                    'installment_type_id' => $data['installment_type_id'],
                    'amount' => $data['amount'],
                    'payment_type_id' => $data['payment_type_id'] ?? null,
                    'description' => $data['installment_description'] ?? 'قسط للطالب: ' . $data['full_name'],
                    'created_by' => Auth::id(),
                ];
            }
        }

        // نحذف الحقول الخاصة بالقسط من بيانات الطالب
        unset(
            $data['has_subscription_fee'],
            $data['academic_year'],
            $data['installment_type_id'],
            $data['amount'],
            $data['payment_type_id'],
            $data['installment_description']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        // إذا كانت هناك بيانات قسط، نضيفها الآن
        if (isset($this->installmentData)) {
            Installment::create($this->installmentData);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
