@extends('coreui::layouts.mini-app')
@section('title', $book->name)

@section('content')
<div class="container py-3">
  <div class="row justify-content-center mb-3">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('apps.hadith.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-2"></i>Daftar Kitab
        </a>
      </div>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">{{ $book->name }}</h4>
          <small>Total {{ $book->total_hadiths }} hadits</small>
        </div>
        <div class="card-body">
          <div class="position-relative mb-3">
            <input type="text" id="searchHadith" class="form-control" placeholder="Cari hadits...">
            <button id="clearSearch" class="btn btn-link position-absolute end-0 top-0 text-muted d-none" style="padding: 0.375rem 0.75rem;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div id="hadithList">
            @foreach($hadiths as $hadith)
            <div class="hadith-item mb-4 p-3 rounded-3" style="background-color: var(--tg-theme-section-bg-color);">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary">Hadits No. {{ $hadith->number }}</span>
              </div>
              <div class="arabic-text text-end mb-3" style="font-family: 'Traditional Arabic', 'Amiri', serif; font-size: 2.3rem; line-height: 3rem;">
                {!! $hadith->arabic !!}
              </div>
              <div class="translation">
                <i class="bi bi-chat-quote"></i> {{ $hadith->translation }}
              </div>
            </div>
            @endforeach
          </div>

          <!-- Pagination Links -->
          <div class="d-flex justify-content-center mt-4">
            {{ $hadiths->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
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
  .arabic-text {
    font-size: 2.3rem !important;
    line-height: 3rem !important;
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
@endpush

@push('scripts')
<script>
  const searchInput = document.getElementById('searchHadith');
  const clearButton = document.getElementById('clearSearch');

  function filterHadith() {
    const filter = searchInput.value.toLowerCase();
    document.querySelectorAll('.hadith-item').forEach(item => {
    const text = item.innerText.toLowerCase();
    item.style.display = text.includes(filter) ? '' : 'none';
    });
    clearButton.classList.toggle('d-none', searchInput.value === '');
  }

  searchInput.addEventListener('keyup', filterHadith);
  clearButton.addEventListener('click', () => {
  searchInput.value = '';
  filterHadith();
  searchInput.focus();
  });
</script>
@endpush