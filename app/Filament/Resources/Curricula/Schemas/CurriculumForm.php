<?php

namespace App\Filament\Resources\Curricula\Schemas;

use App\Models\GradeSubject;
use App\Models\grade;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CurriculumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('book_name')
                    ->label('اسم الكتاب')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('grade_id')
                    ->label('الصف الدراسي')
                    ->options(grade::orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('subject_id', null)),

                Select::make('subject_id')
                    ->label('المادة')
                    ->options(function (Get $get): array {
                        $gradeId = $get('grade_id');
                        if (!$gradeId) {
                            return [];
                        }

                        return GradeSubject::with('subject')
                            ->where('gradeID', $gradeId)
                            ->get()
                            ->mapWithKeys(fn (GradeSubject $gs) => [
                                $gs->subjectID => $gs->subject?->name ?? '-',
                            ])
                            ->filter()
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->disabled(fn (Get $get) => !$get('grade_id')),

                FileUpload::make('file_path')
                    ->label('ملف المنهج (PDF)')
                    ->disk('local')
                    ->directory('curricula')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->maxSize(51200)
                    ->columnSpanFull(),
            ]);
    }
}
