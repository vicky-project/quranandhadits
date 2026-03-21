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
              <div class="arabic-text text-end mb-2" style="font-family: 'Traditional Arabic', 'Amiri', serif; font-size: 1.3rem; line-height: 2rem;">
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
  .arabic-text {
    font-size: 1.3rem !important;
    line-height: 2rem !important;
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