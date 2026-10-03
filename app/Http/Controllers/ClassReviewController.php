<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\ClassReview;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $customerId = $this->authenticatedCustomerId($request);
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = $this->confirmedRegistration($customerId, $data['class_id']);
        if ($error = $this->attendanceEligibilityError($registration, true)) {
            return $error;
        }

        if (ClassReview::where('customer_id', $customerId)->where('class_id', $data['class_id'])->exists()) {
            return response()->json(['message' => 'A review already exists for this class.'], 409);
        }

        $review = ClassReview::create([
            'customer_id' => $customerId,
            'class_id' => $data['class_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json($review->load(['customer:id,name', 'class:id,name']), 201);
    }

    public function update(Request $request, ClassReview $classReview)
    {
        $customerId = $this->authenticatedCustomerId($request);
        abort_unless((int) $classReview->customer_id === $customerId, 403, 'You can only edit your own reviews.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = $this->confirmedRegistration($customerId, $classReview->class_id);
        if ($error = $this->attendanceEligibilityError($registration)) {
            return $error;
        }
        if (now()->gt($classReview->created_at->copy()->addDays(7))) {
            return response()->json(['message' => 'The 7-day review editing window has expired.'], 422);
        }

        $classReview->update($data);
        return response()->json($classReview->fresh()->load(['customer:id,name', 'class:id,name']));
    }

    public function destroy(Request $request, ClassReview $classReview)
    {
        abort_unless($request->user()->role === 'admin', 403, 'Only administrators can delete reviews.');

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

    private function authenticatedCustomerId(Request $request): int
    {
        abort_unless(
            $request->user()?->role === 'customer' && $request->user()->customer_id,
            403,
            'Only customer accounts can submit or edit reviews.'
        );

        return (int) $request->user()->customer_id;
    }

    private function confirmedRegistration(int $customerId, int $classId): Registration
    {
        return Registration::with('class')
            ->where('customer_id', $customerId)
            ->where('class_id', $classId)
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->firstOrFail();
    }

    private function attendanceEligibilityError(Registration $registration, bool $checkSubmissionDeadline = false): ?JsonResponse
    {
        if (!$registration->attendances()->whereIn('status', ['PRESENT', 'LATE'])->exists()) {
            return response()->json(['message' => 'At least one PRESENT or LATE attendance is required to review this class.'], 422);
        }

        if ($checkSubmissionDeadline && now()->gt($registration->class->end_date->copy()->addDays(30)->endOfDay())) {
            return response()->json(['message' => 'The 30-day review submission window has expired.'], 422);
        }

        return null;
    }
}
