<?php
 
echo __DIR__;
exit;
  require_once 'database.php';
handleCORS();

$parentId = requireParentLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'طريقة الطلب غير صحيحة');
}

$body       = json_decode(file_get_contents('php://input'), true);
$child_name = clean($body['child_name'] ?? '');
$birth_year = isset($body['birth_year']) ? (int)$body['birth_year'] : null;
$gender     = clean($body['gender'] ?? '');

if (empty($child_name)) {
    sendJSON(false, 'اسم الطفل مطلوب');
}

// التحقق من صحة الجنس
if (!empty($gender) && !in_array($gender, ['male', 'female'])) {
    sendJSON(false, 'قيمة الجنس غير صحيحة');
}

// التحقق من سنة الميلاد
$currentYear = (int)date('Y');
if ($birth_year && ($birth_year < 2000 || $birth_year > $currentYear)) {
    sendJSON(false, 'سنة الميلاد غير صحيحة');
}

$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO children (parent_id, child_name, birth_year, gender)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([
    $parentId,
    $child_name,
    $birth_year,
    !empty($gender) ? $gender : null
]);

$childId = $db->lastInsertId();

sendJSON(true, 'تمت إضافة الطفل بنجاح', [
    'child_id'   => $childId,
    'child_name' => $child_name
]);