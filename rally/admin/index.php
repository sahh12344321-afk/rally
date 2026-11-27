<?php
include "../config.php";
$base = "../";
$pageTitle = "لوحة التحكم";
$active = "admin";

$message = "";

// ---------- حذف ملعب (مع حجوزاته) ----------
if (isset($_GET['delete'])) {
  $cid = intval($_GET['delete']);
  mysqli_query($conn, "DELETE FROM players WHERE booking_id IN (SELECT id FROM bookings WHERE court_id=$cid)");
  mysqli_query($conn, "DELETE FROM booking_slots WHERE court_id=$cid");
  mysqli_query($conn, "DELETE FROM bookings WHERE court_id=$cid");
  mysqli_query($conn, "DELETE FROM courts WHERE id=$cid");
  $message = "تم حذف الملعب وجميع حجوزاته.";
}

// ---------- الإحصائيات ----------
$today = date('Y-m-d');
$r = mysqli_query($conn, "SELECT id FROM courts");
$courtsCount = mysqli_num_rows($r);
$r = mysqli_query($conn, "SELECT id FROM bookings WHERE booking_date='$today'");
$todayCount = mysqli_num_rows($r);
$r = mysqli_query($conn, "SELECT id FROM bookings WHERE booking_date>='$today'");
$upcomingCount = mysqli_num_rows($r);
$r = mysqli_query($conn, "SELECT SUM(total) AS s FROM bookings WHERE booking_date>='$today'");
$row = mysqli_fetch_assoc($r);
$revenue = intval($row['s']);

$courts = mysqli_query($conn, "SELECT * FROM courts ORDER BY id");

include "../header.php";
?>

<section class="section">
  <div class="container">

    <?php if ($message != "") { ?><div class="alert alert-ok"><?php echo $message; ?></div><?php } ?>

    <div class="stats">
      <div class="stat"><small>عدد الملاعب</small><b><?php echo $courtsCount; ?></b></div>
      <div class="stat"><small>حجوزات اليوم</small><b><?php echo $todayCount; ?></b></div>
      <div class="stat"><small>الحجوزات القادمة</small><b><?php echo $upcomingCount; ?></b></div>
      <div class="stat"><small>إيراد الحجوزات القادمة</small><b><?php echo $revenue; ?> <small>ريال</small></b></div>
    </div>

    <div class="title-row" style="margin-bottom:14px">
      <h2 class="section-title">إدارة الملاعب</h2>
      <div class="actions">
        <a href="bookings.php" class="btn btn-dark">إدارة الحجوزات</a>
        <a href="court_form.php" class="btn btn-primary">+ إضافة ملعب</a>
      </div>
    </div>

    <div class="panel">
      <div class="table-wrap">
        <table>
          <tr><th>الصورة</th><th>الملعب</th><th>النوع</th><th>الموقع</th><th>سعر الساعة</th><th>أوقات العمل</th><th>إجراء</th></tr>
          <?php while ($c = mysqli_fetch_assoc($courts)) { ?>
            <tr>
              <td><img class="thumb" src="../images/courts/<?php echo $c['image']; ?>" alt=""></td>
              <td><b><?php echo $c['name']; ?></b></td>
              <td><span class="badge badge-warn"><?php echo $c['type']; ?></span></td>
              <td><?php echo $c['location']; ?></td>
              <td><?php echo $c['price_hour']; ?> ريال</td>
              <td><?php echo hourLabel($c['open_hour']); ?> – <?php echo hourLabel($c['close_hour']); ?></td>
              <td>
                <div class="actions">
                  <a href="court_form.php?id=<?php echo $c['id']; ?>" class="btn btn-dark btn-sm">تعديل</a>
                  <a href="index.php?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" data-confirm="حذف الملعب سيحذف جميع حجوزاته. متأكد؟">حذف</a>
                </div>
              </td>
            </tr>
          <?php } ?>
        </table>
      </div>
    </div>

  </div>
</section>

<?php include "../footer.php"; ?>
