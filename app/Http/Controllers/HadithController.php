<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\QuranAndHadits\Models\HadithBook;
use Modules\QuranAndHadits\Models\Hadith;
use Illuminate\Support\Facades\Cache;

class HadithController extends Controller
{
  public function index() {
    $books = HadithBook::orderBy('name')->get();
    return view('quranandhadits::hadith.index', compact('books'));
  }

  public function show(Request $request, $slug) {
    $book = HadithBook::where('slug', $slug)->firstOrFail();
    $page = $request->get("page", 1);
    $search = $request->get("q", "");
    $cacheKey = "hadiths_book_{$book->id}_page_{$page}_search_" . md5($search);
    $perPage = config('quranandhadits.pagination.per_page', 20);

    $hadiths = Cache::remember($cacheKey, now()->addDays(), function() use(
      $book,
      $page,
      $perPage,
      $search
    ) {
      $query = Hadith::where('book_id', $book->id);
      if ($search) {
        $query->where(function($q) use($search) {
          $q->where('arabic', 'LIKE', "%{$search}%")
          ->orWhere('translation', 'LIKE', "%{$search}%");
        });
      }

      return $query->orderBy("number")
      ->paginate($perPage, ["*"], "page", $page)->withQueryString();
    });

    return view('quranandhadits::hadith.show',
      compact('book', 'hadiths', 'search'));
  }
}