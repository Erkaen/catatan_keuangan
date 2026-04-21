// =============================================
// Sisa Uangku - app.js
// =============================================

const NAMA_BULAN = [
  'Januari','Februari','Maret','April','Mei','Juni',
  'Juli','Agustus','September','Oktober','November','Desember'
];

// ── TOAST ──────────────────────────────────
function showToast(msg, type, ms) {
  type = type || 'success';
  ms   = ms   || 2600;
  var t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className   = 'toast ' + type;
  void t.offsetWidth;
  t.classList.add('show');
  setTimeout(function(){ t.classList.remove('show'); }, ms);
}


function openModal(id) {
  var el = document.getElementById(id);
  if (el) {
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(id) {
  var el = document.getElementById(id);
  if (el) {
    el.classList.remove('open');
    document.body.style.overflow = '';
  }
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
    document.body.style.overflow = '';
  }
  if (e.target.classList.contains('mypicker-overlay')) {
    e.target.classList.remove('open');
    document.body.style.overflow = '';
  }
  if (e.target.classList.contains('cal-overlay')) {
    e.target.classList.remove('open');
  }
});

function selectCategory(btn, hiddenId) {
  var grid = btn.closest('.category-grid');
  if (grid) {
    grid.querySelectorAll('.cat-option-btn').forEach(function(b) {
      b.classList.remove('selected');
    });
  }
  btn.classList.add('selected');
  var hidden = document.getElementById(hiddenId);
  if (hidden) hidden.value = btn.dataset.value;
}

document.querySelectorAll('.amount-field').forEach(function(inp) {
  inp.addEventListener('input', function() {
    var raw = this.value.replace(/\D/g, '');
    this.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
    var hidden = document.getElementById(this.dataset.raw);
    if (hidden) hidden.value = raw;
  });
});


function navigateMonth(dir) {
  var url = new URL(window.location.href);
  var m   = parseInt(url.searchParams.get('m') || (new Date().getMonth() + 1));
  var y   = parseInt(url.searchParams.get('y') || new Date().getFullYear());
  m += dir;
  if (m < 1)  { m = 12; y--; }
  if (m > 12) { m = 1;  y++; }
  url.searchParams.set('m', m);
  url.searchParams.set('y', y);
  window.location.href = url.toString();
}

var btnPrev = document.getElementById('btn-prev-month');
var btnNext = document.getElementById('btn-next-month');
if (btnPrev) btnPrev.addEventListener('click', function(){ navigateMonth(-1); });
if (btnNext) btnNext.addEventListener('click', function(){ navigateMonth(1); });

var mypYear = new Date().getFullYear();
var mypSelMonth = null;
var mypSelYear  = null;

