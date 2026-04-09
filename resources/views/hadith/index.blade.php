@extends('telegram::layouts.mini-app')

@section('title', 'Koleksi Hadits')

@section('content')
<div class="container py-3" style="max-width:600px; margin:0 auto;">
  <div id="hadith-app">
    <div class="text-center py-5" id="loading-view">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">
        Memuat kitab hadits...
      </p>
    </div>
    <div id="books-view" style="display:none;"></div>
    <div id="detail-view" style="display:none;"></div>
  </div>
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
  .pagination .page-link {
    background-color: var(--tg-theme-bg-color);
    color: var(--tg-theme-text-color);
    border-color: var(--tg-theme-section-separator-color);
  }
  .pagination .active .page-link {
    background-color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
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
</style>
@endpush

@push('scripts')
<script>
  (function() {
  const { fetchWithAuth, showToast, showLoading, hideLoading, escapeHtml } = window.TelegramApp;

  let allBooks = [];
  let currentBook = null;
  let currentPage = 1;
  let currentSearch = '';

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
  </div>
  `;
  container.innerHTML = html;
  renderBooksList(allBooks, '');
  document.getElementById('searchBook').addEventListener('input', (e) => {
  renderBooksList(allBooks, e.target.value);
  });
  document.getElementById('books-view').style.display = 'block';
  document.getElementById('detail-view').style.display = 'none';
  document.getElementById('loading-view').style.display = 'none';
  } catch (err) {
  showToast('Gagal memuat kitab: ' + err.message);
  document.getElementById('loading-view').innerHTML = `<div class="alert alert-danger">Gagal memuat kitab: ${err.message}</div>`;
  } finally {
  hideLoading();
  }
  }

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
  </div>
  `;
  });
  listContainer.innerHTML = html;
  document.querySelectorAll('.book-item').forEach(el => {
  el.addEventListener('click', () => {
  const slug = el.dataset.slug;
  loadHadiths(slug);
  });
  });
  }

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
  </div>
  `;
  document.getElementById('detail-view').innerHTML = html;
  document.getElementById('books-view').style.display = 'none';
  document.getElementById('detail-view').style.display = 'block';
  renderHadiths(hadiths.data, search);
  renderPagination(hadiths, slug, search);
  document.getElementById('backToBooksBtn').addEventListener('click', () => renderBooks());
  document.getElementById('searchForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const newSearch = document.getElementById('searchHadith').value;
  loadHadiths(slug, 1, newSearch);
  });
  }

  function renderHadiths(hadiths, searchTerm) {
  const container = document.getElementById('hadithsList');
  if (!hadiths.length) {
  container.innerHTML = '<div class="text-center text-muted py-4">Tidak ada hadits</div>';
  return;
  }
  let html = '';
  hadiths.forEach(h => {
  html += `
  <div class="hadith-item p-3 mb-3">
  <div class="d-flex justify-content-between">
  <span class="badge bg-primary">Hadits No. ${h.number}</span>
  </div>
  <div class="arabic-text text-end my-2" style="font-size:1.8rem; font-family: 'Traditional Arabic', serif;">${h.arabic}</div>
  <div class="translation">${escapeHtml(h.translation)}</div>
  </div>
  `;
  });
  container.innerHTML = html;
  if (searchTerm) highlightText(container, searchTerm);
  }

  function highlightText(container, term) {
  const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  container.querySelectorAll('.translation, .arabic-text').forEach(el => {
  const original = el.innerText;
  const highlighted = original.replace(regex, '<mark>$1</mark>');
  el.innerHTML = highlighted;
  });
  }

  function renderPagination(pagination, slug, searchTerm) {
  const container = document.getElementById('paginationContainer');
  if (pagination.last_page <= 1) {
  container.innerHTML = '';
  return;
  }
  let html = '<ul class="pagination pagination-sm justify-content-center">';
  for (let i = 1; i <= pagination.last_page; i++) {
  const active = i === pagination.current_page ? 'active' : '';
  html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
  }
  html += '</ul>';
  container.innerHTML = html;
  document.querySelectorAll('.page-link').forEach(link => {
  link.addEventListener('click', (e) => {
  e.preventDefault();
  const page = parseInt(link.dataset.page);
  loadHadiths(slug, page, searchTerm);
  });
  });
  }

  renderBooks();
  })();
</script>
@endpush