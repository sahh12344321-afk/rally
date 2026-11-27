<?php
// الهيدر المشترك: يُضمَّن في أعلى كل صفحة
// قبل تضمينه نحدد:  $base (مسار الملفات)  و  $pageTitle  و  $active
if (!isset($base)) { $base = ""; }
if (!isset($pageTitle)) { $pageTitle = "Rally"; }
if (!isset($active)) { $active = ""; }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?> | Rally</title>
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>css/style.css">
</head>
<body>
<?php if ($active == "admin") { ?>
  <div class="admin-bar">لوحة تحكم صاحب الملعب</div>
<?php } ?>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="<?php echo $base; ?>index.php">
      <img src="<?php echo $base; ?>images/logo.png" alt="Rally Play Padel">
    </a>
    <nav class="nav-links">
      <a href="<?php echo $base; ?>index.php" class="<?php if ($active == 'home') echo 'active'; ?>">الرئيسية</a>
      <a href="<?php echo $base; ?>courts.php" class="<?php if ($active == 'courts') echo 'active'; ?>">الملاعب</a>
      <a href="<?php echo $base; ?>booking.php" class="<?php if ($active == 'booking') echo 'active'; ?>">حجزي</a>
      <a href="<?php echo $base; ?>admin/index.php" class="admin-link">لوحة التحكم</a>
    </nav>
  </div>
</header>
