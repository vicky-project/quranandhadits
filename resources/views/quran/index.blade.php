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
          <div class="mb-3">
            <input type="text" id="searchSurah" class="form-control" placeholder="Cari surah (nama latin atau arti)...">
          </div>
          <div id="surahList">
            @foreach($surahs as $surah)
            <a href="{{ route('apps.quran.surah', $surah->number) }}" class="text-decoration-none">
              <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mb-2 rounded-3 border-0" style="background-color: var(--tg-theme-section-bg-color);">
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
  let items = document.querySelectorAll('#surahList > a');
  items.forEach(item => {
  let text = item.querySelector('strong').innerText.toLowerCase();
  let details = item.querySelector('.small').innerText.toLowerCase();
  if (text.includes(filter) || details.includes(filter)) {
  item.style.display = '';
  } else {
  item.style.display = 'none';
  }
  });
  });
</script>
@endpush