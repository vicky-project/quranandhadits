<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\QuranAndHadits\Models\HadithBook;
use Modules\QuranAndHadits\Models\Hadith;

class HadithController extends Controller
{
  public function index() {
    $books = HadithBook::orderBy('name')->get();
    return view('quranandhadits::hadith.index', compact('books'));
  }

  public function show($slug) {
    $book = HadithBook::where('slug', $slug)->firstOrFail();
    $hadiths = Hadith::where('book_id', $book->id)
    ->orderBy('number')
    ->get();
    return view('quranandhadits::hadith.show', compact('book', 'hadiths'));
  }
}