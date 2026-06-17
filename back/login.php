<?php
echo '<pre>';
print_r(scandir(__DIR__));
echo '</pre>';
exit;
var_dump(__DIR__);
var_dump(file_exists(__DIR__ . '/database.php'));
// ========================================
// استدعاء الملفات الأساسية (مهم جدًا)
// ========================================
require_once __DIR__ . '/back/database.php';
require_once __DIR__ . '/back/helpers.php';

 
// ========================================
// تهيئة النظام
// ========================================
handleCORS();
startSecureSession();

// ========================================
// التأكد من نوع الطلب
// ========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(false, 'طريقة الطلب غير صحيحة');
}

// ========================================
// 1. استقبال البيانات
// ========================================
$body = json_decode(file_get_contents('php://input'), true);

$email    = clean($body['email'] ?? '');
$password = $body['password'] ?? '';

// التحقق من البيانات
if (empty($email) || empty($password)) {
    sendJSON(false, 'البريد الإلكتروني وكلمة المرور مطلوبان');
}

// ========================================
// 2. الاتصال بقاعدة البيانات
// ========================================
$db = getDB();

$stmt = $db->prepare("
    SELECT id, full_name, email, password, is_active 
    FROM parents 
    WHERE email = ?
");

$stmt->execute([$email]);
$parent = $stmt->fetch();

// ========================================
// 3. التحقق من المستخدم وكلمة المرور
// ========================================
if (!$parent || !password_verify($password, $parent['password'])) {
    http_response_code(401);
    sendJSON(false, 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
}

// ========================================
// 4. التحقق من حالة الحساب
// ========================================
if ($parent['is_active'] == 0) {
    sendJSON(false, 'هذا الحساب موقوف، تواصل مع الدعم');
}

// ========================================
// 5. إنشاء الجلسة
// ========================================
session_regenerate_id(true);

$_SESSION['parent_id']    = $parent['id'];
$_SESSION['parent_name']  = $parent['full_name'];
$_SESSION['parent_email'] = $parent['email'];

// ========================================
// 6. إرسال النتيجة
// ========================================
sendJSON(true, 'تم تسجيل الدخول بنجاح', [
    'parent_id' => $parent['id'],
    'full_name' => $parent['full_name'],
    'email'     => $parent['email']
]);