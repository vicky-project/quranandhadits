@extends('coreui::layouts.mini-app')
@section('title', $surah->name_latin)

@section('content')
<div class="container py-3">
  <div class="row justify-content-center mb-3">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('apps.quran.index') }}" class="btn btn-outline-secondary disabled">
          <i class="bi bi-arrow-left me-2"></i>Daftar Surah
        </a>
      </div>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">{{ $surah->number }}. {{ $surah->name_latin }}</h4>
          <small>{{ $surah->name }} • {{ $surah->meaning }} • {{ $surah->number_of_verses }} ayat</small>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('apps.quran.surah', $surah->number) }}" class="mb-3">
            <input type="hidden" name="initData" value="{{ request()->get('initData') }}">
            <div class="position-relative">
              <input type="text" name="q" id="searchVerse" class="form-control" placeholder="Cari ayat..." value="{{ $search }}">
              <button type="button" id="clearSearchVerse" class="btn btn-link position-absolute end-0 top-0 text-muted {{ $search !== '' ? '' : 'd-none'}}" style="padding: 0.375rem 0.75rem;">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </form>
          @if($search)
          <div class="d-flex justify-content-center align-items-center text-center mb-1">
            <div>
              Menampilkan <strong>{{ $verses->count() }}</strong> dari <strong>{{ $verses->total() }}</strong> ayat dengan kata "<strong>{{ $search }}</strong>"
            </div>
          </div>
          <div class="d-flex justify-content-center align-items-center text-center mb-4">
            (halaman {{ $verses->currentPage() }} dari {{ $verses->lastPage() }})
          </div>
          @endif
          <div id="versesList">
            @forelse($verses as $verse)
            <div class="verse-item mb-4 p-3 rounded-3" style="background-color: var(--tg-theme-section-bg-color);">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary">{{ $verse->verse_number }}</span>
              </div>
              <div class="arabic-text text-end mb-3" style="font-family: 'Traditional Arabic', 'Amiri', serif; font-size: 2.3rem; line-height: 2
                3rem;">
                {!! $verse->arabic_text !!}
              </div>
              <div class="latin-text text-muted mb-2">
                {{ $verse->latin_text }}
              </div>
              <div class="translation">
                <i class="bi bi-chat-quote"></i> {{ $verse->translation }}
              </div>
            </div>
            @empty
            <div class="text-center py-4">
              <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
              <p class="text-muted mt-2">
                Tidak ditemukan ayat yang sesuai.
              </p>
            </div>
            @endforelse
          </div>

          <!-- Pagination Links -->
          <div class="d-flex justify-content-center mt-4">
            {{ $verses->appends(['search' => $search])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<button id="scrollToTopBtn" class="btn btn-primary rounded-circle shadow" style="position: fixed;bottom: 20px;right: 20px;width: 48px;height: 48px;display: none;align-items: center;justify-content: center;z-index: 1000;">
  <i class="bi bi-arrow-up fs-5"></i>
</button>

<!-- Loading Overlay Spinner -->
<div id="loadingSpinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
  <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
    <span class="visually-hidden">Loading...</span>
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
  mark {
    background-color: #ffeb3b;
    color: #000;
    border-radius: 3px;
    padding: 0 2px;
  }
  #scrollToTopBtn {
    transition: opacity 0.2s;
  }
  #scrollToTopBtn:hover {
    opacity: 0.8;
  }
  @media (prefers-color-scheme:dark) {
    mark {
      background-color: #ffc107;
      color: #1a1a1a;
    }
  }
  #loadingSpinner {
    display: flex;
  }
</style>
@endpush

@push('scripts')
<script>
  const searchInput = document.getElementById('searchVerse');
  const clearButton = document.getElementById('clearSearchVerse');
  const searchForm = searchInput.closest('form');
  const spinner = document.getElementById('loadingSpinner');

  function toggleClearButton() {
    if (searchInput.value.trim() !== '') {
      clearButton.classList.remove('d-none');
    } else {
      clearButton.classList.add('d-none');
    }
  }

  function showSpinner() {
    spinner.style.display = 'flex';
  }

  function submitSearch() {
    showSpinner();
    searchForm.submit();
  }

  const urlParams = new URLSearchParams(window.location.search);
  const searchTerm = urlParams.get('q');
  if (searchTerm && searchTerm.trim() !== '') {
    const escapedTerm = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escapedTerm})`, 'gi');

    document.querySelectorAll('.verse-item').forEach(item => {
    // Arabic text container
    const arabicDiv = item.querySelector('.arabic-text');
    if(arabicDiv) {
    const originalText = arabicDiv.innerText;
    const highlighted = originalText.replace(regex, '<mark>$1</mark>');
    arabicDiv.innerHTML = highlighted;
    }

    // Latin text
    const latinDiv = item.querySelector('.latin-text');
    if(latinDiv) {
    const originalText = latinDiv.innerText;
    const highlighted = originalText.replace(regex, '<mark>$1</mark>');
    latinDiv.innerHTML = highlighted;
    }

    // Translation text
    const translationDiv = item.querySelector('.translation');
    if(translationDiv) {
    const originalText = translationDiv.innerText;
    const highlighted = originalText.replace(regex, '<mark>$1</mark>');
    translationDiv.innerHTML = highlighted;
    }
    });
  }

  searchInput.addEventListener('input', toggleClearButton);

  searchInput.addEventListener('keyup', function(e) {
  toggleClearButton();
  if(e.key === "Enter") {
  submitSearch();
  }
  });

  clearButton.addEventListener('click', function(e) {
  e.preventDefault();
  searchInput.value = "";
  toggleClearButton();
  submitSearch();
  });

  const scrollBtn = document.getElementById('scrollToTopBtn');
  if (scrollBtn) {
    window.addEventListener('scroll', function() {
    if(window.scrollY > 300) {
    scrollBtn.style.display = 'flex';
    } else {
    scrollBtn.style.display = 'none';
    }
    });

    scrollBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth'});
    });
  }

  document.querySelectorAll('.pagination a').forEach(link => {
  link.addEventListener('click', showSpinner);
  })

  toggleClearButton();
</script>
@endpush