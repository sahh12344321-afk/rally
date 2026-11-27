<?php
// ملف الاتصال بقاعدة البيانات (config.php)
// يُضمَّن في كل صفحة تحتاج قاعدة البيانات باستخدام include

date_default_timezone_set("Asia/Riyadh");

$conn = mysqli_connect("localhost", "root", "");

if ($conn == true) {
  mysqli_select_db($conn, "rally");
  mysqli_set_charset($conn, "utf8mb4");
} else {
  die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// ---------- دوال مساعدة ----------

// تحويل الساعة (0-24) إلى نص مثل: 4:00 م
function hourLabel($h) {
  $h = $h % 24;
  if ($h >= 12) { $ampm = "م"; } else { $ampm = "ص"; }
  $x = $h % 12;
  if ($x == 0) { $x = 12; }
  return $x . ":00 " . $ampm;
}

// حساب حصة كل لاعب = السعر الكلي ÷ عدد اللاعبين
function calculateShare($total, $count) {
  return round($total / $count, 2);
}

// إعادة حساب حصص جميع لاعبي الحجز (تُستدعى بعد إضافة أو حذف لاعب)
function recalcShares($conn, $booking_id) {
  $r = mysqli_query($conn, "SELECT total FROM bookings WHERE id=$booking_id");
  $b = mysqli_fetch_assoc($r);
  $r2 = mysqli_query($conn, "SELECT id FROM players WHERE booking_id=$booking_id");
  $count = mysqli_num_rows($r2);
  if ($count > 0) {
    $share = calculateShare($b['total'], $count);
    mysqli_query($conn, "UPDATE players SET share=$share WHERE booking_id=$booking_id");
  }
}

// التأكد أن التاريخ بصيغة صحيحة YYYY-MM-DD
function validDate($d) {
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) { return false; }
  return date('Y-m-d', strtotime($d)) == $d;
}

// أنواع الملاعب
$courtTypes = array("بانوراما", "داخلي", "خارجي");
?>
