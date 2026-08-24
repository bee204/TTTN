<?php

namespace App\Http\Controllers;

use App\Models\YogaClass;
use App\Models\Teacher;
use App\Models\Customer;
use App\Models\Registration;
use App\Models\ClassReview;
use App\Enums\RegistrationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebController extends Controller
{
    // Account registration page
    public function registerAccount()
    {
        return view('pages.register_account');
    }

    // Handle account registration
    public function registerAccountSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if ($customer) {
            $customer->update(['name' => $request->name]);
        } else {
            do {
                $phone = '09' . random_int(10000000, 99999999);
            } while (Customer::where('phone', $phone)->exists());

            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'birthday' => '1990-01-01',
                'gender' => 'other',
            ]);
        }
        $user = \App\Models\User::create([
            'user_name' => Str::before($request->email, '@') . '-' . uniqid(),
            'name' => $request->name,
            'email' => $request->email,
            'customer_id' => $customer?->id,
            'password' => bcrypt($request->password),
            'role' => 'customer', // default role
        ]);

        return redirect()->route('account.login', ['redirect' => $request->input('redirect')])
            ->with('success', 'Đăng ký tài khoản thành công. Vui lòng đăng nhập để tiếp tục.');
    }

    // Account login page
    public function loginAccount()
    {
        return view('pages.login_account');
    }

    public function teacherLogin()
    {
        return view('pages.login_account', ['portal' => 'teacher']);
    }

    public function accountProfile()
    {
        abort_unless(Auth::check(), 401);
        return view('pages.account_profile', ['user' => Auth::user()->load('customer')]);
    }

    // Handle account login
    public function loginAccountSubmit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $portal = $request->input('portal');
            if ($portal === 'teacher') {
                if ($user->role === 'teacher') {
                    return redirect()->route('teacher.dashboard');
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Cổng này chỉ dành cho tài khoản giáo viên được VITA cấp.',
                ])->withInput($request->only('email'));
            }

            if ($user->role !== 'customer') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tài khoản giảng viên/quản trị viên không thể đăng nhập tại user site. Vui lòng sử dụng cổng đăng nhập riêng.',
                ])->withInput($request->only('email', 'redirect'));
            } else {
                $redirect = $request->input('redirect');
                return $redirect && str_starts_with($redirect, '/')
                    ? redirect($redirect)
                    : redirect()->route('dashboard');
            }
        }
        return back()->withErrors(['email' => 'Thông tin đăng nhập không đúng!']);
    }

    public function logoutAccount(Request $request)
    {
        $isTeacher = Auth::user()?->role === 'teacher';
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isTeacher ? 'teacher.login' : 'account.login')
            ->with('success', 'Đã đăng xuất tài khoản.');
    }

    public function registeredClasses(Request $request)
    {
        abort_unless(Auth::check(), 401);
        $customer = $this->authenticatedCustomer();
        $customerId = $customer?->id;
        $registrations = Registration::with(['class.teacher', 'attendances'])
            ->where('customer_id', $customerId)
            ->whereIn('status', [
                RegistrationStatus::PENDING->value,
                RegistrationStatus::CONFIRMED->value,
            ])
            ->latest()
            ->get();
        return view('pages.registered_classes', compact('registrations'));
    }

    public function cancelRegistration($id)
    {
        $customer = $this->authenticatedCustomer();
        abort_unless($customer, 403);
        $registration = Registration::with('attendances')
            ->where('id', $id)
            ->where('customer_id', $customer->id)
            ->whereIn('status', [RegistrationStatus::PENDING->value, RegistrationStatus::CONFIRMED->value])
            ->firstOrFail();

        if ($registration->attendances->isNotEmpty()) {
            return back()->with('error', 'Không thể hủy đơn vì học viên đã được điểm danh.');
        }

        $registration->update(['status' => RegistrationStatus::CANCELLED]);
        return back()->with('success', 'Đã hủy đơn đăng ký lớp học.');
    }

    public function registeredClassDetail($id)
    {
        abort_unless(Auth::check() && Auth::user()->customer_id, 403);
        $registration = Registration::where('customer_id', Auth::user()->customer_id)
            ->where('class_id', $id)
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->firstOrFail();
        $class = YogaClass::with('teacher')->findOrFail($id);
        $review = ClassReview::where('customer_id', Auth::user()->customer_id)
            ->where('class_id', $id)
            ->first();
        return view('pages.registered_class_detail', compact('class', 'review'));
    }

    public function submitClassReview(Request $request, $id)
    {
        abort_unless(Auth::check() && Auth::user()->customer_id, 403);
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Registration::where('customer_id', Auth::user()->customer_id)
            ->where('class_id', $id)
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->firstOrFail();

        if (ClassReview::where('customer_id', Auth::user()->customer_id)
            ->where('class_id', $id)
            ->exists()) {
            return back()->with('error', 'Bạn đã đánh giá lớp học này và không thể đánh giá lại.');
        }

        ClassReview::create([
            'customer_id' => Auth::user()->customer_id,
            'class_id' => $id,
            ...$data,
        ]);

        return back()->with('success', 'Đánh giá lớp học đã được lưu.');
    }
    public function contactSend(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        // You can implement email sending or save to DB here

        return redirect()->route('contact')->with('success', 'Your message has been sent!');
    }
    public function dashboard()
    {
        $classesCount = YogaClass::count();
        $teachersCount = Teacher::count();
        $membersCount = Customer::count();
        $registrationsCount = Registration::count();

        return view('pages.dashboard', compact(
            'classesCount',
            'teachersCount', 
            'membersCount',
            'registrationsCount'
        ));
    }

    public function classes()
    {
        $classes = YogaClass::with('teacher')->latest()->paginate(12);
        $customer = $this->authenticatedCustomer();
        $registrationStatuses = $customer
            ? Registration::with('attendances:id,registration_id')
                ->where('customer_id', $customer->id)
                ->whereIn('status', [RegistrationStatus::PENDING->value, RegistrationStatus::CONFIRMED->value])
                ->get()
                ->mapWithKeys(fn ($registration) => [
                    $registration->class_id => [
                        'status' => $registration->status->value,
                        'has_attendance' => $registration->attendances->isNotEmpty(),
                    ],
                ])
            : collect();
        return view('pages.classes', compact('classes', 'registrationStatuses'));
    }

    public function classDetail($id)
    {
        $class = YogaClass::with('teacher')->findOrFail($id);
        
        // Get confirmed registrations for this class
        $confirmedRegistrations = Registration::with('customer')
            ->where('class_id', $id)
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->get();
        
        $registeredStudents = $confirmedRegistrations->map(function($registration) {
            return $registration->customer;
        });
        
        $availableSlots = $class->quantity - $registeredStudents->count();
        $customer = $this->authenticatedCustomer();
        $registrationStatus = $customer
            ? Registration::where('customer_id', $customer->id)
                ->where('class_id', $id)
                ->whereIn('status', [RegistrationStatus::PENDING->value, RegistrationStatus::CONFIRMED->value])
                ->value('status')
            : null;
        
        return view('pages.class_detail', compact('class', 'registeredStudents', 'availableSlots', 'registrationStatus'));
    }

    public function team()
    {
        $teachers = Teacher::latest()->paginate(12);
        return view('pages.team', compact('teachers'));
    }

    public function members()
    {
        $customers = Customer::latest()->paginate(12);
        return view('pages.members', compact('customers'));
    }

    public function register(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('account.register', [
                'redirect' => $request->getRequestUri(),
            ])->with('info', 'Vui lòng tạo tài khoản để đăng ký lớp học.');
        }
        $customer = $this->authenticatedCustomer();
        if ($customer && $request->filled('class_id') && Registration::where('customer_id', $customer->id)
            ->where('class_id', $request->class_id)
            ->whereIn('status', [RegistrationStatus::PENDING->value, RegistrationStatus::CONFIRMED->value])
            ->exists()) {
            return redirect()->route('classes')->with('error', 'Bạn đã đăng ký lớp học này hoặc đang chờ duyệt.');
        }
        $activeClassIds = $customer
            ? Registration::where('customer_id', $customer->id)
                ->whereIn('status', [RegistrationStatus::PENDING->value, RegistrationStatus::CONFIRMED->value])
                ->pluck('class_id')
            : collect();
        $classes = YogaClass::with('teacher')
            ->whereNotIn('id', $activeClassIds)
            ->get();
        $selectedClassId = $request->get('class_id');
        $user = Auth::user()->load('customer');
        return view('pages.register', compact('classes', 'selectedClassId', 'user'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function authors()
    {
        $authors = [
            [
                'avatar' => '👩‍💼',
                'name' => 'Nguyễn Thị Cẩm Tú',
                'role' => 'Trưởng nhóm - Frontend (User site)',
                'id' => 'K23DTCN549',
                'task' => 'Develop UI for user site, integrate with API (listen for trigger)',
                'image' => 'tu.jpg'
            ],
            [
                'avatar' => '👨‍💻',
                'name' => 'Hoàng Trọng Lực',
                'role' => 'Frontend (Admin site)',
                'id' => 'K23DTCN542',
                'task' => 'Develop UI for admin site, integrate with API',
                'image' => 'luc.jpg'
            ],
            [
                'avatar' => '👩‍💻',
                'name' => 'Nguyễn Thị Thu Hương',
                'role' => 'Backend (User + Admin), DB Design',
                'id' => 'K23DTCN539',
                'task' => 'Develop backend APIs for both admin and user site, design DB',
                'image' => 'huong.jpg'
            ],
            [
                'avatar' => '👨‍💻',
                'name' => 'Vũ Huy Năng',
                'role' => 'Frontend (User site)',
                'id' => 'K23DTCN543',
                'task' => 'Develop UI for user site, integrate with API',
                'image' => 'hung.jpg'
            ],
            [
                'avatar' => '👨‍💻',
                'name' => 'Nguyễn Trung Hiếu',
                'role' => 'Frontend (Admin site)',
                'id' => 'K23DTCN536',
                'task' => 'Develop UI for admin site, integrate with API',
                'image' => 'hieu.jpg'
            ],
            [
                'avatar' => '👩‍💻',
                'name' => 'Trần Thu Trang',
                'role' => 'Thành viên phát triển',
                'id' => '',
                'task' => 'Tham gia phát triển và kiểm thử hệ thống',
                'image' => null
            ],
            [
                'avatar' => '👨‍💻',
                'name' => 'Hoàng Lâm Phong',
                'role' => 'Thành viên phát triển',
                'id' => '',
                'task' => 'Tham gia phát triển và hoàn thiện chức năng',
                'image' => null
            ],
        ];
        $project = [
            'weeks' => 8,
            'features' => 15,
            'files' => 50,
            'lines' => 1000,
            'goal' => 'Phát triển một hệ thống quản lý trung tâm Yoga toàn diện, hỗ trợ đăng ký lớp học, quản lý thành viên, và các tính năng quản trị cho nhân viên. Hệ thống được thiết kế với giao diện thân thiện và dễ sử dụng.',
            'tech' => ['Laravel','PHP','HTML5','CSS3','JavaScript','MySQL','Bootstrap'],
            'period' => '8 tuần, từ tháng 1 đến tháng 3 năm 2025',
            'context' => 'Đây là đồ án cuối kỳ môn "Lập trình Web" thuộc chương trình Công nghệ Thông tin. Dự án được thực hiện dưới sự hướng dẫn của giảng viên và áp dụng các kiến thức đã học trong suốt khóa học.'
        ];
        return view('pages.authors', compact('authors','project'));
    }

    public function login()
    {
        return view('pages.login');
    }

    public function loginSubmit(Request $request)
    {
        // Handle login logic here
        return redirect()->route('dashboard')->with('success', 'Login successful!');
    }

    public function registerSubmit(Request $request)
    {
        abort_unless(Auth::check(), 401);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'package_months' => 'required|in:1,3,6,12',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'accepted',
        ]);

        $user = Auth::user();
        $class = YogaClass::findOrFail($request->class_id);
        if ($class->start_date->isPast()) {
            return redirect()->route('classes')->with('error', 'Lớp học đã bắt đầu, không thể đăng ký thêm.');
        }
        if ($class->is_full) {
            return back()->withInput()->with('error', 'Lớp học vừa đủ chỗ. Vui lòng chọn một lớp Yoga khác.');
        }
        $customer = $this->authenticatedCustomer();
        $customer ??= Customer::create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $request->phone,
            'birthday' => '1990-01-01',
            'gender' => 'female',
        ]);
        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);
        if (!$user->customer_id) {
            $user->update(['customer_id' => $customer->id]);
        }

        $alreadyRegistered = Registration::where('customer_id', $customer->id)
            ->where('class_id', $request->class_id)
            ->whereIn('status', [RegistrationStatus::PENDING->value, RegistrationStatus::CONFIRMED->value])
            ->exists();
        if ($alreadyRegistered) {
            return redirect()->route('classes')->with('error', 'Bạn đã đăng ký lớp học này hoặc đang chờ duyệt.');
        }

        // Get class and calculate discount
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

        // Create registration with PENDING status
        $registration = Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $request->class_id,
            'package_months' => $packageMonths,
            'discount' => $discountAmount,
            'final_price' => $finalPrice,
            'status' => RegistrationStatus::PENDING->value,
            'note' => $request->notes,
        ]);

        return redirect()->route('registered.classes')->with('success', 'Đăng ký thành công! Đơn đăng ký của bạn đang chờ xét duyệt. Mã đăng ký: #' . $registration->id);
    }

    private function authenticatedCustomer(): ?Customer
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            return null;
        }

        $user = Auth::user();
        $customer = $user->customer;
        if (!$customer && $user->email) {
            $customer = Customer::where('email', $user->email)->first();
            if ($customer) {
                $user->update(['customer_id' => $customer->id]);
            }
        }

        return $customer;
    }

    // Admin routes
    public function adminDashboard()
    {
        $classesCount = YogaClass::count();
        $teachersCount = Teacher::count();
        $membersCount = Customer::count();
        $registrationsCount = Registration::count();

        return view('admin.dashboard', compact(
            'classesCount',
            'teachersCount', 
            'membersCount',
            'registrationsCount'
        ));
    }

    public function adminClasses()
    {
        $classes = YogaClass::with('teacher')->latest()->paginate(15);
        return view('admin.classes', compact('classes'));
    }

    public function adminTeachers()
    {
        $teachers = Teacher::latest()->paginate(15);
        return view('admin.teachers', compact('teachers'));
    }

    public function adminRegistrations()
    {
        $registrations = Registration::with(['customer', 'class.teacher'])->latest()->paginate(15);
        return view('admin.registrations', compact('registrations'));
    }

    public function teachers()
    {
        $teachers = Teacher::withCount('classes')->latest()->paginate(12);
        return view('pages.teachers', compact('teachers'));
    }

    public function teacherDetail($id)
    {
        $teacher = Teacher::with(['classes' => fn ($query) => $query
            ->orderByRaw('end_date < ? asc', [today()->toDateString()])
            ->orderBy('start_date')])
            ->findOrFail($id);
        return view('pages.teacher_detail', compact('teacher'));
    }
}
