START TRANSACTION;

-- تحديث employee_permissions
UPDATE employee_permissions SET employee_id = 9999 WHERE employee_id = 1;

-- تحديث employees
UPDATE employees SET id = 9999 WHERE id = 1;

COMMIT;
