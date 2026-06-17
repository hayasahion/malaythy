<?php
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS();

$parentId = requireParentLogin();

$db   = getDB();
$stmt = $db->prepare("
    SELECT 
        c.id,
        c.child_name,
        c.birth_year,
        c.gender,
        c.avatar,
        -- حساب عمر الطفل تلقائياً
        (YEAR(NOW()) - c.birth_year) AS age,
        -- عدد قصص هذا الطفل
        COUNT(s.id) AS stories_count
    FROM children c
    LEFT JOIN stories s ON c.id = s.child_id
    WHERE c.parent_id = ?
    GROUP BY c.id
    ORDER BY c.created_at ASC
");
$stmt->execute([$parentId]);
$children = $stmt->fetchAll();

sendJSON(true, 'تم جلب الأطفال بنجاح', ['children' => $children]);