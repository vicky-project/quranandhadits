<?php

namespace Modules\QuranAndHadits\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\QuranAndHadits\Models\HadithBook;
use Modules\QuranAndHadits\Models\Hadith;
use Illuminate\Pagination\LengthAwarePaginator;
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
    $perPage = config("quranandhadits.pagination.per_page", 20);

    $hadiths = $this->getCachedHadiths($book->id, $page, $perPage, $search);

    return view('quranandhadits::hadith.show', compact('book', 'hadiths', 'search'));
  }

  /**
  * Get pagination parameters from request
  */
  protected function getCachedHadiths(
    string $bookId,
    int $page,
    int $perPage = 20,
    string $search = ""
  ): LengthAwarePaginator
  {
    $cacheKey = "hadiths_book_{$bookId}_page_{$page}_search_" . md5($search) . "per_{$perPage}";

    $cached = Cache::remember($cacheKey, now()->addDays(), function() use(
      $bookId,
      $page,
      $perPage,
      $search
    ) {
      $query = Hadith::where('book_id', $bookId);
      if ($query) {
        $query->where(function($q) use($search) {
          $q->where('arabic', 'LIKE', "%{$search}%")
          ->orWhere('arabic', 'LIKE', "%{$search}%");
        });
      }

      $total = $query->count();
      $items = $query->orderBy("number")
      ->skip(($page - 1) * $perPage)
      ->take($perPage)
      ->get(["number", "arabic", "translation "]);

      return [
        'items' => $items,
        'total' => $total
      ];
    });

    return LengthAwarePaginator(
      $cached["items"],
      $cached["total"],
      $perPage,
      $page,
      ["path" => request()->url(),
        "query" => [
          "q" => $search]]
    );
  }
}