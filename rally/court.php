<?php
include "config.php";

// جلب بيانات الملعب
$id = 0;
if (isset($_GET['id'])) { $id = intval($_GET['id']); }
$result = mysqli_query($conn, "SELECT * FROM courts WHERE id=$id");

$pageTitle = "جدول المواعيد";
$active = "courts";
include "header.php";

if (mysqli_num_rows($result) == 0) {
  echo '<section class="section"><div class="container"><div class="alert alert-err">الملعب غير موجود.</div>';
  echo '<a class="btn btn-dark" href="courts.php">العودة للملاعب</a></div></section>';
  include "footer.php";
  exit;
}
$court = mysqli_fetch_assoc($result);

// التاريخ المختار (من اليوم إلى 13 يوم قادمة)
$today = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+13 days'));
$date = $today;
if (isset($_GET['date']) && validDate($_GET['date'])) {
  if ($_GET['date'] >= $today && $_GET['date'] <= $maxDate) { $date = $_GET['date']; }
}

// الساعات المحجوزة في هذا اليوم
$booked = array();
$r = mysqli_query($conn, "SELECT slot_hour FROM booking_slots WHERE court_id=$id AND slot_date='$date'");
while ($s = mysqli_fetch_assoc($r)) {
  $booked[] = $s['slot_hour'];
}

// الساعات غير المتاحة للحجز = محجوزة + منتهية (في حال اليوم الحالي)
$nowHour = intval(date('G'));
$unavailable = array();
for ($h = $court['open_hour']; $h < $court['close_hour']; $h++) {
  if (in_array($h, $booked)) {
    $unavailable[] = $h;
  } else if ($date == $today && $h <= $nowHour) {
    $unavailable[] = $h;
  }
}

$dayNames = array("الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت");
?>

<section class="section">
  <div class="container">

    <div class="court-head">
      <div class="info">
        <span class="badge badge-warn" style="align-self:flex-start"><?php echo $court['type']; ?></span>
        <h1><?php echo $court['name']; ?></h1>
        <div class="meta">📍 <?php echo $court['location']; ?></div>
        <div class="meta">🕒 ساعات العمل: من <?php echo hourLabel($court['open_hour']); ?> إلى <?php echo hourLabel($court['close_hour']); ?></div>
        <div class="price"><?php echo $court['price_hour']; ?> ريال <small>/ الساعة</small></div>
      </div>
      <img src="images/courts/<?php echo $court['image']; ?>" alt="<?php echo $court['name']; ?>">
    </div>

    <div class="layout">

      <!-- جدول المواعيد -->
      <div class="panel">
        <h2>جدول المواعيد</h2>

        <div class="days">
          <?php for ($i = 0; $i < 14; $i++) {
            $d = date('Y-m-d', strtotime("+$i days"));
            $dn = $dayNames[date('w', strtotime($d))];
          ?>
            <a class="day <?php if ($d == $date) echo 'active'; ?>" href="court.php?id=<?php echo $id; ?>&date=<?php echo $d; ?>">
              <small><?php echo $dn; ?></small>
              <b><?php echo date('d/m', strtotime($d)); ?></b>
            </a>
          <?php } ?>
        </div>

        <div class="legend">
          <span><i class="lg-free"></i>متاحة</span>
          <span><i class="lg-booked"></i>محجوزة</span>
          <span><i class="lg-passed"></i>انتهت</span>
          <span><i class="lg-sel"></i>اختيارك</span>
        </div>

        <div class="slots" id="slots">
          <?php for ($h = $court['open_hour']; $h < $court['close_hour']; $h++) {
            if (in_array($h, $booked)) {
              $cls = "booked"; $txt = "محجوزة";
            } else if (in_array($h, $unavailable)) {
              $cls = "passed"; $txt = "انتهت";
            } else {
              $cls = ""; $txt = "متاحة";
            }
          ?>
            <button type="button" class="slot <?php echo $cls; ?>" data-hour="<?php echo $h; ?>" <?php if ($cls != "") echo "disabled"; ?>>
              <?php echo hourLabel($h); ?>
              <small><?php echo $txt; ?></small>
            </button>
          <?php } ?>
        </div>
      </div>

      <!-- فورم الحجز -->
      <div class="panel">
        <h2>احجز الآن</h2>
        <form action="book.php" method="post" id="bookForm"
              data-price="<?php echo $court['price_hour']; ?>"
              data-open="<?php echo $court['open_hour']; ?>"
              data-close="<?php echo $court['close_hour']; ?>"
              data-unavailable="<?php echo implode(',', $unavailable); ?>">
          <input type="hidden" name="court_id" value="<?php echo $id; ?>">
          <input type="hidden" name="date" value="<?php echo $date; ?>">
          <input type="hidden" name="start_hour" id="start_hour" value="">

          <div class="form-group">
            <label>التاريخ</label>
            <input type="text" value="<?php echo $dayNames[date('w', strtotime($date))] . ' ' . date('d/m/Y', strtotime($date)); ?>" readonly>
          </div>

          <div class="form-group">
            <label for="hours">مدة الحجز</label>
            <select name="hours" id="hours">
              <option value="1">ساعة واحدة</option>
              <option value="2">ساعتان</option>
              <option value="3">ثلاث ساعات</option>
            </select>
            <div class="hint">اختر وقت البداية من الجدول ثم المدة.</div>
          </div>

          <div class="form-group">
            <label for="name">اسمك</label>
            <input type="text" name="name" id="name" placeholder="الاسم الكامل" required>
          </div>

          <div class="form-group">
            <label for="phone">رقم الجوال</label>
            <input type="tel" name="phone" id="phone" placeholder="05xxxxxxxx" maxlength="10" required>
          </div>

          <div class="summary" id="summary">
            <div><span>وقت اللعب</span><b id="sumTime">اختر ساعة من الجدول</b></div>
            <div><span>السعر الكلي</span><b id="sumTotal">—</b></div>
          </div>

          <div class="alert alert-err" id="formError" style="display:none"></div>

          <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%" disabled>تأكيد الحجز</button>
        </form>
      </div>

    </div>
  </div>
</section>

<?php include "footer.php"; ?>
