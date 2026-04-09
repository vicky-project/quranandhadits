@extends('telegram::layouts.mini-app')

@section('title', 'Al-Qur\'an')

@section('content')
<div class="container py-3" style="max-width:600px; margin:0 auto;">
  <div id="quran-app">
    <div class="text-center py-5" id="loading-view">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">
        Memuat daftar surah...
      </p>
    </div>
    <div id="surah-view" style="display:none;"></div>
    <div id="detail-view" style="display:none;"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function() {
  // ==================== AMBIL FUNGSI GLOBAL DARI LAYOUT ====================
  const { fetchWithAuth, showToast, showLoading, hideLoading, escapeHtml } = window.TelegramApp;

  let allSurahs = [];
  let currentSurah = null;
  let currentPage = 1;
  let currentSearch = '';

  // Render daftar surah (tanpa refresh)
  async function renderSurahs() {
  showLoading('Memuat surah...');
  try {
  allSurahs = await fetchWithAuth('{{ config("app.url") }}/api/quran/surahs');
  const container = document.getElementById('surah-view');
  let html = `
  <div class="card shadow">
  <div class="card-header bg-primary text-white">
  <h4 class="mb-0"><i class="bi bi-book me-2"></i>Daftar Surah</h4>
  </div>
  <div class="card-body">
  <div class="mb-3">
  <input type="text" id="searchSurah" class="form-control" placeholder="Cari surah (nama latin, arab, arti)...">
  </div>
  <div id="surahList"></div>
  </div>
  </div>
  `;
  container.innerHTML = html;
  renderSurahList(allSurahs, '');
  document.getElementById('searchSurah').addEventListener('input', (e) => {
  renderSurahList(allSurahs, e.target.value);
  });
  document.getElementById('surah-view').style.display = 'block';
  document.getElementById('detail-view').style.display = 'none';
  document.getElementById('loading-view').style.display = 'none';
  } catch (err) {
  showToast('Gagal memuat surah: ' + err.message);
  document.getElementById('loading-view').innerHTML = `<div class="alert alert-danger">Gagal memuat surah: ${err.message}</div>`;
  } finally {
  hideLoading();
  }
  }

  function renderSurahList(surahs, filter) {
  const listContainer = document.getElementById('surahList');
  const term = filter.toLowerCase();
  const filtered = surahs.filter(s =>
  s.name_latin.toLowerCase().includes(term) ||
  s.name.toLowerCase().includes(term) ||
  s.meaning.toLowerCase().includes(term)
  );
  if (filtered.length === 0) {
  listContainer.innerHTML = '<div class="text-center text-muted py-4">Tidak ada surah</div>';
  return;
  }
  let html = '';
  filtered.forEach(s => {
  html += `
  <div class="surah-item p-3 mb-2 border rounded-3" style="cursor:pointer; background: var(--tg-theme-secondary-bg-color);" data-number="${s.number}">
  <div class="d-flex justify-content-between align-items-center">
  <div>
  <strong>${s.number}. ${escapeHtml(s.name_latin)}</strong>
  <span class="arabic-name ms-2" style="font-family: 'Traditional Arabic', serif;">${escapeHtml(s.name)}</span>
  <div class="small text-muted">${escapeHtml(s.meaning)} • ${s.place} • ${s.number_of_verses} ayat</div>
  </div>
  <i class="bi bi-chevron-right"></i>
  </div>
  </div>
  `;
  });
  listContainer.innerHTML = html;
  document.querySelectorAll('.surah-item').forEach(el => {
  el.addEventListener('click', () => {
  const number = parseInt(el.dataset.number);
  loadSurahDetail(number);
  });
  });
  }

  async function loadSurahDetail(surahNumber, page = 1, search = '') {
  showLoading('Memuat surah...');
  currentSurah = surahNumber;
  currentPage = page;
  currentSearch = search;
  try {
  let url = `{{ config("app.url") }}/api/quran/surah/${surahNumber}?page=${page}`;
  if (search) url += `&q=${encodeURIComponent(search)}`;
  const data = await fetchWithAuth(url);
  renderDetailView(data, surahNumber, page, search);
  } catch (err) {
  showToast('Gagal memuat detail surah: ' + err.message);
  renderSurahs(); // kembali ke daftar
  } finally {
  hideLoading();
  }
  }

  function renderDetailView(data, surahNumber, page, search) {
  const surah = data.surah;
  const verses = data.verses; // { data, current_page, last_page, total }
  let html = `
  <div class="mb-3">
  <button id="backToSurahsBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Daftar Surah</button>
  </div>
  <div class="card shadow">
  <div class="card-header bg-primary text-white">
  <h4 class="mb-0">${surah.number}. ${escapeHtml(surah.name_latin)}</h4>
  <small>${escapeHtml(surah.name)} • ${escapeHtml(surah.meaning)} • ${surah.number_of_verses} ayat</small>
  </div>
  <div class="card-body">
  <form id="searchForm" class="mb-3">
  <div class="input-group">
  <input type="text" id="searchVerse" class="form-control" placeholder="Cari ayat..." value="${escapeHtml(search)}">
  <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
  </div>
  </form>
  ${search ? `<div class="alert alert-info text-center py-2">Menampilkan <strong>${verses.data.length}</strong> dari <strong>${verses.total}</strong> ayat dengan kata "<strong>${escapeHtml(search)}</strong>" (halaman ${verses.current_page} dari ${verses.last_page})</div>` : ''}
  <div id="versesList"></div>
  <div id="paginationContainer" class="d-flex justify-content-center mt-4"></div>
  </div>
  </div>
  `;
  document.getElementById('detail-view').innerHTML = html;
  document.getElementById('surah-view').style.display = 'none';
  document.getElementById('detail-view').style.display = 'block';

  renderVerses(verses.data, search);
  renderPagination(verses, surahNumber, search);

  document.getElementById('backToSurahsBtn').addEventListener('click', () => {
  renderSurahs();
  });
  const searchForm = document.getElementById('searchForm');
  searchForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const newSearch = document.getElementById('searchVerse').value;
  loadSurahDetail(surahNumber, 1, newSearch);
  });
  }

  function renderVerses(verses, searchTerm) {
  const container = document.getElementById('versesList');
  if (!verses.length) {
  container.innerHTML = '<div class="text-center text-muted py-4">Tidak ada ayat</div>';
  return;
  }
  let html = '';
  verses.forEach(v => {
  html += `
  <div class="verse-item p-3 mb-3 border rounded-3" style="background: var(--tg-theme-secondary-bg-color);">
  <div class="d-flex justify-content-between">
  <span class="badge bg-primary">${v.verse_number}</span>
  </div>
  <div class="arabic-text text-end my-2" style="font-size:1.8rem; font-family: 'Traditional Arabic', serif;">${v.arabic_text}</div>
  <div class="latin-text text-muted mb-1">${escapeHtml(v.latin_text)}</div>
  <div class="translation">${escapeHtml(v.translation)}</div>
  </div>
  `;
  });
  container.innerHTML = html;
  if (searchTerm) highlightText(container, searchTerm);
  }

  function highlightText(container, term) {
  const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  container.querySelectorAll('.latin-text, .translation, .arabic-text').forEach(el => {
  const original = el.innerText;
  const highlighted = original.replace(regex, '<mark>$1</mark>');
  el.innerHTML = highlighted;
  });
  }

  function renderPagination(verses, surahNumber, searchTerm) {
  const container = document.getElementById('paginationContainer');
  if (verses.last_page <= 1) {
  container.innerHTML = '';
  return;
  }
  let html = '<ul class="pagination pagination-sm justify-content-center">';
  for (let i = 1; i <= verses.last_page; i++) {
  const active = i === verses.current_page ? 'active' : '';
  html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
  }
  html += '</ul>';
  container.innerHTML = html;
  document.querySelectorAll('.page-link').forEach(link => {
  link.addEventListener('click', (e) => {
  e.preventDefault();
  const page = parseInt(link.dataset.page);
  loadSurahDetail(surahNumber, page, searchTerm);
  });
  });
  }

  // Mulai aplikasi
  renderSurahs();
  })();
</script>
@endpush

@push('styles')
<style>
  /* Gaya tambahan untuk Quran */
  .surah-item, .verse-item {
    transition: transform 0.1s ease;
  }
  .surah-item:active, .verse-item:active {
    transform: scale(0.98);
  }
  .arabic-text {
    direction: rtl;
    font-size: 1.8rem;
    line-height: 1.5;
  }
  mark {
    background-color: #ffeb3b;
    color: #000;
    border-radius: 4px;
    padding: 0 2px;
  }
  @media (prefers-color-scheme: dark) {
    mark {
      background-color: #f9a825;
      color: #1a1a1a;
    }
  }
  .pagination .page-link {
    background-color: var(--tg-theme-bg-color, #fff);
    color: var(--tg-theme-text-color, #000);
    border-color: var(--tg-theme-section-separator-color, #dee2e6);
    }
    .pagination .active .page-link {
    background-color: var(--tg-theme-button-color, #007aff);
    border-color: var(--tg-theme-button-color, #007aff);
    color: white;
    }
    </style>
    @endpush