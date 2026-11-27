<?php
include "config.php";
$pageTitle = "الرئيسية";
$active = "home";
include "header.php";

// أبرز 3 ملاعب
$sql = "SELECT * FROM courts ORDER BY id LIMIT 3";
$result = mysqli_query($conn, $sql);

// إحصائيات بسيطة
$r1 = mysqli_query($conn, "SELECT id FROM courts");
$courtsCount = mysqli_num_rows($r1);
$r2 = mysqli_query($conn, "SELECT id FROM bookings");
$bookingsCount = mysqli_num_rows($r2);
?>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <h1>احجز ملعب البادل <span>بالساعة</span> وقسّم المبلغ مع فريقك</h1>
      <p>اختر الملعب، شوف الساعات المتاحة، واحجز بخطوات بسيطة. وإذا حبيت تقسم السعر بين اللاعبين، النظام يحسبها لك.</p>
      <div class="hero-actions">
        <a href="courts.php" class="btn btn-primary">تصفح الملاعب</a>
        <a href="booking.php" class="btn btn-yellow">استعلم عن حجزي</a>
      </div>
    </div>
    <div class="hero-card">
      <h3>ليش Rally؟</h3>
      <ul>
        <li><span class="dot"></span> <?php echo $courtsCount; ?> ملاعب بأنواع ومواقع مختلفة</li>
        <li><span class="dot"></span> جدول مواعيد واضح لكل ملعب</li>
        <li><span class="dot"></span> الساعة تنقفل أول ما تنحجز</li>
        <li><span class="dot"></span> تقسيم المبلغ ومعرفة مين دفع</li>
        <li><span class="dot"></span> <?php echo $bookingsCount; ?> حجوزات مسجلة حتى الآن</li>
      </ul>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title">كيف تحجز؟</h2>
    <p class="section-sub">ثلاث خطوات وتكون جاهز للعب</p>
    <div class="steps">
      <div class="step"><div class="num">1</div><h3>اختر الملعب</h3><p>قارن بين الملاعب حسب النوع والموقع وسعر الساعة.</p></div>
      <div class="step"><div class="num">2</div><h3>اختر الساعة</h3><p>من جدول المواعيد، من ساعة إلى ثلاث ساعات.</p></div>
      <div class="step"><div class="num">3</div><h3>قسّم المبلغ</h3><p>أضف اللاعبين وتابع مين دفع ومين باقي عليه.</p></div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="title-row">
      <div>
        <h2 class="section-title">ملاعب مميزة</h2>
        <p class="section-sub">بعض ملاعبنا المتاحة للحجز</p>
      </div>
      <a href="courts.php" class="btn btn-outline btn-sm">عرض كل الملاعب</a>
    </div>
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
            <div class="card-foot">
              <div class="price"><?php echo $row['price_hour']; ?> ريال <small>/ الساعة</small></div>
              <a href="court.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">احجز الآن</a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</section>

<?php include "footer.php"; ?>
