<?php
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS();

$parentId = requireParentLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'طريقة الطلب غير صحيحة');
}

$body    = json_decode(file_get_contents('php://input'), true);
$storyId = isset($body['story_id']) ? (int)$body['story_id'] : 0;

if ($storyId <= 0) {
    sendJSON(false, 'معرّف القصة غير صحيح');
}

// ========================================
// نبني الاستعلام ديناميكياً حسب ما أُرسل
// ========================================
$db = getDB();

// أولاً: نتأكد أن هذه القصة تخص هذا الوالد (أمان)
$stmt = $db->prepare("SELECT id FROM stories WHERE id = ? AND parent_id = ?");
$stmt->execute([$storyId, $parentId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    sendJSON(false, 'لا تملك صلاحية تعديل هذه القصة');
}

$fields = [];
$values = [];

// تعديل العنوان (اختياري)
if (!empty($body['title'])) {
    $fields[] = "title = ?";
    $values[] = clean($body['title']);
}

// تبديل حالة المفضّلة (اختياري)
if (isset($body['is_favorite'])) {
    $fields[] = "is_favorite = ?";
    $values[] = $body['is_favorite'] ? 1 : 0;
}

if (empty($fields)) {
    sendJSON(false, 'لا توجد بيانات للتعديل');
}

$values[] = $storyId;
$values[] = $parentId;

$sql  = "UPDATE stories SET " . implode(', ', $fields) . " WHERE id = ? AND parent_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute($values);

sendJSON(true, 'تم تعديل القصة بنجاح');