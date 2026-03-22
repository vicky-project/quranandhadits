<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\QuranAndHadits\Models\Surah;
use Modules\QuranAndHadits\Models\Verse;

class QuranController extends Controller
{
  /**
  * Display a listing of the resource.
  */
  public function index(Request $request) {
    $surahs = Surah::orderBy('number')->get();

    return view('quranandhadits::quran.index', compact('surahs'));
  }

  /**
  * Show the specified resource.
  */
  public function show(Request $request, $surahNumber) {
    $surah = Surah::where('number', $surahNumber)->firstOrFail();
    $page = $request->get("page", 1);
    $cacheKey = "verses_surah_{$surah->id}_page_{$page}_search_" . md5($search);
    $search = $request->get("q", "");
    $perPage = config('quranandhadits.pagination.per_page', 20);

    $verses = Cache::remember($cacheKey, now()->addDays(), function() use(
      $surah,
      $page,
      $perPage,
      $search
    ) {
      $query = Verse::where('surah_id', $surah->id);
      if ($search) {
        $query->where(function($q) use($search) {
          $q->where('arabic_text', 'LIKE', "%{$search}%")->orWhere('latin_text', 'LIKE', "%{$search}%")->orWhere('translation', 'LIKE', "%{$search}%");
        });
      }
      return $query->orderBy('verse_number')->paginate($perPage, ["*"], 'page', $page)->withQueryString();
    });

    return view('quranandhadits::quran.show',
      compact('surah', 'verses', 'search'));
  }
}