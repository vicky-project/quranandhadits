@extends('coreui::layouts.mini-app')
@section('title', $surah->name_latin)

@section('content')
<div class="container py-3">
  <div class="row justify-content-center mb-3">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('apps.quran.index') }}" class="btn btn-outline-secondary">
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
          <input type="text" id="searchVerse" class="form-control mb-3" placeholder="Cari ayat...">
          <div id="versesList">
            @foreach($verses as $verse)
            <div class="verse-item mb-4 p-3 rounded-3" style="background-color: var(--tg-theme-section-bg-color);">
              <span class="badge bg-primary mb-2">{{ $verse->verse_number }}</span>
              <div class="arabic-text text-end mb-2 fs-4" style="font-family: 'Traditional Arabic', 'Amiri', serif;">
                {!! $verse->arabic_text !!}
              </div>
              <div class="latin-text text-muted mb-2">
                {{ $verse->latin_text }}
              </div>
              <div class="translation">
                <i class="bi bi-chat-quote"></i> {{ $verse->translation }}
              </div>
            </div>
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
  .arabic-text {
    font-size: 1.3rem;
  }
</style>
@endpush

@push('scripts')
<script>
  document.getElementById('searchVerse').addEventListener('keyup', function() {
  let filter = this.value.toLowerCase();
  document.querySelectorAll('.verse-item').forEach(item => {
  item.style.display = item.innerText.toLowerCase().includes(filter) ? '' : 'none';
  });
  });
</script>
@endpush