function openMonthPicker() {
  var url = new URL(window.location.href);
  mypYear     = parseInt(url.searchParams.get('y') || new Date().getFullYear());
  mypSelMonth = parseInt(url.searchParams.get('m') || (new Date().getMonth() + 1));
  mypSelYear  = mypYear;
  renderMYP();
  var overlay = document.getElementById('mypicker-overlay');
  if (overlay) {
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeMYP() {
  var overlay = document.getElementById('mypicker-overlay');
  if (overlay) {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }
}

function mypNavYear(dir) {
  mypYear += dir;
  var lbl = document.getElementById('myp-year-lbl');
  if (lbl) lbl.textContent = mypYear;
  renderMYP();
}

function renderMYP() {
  var lbl = document.getElementById('myp-year-lbl');
  if (lbl) lbl.textContent = mypYear;
  var grid = document.getElementById('myp-month-grid');
  if (!grid) return;
  grid.innerHTML = '';
  var nowM = new Date().getMonth() + 1;
  var nowY = new Date().getFullYear();
  NAMA_BULAN.forEach(function(nama, i) {
    var m   = i + 1;
    var btn = document.createElement('button');
    btn.type        = 'button';
    btn.className   = 'myp-month-btn';
    btn.textContent = nama.substring(0, 3);
    if (m === mypSelMonth && mypYear === mypSelYear) btn.classList.add('is-selected');
    if (m === nowM && mypYear === nowY) btn.classList.add('is-current');
    btn.addEventListener('click', function() {
      grid.querySelectorAll('.myp-month-btn').forEach(function(b){ b.classList.remove('is-selected'); });
      btn.classList.add('is-selected');
      mypSelMonth = m;
      mypSelYear  = mypYear;
    });
    grid.appendChild(btn);
  });
}

function confirmMYP() {
  if (!mypSelMonth || !mypSelYear) {
    showToast('Pilih bulan terlebih dahulu!', 'error'); return;
  }
  var url = new URL(window.location.href);
  url.searchParams.set('m', mypSelMonth);
  url.searchParams.set('y', mypSelYear);
  closeMYP();
  window.location.href = url.toString();
}

var calTarget  = null;
var calYear    = new Date().getFullYear();
var calMonth   = new Date().getMonth();
var calSelDate = null;

function openCal(target) {
  calTarget  = target;
  var today  = new Date();
  calYear    = today.getFullYear();
  calMonth   = today.getMonth();
  calSelDate = null;
  renderCal();
  var overlay = document.getElementById('cal-overlay');
  if (overlay) overlay.classList.add('open');
}

function closeCal() {
  var overlay = document.getElementById('cal-overlay');
  if (overlay) overlay.classList.remove('open');
  calTarget = null;
}

function calNavMonth(dir) {
  calMonth += dir;
  if (calMonth < 0)  { calMonth = 11; calYear--; }
  if (calMonth > 11) { calMonth = 0;  calYear++; }
  renderCal();
}

function renderCal() {
  var lbl = document.getElementById('cal-month-label');
  if (lbl) lbl.textContent = NAMA_BULAN[calMonth] + ' ' + calYear;
  var grid  = document.getElementById('cal-days-grid');
  if (!grid) return;
  grid.innerHTML = '';
  var today   = new Date();
  today.setHours(0,0,0,0);
  var firstDay = new Date(calYear, calMonth, 1).getDay();
  var offset   = (firstDay === 0) ? 6 : firstDay - 1;
  var totalDays = new Date(calYear, calMonth + 1, 0).getDate();

  for (var i = 0; i < offset; i++) {
    var empty = document.createElement('div');
    empty.className = 'cal-day';
    grid.appendChild(empty);
  }

  for (var d = 1; d <= totalDays; d++) {
    (function(day) {
      var cell    = document.createElement('div');
      var thisDay = new Date(calYear, calMonth, day);
      thisDay.setHours(0,0,0,0);
      var isFuture  = thisDay > today;
      var isToday   = thisDay.getTime() === today.getTime();
      var isSel     = calSelDate && calSelDate.getTime() === thisDay.getTime();
      cell.className = 'cal-day';
      cell.textContent = day;
      if (isFuture) {
        cell.classList.add('is-future');
      } else {
        cell.classList.add('is-clickable');
        if (isToday) cell.classList.add('is-today');
        if (isSel)   cell.classList.add('is-selected');
        cell.addEventListener('click', function() {
          calSelDate = new Date(calYear, calMonth, day);
          renderCal();
        });
      }
      grid.appendChild(cell);
    })(d);
  }
}

function confirmCal() {
  if (!calSelDate) { showToast('Pilih tanggal terlebih dahulu!', 'error'); return; }
  var y  = calSelDate.getFullYear();
  var m  = String(calSelDate.getMonth() + 1).padStart(2, '0');
  var d  = String(calSelDate.getDate()).padStart(2, '0');
  var iso = y + '-' + m + '-' + d;
  var disp = d + '/' + m + '/' + y;
  var valEl  = document.getElementById('date-val-' + calTarget);
  var dispEl = document.getElementById('date-disp-' + calTarget);
  if (valEl)  valEl.value = iso;
  if (dispEl) { dispEl.textContent = disp; dispEl.classList.add('has-value'); }
  closeCal();
  calSelDate = null;
}

document.querySelectorAll('.tx-delete-btn').forEach(function(btn) {
  btn.addEventListener('click', async function() {
    if (!confirm('Hapus transaksi ini?')) return;
    var id  = this.dataset.id;
    var row = this.closest('.tx-row');
    try {
      var res  = await fetch('ajax/delete_transaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
      });
      var json = await res.json();
      if (json.success) {
        row.style.transition = 'opacity 0.3s, transform 0.3s';
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        setTimeout(function(){ row.remove(); location.reload(); }, 350);
        showToast('Transaksi dihapus.', 'success');
      } else {
        showToast(json.message || 'Gagal menghapus.', 'error');
      }
    } catch(e) {
      showToast('Gagal menghubungi server.', 'error');
    }
  });
});

function setupForm(formId, modalId, rawId) {
  var form = document.getElementById(formId);
  if (!form) return;
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
  
    var dateEl = document.getElementById('date-val-' + (formId === 'form-masuk' ? 'masuk' : 'keluar'));
    if (!dateEl || !dateEl.value) {
      showToast('Pilih tanggal terlebih dahulu!', 'error'); return;
    }
  
    var rawEl = document.getElementById(rawId);
    if (!rawEl || !rawEl.value || parseInt(rawEl.value) <= 0) {
      showToast('Masukkan jumlah uang terlebih dahulu!', 'error'); return;
    }
    var btn  = form.querySelector('.submit-btn');
    var orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> Menyimpan...';
    btn.disabled  = true;
    try {
      var res  = await fetch('ajax/save_transaction.php', {
        method: 'POST',
        body: new FormData(form)
      });
      var json = await res.json();
      if (json.success) {
        showToast(json.message, 'success');
        closeModal(modalId);
        setTimeout(function(){ location.reload(); }, 900);
      } else {
        showToast(json.message || 'Terjadi kesalahan.', 'error');
        btn.innerHTML = orig;
        btn.disabled  = false;
      }
    } catch(e) {
      showToast('Gagal menghubungi server.', 'error');
      btn.innerHTML = orig;
      btn.disabled  = false;
    }
  });
}

setupForm('form-masuk',  'modal-masuk',  'raw-masuk');
setupForm('form-keluar', 'modal-keluar', 'raw-keluar');
