<?php
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS();

$parentId = requireParentLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'طريقة الطلب غير صحيحة');
}

// ========================================
// التحقق من وجود الملف
// ========================================
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    sendJSON(false, 'لم يتم إرسال الصورة بشكل صحيح');
}

$file = $_FILES['image'];

// ========================================
// التحقق من نوع الملف (صور فقط)
// ========================================
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo        = finfo_open(FILEINFO_MIME_TYPE);
$mimeType     = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    sendJSON(false, 'نوع الملف غير مسموح به. الأنواع المسموحة: JPEG, PNG, WebP, GIF');
}

// ========================================
// التحقق من حجم الملف (5 ميغا كحد أقصى)
// ========================================
$maxSize = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $maxSize) {
    sendJSON(false, 'حجم الصورة يتجاوز الحد المسموح (5 ميغابايت)');
}

// ========================================
// حفظ الصورة باسم عشوائي (أمان)
// ========================================
$extension   = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFilename = uniqid('img_', true) . '.' . strtolower($extension);
$uploadDir   = __DIR__ . '/images/';
$uploadPath  = $uploadDir . $newFilename;

// إنشاء المجلد إن لم يكن موجوداً
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    sendJSON(false, 'فشل في رفع الصورة، حاول مجدداً');
}

// إرجاع رابط الصورة
$imageUrl = 'http://localhost/malathy/uploads/images/' . $newFilename;

sendJSON(true, 'تم رفع الصورة بنجاح', [
    'filename'  => $newFilename,
    'image_url' => $imageUrl
]);