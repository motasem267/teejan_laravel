<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CurriculumController extends Controller
{
    /**
     * GET /api/curricula
     * قائمة المناهج، قابلة للفلترة حسب الصف و/أو المادة.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Curriculum::query()->with(['grade', 'subject']);

        if ($gradeId = $request->query('grade_id')) {
            $query->where('grade_id', $gradeId);
        }

        if ($subjectId = $request->query('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        $curricula = $query->orderBy('grade_id')->orderBy('subject_id')->get();

        return response()->json([
            'success' => true,
            'data' => $curricula->map(fn (Curriculum $c) => [
                'id' => $c->id,
                'book_name' => $c->book_name,
                'grade' => $c->grade?->name,
                'subject' => $c->subject?->name,
                'file_url' => route('curricula.file', $c->id),
            ]),
        ]);
    }

    /**
     * GET /api/curricula/{id}/file
     * عرض ملف المنهج (PDF) مباشرة.
     */
    public function file(int $id): StreamedResponse
    {
        $curriculum = Curriculum::findOrFail($id);

        abort_unless(Storage::disk('local')->exists($curriculum->file_path), 404);

        return Storage::disk('local')->response(
            $curriculum->file_path,
            $curriculum->book_name . '.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
