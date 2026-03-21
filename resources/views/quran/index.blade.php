@extends('coreui::layouts.mini-app')
@section('title', 'Al-Qur\'an')

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
          <h4 class="mb-0"><i class="bi bi-book me-2"></i>Daftar Surah</h4>
        </div>
        <div class="card-body">
          <div class="position-relative mb-3">
            <input type="text" id="searchSurah" class="form-control" placeholder="Cari surah...">
            <button id="clearSearchSurah" class="btn btn-link position-absolute end-0 top-0 text-muted d-none" style="padding: 0.375rem 0.75rem;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div id="surahList">
            @foreach($surahs as $surah)
            <a href="{{ route('apps.quran.surah', $surah->number) }}" class="text-decoration-none">
              <div class="list-group-item d-flex justify-content-between align-items-center mb-2 rounded-3 border-0" style="background-color: var(--tg-theme-section-bg-color);">
                <div>
                  <div class="d-flex align-items-baseline gap-2 mb-1">
                    <strong>{{ $surah->number }}. {{ $surah->name_latin }}</strong>
                    <span class="arabic-name" style="font-family: 'Traditional Arabic', 'Amiri', serif; font-size: 1.1rem;">{{ $surah->name }}</span>
                  </div>
                  <div class="small text-muted">
                    {{ $surah->meaning }} • {{ $surah->place }}
                  </div>
                </div>
                <div class="text-muted">
                  {{ $surah->number_of_verses }} ayat
                </div>
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

@push('styles')
<style>
  .arabic-name {
    font-size: 1.2rem;
    line-height: 1.4;
  }
  /* Styling untuk tombol clear */
  #clearSearchSurah {
    z-index: 10;
    opacity: 0.7;
    transition: opacity 0.2s;
  }
  #clearSearchSurah:hover {
    opacity: 1;
  }
</style>
@endpush

@push('scripts')
<script>
  const searchInput = document.getElementById('searchSurah');
  const clearButton = document.getElementById('clearSearchSurah');

  function filterSurah() {
    let filter = searchInput.value.toLowerCase();
    document.querySelectorAll('#surahList > a').forEach(item => {
    let text = item.querySelector('strong').innerText.toLowerCase();
    let details = item.querySelector('.small').innerText.toLowerCase();
    item.style.display = (text.includes(filter) || details.includes(filter)) ? '' : 'none';
    });
    // Toggle clear button visibility
    clearButton.classList.toggle('d-none', searchInput.value === '');
  }

  searchInput.addEventListener('keyup', filterSurah);
  clearButton.addEventListener('click', () => {
  searchInput.value = '';
  filterSurah();
  searchInput.focus();
  });
</script>
@endpush