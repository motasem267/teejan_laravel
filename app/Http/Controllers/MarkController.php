<?php

namespace App\Http\Controllers;

use App\Models\mark;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MarkController extends Controller
{
    /**
     * Store a new mark
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate input
        $validator = Validator::make($request->all(), mark::validationRules());
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Check for duplicate
        if (mark::markExists(
            $request->student_id,
            $request->subject_id,
            $request->AcademicPeriodID,
            $request->academic_year_id
        )) {
            return response()->json([
                'success' => false,
                'message' => 'الدرجة موجودة بالفعل لهذا الطالب في نفس المادة والفترة والسنة',
            ], 409);
        }
        
        try {
            $mark = mark::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الدرجة بنجاح',
                'data' => $mark,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة الدرجة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Update an existing mark
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $mark = mark::find($id);
        
        if (!$mark) {
            return response()->json([
                'success' => false,
                'message' => 'الدرجة غير موجودة',
            ], 404);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), mark::validationRules($id));
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Check for duplicate (excluding current mark)
        if (mark::markExists(
            $request->student_id,
            $request->subject_id,
            $request->AcademicPeriodID,
            $request->academic_year_id,
            $id
        )) {
            return response()->json([
                'success' => false,
                'message' => 'الدرجة موجودة بالفعل لهذا الطالب في نفس المادة والفترة والسنة',
            ], 409);
        }
        
        try {
            $mark->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الدرجة بنجاح',
                'data' => $mark,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الدرجة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Delete a mark
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $mark = mark::find($id);
        
        if (!$mark) {
            return response()->json([
                'success' => false,
                'message' => 'الدرجة غير موجودة',
            ], 404);
        }
        
        try {
            $mark->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الدرجة بنجاح',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الدرجة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
