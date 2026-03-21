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
          <input type="text" id="searchSurah" class="form-control mb-3" placeholder="Cari surah...">
          <div id="surahList">
            @foreach($surahs as $surah)
            <a href="{{ route('apps.quran.surah', $surah->number) }}" class="text-decoration-none">
              <div class="list-group-item d-flex justify-content-between align-items-center mb-2 rounded-3 border-0" style="background-color: var(--tg-theme-section-bg-color);">
                <div>
                  <strong>{{ $surah->number }}. {{ $surah->name_latin }}</strong>
                  <div class="small text-muted">
                    {{ $surah->name }} • {{ $surah->meaning }} • {{ $surah->place }}
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

@push('scripts')
<script>
  document.getElementById('searchSurah').addEventListener('keyup', function() {
  let filter = this.value.toLowerCase();
  document.querySelectorAll('#surahList > a').forEach(item => {
  let text = item.querySelector('strong').innerText.toLowerCase();
  let detail = item.querySelector('.small').innerText.toLowerCase();
  item.style.display = (text.includes(filter) || detail.includes(filter)) ? '' : 'none';
  });
  });
</script>
@endpush

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
  .timeout-option {
    margin-top: 1rem;
    font-size: 0.9rem;
  }
  #dateDisplay, #coordDisplay {
    font-size: 0.9rem;
  }
</style>
@endpush