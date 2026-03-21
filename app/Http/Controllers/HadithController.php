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
    $hadiths = $book
    ->hadiths()
    ->select("number", "arabic", "translation")
    ->orderBy("number")
    ->paginate(10)
    ->withQueryString();
    $book->setRelation("hadiths", $hadiths);

    return view('quranandhadits::hadith.show', compact('book', 'hadiths'));
  }

  /**
  * Get pagination parameters from request
  */
  protected function getPaginationParams(Request $request): array
  {
    return [
      "page" => $request->input("page", 1),
      "per_page" => $request->input("per_page", 10),
    ];
  }
}