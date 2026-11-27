<?php
include "config.php";
$pageTitle = "تأكيد الحجز";
$active = "courts";
include "header.php";

$errors = array();
$done = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {

  $court_id = intval($_POST['court_id']);
  $date = $_POST['date'];
  $start = intval($_POST['start_hour']);
  $hours = intval($_POST['hours']);
  $name = trim($_POST['name']);
  $phone = trim($_POST['phone']);

  // ---------- التحقق من المدخلات ----------
  if ($name == "") { $errors[] = "الرجاء كتابة الاسم."; }
  if (!preg_match('/^05[0-9]{8}$/', $phone)) { $errors[] = "رقم الجوال غير صحيح (مثال: 0551234567)."; }
  if ($hours < 1 || $hours > 3) { $errors[] = "مدة الحجز من ساعة إلى ثلاث ساعات فقط."; }
  if (!validDate($date)) { $errors[] = "التاريخ غير صحيح."; }

  $result = mysqli_query($conn, "SELECT * FROM courts WHERE id=$court_id");
  if (mysqli_num_rows($result) == 0) {
    $errors[] = "الملعب غير موجود.";
  } else {
    $court = mysqli_fetch_assoc($result);
    if ($start < $court['open_hour'] || $start + $hours > $court['close_hour']) {
      $errors[] = "الوقت المختار خارج ساعات عمل الملعب.";
    }
  }

  if (count($errors) == 0) {
    $today = date('Y-m-d');
    $maxDate = date('Y-m-d', strtotime('+13 days'));
    if ($date < $today || $date > $maxDate) { $errors[] = "التاريخ خارج فترة الحجز المتاحة."; }
    if ($date == $today && $start <= intval(date('G'))) { $errors[] = "هذه الساعة انتهت، اختر ساعة قادمة."; }
  }

  // ---------- التحقق من توفر الساعات (منع الحجز المكرر) ----------
  if (count($errors) == 0) {
    $end = $start + $hours;
    $sql = "SELECT id FROM booking_slots WHERE court_id=$court_id AND slot_date='$date' AND slot_hour>=$start AND slot_hour<$end";
    $check = mysqli_query($conn, $sql);
    if (mysqli_num_rows($check) > 0) {
      $errors[] = "عذرًا، إحدى الساعات المختارة انحجزت قبل قليل. اختر وقتًا آخر.";
    }
  }

  // ---------- حفظ الحجز ----------
  if (count($errors) == 0) {
    $total = $court['price_hour'] * $hours;

    $sql = "INSERT INTO bookings (code, court_id, booking_date, start_hour, hours, owner_name, owner_phone, total)
            VALUES ('', $court_id, '$date', $start, $hours, '$name', '$phone', $total)";
    mysqli_query($conn, $sql);
    $booking_id = mysqli_insert_id($conn);

    // رقم الحجز مثل: RLY-20261125-0007
    $code = "RLY-" . date('Ymd') . "-" . sprintf('%04d', $booking_id);
    mysqli_query($conn, "UPDATE bookings SET code='$code' WHERE id=$booking_id");

    // قفل الساعات: صف لكل ساعة
    $ok = true;
    for ($h = $start; $h < $start + $hours; $h++) {
      $ins = mysqli_query($conn, "INSERT INTO booking_slots (booking_id, court_id, slot_date, slot_hour) VALUES ($booking_id, $court_id, '$date', $h)");
      if (!$ins) { $ok = false; }
    }

    if ($ok) {
      // صاحب الحجز أول لاعب ويتحمل كامل المبلغ حتى يضيف لاعبين
      mysqli_query($conn, "INSERT INTO players (booking_id, name, share, paid) VALUES ($booking_id, '$name', $total, 0)");
      $done = true;
    } else {
      // حجزها شخص آخر في نفس اللحظة: نلغي العملية
      mysqli_query($conn, "DELETE FROM booking_slots WHERE booking_id=$booking_id");
      mysqli_query($conn, "DELETE FROM bookings WHERE id=$booking_id");
      $errors[] = "عذرًا، إحدى الساعات انحجزت للتو. اختر وقتًا آخر.";
    }
  }
} else {
  $errors[] = "لا توجد بيانات حجز. اختر الملعب والساعة أولًا.";
}
?>

<section class="section">
  <div class="container" style="max-width:760px">

  <?php if ($done) { ?>
    <div class="alert alert-ok">تم تأكيد حجزك بنجاح ✔ والساعة الآن مقفلة باسمك.</div>
    <div class="ticket">
      <div>
        <small>رقم الحجز</small>
        <span class="code"><?php echo $code; ?></span>
        <small>احتفظ بالرقم لإضافة اللاعبين ومتابعة الدفع</small>
      </div>
      <a href="booking.php?code=<?php echo $code; ?>" class="btn btn-yellow">إضافة اللاعبين وتقسيم المبلغ</a>
    </div>
    <div class="panel" style="margin-top:20px">
      <h2>ملخص الحجز</h2>
      <div class="kv">
        <div><small>الملعب</small><b><?php echo $court['name']; ?></b></div>
        <div><small>التاريخ</small><b><?php echo date('d/m/Y', strtotime($date)); ?></b></div>
        <div><small>الوقت</small><b><?php echo hourLabel($start); ?> – <?php echo hourLabel($start + $hours); ?></b></div>
        <div><small>المدة</small><b><?php echo $hours; ?> ساعة</b></div>
        <div><small>السعر الكلي</small><b><?php echo $total; ?> ريال</b></div>
      </div>
    </div>

  <?php } else { ?>
    <div class="alert alert-err">
      <?php foreach ($errors as $e) { echo $e . "<br>"; } ?>
    </div>
    <?php
      $back = "courts.php";
      if (isset($court_id) && $court_id > 0) { $back = "court.php?id=" . $court_id; }
    ?>
    <a href="<?php echo $back; ?>" class="btn btn-dark">الرجوع لجدول المواعيد</a>
  <?php } ?>

  </div>
</section>

<?php include "footer.php"; ?>
