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

$db = getDB();

// نتحقق أن القصة تخص هذا الوالد قبل الحذف
$stmt = $db->prepare("SELECT cover_image FROM stories WHERE id = ? AND parent_id = ?");
$stmt->execute([$storyId, $parentId]);
$story = $stmt->fetch();

if (!$story) {
    http_response_code(403);
    sendJSON(false, 'لا تملك صلاحية حذف هذه القصة');
}

// حذف صورة الغلاف من السيرفر (إن وُجدت)
if (!empty($story['cover_image'])) {
    $imagePath = '../uploads/images/' . basename($story['cover_image']);
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// حذف القصة (التقييمات ستُحذف تلقائياً بسبب CASCADE)
$stmt = $db->prepare("DELETE FROM stories WHERE id = ? AND parent_id = ?");
$stmt->execute([$storyId, $parentId]);

sendJSON(true, 'تم حذف القصة بنجاح');