<?php
include "config.php";
$pageTitle = "حجزي";
$active = "booking";

$maxPlayers = 4; // عدد لاعبين البادل
$message = "";
$msgType = "ok";

// رقم الحجز من الرابط أو من الفورم
$code = "";
if (isset($_GET['code'])) { $code = trim($_GET['code']); }
if (isset($_POST['code'])) { $code = trim($_POST['code']); }

$found = false;
if ($code != "") {
  $result = mysqli_query($conn, "SELECT b.*, c.name AS court_name, c.location FROM bookings b JOIN courts c ON b.court_id=c.id WHERE b.code='$code'");
  if (mysqli_num_rows($result) > 0) {
    $found = true;
    $b = mysqli_fetch_assoc($result);
    $bid = $b['id'];

    // ---------- إضافة لاعب ----------
    if (isset($_POST['add_player'])) {
      $pname = trim($_POST['player_name']);
      $r = mysqli_query($conn, "SELECT id FROM players WHERE booking_id=$bid");
      if ($pname == "") {
        $message = "اكتب اسم اللاعب."; $msgType = "err";
      } else if (mysqli_num_rows($r) >= $maxPlayers) {
        $message = "وصلت للحد الأقصى ($maxPlayers لاعبين).";
        $msgType = "err";
      } else {
        mysqli_query($conn, "INSERT INTO players (booking_id, name, share, paid) VALUES ($bid, '$pname', 0, 0)");
        recalcShares($conn, $bid);
        $message = "تمت إضافة اللاعب وإعادة تقسيم المبلغ.";
      }
    }

    // ---------- تغيير حالة الدفع ----------
    if (isset($_GET['pay'])) {
      $pid = intval($_GET['pay']);
      $r = mysqli_query($conn, "SELECT paid FROM players WHERE id=$pid AND booking_id=$bid");
      if (mysqli_num_rows($r) > 0) {
        $p = mysqli_fetch_assoc($r);
        $new = 1 - $p['paid'];
        mysqli_query($conn, "UPDATE players SET paid=$new WHERE id=$pid");
        $message = "تم تحديث حالة الدفع.";
      }
    }

    // ---------- حذف لاعب ----------
    if (isset($_GET['remove'])) {
      $pid = intval($_GET['remove']);
      $r = mysqli_query($conn, "SELECT id FROM players WHERE booking_id=$bid ORDER BY id LIMIT 1");
      $first = mysqli_fetch_assoc($r);
      if ($pid == $first['id']) {
        $message = "لا يمكن حذف صاحب الحجز."; $msgType = "err";
      } else {
        mysqli_query($conn, "DELETE FROM players WHERE id=$pid AND booking_id=$bid");
        recalcShares($conn, $bid);
        $message = "تم حذف اللاعب وإعادة تقسيم المبلغ.";
      }
    }

    // ---------- جلب اللاعبين ----------
    $players = mysqli_query($conn, "SELECT * FROM players WHERE booking_id=$bid ORDER BY id");
    $count = mysqli_num_rows($players);
    $paidSum = 0;
    $rows = array();
    while ($p = mysqli_fetch_assoc($players)) {
      $rows[] = $p;
      if ($p['paid'] == 1) { $paidSum += $p['share']; }
    }
    $remaining = $b['total'] - $paidSum;
    if ($remaining < 0) { $remaining = 0; }
    $percent = 0;
    if ($b['total'] > 0) { $percent = round(($paidSum / $b['total']) * 100); }
    if ($percent > 100) { $percent = 100; }
  }
}

include "header.php";
?>

