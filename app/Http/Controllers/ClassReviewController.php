<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\ClassReview;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = ClassReview::with(['customer:id,name', 'class:id,name'])
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $isConfirmed = Registration::where('customer_id', $data['customer_id'])
            ->where('class_id', $data['class_id'])
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->exists();

        if (!$isConfirmed) {
            return response()->json(['message' => 'Only students with a confirmed registration can review this class.'], 422);
        }

        $review = ClassReview::updateOrCreate(
            ['customer_id' => $data['customer_id'], 'class_id' => $data['class_id']],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return response()->json($review->load(['customer:id,name', 'class:id,name']), 201);
    }

    public function update(Request $request, ClassReview $classReview)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $classReview->update($data);
        return response()->json($classReview->fresh()->load(['customer:id,name', 'class:id,name']));
    }

    public function destroy(ClassReview $classReview)
    {
        $classReview->delete();
        return response()->json(['message' => 'Review deleted successfully.']);
    }

    public function ranking()
    {
        $ranking = ClassReview::query()
            ->select('class_id')
            ->selectRaw('ROUND(AVG(rating), 2) AS average_rating')
            ->selectRaw('COUNT(*) AS review_count')
            ->with('class:id,name')
            ->groupBy('class_id')
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count')
            ->get();

        return response()->json($ranking);
    }
}
