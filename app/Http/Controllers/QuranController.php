<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\QuranAndHadits\Models\Surah;
use Modules\QuranAndHadits\Models\Verse;
use Modules\QuranAndHadits\Traits\HasNotes;

class QuranController extends Controller
{
  use HasNotes;

  /**
  * Display a listing of the resource.
  */
  public function index(Request $request) {
    return view('quranandhadits::quran', [
      'notesConfig' => $this->notesJsConfig()
    ]);
  }

  public function surahs() {
    return response()->json(Surah::orderBy('number')->get());
  }

  /**
  * Show the specified resource.
  */
  public function surah(Request $request, $number) {
    $surah = Surah::where('number', $number)->firstOrFail();
    $page = $request->get("page", 1);
    $search = $request->get("q", "");
    $cacheKey = "verses_surah_{$surah->id}_page_{$page}_search_" . md5($search);
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

    return response()->json([
      "surah" => $surah,
      "verses" => $verses
    ]);
  }
}