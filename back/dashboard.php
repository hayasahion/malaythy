<?php
require_once '../config/database.php';
require_once '../helpers/functions.php';

handleCORS();
requireAdminLogin();

$db = getDB();

// ========================================
// جمع الإحصائيات الرئيسية بـ استعلام واحد لكل جدول
// ========================================
$stats = [];

// إجمالي أولياء الأمور
$stats['total_parents'] = $db->query("SELECT COUNT(*) FROM parents")->fetchColumn();

// إجمالي الأطفال
$stats['total_children'] = $db->query("SELECT COUNT(*) FROM children")->fetchColumn();

// إجمالي القصص
$stats['total_stories'] = $db->query("SELECT COUNT(*) FROM stories")->fetchColumn();

// إجمالي التقييمات
$stats['total_ratings'] = $db->query("SELECT COUNT(*) FROM story_ratings")->fetchColumn();

// متوسط التقييم العام
$stats['avg_rating'] = round(
    $db->query("SELECT AVG(rating) FROM story_ratings")->fetchColumn() ?? 0, 
    1
);

// أحدث 5 مستخدمين مسجّلين
$recentUsers = $db->query("
    SELECT id, full_name, email, created_at 
    FROM parents 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// أكثر 5 قصص تقييماً
$topStories = $db->query("
    SELECT 
        s.id, s.title, s.character_type,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        COUNT(r.id) AS rating_count
    FROM stories s
    JOIN story_ratings r ON s.id = r.story_id
    GROUP BY s.id
    ORDER BY avg_rating DESC, rating_count DESC
    LIMIT 5
")->fetchAll();

sendJSON(true, 'تم جلب إحصائيات لوحة التحكم', [
    'stats'        => $stats,
    'recent_users' => $recentUsers,
    'top_stories'  => $topStories
]);