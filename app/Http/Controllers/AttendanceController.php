<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Attendance;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::with(['registration.customer', 'registration.class'])
            ->when($request->user()?->role === 'teacher', fn ($query) => $query->whereHas('registration.class', fn ($class) => $class->where('teacher_id', $request->user()->teacher_id)))
            ->when($request->filled('class_id'), fn ($query) => $query->whereHas('registration', fn ($registration) => $registration->where('class_id', $request->class_id)))
            ->when($request->filled('registration_id'), fn ($query) => $query->where('registration_id', $request->registration_id))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('attendance_date', $request->date))
            ->latest('attendance_date')
            ->paginate((int) $request->get('per_page', 30));

        return response()->json($attendances);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['PRESENT', 'LATE', 'ABSENT', 'EXCUSED'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $registration = Registration::with('class')->findOrFail($data['registration_id']);
        $this->ensureClassAccess($request, $registration->class_id);
        $this->ensureEligibleDate($registration, $data['attendance_date']);

        if ($registration->status !== RegistrationStatus::CONFIRMED) {
            return response()->json(['message' => 'Only confirmed registrations can be marked attendance.'], 422);
        }

        $attendance = Attendance::updateOrCreate(
            ['registration_id' => $registration->id, 'attendance_date' => $data['attendance_date']],
            ['status' => $data['status'], 'note' => $data['note'] ?? null]
        );

        return response()->json($attendance->load('registration.customer'), 201);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->ensureClassAccess($request, $attendance->registration->class_id);

        $data = $request->validate([
            'status' => ['required', Rule::in(['PRESENT', 'LATE', 'ABSENT', 'EXCUSED'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ensureClassAccess($request, $data['class_id']);

        $attendance->update($data);
        return response()->json($attendance->fresh()->load('registration.customer'));
    }

    public function summary(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
        ]);

        $query = Attendance::whereHas('registration', function ($registration) use ($data) {
            $registration->where('class_id', $data['class_id'])
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->when($data['customer_id'] ?? null, fn ($query) => $query->where('customer_id', $data['customer_id']));
        });

        $total = (clone $query)->count();
        $attended = (clone $query)->whereIn('status', ['PRESENT', 'LATE'])->count();

        return response()->json([
            'class_id' => (int) $data['class_id'],
            'customer_id' => isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            'total_sessions' => $total,
            'attended_sessions' => $attended,
            'absent_sessions' => (clone $query)->where('status', 'ABSENT')->count(),
            'excused_sessions' => (clone $query)->where('status', 'EXCUSED')->count(),
            'attendance_rate' => $total > 0 ? round($attended / $total * 100, 2) : 0,
        ]);
    }

    private function ensureEligibleDate(Registration $registration, string $date): void
    {
        $class = $registration->class;
        if ($date < $class->start_date->format('Y-m-d') || $date > $class->end_date->format('Y-m-d')) {
            abort(422, 'Attendance date must be within the class period.');
        }
    }

    private function ensureClassAccess(Request $request, int $classId): void
    {
        abort_unless($this->classIsAccessible($request, $classId), 403, 'You cannot manage attendance for this class.');
    }

    private function classIsAccessible(Request $request, $class): bool
    {
        $user = $request->user();
        if ($user?->role === 'admin') {
            return true;
        }

        $teacherId = $user?->teacher_id;
        $classTeacherId = is_object($class) ? $class->teacher_id : \App\Models\YogaClass::whereKey($class)->value('teacher_id');

        return $teacherId !== null && (int) $classTeacherId === (int) $teacherId;
    }
}
