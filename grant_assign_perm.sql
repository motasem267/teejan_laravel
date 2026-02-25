-- منح صلاحية إعطاء الصلاحيات للمستخدم 9999
INSERT INTO employee_permissions (employee_id, permission_id)
SELECT 9999, id
FROM permissions
WHERE name = 'assign-permissions.view'
AND NOT EXISTS (
    SELECT 1 FROM employee_permissions 
    WHERE employee_id = 9999 
    AND permission_id = (SELECT id FROM permissions WHERE name = 'assign-permissions.view')
);