<section class="section">
  <div class="container" style="max-width:860px">

  <?php if (!$found) { ?>
    <h2 class="section-title">استعلام عن حجز</h2>
    <p class="section-sub">اكتب رقم الحجز لعرض التفاصيل وتقسيم المبلغ</p>
    <?php if ($code != "") { ?>
      <div class="alert alert-err">ما لقينا حجز بهذا الرقم، تأكد منه وحاول مرة ثانية.</div>
    <?php } ?>
    <div class="panel">
      <form method="get" action="booking.php">
        <div class="form-group">
          <label for="code">رقم الحجز</label>
          <input type="text" name="code" id="code" placeholder="RLY-20261125-0001" style="direction:ltr;text-align:left" required>
        </div>
        <button type="submit" class="btn btn-primary">بحث</button>
      </form>
      <p class="hint" style="margin-top:12px">للتجربة: <b>RLY-DEMO-01</b></p>
    </div>

  <?php } else { ?>

    <?php if ($message != "") { ?>
      <div class="alert alert-<?php echo $msgType; ?>"><?php echo $message; ?></div>
    <?php } ?>

    <div class="ticket">
      <div>
        <small>رقم الحجز</small>
        <span class="code"><?php echo $b['code']; ?></span>
        <small><?php echo htmlspecialchars($b['owner_name']); ?> · <?php echo $b['owner_phone']; ?></small>
      </div>
      <a href="court.php?id=<?php echo $b['court_id']; ?>" class="btn btn-yellow btn-sm">جدول الملعب</a>
    </div>

    <div class="panel" style="margin-top:20px">
      <h2>تفاصيل الحجز</h2>
      <div class="kv">
        <div><small>الملعب</small><b><?php echo $b['court_name']; ?></b></div>
        <div><small>الموقع</small><b><?php echo $b['location']; ?></b></div>
        <div><small>التاريخ</small><b><?php echo date('d/m/Y', strtotime($b['booking_date'])); ?></b></div>
        <div><small>الوقت</small><b><?php echo hourLabel($b['start_hour']); ?> – <?php echo hourLabel($b['start_hour'] + $b['hours']); ?></b></div>
        <div><small>السعر الكلي</small><b><?php echo $b['total']; ?> ريال</b></div>
      </div>
    </div>

    <div class="panel" style="margin-top:20px">
      <h2>تقسيم المبلغ بين اللاعبين</h2>

      <div class="summary">
        <div><span>عدد اللاعبين</span><b><?php echo $count; ?> من <?php echo $maxPlayers; ?></b></div>
        <div><span>حصة كل لاعب</span><b><?php echo calculateShare($b['total'], $count); ?> ريال</b></div>
        <div><span>المدفوع</span><b><?php echo $paidSum; ?> ريال</b></div>
        <div class="total"><span>المتبقي</span><b><?php echo $remaining; ?> ريال</b></div>
      </div>
      <div class="progress"><span style="width:<?php echo $percent; ?>%"></span></div>
      <div class="hint">تم دفع <?php echo $percent; ?>% من المبلغ</div>

      <div class="table-wrap" style="margin-top:16px">
        <table>
          <tr><th>#</th><th>اللاعب</th><th>الحصة</th><th>الحالة</th><th>إجراء</th></tr>
          <?php $i = 1; foreach ($rows as $p) { ?>
            <tr>
              <td><?php echo $i; ?></td>
              <td><?php echo htmlspecialchars($p['name']); ?> <?php if ($i == 1) echo '<span class="badge badge-warn">صاحب الحجز</span>'; ?></td>
              <td><b><?php echo $p['share']; ?></b> ريال</td>
              <td>
                <?php if ($p['paid'] == 1) { ?>
                  <span class="badge badge-ok">دفع ✔</span>
                <?php } else { ?>
                  <span class="badge badge-warn">باقي عليه</span>
                <?php } ?>
              </td>
              <td>
                <div class="actions">
                  <a class="btn btn-dark btn-sm" href="booking.php?code=<?php echo $b['code']; ?>&pay=<?php echo $p['id']; ?>">
                    <?php if ($p['paid'] == 1) echo "إلغاء الدفع"; else echo "تأكيد الدفع"; ?>
                  </a>
                  <?php if ($i > 1) { ?>
                    <a class="btn btn-danger btn-sm" href="booking.php?code=<?php echo $b['code']; ?>&remove=<?php echo $p['id']; ?>" data-confirm="حذف هذا اللاعب؟">حذف</a>
                  <?php } ?>
                </div>
              </td>
            </tr>
          <?php $i++; } ?>
        </table>
      </div>

      <?php if ($count < $maxPlayers) { ?>
        <form method="post" action="booking.php?code=<?php echo $b['code']; ?>" id="playerForm" style="margin-top:20px"
              data-total="<?php echo $b['total']; ?>" data-count="<?php echo $count; ?>">
          <input type="hidden" name="code" value="<?php echo $b['code']; ?>">
          <input type="hidden" name="add_player" value="1">
          <div class="form-group">
            <label for="player_name">إضافة لاعب</label>
            <input type="text" name="player_name" id="player_name" placeholder="اسم اللاعب" required>
            <div class="hint" id="shareHint"></div>
          </div>
          <button type="submit" class="btn btn-primary">إضافة وإعادة التقسيم</button>
        </form>
      <?php } else { ?>
        <div class="alert alert-warn" style="margin-top:16px">اكتمل عدد اللاعبين.</div>
      <?php } ?>
    </div>

  <?php } ?>

  </div>
</section>

<?php include "footer.php"; ?>
