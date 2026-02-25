<?php

// التحقق من تقييمات المعلمة مفيدة - تفاصيل دقيقة

echo "=== فحص تقييمات المعلمات ===\n\n";

// التحقق من قاعدة البيانات مباشرة
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. البحث عن المعلمة 'مفيدة':\n";
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE name LIKE '%مفيدة%'");
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($teachers)) {
        echo "   لم يتم العثور على المعلمة مفيدة\n";
        echo "   البحث عن جميع المعلمات:\n";
        $stmt = $pdo->prepare("SELECT * FROM teachers");
        $stmt->execute();
        $allTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allTeachers as $teacher) {
            echo "   - " . $teacher['name'] . " (ID: " . $teacher['id'] . ")\n";
        }
    } else {
        foreach ($teachers as $teacher) {
            echo "   ✓ وجدت المعلمة: " . $teacher['name'] . " (ID: " . $teacher['id'] . ")\n\n";
            
            echo "2. تقييمات هذه المعلمة:\n";
            $stmt = $pdo->prepare("
                SELECT 
                    ser.*, 
                    s.name as student_name,
                    sub.name as subject_name
                FROM student_evaluation_records ser
                LEFT JOIN students s ON ser.student_id = s.id  
                LEFT JOIN subjects sub ON ser.subject_id = sub.id
                WHERE ser.teacher_id = ?
                ORDER BY ser.subject_id, ser.week
            ");
            $stmt->execute([$teacher['id']]);
            $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($evaluations)) {
                echo "   لا توجد تقييمات لهذه المعلمة\n";
            } else {
                echo "   عدد التقييمات الإجمالي: " . count($evaluations) . "\n\n";
                
                // تجميع حسب المادة
                $bySubject = [];
                foreach ($evaluations as $eval) {
                    $subjectId = $eval['subject_id'];
                    if (!isset($bySubject[$subjectId])) {
                        $bySubject[$subjectId] = [
                            'subject_name' => $eval['subject_name'],
                            'evaluations' => [],
                            'unique_students' => []
                        ];
                    }
                    $bySubject[$subjectId]['evaluations'][] = $eval;
                    $bySubject[$subjectId]['unique_students'][$eval['student_id']] = $eval['student_name'];
                }
                
                foreach ($bySubject as $subjectId => $data) {
                    echo "   🔸 المادة: " . $data['subject_name'] . "\n";
                    echo "      عدد الطلاب المقيمين: " . count($data['unique_students']) . "\n";
                    echo "      عدد التقييمات: " . count($data['evaluations']) . "\n";
                    echo "      الطلاب المقيمين:\n";
                    
                    foreach ($data['unique_students'] as $studentId => $studentName) {
                        echo "        • " . $studentName . " (ID: $studentId)\n";
                    }
                    
                    echo "      تفاصيل التقييمات:\n";
                    foreach ($data['evaluations'] as $eval) {
                        echo "        - " . $eval['student_name'] . 
                             " | أسبوع: " . $eval['week'] . 
                             " | شهر: " . $eval['month'] . 
                             " | التاريخ: " . $eval['created_at'] . "\n";
                    }
                    echo "\n";
                }
            }
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في قاعدة البيانات: " . $e->getMessage() . "\n";
    echo "جرب التحقق من وجود ملف database.sqlite\n";
    
    // التحقق من ملفات قاعدة البيانات الموجودة
    echo "\nملفات قاعدة البيانات الموجودة:\n";
    $files = glob(__DIR__ . '/database/*.sqlite');
    foreach ($files as $file) {
        echo "- " . basename($file) . "\n";
    }
}