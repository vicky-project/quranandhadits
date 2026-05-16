@extends('telegram::layouts.mini-app')

@section('title', 'Koleksi Hadits')

@section('content')
<div id="hadith-app">
  <div id="books-view" style="display:none;"></div>
  <div id="detail-view" style="display:none;"></div>
</div>
@endsection

@push('styles')
<style>
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
  .btn-primary {
    background-color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .btn-outline-secondary {
    border-color: var(--tg-theme-section-separator-color);
    color: var(--tg-theme-hint-color);
  }
  .text-muted {
    color: var(--tg-theme-hint-color) !important;
  }
  .book-item, .hadith-item {
    background-color: var(--tg-theme-secondary-bg-color);
    border: 1px solid var(--tg-theme-section-separator-color);
    color: var(--tg-theme-text-color);
    border-radius: 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s;
  }
  .book-item:active, .hadith-item:active {
    background-color: var(--tg-theme-section-separator-color);
    transform: scale(0.98);
  }
  mark {
    background-color: #ffeb3b;
    color: #000;
  }
  @media (prefers-color-scheme: dark) {
    mark {
      background-color: #f9a825;
      color: #1a1a1a;
    }
  }

  /* Tombol Simpan ke Notes */
  .btn-save-note {
    background: rgba(255, 193, 7, 0.15);
    border: 1px solid rgba(255, 193, 7, 0.3);
    color: #ffc107;
    font-size: 0.8rem;
    padding: 4px 10px;
    border-radius: 8px;
    transition: all 0.2s;
  }
  .btn-save-note:hover, .btn-save-note:active {
    background: rgba(255, 193, 7, 0.3);
    border-color: #ffc107;
  }
  .btn-save-note:disabled {
    opacity: 0.7;
  }
</style>
@endpush

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/eruda"></script>
<script>
  eruda.init();
</script>
<script>
  window.NotesConfig = @json($notesConfig ?? ['notesAvailable' => false, 'notesEndpoint' => null]);
</script>
<script>
  (function() {
  const { fetchWithAuth, showToast, showLoading, hideLoading, escapeHtml, renderPagination } = window.TelegramApp;

  let allBooks = [];
  let currentBook = null;
  let currentPage = 1;
  let currentSearch = '';

  // ========== RENDER BOOKS ==========
  async function renderBooks() {
  showLoading('Memuat kitab...');
  try {
  allBooks = await fetchWithAuth('{{ config("app.url") }}/api/hadith/books');
  const container = document.getElementById('books-view');
  let html = `
  <div class="card shadow">
  <div class="card-header">
  <h4 class="mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Kitab Hadits</h4>
  </div>
  <div class="card-body">
  <div class="mb-3">
  <input type="text" id="searchBook" class="form-control" placeholder="Cari kitab...">
  </div>
  <div id="booksList"></div>
  </div>
  </div>`;
  container.innerHTML = html;
  renderBooksList(allBooks, '');
  document.getElementById('searchBook').addEventListener('input', (e) => {
  renderBooksList(allBooks, e.target.value);
  });
  document.getElementById('books-view').style.display = 'block';
  document.getElementById('detail-view').style.display = 'none';
  } catch (err) {
  showToast('Gagal memuat kitab: ' + err.message);
  document.getElementById('books-view').style.display = 'block';
  document.getElementById('books-view').innerHTML = `<div class="alert alert-danger">Gagal memuat kitab: ${err.message}</div>`;
  } finally {
  hideLoading();
  }
  }

  // ========== RENDER BOOKS LIST ==========
  function renderBooksList(books, filter) {
  const listContainer = document.getElementById('booksList');
  const term = filter.toLowerCase();
  const filtered = books.filter(b => b.name.toLowerCase().includes(term));
  if (filtered.length === 0) {
  listContainer.innerHTML = '<div class="text-center text-muted py-4">Tidak ada kitab</div>';
  return;
  }
  let html = '';
  filtered.forEach(book => {
  html += `
  <div class="book-item p-3" data-slug="${book.slug}">
  <div class="d-flex justify-content-between align-items-center">
  <div>
  <strong>${escapeHtml(book.name)}</strong>
  <div class="small text-muted">Total ${book.total_hadiths} hadits</div>
  </div>
  <i class="bi bi-chevron-right"></i>
  </div>
  </div>`;
  });
  listContainer.innerHTML = html;
  document.querySelectorAll('.book-item').forEach(el => {
  el.addEventListener('click', () => {
  const slug = el.dataset.slug;
  loadHadiths(slug);
  });
  });
  }

  // ========== LOAD HADITHS ==========
  async function loadHadiths(slug, page = 1, search = '') {
  showLoading('Memuat hadits...');
  currentBook = slug;
  currentPage = page;
  currentSearch = search;
  try {
  let url = `{{ config("app.url") }}/api/hadith/book/${slug}?page=${page}`;
  if (search) url += `&q=${encodeURIComponent(search)}`;
  const data = await fetchWithAuth(url);
  renderDetailView(data, slug, page, search);
  } catch (err) {
  showToast('Gagal memuat hadits: ' + err.message);
  renderBooks();
  } finally {
  hideLoading();
  }
  }

  // ========== RENDER DETAIL VIEW ==========
  function renderDetailView(data, slug, page, search) {
  const book = data.book;
  const hadiths = data.hadiths;
  let html = `
  <div class="mb-3">
  <button id="backToBooksBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Daftar Kitab</button>
  </div>
  <div class="card shadow">
  <div class="card-header">
  <h4 class="mb-0">${escapeHtml(book.name)}</h4>
  <small>Total ${book.total_hadiths} hadits</small>
  </div>
  <div class="card-body">
  <form id="searchForm" class="mb-3">
  <div class="input-group">
  <input type="text" id="searchHadith" class="form-control" placeholder="Cari hadits..." value="${escapeHtml(search)}">
  <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
  </div>
  </form>
  ${search ? `<div class="alert alert-info text-center py-2">Menampilkan <strong>${hadiths.data.length}</strong> dari <strong>${hadiths.total}</strong> hadits dengan kata "<strong>${escapeHtml(search)}</strong>" (halaman ${hadiths.current_page} dari ${hadiths.last_page})</div>` : ''}
  <div id="hadithsList"></div>
  <div id="paginationContainer" class="d-flex justify-content-center mt-4"></div>
  </div>
  </div>`;
  document.getElementById('detail-view').innerHTML = html;
  document.getElementById('books-view').style.display = 'none';
  document.getElementById('detail-view').style.display = 'block';
  renderHadiths(hadiths.data, search);
  renderPagination('paginationContainer', hadiths.current_page, hadiths.last_page, (page) => loadHadiths(slug, page, currentSearch));
  document.getElementById('backToBooksBtn').addEventListener('click', () => renderBooks());
  document.getElementById('searchForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const newSearch = document.getElementById('searchHadith').value;
  loadHadiths(slug, 1, newSearch);
  });
  }

  // ========== RENDER HADITHS ==========
  function renderHadiths(hadiths, searchTerm) {
  const container = document.getElementById('hadithsList');
  if (!hadiths.length) {
  container.innerHTML = '<div class="text-center text-muted py-4">Tidak ada hadits</div>';
  return;
  }
  let html = '';
  hadiths.forEach(h => {
  // Escape payload
  const payload = buildNotePayload(h);
  const payloadStr = JSON.stringify(payload).replace(/"/g, '&quot;').replace(/'/g, "&#39;");

  const saveButtonHtml = window.NotesConfig?.notesAvailable ? `
  <button class="btn btn-save-note btn-sm save-to-notes-btn"
  data-payload="${payloadStr}">
  <i class="bi bi-journal-plus"></i>
  </button>` : '';

  html += `
  <div class="hadith-item p-3 mb-3">
  <div class="d-flex justify-content-between ${saveButtonHtml ? 'align-items-center' : 'align-items-start'} mb-2">
  <span class="badge bg-primary">Hadits No. ${h.number}</span>
  ${saveButtonHtml}
  </div>
  <div class="arabic-text text-end my-2" style="font-size:2.8rem; font-family: 'Traditional Arabic', serif;">${h.arabic}</div>
  <div class="translation">${escapeHtml(h.translation)}</div>
  </div>`;
  });
  container.innerHTML = html;

  if (searchTerm) highlightText(container, searchTerm);

  // Pasang listener
  if (window.NotesConfig?.notesAvailable) {
  attachSaveButtonListeners(container);
  }
  }

  // ========== BUILD PAYLOAD ==========
  function buildNotePayload(h) {
  return {
  title: `Hadits dari ${currentBook} No. ${h.number}`,
  content: `<div style="font-family: 'Traditional Arabic', serif; font-size:1.8rem; text-align:right;">${h.arabic}</div>
  <p>"${h.translation}"</p>`,
  type: 'text',
  tags: ['hadits', currentBook, `no-${h.number}`],
  source_module: 'QuranAndHadits', // atau 'Hadith' sesuai modul
  source_id: `${currentBook}:${h.number}`,
  metadata: {
  book: currentBook,
  number: h.number,
  arabic: h.arabic,
  translation: h.translation
  }
  };
  }

  // ========== ATTACH LISTENERS ==========
  function attachSaveButtonListeners(container) {
  container.querySelectorAll('.save-to-notes-btn').forEach(btn => {
  btn.addEventListener('click', async function(e) {
  e.stopPropagation();
  let payload;
  try {
  const str = this.dataset.payload.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
  payload = JSON.parse(str);
  } catch (err) {
  showToast('❌ Gagal membaca data hadits', 'danger');
  return;
  }

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

  // ========== HIGHLIGHT TEXT ==========
  function highlightText(container, term) {
  const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  container.querySelectorAll('.translation, .arabic-text').forEach(el => {
  const original = el.innerText;
  const highlighted = original.replace(regex, '<mark>$1</mark>');
  el.innerHTML = highlighted;
  });
  }

  // ========== INIT ==========
  renderBooks();
  })();
  </script>
  @endpush