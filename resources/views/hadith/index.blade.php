@extends('coreui::layouts.mini-app')
@section('title', 'Koleksi Hadits')

@section('content')
<div class="container py-3">
  <div class="row justify-content-center mb-3">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('telegram.home') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
      </div>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Kitab Hadits</h4>
        </div>
        <div class="card-body">
          <div class="position-relative mb-3">
            <input type="text" id="searchBook" class="form-control" placeholder="Cari kitab...">
            <button id="clearSearch" class="btn btn-link position-absolute end-0 top-0 text-muted d-none" style="padding: 0.375rem 0.75rem;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div id="bookList">
            @foreach($books as $book)
            <a href="{{ route('apps.hadith.show', $book->slug) }}" class="text-decoration-none">
              <div class="list-group-item d-flex justify-content-between align-items-center mb-2 rounded-3 border-0" style="background-color: var(--tg-theme-section-bg-color);">
                <div>
                  <strong>{{ $book->name }}</strong>
                  <div class="small text-muted">
                    Total {{ $book->total_hadiths }} hadits
                  </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
              </div>
            </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const searchInput = document.getElementById('searchBook');
  const clearButton = document.getElementById('clearSearch');
  const bookList = document.getElementById('bookList');

  function filterBooks() {
    const filter = searchInput.value.toLowerCase();
    document.querySelectorAll('#bookList > a').forEach(item => {
    const text = item.querySelector('strong').innerText.toLowerCase();
    item.style.display = text.includes(filter) ? '' : 'none';
    });
    clearButton.classList.toggle('d-none', searchInput.value === '');
  }

  searchInput.addEventListener('keyup', filterBooks);
  clearButton.addEventListener('click', () => {
  searchInput.value = '';
  filterBooks();
  searchInput.focus();
  });
</script>
@endpush

<style>
  /* Menggunakan tema Telegram */
  body {
    background-color: var(--tg-theme-bg-color);
    color: var(--tg-theme-text-color);
  }
  .card {
    background-color: var(--tg-theme-secondary-bg-color);
    border: none;
  }
  .card-header {
    background-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
    border-bottom: none;
  }
  .btn-primary {
    background-color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .btn-outline-primary {
    color: var(--tg-theme-button-color);
    border-color: var(--tg-theme-button-color);
  }
  .btn-outline-primary:hover {
    background-color: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
  }
  .btn-outline-secondary {
    color: var(--tg-theme-hint-color);
    border-color: var(--tg-theme-hint-color);
  }
  .btn-outline-secondary:hover {
    background-color: var(--tg-theme-hint-color);
    color: var(--tg-theme-button-text-color);
  }
  .text-muted {
    color: var(--tg-theme-hint-color) !important;
  }
  .table {
    color: var(--tg-theme-text-color);
  }
  .table-hover tbody tr:hover {
    background-color: var(--tg-theme-section-separator-color);
  }
  .table td, .table th {
    border-color: var(--tg-theme-section-separator-color);
  }
  .spinner-border {
    color: var(--tg-theme-button-color) !important;
  }
  /* Styling untuk tombol clear */
  #clearSearch {
    z-index: 10;
    opacity: 0.7;
    transition: opacity 0.2s;
  }
  #clearSearch:hover {
    opacity: 1;
  }
</style>