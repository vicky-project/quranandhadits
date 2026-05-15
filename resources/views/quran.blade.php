@extends('telegram::layouts.mini-app')

@section('title', 'Al-Qur\'an')

@section('content')
<div id="quran-app">
  <div id="surah-view" style="display:none;"></div>
  <div id="detail-view" style="display:none;"></div>
</div>
@endsection

@push('styles')
<style>
  /* ==================== TELEGRAM THEME STYLES ==================== */
  body {
    background-color: var(--tg-theme-bg-color);
    color: var(--tg-theme-text-color);
  }
  .card {
    background-color: var(--tg-theme-secondary-bg-color);
    border-color: var(--tg-theme-section-separator-color);
  }
  .card-header {
    background-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .form-control, .input-group-text {
    background-color: var(--tg-theme-bg-color);
    color: var(--tg-theme-text-color);
    border-color: var(--tg-theme-section-separator-color);
  }
  .form-control::placeholder {
    color: var(--tg-theme-hint-color);
  }
  .btn-primary {
    background-color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .btn-outline-secondary {
    border-color: var(--tg-theme-section-separator-color);
    color: var(--tg-theme-hint-color);
  }
  .btn-outline-secondary:hover {
    background-color: var(--tg-theme-secondary-bg-color);
    color: var(--tg-theme-text-color);
  }
  .text-muted {
    color: var(--tg-theme-hint-color) !important;
  }
  .surah-item, .verse-item {
    background-color: var(--tg-theme-secondary-bg-color);
    border: 1px solid var(--tg-theme-section-separator-color);
    color: var(--tg-theme-text-color);
    border-radius: 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s;
  }
  .surah-item:active, .verse-item:active {
    background-color: var(--tg-theme-section-separator-color);
    transform: scale(0.98);
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/eruda"></script>
<script>
  eruda.init();
</script>
<script>
  window.NotesConfig = @json($notesConfig)
  (function() {
  const { fetchWithAuth, showToast, showLoading, hideLoading, escapeHtml, renderPagination } = window.TelegramApp;

  let allSurahs = [];
  let currentSurah = null;
  let currentPage = 1;
  let currentSearch = '';

  async function renderSurahs() {
  showLoading('Memuat surah...');
  try {
  allSurahs = await fetchWithAuth('{{ config("app.url") }}/api/quran/surahs');
  const container = document.getElementById('surah-view');
  let html = `
  <div class="card shadow">
  <div class="card-header">
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
  } catch (err) {
  showToast('Gagal memuat surah: ' + err.message);
  document.getElementById('surah-view').style.display = 'block';
  document.getElementById('surah-view').innerHTML = `<div class="alert alert-danger">Gagal memuat surah: ${err.message}</div>`;
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
  <div class="surah-item p-3" data-number="${s.number}">
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
  renderSurahs();
  } finally {
  hideLoading();
  }
  }

  function renderDetailView(data, surahNumber, page, search) {
  const surah = data.surah;
  const verses = data.verses;
  let html = `
  <div class="mb-3">
  <button id="backToSurahsBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Daftar Surah</button>
  </div>
  <div class="card shadow">
  <div class="card-header">
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
  <div id="paginationContainer" class="d-flex justify-content-center mt-4">
  </div>
  </div>
  </div>
  `;
  document.getElementById('detail-view').innerHTML = html;
  document.getElementById('surah-view').style.display = 'none';
  document.getElementById('detail-view').style.display = 'block';
  renderVerses(verses.data, search);
  renderPagination('paginationContainer', verses.current_page, verses.last_page, (page) => loadSurahDetail(surahNumber, page, currentSearch));
  document.getElementById('backToSurahsBtn').addEventListener('click', () => renderSurahs());
  document.getElementById('searchForm').addEventListener('submit', (e) => {
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
  const saveButtonHtml = window.NotesConfig?.notesAvailable ? `
  <button class="btn btn-save-note btn-sm save-to-notes-btn"
  data-payload='${JSON.stringify(buildNotePayload(v)).replace(/'/g, "&#39;")}'>
  <i class="bi bi-journal-plus me-1"></i> Simpan ke Notes
  </button>
  ` : '';

  html += `
  <div class="verse-item p-3 mb-3">
  <div class="d-flex justify-content-between align-items-start mb-2">
  <span class="badge bg-primary">${v.verse_number}</span>
  ${saveButtonHtml}
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
  if(window.NotesConfig?.notesAvailable) {
  attachSaveButtonListeners(container);
  }

  function buildNotePayload(v) {
  return {
  title: `QS. ${currentSurah}:${v.verse_number}`,
  content: `<div style="font-family: 'Traditional Arabic', serif; font-size:1.8rem; text-align:right;">${v.arabic_text}</div>
  <p style="color:#a0a0a0;">${v.latin_text}</p>
  <p>"${v.translation}"</p>`,
  type: 'text',
  tags: ['quran', `surah-${currentSurah}`, `ayat-${v.verse_number}`],
  source_module: 'QuranAndHadits',
  source_id: `${currentSurah}:${v.verse_number}`,
  metadata: {
  surah_number: currentSurah,
  verse_number: v.verse_number,
  arabic_text: v.arabic_text,
  latin_text: v.latin_text,
  translation: v.translation
  }
  };
  }

  function attachSaveButtonListeners(container) {
  container.querySelectorAll('.save-to-notes-btn').forEach(btn => {
  btn.addEventListener('click', async function(e) {
  e.stopPropagation();
  const payload = JSON.parse(this.dataset.payload);
  this.disabled = true;
  const originalHtml = this.innerHTML;
  this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

  try {
  await fetchWithAuth(window.NotesConfig.notesEndpoint, {
  method: 'POST',
  body: JSON.stringify(payload)
  });
  showToast('✅ Berhasil disimpan ke Notes!', 'success');
  this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Tersimpan';
  this.classList.add('btn-success');
  setTimeout(() => {
  this.disabled = false;
  this.innerHTML = originalHtml;
  this.classList.remove('btn-success');
  }, 2000);
  } catch (err) {
  showToast('❌ Gagal menyimpan: ' + err.message, 'danger');
  this.disabled = false;
  this.innerHTML = originalHtml;
  }
  });
  });
  }

  function highlightText(container, term) {
  const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  container.querySelectorAll('.latin-text, .translation, .arabic-text').forEach(el => {
  const original = el.innerText;
  const highlighted = original.replace(regex, '<mark>$1</mark>');
  el.innerHTML = highlighted;
  });
  }

  renderSurahs();
  })();
  </script>
  @endpush