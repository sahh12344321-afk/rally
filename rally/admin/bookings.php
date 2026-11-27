<?php
include "../config.php";
$base = "../";
$pageTitle = "إدارة الحجوزات";
$active = "admin";

$message = "";

// ---------- إلغاء حجز ----------
if (isset($_GET['cancel'])) {
  $bid = intval($_GET['cancel']);
  mysqli_query($conn, "DELETE FROM players WHERE booking_id=$bid");
  mysqli_query($conn, "DELETE FROM booking_slots WHERE booking_id=$bid");
  mysqli_query($conn, "DELETE FROM bookings WHERE id=$bid");
  $message = "تم إلغاء الحجز وفتح الساعات من جديد.";
}

// ---------- فلترة (حسب الملعب والتاريخ) ----------
$f_court = 0;
if (isset($_GET['court'])) { $f_court = intval($_GET['court']); }
$f_date = "";
if (isset($_GET['date']) && validDate($_GET['date'])) { $f_date = $_GET['date']; }

$where = "WHERE 1=1";
if ($f_court > 0) { $where .= " AND b.court_id=$f_court"; }
if ($f_date != "") { $where .= " AND b.booking_date='$f_date'"; }

$sql = "SELECT b.*, c.name AS court_name FROM bookings b JOIN courts c ON b.court_id=c.id $where ORDER BY b.booking_date DESC, b.start_hour";
$result = mysqli_query($conn, $sql);
$courts = mysqli_query($conn, "SELECT id, name FROM courts ORDER BY id");

include "../header.php";
?>

<section class="section">
  <div class="container">

    <div class="title-row" style="margin-bottom:14px">
      <h2 class="section-title">إدارة الحجوزات</h2>
      <a href="index.php" class="btn btn-outline btn-sm">العودة للوحة</a>
    </div>

    <?php if ($message != "") { ?><div class="alert alert-ok"><?php echo $message; ?></div><?php } ?>

    <div class="panel" style="margin-bottom:20px">
      <form method="get" action="bookings.php" class="form-grid" style="align-items:end">
        <div class="form-group">
          <label for="court">الملعب</label>
          <select name="court" id="court">
            <option value="0">كل الملاعب</option>
            <?php while ($c = mysqli_fetch_assoc($courts)) { ?>
              <option value="<?php echo $c['id']; ?>" <?php if ($f_court == $c['id']) echo "selected"; ?>><?php echo $c['name']; ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label for="date">التاريخ</label>
          <input type="date" name="date" id="date" value="<?php echo $f_date; ?>">
        </div>
        <div class="form-group actions">
          <button type="submit" class="btn btn-dark">تصفية</button>
          <a href="bookings.php" class="btn btn-outline">إعادة ضبط</a>
        </div>
      </form>
    </div>

    <div class="panel">
      <?php if (mysqli_num_rows($result) == 0) { ?>
        <div class="empty">لا توجد حجوزات.</div>
      <?php } else { ?>
      <div class="table-wrap">
        <table>
          <tr><th>رقم الحجز</th><th>الملعب</th><th>التاريخ</th><th>الوقت</th><th>صاحب الحجز</th><th>الجوال</th><th>المبلغ</th><th>الدفع</th><th>إجراء</th></tr>
          <?php while ($b = mysqli_fetch_assoc($result)) {
            // حالة الدفع لكل حجز
            $pr = mysqli_query($conn, "SELECT SUM(share) AS s FROM players WHERE booking_id=" . $b['id'] . " AND paid=1");
            $prow = mysqli_fetch_assoc($pr);
            $paid = floatval($prow['s']);
          ?>
            <tr>
              <td style="direction:ltr;text-align:right"><b><?php echo $b['code']; ?></b></td>
              <td><?php echo $b['court_name']; ?></td>
              <td><?php echo date('d/m/Y', strtotime($b['booking_date'])); ?></td>
              <td><?php echo hourLabel($b['start_hour']); ?> – <?php echo hourLabel($b['start_hour'] + $b['hours']); ?></td>
              <td><?php echo htmlspecialchars($b['owner_name']); ?></td>
              <td><?php echo $b['owner_phone']; ?></td>
              <td><?php echo $b['total']; ?> ريال</td>
              <td>
                <?php if ($paid >= $b['total']) { ?>
                  <span class="badge badge-ok">مكتمل</span>
                <?php } else { ?>
                  <span class="badge badge-warn"><?php echo $paid; ?> / <?php echo $b['total']; ?></span>
                <?php } ?>
              </td>
              <td>
                <div class="actions">
                  <a href="../booking.php?code=<?php echo $b['code']; ?>" class="btn btn-dark btn-sm">عرض</a>
                  <a href="bookings.php?cancel=<?php echo $b['id']; ?>" class="btn btn-danger btn-sm" data-confirm="إلغاء هذا الحجز؟">إلغاء</a>
                </div>
              </td>
            </tr>
          <?php } ?>
        </table>
      </div>
      <?php } ?>
    </div>

  </div>
</section>

<?php include "../footer.php"; ?>
