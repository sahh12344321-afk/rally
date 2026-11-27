// ===== Rally - JavaScript =====

// تأكيد قبل الحذف: أي رابط فيه data-confirm
var confirmLinks = document.querySelectorAll("[data-confirm]");
for (var i = 0; i < confirmLinks.length; i++) {
  confirmLinks[i].addEventListener("click", function (e) {
    if (!confirm(this.getAttribute("data-confirm"))) {
      e.preventDefault();
    }
  });
}

// ===== صفحة الملعب: اختيار الساعة وحساب السعر =====
var bookForm = document.getElementById("bookForm");
if (bookForm) {
  var price = parseInt(bookForm.getAttribute("data-price"));
  var closeHour = parseInt(bookForm.getAttribute("data-close"));
  var unavailableText = bookForm.getAttribute("data-unavailable");
  var unavailable = [];
  if (unavailableText != "") {
    unavailable = unavailableText.split(",").map(Number);
  }

  var slots = document.querySelectorAll("#slots .slot");
  var hoursSelect = document.getElementById("hours");
  var startInput = document.getElementById("start_hour");
  var sumTime = document.getElementById("sumTime");
  var sumTotal = document.getElementById("sumTotal");
  var submitBtn = document.getElementById("submitBtn");
  var formError = document.getElementById("formError");
  var startHour = null;

  function hourText(h) {
    h = h % 24;
    var ampm = h >= 12 ? "م" : "ص";
    var x = h % 12;
    if (x == 0) x = 12;
    return x + ":00 " + ampm;
  }

  function update() {
    // تنظيف الألوان
    for (var i = 0; i < slots.length; i++) {
      slots[i].classList.remove("selected");
      slots[i].classList.remove("in-range");
    }
    formError.style.display = "none";

    if (startHour === null) {
      sumTime.textContent = "اختر ساعة من الجدول";
      sumTotal.textContent = "—";
      submitBtn.disabled = true;
      return;
    }

    var hours = parseInt(hoursSelect.value);
    var end = startHour + hours;
    var ok = true;
    var msg = "";

    if (end > closeHour) {
      ok = false;
      msg = "المدة تتجاوز وقت إغلاق الملعب.";
    }
    for (var h = startHour; h < end; h++) {
      if (unavailable.indexOf(h) != -1) {
        ok = false;
        msg = "إحدى الساعات ضمن هذه المدة غير متاحة، قلل المدة أو اختر وقتًا آخر.";
      }
    }

    // تلوين الساعات المختارة
    for (var i = 0; i < slots.length; i++) {
      var sh = parseInt(slots[i].getAttribute("data-hour"));
      if (sh == startHour) {
        slots[i].classList.add("selected");
      } else if (sh > startHour && sh < end) {
        slots[i].classList.add("in-range");
      }
    }

    startInput.value = startHour;
    sumTime.textContent = hourText(startHour) + " – " + hourText(end);
    sumTotal.textContent = (price * hours) + " ريال";
    submitBtn.disabled = !ok;

    if (!ok) {
      formError.textContent = msg;
      formError.style.display = "block";
    }
  }

  for (var i = 0; i < slots.length; i++) {
    slots[i].addEventListener("click", function () {
      startHour = parseInt(this.getAttribute("data-hour"));
      update();
    });
  }
  hoursSelect.addEventListener("change", update);
}

// ===== صفحة الحجز: معاينة الحصة بعد إضافة لاعب =====
var playerForm = document.getElementById("playerForm");
if (playerForm) {
  var total = parseFloat(playerForm.getAttribute("data-total"));
  var count = parseInt(playerForm.getAttribute("data-count"));
  var hint = document.getElementById("shareHint");
  var share = (total / (count + 1)).toFixed(2);
  hint.textContent = "بعد إضافة لاعب جديد تصير حصة كل لاعب: " + share + " ريال";
}
