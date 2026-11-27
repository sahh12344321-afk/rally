<?php
include "config.php";
$pageTitle = "الملاعب";
$active = "courts";
include "header.php";

// الفلترة حسب نوع الملعب
$type = "";
if (isset($_GET['type'])) { $type = $_GET['type']; }

if (in_array($type, $courtTypes)) {
  $sql = "SELECT * FROM courts WHERE type='$type' ORDER BY id";
} else {
  $type = "";
  $sql = "SELECT * FROM courts ORDER BY id";
}
$result = mysqli_query($conn, $sql);
?>

<section class="section">
  <div class="container">
    <h2 class="section-title">ملاعب البادل</h2>
    <p class="section-sub">اختر الملعب المناسب لك وشوف جدول مواعيده</p>

    <div class="filters">
      <a href="courts.php" class="chip <?php if ($type == '') echo 'active'; ?>">الكل</a>
      <?php foreach ($courtTypes as $t) { ?>
        <a href="courts.php?type=<?php echo urlencode($t); ?>" class="chip <?php if ($type == $t) echo 'active'; ?>"><?php echo $t; ?></a>
      <?php } ?>
    </div>

    <?php if (mysqli_num_rows($result) == 0) { ?>
      <div class="panel empty">ما فيه ملاعب من هذا النوع حاليًا.</div>
    <?php } else { ?>
    <div class="grid">
      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="card">
          <div class="card-img">
            <img src="images/courts/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
            <span class="badge badge-type"><?php echo $row['type']; ?></span>
          </div>
          <div class="card-body">
            <h3><?php echo $row['name']; ?></h3>
            <div class="meta">📍 <?php echo $row['location']; ?></div>
            <div class="meta">🕒 من <?php echo hourLabel($row['open_hour']); ?> إلى <?php echo hourLabel($row['close_hour']); ?></div>
            <div class="card-foot">
              <div class="price"><?php echo $row['price_hour']; ?> ريال <small>/ الساعة</small></div>
              <a href="court.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">المواعيد والحجز</a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <?php } ?>
  </div>
</section>

<?php include "footer.php"; ?>
