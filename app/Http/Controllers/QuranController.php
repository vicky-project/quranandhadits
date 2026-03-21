<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
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
  public function show($surahNumber) {
    $surah = Surah::where('number', $surahNumber)->firstOrFail();
    $verses = Verse::where('surah_id', $surah->id)
    ->orderBy('verse_number')
    ->get();

    return view('quranandhadits::quran.show', compact('surah', 'verses'));
  }
}