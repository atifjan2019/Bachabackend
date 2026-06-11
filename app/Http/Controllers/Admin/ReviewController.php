<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::with('product:id,name,slug')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggle(string $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->update(['is_approved' => ! $review->is_approved]);
        Cache::flush();

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated.');
    }

    public function destroy(string $id)
    {
        ProductReview::findOrFail($id)->delete();
        Cache::flush();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }
}
