<?php
include "../config.php";
$base = "../";
$active = "admin";

// هل هو تعديل (id موجود) أم إضافة؟
$id = 0;
if (isset($_GET['id'])) { $id = intval($_GET['id']); }

$court = array("name" => "", "type" => "داخلي", "location" => "", "image" => "", "price_hour" => "", "open_hour" => 16, "close_hour" => 24);
if ($id > 0) {
  $r = mysqli_query($conn, "SELECT * FROM courts WHERE id=$id");
  if (mysqli_num_rows($r) > 0) {
    $court = mysqli_fetch_assoc($r);
  } else {
    $id = 0;
  }
}

$errors = array();
$saved = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $name = trim($_POST['name']);
  $type = $_POST['type'];
  $location = trim($_POST['location']);
  $price = intval($_POST['price_hour']);
  $open = intval($_POST['open_hour']);
  $close = intval($_POST['close_hour']);
  $image = $court['image'];

  if ($name == "") { $errors[] = "اسم الملعب مطلوب."; }
  if (!in_array($type, $courtTypes)) { $errors[] = "نوع الملعب غير صحيح."; }
  if ($location == "") { $errors[] = "موقع الملعب مطلوب."; }
  if ($price <= 0) { $errors[] = "سعر الساعة يجب أن يكون أكبر من صفر."; }
  if ($open < 0 || $close > 24 || $open >= $close) { $errors[] = "وقت الإغلاق يجب أن يكون بعد وقت الفتح."; }

  // ---------- رفع الصورة ----------
  if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = array("jpg", "jpeg", "png", "webp");
    if (!in_array($ext, $allowed)) {
      $errors[] = "الصورة يجب أن تكون jpg أو png أو webp.";
    } else {
      $newName = time() . "_" . rand(100, 999) . "." . $ext;
      if (move_uploaded_file($_FILES['image']['tmp_name'], "../images/courts/" . $newName)) {
        $image = $newName;
      } else {
        $errors[] = "تعذر رفع الصورة.";
      }
    }
  }
  if ($image == "") { $image = "court1.svg"; }

  // ---------- عدم تعارض أوقات العمل مع حجوزات قائمة (عند التعديل) ----------
  if (count($errors) == 0 && $id > 0) {
    $chk = mysqli_query($conn, "SELECT id FROM booking_slots WHERE court_id=$id AND slot_date>=CURDATE() AND (slot_hour<$open OR slot_hour>=$close)");
    if (mysqli_num_rows($chk) > 0) {
      $errors[] = "يوجد حجوزات قادمة خارج أوقات العمل الجديدة. ألغها أولًا من صفحة الحجوزات.";
    }
  }

  if (count($errors) == 0) {
    if ($id > 0) {
      $sql = "UPDATE courts SET name='$name', type='$type', location='$location', image='$image',
              price_hour=$price, open_hour=$open, close_hour=$close WHERE id=$id";
    } else {
      $sql = "INSERT INTO courts (name, type, location, image, price_hour, open_hour, close_hour)
              VALUES ('$name', '$type', '$location', '$image', $price, $open, $close)";
    }
    mysqli_query($conn, $sql);
    $saved = true;
  }

  // إبقاء القيم في الفورم
  $court = array("name" => $name, "type" => $type, "location" => $location, "image" => $image,
                 "price_hour" => $price, "open_hour" => $open, "close_hour" => $close);
}

if ($id > 0) { $pageTitle = "تعديل ملعب"; } else { $pageTitle = "إضافة ملعب"; }
include "../header.php";
?>

<section class="section">
  <div class="container" style="max-width:760px">

    <div class="title-row" style="margin-bottom:14px">
      <h2 class="section-title"><?php echo $pageTitle; ?></h2>
      <a href="index.php" class="btn btn-outline btn-sm">العودة للوحة</a>
    </div>

    <?php if ($saved) { ?>
      <div class="alert alert-ok">تم حفظ بيانات الملعب بنجاح ✔ <a href="index.php"><b>عرض قائمة الملاعب</b></a></div>
    <?php } ?>
    <?php if (count($errors) > 0) { ?>
      <div class="alert alert-err"><?php foreach ($errors as $e) { echo $e . "<br>"; } ?></div>
    <?php } ?>

    <div class="panel">
      <form method="post" enctype="multipart/form-data" action="court_form.php<?php if ($id > 0) echo '?id=' . $id; ?>">
        <div class="form-grid">
          <div class="form-group">
            <label for="name">اسم الملعب</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($court['name']); ?>" required>
          </div>
          <div class="form-group">
            <label for="type">نوع الملعب</label>
            <select name="type" id="type">
              <?php foreach ($courtTypes as $t) { ?>
                <option value="<?php echo $t; ?>" <?php if ($court['type'] == $t) echo "selected"; ?>><?php echo $t; ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label for="location">الموقع</label>
            <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($court['location']); ?>" placeholder="الحي، المدينة" required>
          </div>
          <div class="form-group">
            <label for="price_hour">سعر الساعة (ريال)</label>
            <input type="number" name="price_hour" id="price_hour" min="1" value="<?php echo $court['price_hour']; ?>" required>
          </div>
          <div class="form-group">
            <label for="open_hour">يفتح الساعة</label>
            <select name="open_hour" id="open_hour">
              <?php for ($h = 0; $h <= 23; $h++) { ?>
                <option value="<?php echo $h; ?>" <?php if ($court['open_hour'] == $h) echo "selected"; ?>><?php echo hourLabel($h); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label for="close_hour">يغلق الساعة</label>
            <select name="close_hour" id="close_hour">
              <?php for ($h = 1; $h <= 24; $h++) { ?>
                <option value="<?php echo $h; ?>" <?php if ($court['close_hour'] == $h) echo "selected"; ?>><?php echo hourLabel($h); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="image">صورة الملعب</label>
          <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp">
          <?php if ($court['image'] != "") { ?>
            <img class="preview-img" src="../images/courts/<?php echo $court['image']; ?>" alt="">
            <div class="hint">اترك الحقل فارغًا للإبقاء على الصورة الحالية.</div>
          <?php } ?>
        </div>

        <button type="submit" class="btn btn-primary">حفظ الملعب</button>
      </form>
    </div>
  </div>
</section>

<?php include "../footer.php"; ?>
