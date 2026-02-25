-- منح جميع صلاحيات الجدول الدراسي والصفحات الإضافية لجميع الموظفين
-- Grant all timetable and additional pages permissions to all employees

-- منح الصلاحيات لجميع الموظفين
-- Grant permissions to all employees
INSERT INTO employee_permissions (employee_id, permission_id)
SELECT 
    e.id,
    p.id
FROM 
    employees e
CROSS JOIN 
    permissions p
WHERE 
    p.name IN (
        'days',
        'days.view',
        'days.create',
        'days.edit',
        'days.delete',
        'lesson-times',
        'lesson-times.view',
        'lesson-times.create',
        'lesson-times.edit',
        'lesson-times.delete',
        'school-schedules',
        'school-schedules.view',
        'school-schedules.create',
        'school-schedules.edit',
        'school-schedules.delete',
        'salaries',
        'salaries.view',
        'salaries.create',
        'salaries.edit',
        'salaries.delete',
        'work-days-calendars',
        'work-days-calendars.view',
        'work-days-calendars.create',
        'work-days-calendars.edit',
        'work-days-calendars.delete',
        'academic-period-grades',
        'academic-period-grades.view',
        'academic-period-grades.create',
        'academic-period-grades.edit',
        'academic-period-grades.delete'
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM employee_permissions ep 
        WHERE ep.employee_id = e.id 
        AND ep.permission_id = p.id
    );
