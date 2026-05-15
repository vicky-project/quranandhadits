<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\QuranAndHadits\Models\HadithBook;
use Modules\QuranAndHadits\Models\Hadith;
use Modules\QuranAndHadits\Traits\HasNotes;

class HadithController extends Controller
{
  use HasNotes;

  public function index() {
    return view('quranandhadits::hadith', [
      'notesConfig' => $this->notesJsConfig()
    ]);
  }

  public function books() {
    $books = HadithBook::orderBy('name')->get();
    return response()->json($books);
  }

  public function book(Request $request, $slug) {
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

    return response()->json([
      'book' => $book,
      'hadiths' => [
        'data' => $hadiths->items(),
        'current_page' => $hadiths->currentPage(),
        'last_page' => $hadiths->lastPage(),
        'total' => $hadiths->total()
      ]
    ]);
  }
}