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
          <form method="GET" action="{{ route('apps.hadith.show', $book->slug) }}" class="mb-3">
            <input type="hidden" name="initData" value="{{ request()->get('initData') }}">
            <div class="position-relative">
              <input type="text" name="q" id="searchHadith" class="form-control" placeholder="Cari hadits..." value="{{ $search }}">
              <button type="button" id="clearSearchHadith" class="btn btn-link position-absolute end-0 top-0 text-muted {{ $search !== '' ? '' : 'd-none'}}" style="padding: 0.375rem 0.75rem;">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </form>
          @if($search)
          <div class="d-flex justify-content-center align-items-center text-center mb-4">
            Menampilkan <strong>{{ $hadiths->count() }}</strong> dari <strong>{{ $hadiths->total() }}</strong> hadiths dengan kata "<strong>{{ $search }}</strong>"
            @if($hadiths->currentPage() > 1)
            (halaman {{ $hadiths->currentPage() }} dari {{ $hadiths->lastPage() }})
            @endif
          </div>
          @endif
          <div id="hadithList">
            @forelse($hadiths as $hadith)
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
            @empty
            <div class="text-center py-4">
              <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
              <p class="text-muted mt-2">
                Tidak ditemukan hadits yang sesuai.
              </p>
            </div>
            @endforelse
          </div>

          <!-- Pagination Links -->
          <div class="d-flex justify-content-center mt-4">
            {{ $hadiths->appends(['search' => $search])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<button id="scrollToTopBtn" class="btn btn-primary rounded-circle shadow" style="position: fixed;bottom: 20px;right: 20px;width: 48px;height: 48px;display: none;align-items: center;justify-content: center;z-index: 1000;">
  <i class="bi bi-arrow-up"></i>
</button>
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
</style>
@endpush

@push('scripts')
<script>
  const searchInput = document.getElementById('searchHadith');
  const clearButton = document.getElementById('clearSearchHadith');
  const searchForm = searchInput.closest('form');

  function toggleClearButton() {
    if (searchInput.value.trim() !== '') {
      clearButton.classList.remove('d-none');
    } else {
      clearButton.classList.add('d-none');
    }
  }

  function submitSearch() {
    searchForm.submit();
  }

  const urlParams = new URLSearchParams(window.location.search);
  const searchTerm = urlParams.get('q');
  if (searchTerm && searchTerm.trim() !== '') {
    const escapedTerm = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escapedTerm})`, 'gi');

    document.querySelectorAll('.hadith-item').forEach(item => {
    // Arabic text container
    const arabicDiv = item.querySelector('.arabic-text');
    if(arabicDiv) {
    const clone = arabicDiv.cloneNode(true);
    const originalText = arabicDiv.innerText;
    const highlighted = originalText.replace(regex, '<mark>$1</mark>');
    arabicDiv.innerHTML = highlighted;
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
</script>
@endpush