<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\YogaClass;
use App\Models\Teacher;
use App\Models\Customer;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\ClassReview;
use App\Enums\RegistrationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Show create registration form (admin)
    public function showCreateRegistration()
    {
        $classes = YogaClass::with('teacher')
            ->withCount([
                'registrations as confirmed_registrations_count' => fn ($query) => $query
                    ->where('status', RegistrationStatus::CONFIRMED->value),
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn (YogaClass $class) => $class->quantity > $class->confirmed_registrations_count)
            ->values();

        return view('admin.registration_create', compact('classes'));
    }
    // Show admin login page
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    // Handle admin login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);


    // Authenticate only with user_name (users table does not have email column)
    $user = User::where('user_name', $request->username)->first();

        if ($user && $user->role === 'admin' && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors(['username' => 'Thông tin đăng nhập không đúng!']);
    }

    // Handle admin logout
    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login')->with('success', 'Đã đăng xuất thành công!');
    }

    // Admin dashboard
    public function dashboard()
    {
        $stats = [
            'classes' => YogaClass::count(),
            'teachers' => Teacher::count(),
            'customers' => Customer::count(),
            'registrations' => Registration::count(),
            'pending_registrations' => Registration::where('status', RegistrationStatus::PENDING->value)->count(),
            'approved_registrations' => Registration::where('status', RegistrationStatus::CONFIRMED->value)->count(),
            'cancelled_registrations' => Registration::where('status', RegistrationStatus::CANCELLED->value)->count(),
        ];

        $recentRegistrations = Registration::with(['customer', 'class'])
                                         ->latest()
                                         ->take(5)
                                         ->get();

        return view('admin.dashboard', compact('stats', 'recentRegistrations'));
    }

    public function dashboardAnalytics(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'integer', 'in:30,90,180,365'],
            'revenue_months' => ['nullable', 'integer', 'in:6,12'],
        ]);

        $period = (int) ($validated['period'] ?? 90);
        $revenueMonths = (int) ($validated['revenue_months'] ?? 6);
        $end = now()->endOfDay();
        $start = now()->subDays($period - 1)->startOfDay();
        $previousStart = $start->copy()->subDays($period);
        $previousEnd = $start->copy()->subSecond();

        $newCustomers = Customer::whereBetween('created_at', [$start, $end])->count();
        $previousCustomers = Customer::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $totalCustomers = Customer::count();

        $newClasses = YogaClass::whereBetween('created_at', [$start, $end])->count();
        $previousClasses = YogaClass::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $totalClasses = YogaClass::count();

        $today = today();
        $classLifecycle = [
            'active' => YogaClass::whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'upcoming' => YogaClass::whereDate('start_date', '>', $today)->count(),
            'ended' => YogaClass::whereDate('end_date', '<', $today)->count(),
        ];

        $teacherClassCounts = YogaClass::query()
            ->with('teacher:id,name')
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->groupBy('teacher_id')
            ->map(fn ($classes) => [
                'name' => $classes->first()->teacher?->name ?? 'Chưa phân công',
                'value' => $classes->count(),
            ])
            ->sortByDesc('value')
            ->values();

        $totalTeacherAssignments = $teacherClassCounts->sum('value');
        $teacherDistribution = $teacherClassCounts->take(8)->values()->map(fn ($item) => [
            'name' => $item['name'],
            'value' => $item['value'],
            'rate' => $totalTeacherAssignments > 0 ? round($item['value'] / $totalTeacherAssignments * 100, 1) : 0,
        ]);

        if ($teacherClassCounts->count() > 8) {
            $otherCount = $teacherClassCounts->slice(8)->sum('value');
            $teacherDistribution->push([
                'name' => 'Giáo viên khác',
                'value' => $otherCount,
                'rate' => $totalTeacherAssignments > 0 ? round($otherCount / $totalTeacherAssignments * 100, 1) : 0,
            ]);
        }

        $revenueStart = now()->startOfMonth()->subMonths($revenueMonths - 1);
        $revenueByMonth = Registration::query()
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->whereBetween('created_at', [$revenueStart, $end])
            ->get(['final_price', 'created_at'])
            ->groupBy(fn (Registration $registration) => $registration->created_at->format('Y-m'))
            ->map(fn ($registrations) => (float) $registrations->sum('final_price'));

        $revenue = collect(range(0, $revenueMonths - 1))->map(function ($offset) use ($revenueStart, $revenueByMonth) {
            $month = $revenueStart->copy()->addMonths($offset);

            return [
                'key' => $month->format('Y-m'),
                'label' => 'T'.$month->format('n').'/'.$month->format('y'),
                'value' => round((float) ($revenueByMonth[$month->format('Y-m')] ?? 0), 2),
            ];
        });

        $currentMonthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();
        $currentMonthRevenue = (float) Registration::query()
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->whereBetween('created_at', [$currentMonthStart, $end])
            ->sum('final_price');
        $previousMonthRevenue = (float) Registration::query()
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('final_price');
        $elapsedDays = max(1, now()->day);
        $daysInMonth = now()->daysInMonth;
        $forecastRevenue = round($currentMonthRevenue / $elapsedDays * $daysInMonth, 2);
        $forecastChange = $previousMonthRevenue > 0
            ? round(($forecastRevenue - $previousMonthRevenue) / $previousMonthRevenue * 100, 1)
            : ($forecastRevenue > 0 ? 100 : 0);

        return response()->json([
            'meta' => [
                'period' => $period,
                'period_label' => $period === 365 ? '12 tháng qua' : $period.' ngày qua',
                'revenue_months' => $revenueMonths,
                'updated_at' => now()->format('H:i d/m/Y'),
            ],
            'growth' => [
                'customers' => $this->growthMetric($newCustomers, $previousCustomers, $totalCustomers),
                'classes' => $this->growthMetric($newClasses, $previousClasses, $totalClasses),
            ],
            'classes' => [
                'lifecycle' => $classLifecycle,
                'total' => array_sum($classLifecycle),
            ],
            'teachers' => [
                'items' => $teacherDistribution,
                'total_assignments' => $totalTeacherAssignments,
            ],
            'revenue' => [
                'items' => $revenue,
                'total' => round((float) $revenue->sum('value'), 2),
                'average' => round((float) $revenue->avg('value'), 2),
            ],
            'revenue_forecast' => [
                'current_month' => round($currentMonthRevenue, 2),
                'forecast' => $forecastRevenue,
                'previous_month' => round($previousMonthRevenue, 2),
                'change_rate' => $forecastChange,
                'elapsed_days' => $elapsedDays,
                'days_in_month' => $daysInMonth,
                'month_label' => 'Tháng '.now()->format('m/Y'),
                'previous_month_label' => 'Tháng '.$previousMonthStart->format('m/Y'),
            ],
        ]);
    }

    private function growthMetric(int $current, int $previous, int $total): array
    {
        $growthRate = $previous > 0
            ? round(($current - $previous) / $previous * 100, 1)
            : ($current > 0 ? 100 : 0);

        return [
            'new' => $current,
            'previous' => $previous,
            'total' => $total,
            'share' => $total > 0 ? round($current / $total * 100, 1) : 0,
            'growth_rate' => $growthRate,
        ];
    }

    public function teacherDashboard(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'Tài khoản chưa được liên kết với hồ sơ giảng viên.');
        $today = today();

        $classes = YogaClass::where('teacher_id', $teacher->id)
            ->withCount(['registrations' => function ($query) {
                $query->where('status', RegistrationStatus::CONFIRMED->value);
            }])
            ->orderByRaw('CASE WHEN end_date < ? THEN 2 WHEN start_date > ? THEN 1 ELSE 0 END', [$today->toDateString(), $today->toDateString()])
            ->orderBy('start_date')
            ->get();

        $stats = [
            'total' => $classes->count(),
            'active' => $classes->filter(fn (YogaClass $class) => $class->start_date->lte($today) && $class->end_date->gte($today))->count(),
            'upcoming' => $classes->filter(fn (YogaClass $class) => $class->start_date->gt($today))->count(),
            'students' => $classes->sum('registrations_count'),
        ];

        return view('teacher.dashboard', compact('teacher', 'classes', 'stats'));
    }

    // Registration Management

    public function registrations(Request $request)
    {
        $query = Registration::with(['customer', 'class.teacher']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($customerQuery) use ($search) {
                    $customerQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%")
                                 ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('class', function($classQuery) use ($search) {
                    $classQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        if ($request->filled('status')) {
            $status = strtoupper($request->status);
            if (in_array($status, array_column(RegistrationStatus::cases(), 'value'), true)) {
                $query->where('status', $status);
            }
        }
        $registrations = $query->latest()->paginate(15)->withQueryString();
        $stats = [
            'pending' => Registration::where('status', RegistrationStatus::PENDING->value)->count(),
            'confirmed' => Registration::where('status', RegistrationStatus::CONFIRMED->value)->count(),
            'cancelled' => Registration::where('status', RegistrationStatus::CANCELLED->value)->count(),
        ];
        return view('admin.registrations', compact('registrations', 'stats'));
    }

    // Admin create registration (auto-confirmed)
    public function createRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'package_months' => 'required|in:1,3,6,12',
            'notes' => 'nullable|string|max:255',
        ]);

        // Tìm customer theo email hoặc phone, nếu có thì update, nếu không thì tạo mới
        $customer = Customer::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();
            
        if ($customer) {
            // Update existing customer
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'birthday' => $customer->birthday, // Keep existing birthday
                'gender' => $request->gender ?? 'female',
                'address' => $customer->address, // Keep existing address
                'note' => $customer->note, // Keep existing note
            ]);
        } else {
            // Create new customer
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'birthday' => '1990-01-01', // Default birthday since field is required
                'gender' => $request->gender ?? 'female',
                'address' => null,
                'note' => null,
            ]);
        }

        // Get class and calculate discount
        $class = YogaClass::findOrFail($request->class_id);
        $packageMonths = $request->package_months;
        
        // Discount rates based on package
        $discountRates = [
            1 => 0,    // 1 month: 0% discount
            3 => 5,    // 3 months: 5% discount
            6 => 10,   // 6 months: 10% discount
            12 => 15   // 12 months: 15% discount
        ];
        
        $monthlyPrice = $class->price;
        $totalPrice = $monthlyPrice * $packageMonths; // Tổng giá cho tất cả tháng
        $discountRate = $discountRates[$packageMonths] ?? 0;
        $discountAmount = ($totalPrice * $discountRate) / 100;
        $finalPrice = $totalPrice - $discountAmount;

        // Create registration with CONFIRMED status (auto-approved by admin)
        $registration = Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $request->class_id,
            'package_months' => $packageMonths,
            'discount' => $discountAmount,
            'final_price' => $finalPrice,
            'status' => RegistrationStatus::CONFIRMED,
            'note' => $request->notes,
        ]);

        return redirect()->route('admin.registrations')->with('success', 'Đã tạo đơn đăng ký thành công! Mã đăng ký: #' . $registration->id . ' (Tự động duyệt)');
    }

    public function registrationDetail($id)
    {
        $registration = Registration::with(['customer', 'class.teacher'])->findOrFail($id);
        return view('admin.registration_detail', compact('registration'));
    }

    public function showEditRegistration($id)
    {
        $registration = Registration::with(['customer', 'class'])->findOrFail($id);
        $classes = YogaClass::with('teacher')
            ->withCount([
                'registrations as confirmed_registrations_count' => fn ($query) => $query
                    ->where('status', RegistrationStatus::CONFIRMED->value),
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn (YogaClass $class) => $class->id === $registration->class_id
                || $class->quantity > $class->confirmed_registrations_count)
            ->values();

        return view('admin.registration_edit', compact('registration', 'classes'));
    }

    public function updateRegistration(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'package_months' => 'required|in:1,3,6,12',
            'notes' => 'nullable|string|max:255',
        ]);

        // Update customer
        $customer = $registration->customer;
        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'birthday' => $customer->birthday, // Keep existing birthday
        ]);

        // Get class and calculate discount
        $class = YogaClass::findOrFail($request->class_id);
        $packageMonths = $request->package_months;
        
        // Discount rates based on package
        $discountRates = [
            1 => 0,    // 1 month: 0% discount
            3 => 5,    // 3 months: 5% discount
            6 => 10,   // 6 months: 10% discount
            12 => 15   // 12 months: 15% discount
        ];
        
        $monthlyPrice = $class->price;
        $totalPrice = $monthlyPrice * $packageMonths; // Tổng giá cho tất cả tháng
        $discountRate = $discountRates[$packageMonths] ?? 0;
        $discountAmount = ($totalPrice * $discountRate) / 100;
        $finalPrice = $totalPrice - $discountAmount;

        // Update registration
        $registration->update([
            'class_id' => $request->class_id,
            'package_months' => $packageMonths,
            'discount' => $discountAmount,
            'final_price' => $finalPrice,
            'note' => $request->notes,
        ]);

        return redirect()
            ->route('admin.registrations.detail', $registration->id)
            ->with('success', 'Đã cập nhật đơn đăng ký #' . $registration->id . ' thành công!');
    }

    public function destroyRegistration($id)
    {
        $registration = Registration::findOrFail($id);
        
        // Soft delete the registration
        $registration->delete();
        
        return redirect()->route('admin.registrations')->with('success', 'Đã xóa đơn đăng ký #' . $registration->id . ' thành công!');
    }

    public function searchCustomer(Request $request)
    {
        $email = $request->input('email');
        $phone = $request->input('phone');
        
        $customer = null;
        
        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }
        
        if (!$customer && $phone) {
            $customer = Customer::where('phone', $phone)->first();
        }
        
        return response()->json([
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'birthday' => $customer->birthday,
                'gender' => $customer->gender,
                'address' => $customer->address,
                'note' => $customer->note
            ] : null
        ]);
    }

    public function approveRegistration($id)
    {
        $registration = Registration::findOrFail($id);
        $registration->update(['status' => RegistrationStatus::CONFIRMED]);
        
        return redirect()->route('admin.registrations')
                        ->with('success', 'Đã phê duyệt đăng ký #' . $id . ' thành công!');
    }

    public function rejectRegistration($id)
    {
        $registration = Registration::findOrFail($id);
        $registration->update(['status' => RegistrationStatus::CANCELLED]);
        
        return redirect()->route('admin.registrations')
                        ->with('success', 'Đã từ chối đăng ký #' . $id);
    }

    // Class Management
    public function classes(Request $request)
    {
        $today = today();
        $query = YogaClass::with('teacher')
                          ->withCount(['registrations' => function($q) {
                              $q->where('status', RegistrationStatus::CONFIRMED->value);
                          }]);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('teacher', function($teacherQuery) use ($search) {
                      $teacherQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $status = strtolower((string) $request->input('status'));
        if ($status === 'ongoing') {
            $query->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today);
        } elseif ($status === 'upcoming') {
            $query->whereDate('start_date', '>', $today);
        } elseif ($status === 'ended') {
            $query->whereDate('end_date', '<', $today);
        }
        
        $classes = $query->orderBy('start_date')->paginate(12)->withQueryString();
        $stats = [
            'total' => YogaClass::count(),
            'ongoing' => YogaClass::whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'upcoming' => YogaClass::whereDate('start_date', '>', $today)->count(),
            'ended' => YogaClass::whereDate('end_date', '<', $today)->count(),
        ];

        return view('admin.classes', compact('classes', 'stats'));
    }

    public function createClass()
    {
        $teachers = Teacher::orderBy('name')->get();
        return view('admin.class_create', compact('teachers'));
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'teacher_id' => 'required|exists:teachers,id',
            'lich_hoc' => 'required|string|max:50',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'quantity' => 'required|integer|min:1|max:50',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'teacher_id', 'lich_hoc', 'start_time', 'end_time',
            'start_date', 'end_date', 'quantity', 'price', 'location', 'description',
        ]);
        if ($this->classNameExists($data['name'])) {
            return back()->withInput()->withErrors(['name' => 'Tên lớp học đã tồn tại. Vui lòng chọn tên khác.']);
        }
        if ($this->teacherHasScheduleConflict($data)) {
            return back()->withInput()->withErrors([
                'teacher_id' => 'Giảng viên đã có lớp bị trùng ngày hoặc khung giờ trong khoảng thời gian này.',
            ]);
        }

        YogaClass::create($data);
        
        return redirect()->route('admin.classes')->with('success', 'Đã tạo lớp học thành công!');
    }

    public function classDetail($id)
    {
        $class = YogaClass::with('teacher')->findOrFail($id);
        if (request()->user()->role === 'teacher') {
            abort_unless((int) request()->user()->teacher_id === (int) $class->teacher_id, 403);
        }
        $registrations = Registration::with(['customer', 'attendances' => fn ($query) => $query->latest('attendance_date')])
                                   ->where('class_id', $id)
                                   ->where('status', RegistrationStatus::CONFIRMED->value)
                                   ->get();
        
        return view('admin.class_detail', compact('class', 'registrations'));
    }

    public function attendancePage($id)
    {
        $class = YogaClass::findOrFail($id);
        if (request()->user()->role === 'teacher') {
            abort_unless((int) request()->user()->teacher_id === (int) $class->teacher_id, 403);
        }
        if ($class->start_date->isFuture()) {
            return redirect()->back()->with('error', 'Chưa đến ngày bắt đầu lớp học, chưa thể điểm danh.');
        }
        $registrations = Registration::with(['customer', 'attendances' => fn ($query) => $query->latest('attendance_date')])
            ->where('class_id', $id)
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->get();

        return view('admin.attendance', compact('class', 'registrations'));
    }

    public function storeAttendance(Request $request, $registrationId)
    {
        $data = $request->validate([
            'attendance_date' => 'required|date',
            'status' => 'required|in:PRESENT,LATE,ABSENT,EXCUSED',
        ]);
        $registration = Registration::with('class')->findOrFail($registrationId);
        if ($request->user()->role === 'teacher') {
            abort_unless((int) $request->user()->teacher_id === (int) $registration->class->teacher_id, 403);
        }
        if ($registration->status !== RegistrationStatus::CONFIRMED) {
            return back()->with('error', 'Chỉ có thể điểm danh học viên đã được duyệt.');
        }
        if ($data['attendance_date'] < $registration->class->start_date->format('Y-m-d')
            || $data['attendance_date'] > $registration->class->end_date->format('Y-m-d')
            || $data['attendance_date'] > now()->toDateString()) {
            return back()->with('error', 'Ngày điểm danh phải nằm trong thời gian lớp học và không được là ngày tương lai.');
        }

        Attendance::updateOrCreate(
            ['registration_id' => $registration->id, 'attendance_date' => $data['attendance_date']],
            ['status' => $data['status']]
        );

        return back()->with('success', 'Đã lưu điểm danh.');
    }

    public function reviewsPage($id)
    {
        $class = YogaClass::findOrFail($id);
        if (request()->user()->role === 'teacher') {
            abort_unless((int) request()->user()->teacher_id === (int) $class->teacher_id, 403);
        }
        $reviews = ClassReview::with('customer')->where('class_id', $id)->latest()->get();
        return view('admin.class_reviews', compact('class', 'reviews'));
    }

    public function deleteClassReview($classId, $reviewId)
    {
        $class = YogaClass::findOrFail($classId);
        $review = ClassReview::where('class_id', $class->id)->findOrFail($reviewId);
        $review->delete();

        return redirect()->route('admin.classes.reviews', $class->id)
            ->with('success', 'Đã xóa đánh giá lớp học.');
    }

    public function editClass($id)
    {
        $class = YogaClass::withCount([
            'registrations as confirmed_registrations_count' => fn ($query) => $query
                ->where('status', RegistrationStatus::CONFIRMED->value),
        ])->findOrFail($id);
        $teachers = Teacher::orderBy('name')->get();
        return view('admin.class_edit', compact('class', 'teachers'));
    }

    public function updateClass(Request $request, $id)
    {
        $class = YogaClass::findOrFail($id);
        $confirmedCount = $class->registrations()
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->count();

        $request->validate([
            'name' => 'required|string|max:100',
            'teacher_id' => 'required|exists:teachers,id',
            'lich_hoc' => 'required|string|max:50',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'quantity' => 'required|integer|min:' . max(1, $confirmedCount) . '|max:50',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'teacher_id', 'lich_hoc', 'start_time', 'end_time',
            'start_date', 'end_date', 'quantity', 'price', 'location', 'description',
        ]);
        if ($this->classNameExists($data['name'], $class->id)) {
            return back()->withInput()->withErrors(['name' => 'Tên lớp học đã tồn tại. Vui lòng chọn tên khác.']);
        }
        if ($this->teacherHasScheduleConflict($data, $class->id)) {
            return back()->withInput()->withErrors([
                'teacher_id' => 'Giảng viên đã có lớp bị trùng ngày hoặc khung giờ trong khoảng thời gian này.',
            ]);
        }

        $class->update($data);
        
        return redirect()->route('admin.classes.detail', $class->id)->with('success', 'Đã cập nhật lớp học thành công!');
    }

    public function deleteClass($id)
    {
        $class = YogaClass::findOrFail($id);
        
        // Check if class has any registrations
        $registrationCount = $class->registrations()->count();
        if ($registrationCount > 0) {
            return redirect()->route('admin.classes')
                           ->with('error', "Không thể xóa lớp học {$class->name} vì đã có {$registrationCount} học viên đăng ký. Vui lòng hủy các đăng ký trước.");
        }
        
        $class->delete();
        
        return redirect()->route('admin.classes')->with('success', 'Đã xóa lớp học thành công!');
    }

    private function teacherHasScheduleConflict(array $data, ?int $exceptClassId = null): bool
    {
        $days = collect(preg_split('/[^a-z]+/i', strtolower($data['lich_hoc'] ?? '')))
            ->filter()
            ->values();
        $classes = YogaClass::where('teacher_id', $data['teacher_id'])
            ->when($exceptClassId, fn ($query) => $query->whereKey('!=', $exceptClassId))
            ->get();

        foreach ($classes as $class) {
            $existingDays = collect(preg_split('/[^a-z]+/i', strtolower($class->lich_hoc ?? '')))->filter();
            $sameDay = $days->intersect($existingDays)->isNotEmpty();
            $dateOverlap = $data['start_date'] <= $class->end_date->format('Y-m-d')
                && $data['end_date'] >= $class->start_date->format('Y-m-d');
            $timeOverlap = $data['start_time'] < $class->end_time->format('H:i:s')
                && $data['end_time'] > $class->start_time->format('H:i:s');

            if ($sameDay && $dateOverlap && $timeOverlap) {
                return true;
            }
        }

        return false;
    }

    private function classNameExists(string $name, ?int $exceptClassId = null): bool
    {
        return YogaClass::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->when($exceptClassId, fn ($query) => $query->where('id', '!=', $exceptClassId))
            ->exists();
    }

    // Customer Management
    public function customers(Request $request)
    {
        $query = Customer::withCount([
            'registrations',
            'registrations as confirmed_registrations_count' => fn ($registrationQuery) => $registrationQuery
                ->where('status', RegistrationStatus::CONFIRMED->value),
        ]);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $status = strtolower((string) $request->input('status'));
        if ($status === 'registered') {
            $query->has('registrations');
        } elseif ($status === 'unregistered') {
            $query->doesntHave('registrations');
        }
        
        $customers = $query->latest()->paginate(15)->withQueryString();
        $stats = [
            'total' => Customer::count(),
            'registered' => Customer::has('registrations')->count(),
            'unregistered' => Customer::doesntHave('registrations')->count(),
            'new_this_month' => Customer::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];
        
        return view('admin.customers', compact('customers', 'stats'));
    }

    public function createCustomer()
    {
        return view('admin.customer_create');
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'required|email|max:100|unique:customers,email',
            'birthday' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        Customer::create($request->only(['name', 'phone', 'email', 'birthday', 'gender', 'address', 'note']));
        
        return redirect()->route('admin.customers')->with('success', 'Đã tạo học viên thành công!');
    }

    public function customerDetail($id)
    {
        $customer = Customer::findOrFail($id);
        $registrations = Registration::with('class.teacher')
                                   ->where('customer_id', $id)
                                   ->orderBy('created_at', 'desc')
                                   ->get();
        
        return view('admin.customer_detail', compact('customer', 'registrations'));
    }

    public function editCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customer_edit', compact('customer'));
    }

    public function updateCustomer(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $id,
            'birthday' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only(['name', 'phone', 'birthday', 'gender', 'address', 'note']));
        
        return redirect()->route('admin.customers.detail', $customer->id)->with('success', 'Đã cập nhật học viên thành công!');
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Check if customer has any registrations
        $registrationCount = $customer->registrations()->count();
        if ($registrationCount > 0) {
            return redirect()->route('admin.customers')
                           ->with('error', "Không thể xóa học viên {$customer->name} vì đã có {$registrationCount} lượt đăng ký lớp học. Vui lòng xóa các đăng ký trước.");
        }
        
        $customer->delete();
        
        return redirect()->route('admin.customers')->with('success', 'Đã xóa học viên thành công!');
    }

    // Teacher Management
    public function teachers(Request $request)
    {
        $query = Teacher::with('user')->withCount([
            'classes',
            'classes as active_classes_count' => fn ($classQuery) => $classQuery->whereDate('end_date', '>=', today()),
        ]);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('exp_year', 'like', "%{$search}%");
            });
        }

        $status = strtolower((string) $request->input('status'));
        if ($status === 'assigned') {
            $query->has('classes');
        } elseif ($status === 'unassigned') {
            $query->doesntHave('classes');
        }
        
        $teachers = $query->latest()->paginate(12)->withQueryString();
        $stats = [
            'total' => Teacher::count(),
            'assigned' => Teacher::has('classes')->count(),
            'unassigned' => Teacher::doesntHave('classes')->count(),
            'new_this_month' => Teacher::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];
        return view('admin.teachers', compact('teachers', 'stats'));
    }

    public function createTeacher()
    {
        return view('admin.teacher_create');
    }

    public function storeTeacher(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:teachers,phone',
            'email' => 'required|email|max:100|unique:teachers,email',
            'birthday' => 'required|date|before_or_equal:today',
            'exp_year' => 'required|integer|min:0|max:60',
            'description' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'account_password' => 'nullable|string|min:6|confirmed',
        ]);

        $teacherData = $request->only(['name', 'phone', 'email', 'birthday', 'exp_year', 'description', 'avatar']);
        $teacherData['avatar'] = $request->avatar ?? 'default-avatar.jpg'; // Giá trị mặc định
        
        $teacher = Teacher::create($teacherData);
        $this->syncTeacherAccount($teacher, $request->input('account_password'));
        
        return redirect()->route('admin.teachers')->with('success', 'Đã tạo giảng viên thành công!');
    }

    public function teacherDetail($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        $classes = YogaClass::withCount([
            'registrations as confirmed_registrations_count' => fn ($query) => $query->where('status', RegistrationStatus::CONFIRMED->value),
        ])->where('teacher_id', $id)->orderByDesc('start_date')->get();
        
        return view('admin.teacher_detail', compact('teacher', 'classes'));
    }

    public function editTeacher($id)
    {
        $teacher = Teacher::with('user')->withCount('classes')->findOrFail($id);
        return view('admin.teacher_edit', compact('teacher'));
    }

    public function updateTeacher(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:teachers,phone,' . $id,
            'birthday' => 'required|date|before_or_equal:today',
            'exp_year' => 'required|integer|min:0|max:60',
            'description' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'account_password' => 'nullable|string|min:6|confirmed',
        ]);

        $teacher = Teacher::findOrFail($id);
        $teacher->update($request->only(['name', 'phone', 'birthday', 'exp_year', 'description', 'avatar']));
        $this->syncTeacherAccount($teacher, $request->input('account_password'));
        
        return redirect()->route('admin.teachers.detail', $teacher->id)->with('success', 'Đã cập nhật giảng viên thành công!');
    }

    private function syncTeacherAccount(Teacher $teacher, ?string $password = null): void
    {
        $user = User::where('teacher_id', $teacher->id)
            ->orWhere(function ($query) use ($teacher) {
                $query->where('email', $teacher->email)->whereNull('teacher_id');
            })
            ->first();

        if (!$user && !$password) {
            return;
        }

        $user ??= new User();
        $user->fill([
            'user_name' => $teacher->email,
            'name' => $teacher->name,
            'email' => $teacher->email,
            'role' => 'teacher',
            'teacher_id' => $teacher->id,
        ]);
        if ($password) {
            $user->password = Hash::make($password);
        }
        $user->save();
    }

    public function deleteTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Check if teacher has any classes
        $classCount = $teacher->classes()->count();
        if ($classCount > 0) {
            return redirect()->route('admin.teachers')
                           ->with('error', "Không thể xóa giảng viên {$teacher->name} vì đang dạy {$classCount} lớp học. Vui lòng chuyển các lớp học cho giảng viên khác trước.");
        }
        
        $teacher->delete();
        
        return redirect()->route('admin.teachers')->with('success', 'Đã xóa giảng viên thành công!');    
    }
    // Helper for discount calculation (same as RegistrationController)
    private function discountForMonths(int $months): int
    {
        return match($months) {
            1 => 0,
            3 => 5,
            6 => 10,
            12 => 20,
            default => 0,
        };
    }
}
