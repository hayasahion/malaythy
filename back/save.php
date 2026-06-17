<?php
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS();

// نتأكد أن المستخدم مسجّل الدخول ونجلب الـ ID
$parentId = requireParentLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'طريقة الطلب غير صحيحة');
}

// ========================================
// 1. استقبال بيانات القصة من React
// ========================================
$body = json_decode(file_get_contents('php://input'), true);

$title          = clean($body['title']          ?? '');
$character_type = clean($body['character_type'] ?? '');
$environment    = clean($body['environment']    ?? '');
$emotion        = clean($body['emotion']        ?? '');
$content        = $body['content']              ?? []; // مصفوفة الفقرات
$analysis       = clean($body['analysis']       ?? '');
$advice         = clean($body['advice']         ?? '');
$child_id       = !empty($body['child_id']) ? (int)$body['child_id'] : null;

// ========================================
// 2. التحقق من البيانات الأساسية
// ========================================
if (empty($title) || empty($character_type) || empty($environment) || empty($emotion)) {
    sendJSON(false, 'بيانات القصة غير مكتملة');
}

if (empty($content)) {
    sendJSON(false, 'محتوى القصة مطلوب');
}

// نحوّل مصفوفة الفقرات إلى JSON لحفظها في قاعدة البيانات
$contentJSON = json_encode($content, JSON_UNESCAPED_UNICODE);

// ========================================
// 3. حفظ القصة في قاعدة البيانات
// ========================================
$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO stories 
        (parent_id, child_id, title, character_type, environment, emotion, content, analysis, advice)
    VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $parentId,
    $child_id,
    $title,
    $character_type,
    $environment,
    $emotion,
    $contentJSON,
    $analysis,
    $advice
]);

$storyId = $db->lastInsertId();

sendJSON(true, 'تم حفظ القصة بنجاح', ['story_id' => $storyId]);