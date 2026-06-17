<?php
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS();
requireAdminLogin();

$db = getDB();

// ========================================
// البحث عن مستخدم (اختياري)
// ========================================
$search = clean($_GET['search'] ?? '');

if (!empty($search)) {
    $stmt = $db->prepare("
        SELECT 
            p.id, p.full_name, p.email, p.phone, p.is_active, p.created_at,
            COUNT(DISTINCT c.id) AS children_count,
            COUNT(DISTINCT s.id) AS stories_count
        FROM parents p
        LEFT JOIN children c ON p.id = c.parent_id
        LEFT JOIN stories  s ON p.id = s.parent_id
        WHERE p.full_name LIKE ? OR p.email LIKE ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $stmt = $db->prepare("
        SELECT 
            p.id, p.full_name, p.email, p.phone, p.is_active, p.created_at,
            COUNT(DISTINCT c.id) AS children_count,
            COUNT(DISTINCT s.id) AS stories_count
        FROM parents p
        LEFT JOIN children c ON p.id = c.parent_id
        LEFT JOIN stories  s ON p.id = s.parent_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
}

$users = $stmt->fetchAll();
sendJSON(true, 'تم جلب المستخدمين بنجاح', ['users' => $users]);