<?php
// ضم الملفات المطلوبة
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS(); // معالجة CORS أولاً

// نقبل فقط طلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'طريقة الطلب غير صحيحة');
}

// ========================================
// 1. استقبال وتنظيف البيانات
// ========================================
// نقرأ البيانات القادمة من React كـ JSON
$body      = json_decode(file_get_contents('php://input'), true);

$full_name = clean($body['full_name'] ?? '');
$email     = clean($body['email']     ?? '');
$password  =       $body['password']  ?? '';  // لا نُنظّف كلمة المرور (سنشفّرها)
$phone     = clean($body['phone']     ?? '');

// ========================================
// 2. التحقق من البيانات
// ========================================
if (empty($full_name)) {
    sendJSON(false, 'الاسم الكامل مطلوب');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSON(false, 'البريد الإلكتروني غير صحيح');
}

// كلمة المرور: 8 أحرف على الأقل، حرف كبير، حرف صغير، رقم
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    sendJSON(false, 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل، حرف كبير، حرف صغير، ورقم');
}

// ========================================
// 3. التحقق من عدم تكرار البريد الإلكتروني
// ========================================
$db   = getDB();
$stmt = $db->prepare("SELECT id FROM parents WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    sendJSON(false, 'البريد الإلكتروني مسجّل مسبقاً');
}

// ========================================
// 4. تشفير كلمة المرور وحفظ البيانات
// ========================================
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $db->prepare("
    INSERT INTO parents (full_name, email, password, phone)
    VALUES (?, ?, ?, ?)
");

$stmt->execute([$full_name, $email, $hashedPassword, $phone]);

$newParentId = $db->lastInsertId();

// ========================================
// 5. إرجاع الاستجابة للـ React
// ========================================
sendJSON(true, 'تم إنشاء الحساب بنجاح', [
    'parent_id' => $newParentId,
    'full_name' => $full_name,
    'email'     => $email
]);