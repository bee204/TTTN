<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Models\Customer;
use App\Models\Registration;
use App\Models\Teacher;
use App\Models\User;
use App\Models\YogaClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_account_cannot_login_through_user_site(): void
    {
        User::factory()->create([
            'role' => 'teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('123456'),
        ]);

        $this->post('/account/login', [
            'email' => 'teacher@example.com',
            'password' => '123456',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_session_can_switch_to_teacher_login_portal(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'email' => 'teacher-portal@example.com',
            'password' => bcrypt('123456'),
        ]);

        $this->actingAs($customerUser)->get('/teacher')
            ->assertRedirect(route('teacher.login'));

        $this->post('/teacher/login', [
            'email' => $teacherUser->email,
            'password' => '123456',
            'portal' => 'teacher',
        ])->assertRedirect(route('teacher.dashboard'));

        $this->assertAuthenticatedAs($teacherUser);
    }

    public function test_teacher_login_page_does_not_show_customer_account_information(): void
    {
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer Private Name',
        ]);

        $this->actingAs($customerUser)->get('/teacher/login')
            ->assertOk()
            ->assertDontSee('Customer Private Name')
            ->assertDontSee('Thông tin tài khoản');
    }

    public function test_teacher_login_page_does_not_offer_account_registration(): void
    {
        $this->get('/teacher/login')
            ->assertOk()
            ->assertDontSee('Đăng ký tài khoản');
    }

    public function test_teacher_panel_dropdown_only_offers_logout(): void
    {
        $teacher = Teacher::factory()->create();
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacherUser)->get('/teacher')
            ->assertOk()
            ->assertDontSee('🏠 Trang chính')
            ->assertSee('🚪 Đăng xuất');
    }

    public function test_teacher_can_view_reviews_only_for_owned_classes(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'teacher_id' => $teacher->id,
        ]);
        $ownedClass = YogaClass::factory()->create(['teacher_id' => $teacher->id]);
        $otherClass = YogaClass::factory()->create(['teacher_id' => $otherTeacher->id]);

        $this->actingAs($teacherUser)->get('/teacher/classes/'.$ownedClass->id.'/reviews')
            ->assertOk()
            ->assertSee(route('teacher.dashboard'));
        $this->actingAs($teacherUser)->get('/teacher/classes/'.$otherClass->id.'/reviews')
            ->assertForbidden();
    }

    public function test_teacher_can_view_details_only_for_owned_classes(): void
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'teacher_id' => $teacher->id,
        ]);
        $ownedClass = YogaClass::factory()->create(['teacher_id' => $teacher->id]);
        $otherClass = YogaClass::factory()->create(['teacher_id' => $otherTeacher->id]);

        $this->actingAs($teacherUser)->get('/teacher/classes/'.$ownedClass->id)
            ->assertOk()
            ->assertSee($ownedClass->name)
            ->assertSee(route('teacher.dashboard'));
        $this->actingAs($teacherUser)->get('/teacher/classes/'.$otherClass->id)
            ->assertForbidden();
    }

    public function test_teacher_logout_redirects_to_teacher_login(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacherUser)->post(route('account.logout'))
            ->assertRedirect(route('teacher.login'))
            ->assertSessionHas('success');

        $this->assertGuest();
    }

    public function test_student_history_only_contains_the_logged_in_student_registrations(): void
    {
        $student = Customer::factory()->create();
        $otherStudent = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $student->id]);
        $teacher = Teacher::factory()->create();
        $studentClass = YogaClass::factory()->create(['teacher_id' => $teacher->id, 'name' => 'Lop cua hoc vien']);
        $otherClass = YogaClass::factory()->create(['teacher_id' => $teacher->id, 'name' => 'Lop cua hoc vien khac']);

        foreach ([[$student, $studentClass], [$otherStudent, $otherClass]] as [$customer, $class]) {
            Registration::create([
                'customer_id' => $customer->id,
                'class_id' => $class->id,
                'package_months' => 1,
                'discount' => 0,
                'final_price' => $class->price,
                'status' => RegistrationStatus::CONFIRMED,
            ]);
        }

        $this->actingAs($user)->get('/registered-classes')
            ->assertOk()
            ->assertSee($studentClass->name)
            ->assertDontSee($otherClass->name);
    }

    public function test_pending_registration_cannot_be_submitted_again_for_the_same_class(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $teacher = Teacher::factory()->create();
        $class = YogaClass::factory()->create(['teacher_id' => $teacher->id]);
        Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::PENDING,
        ]);

        $this->actingAs($user)->post('/register', [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'class_id' => $class->id,
            'package_months' => 1,
        ])->assertRedirect(route('classes'))
            ->assertSessionHas('error');

        $this->assertSame(1, Registration::where('customer_id', $customer->id)
            ->where('class_id', $class->id)->count());
    }

    public function test_registered_class_is_removed_from_the_registration_dropdown(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $teacher = Teacher::factory()->create();
        $registeredClass = YogaClass::factory()->create(['teacher_id' => $teacher->id, 'name' => 'Lớp đã gửi đơn']);
        $availableClass = YogaClass::factory()->create(['teacher_id' => $teacher->id, 'name' => 'Lớp còn trống']);
        Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $registeredClass->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $registeredClass->price,
            'status' => RegistrationStatus::PENDING,
        ]);

        $this->actingAs($user)->get('/register')
            ->assertOk()
            ->assertDontSee($registeredClass->name)
            ->assertSee($availableClass->name);
    }

    public function test_attended_class_is_shown_as_in_progress_on_user_class_list(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::CONFIRMED,
        ]);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'PRESENT',
        ]);

        $this->actingAs($user)->get('/classes')
            ->assertOk()
            ->assertSee('Đang học')
            ->assertDontSee('Đã được duyệt');
    }

    public function test_existing_registration_is_detected_when_user_customer_link_is_missing(): void
    {
        $customer = Customer::factory()->create(['email' => 'linked-by-email@example.com']);
        $user = User::factory()->create([
            'role' => 'customer',
            'email' => $customer->email,
            'customer_id' => null,
        ]);
        $teacher = Teacher::factory()->create();
        $class = YogaClass::factory()->create(['teacher_id' => $teacher->id]);
        Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::PENDING,
        ]);

        $this->actingAs($user)->get('/classes')
            ->assertOk()
            ->assertSee('Đang chờ duyệt')
            ->assertDontSee(route('register', ['class_id' => $class->id]));

        $this->actingAs($user)->get('/register?class_id='.$class->id)
            ->assertRedirect(route('classes'))
            ->assertSessionHas('error');

        $this->assertNotNull($user->fresh()->customer_id);
    }

    public function test_customer_with_missing_customer_link_can_open_registered_classes_and_review_page(): void
    {
        $customer = Customer::factory()->create(['email' => 'review-link@example.com']);
        $user = User::factory()->create([
            'role' => 'customer',
            'email' => $customer->email,
            'customer_id' => null,
        ]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'PRESENT',
        ]);

        $this->actingAs($user)->post(route('registered.class.review', $class->id), [
            'rating' => 5,
            'comment' => 'Danh gia sau khi tu lien ket customer.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($user)->get(route('registered.class.detail', $class->id))
            ->assertOk()
            ->assertSee('Sửa đánh giá');

        $this->actingAs($user)->get(route('registered.classes'))
            ->assertOk()
            ->assertSee($class->name);

        $this->assertSame($customer->id, $user->fresh()->customer_id);
    }

    public function test_cancelled_registration_can_be_submitted_again_for_the_same_class(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $teacher = Teacher::factory()->create();
        $class = YogaClass::factory()->create(['teacher_id' => $teacher->id]);
        Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::CANCELLED,
        ]);

        $this->actingAs($user)->post('/register', [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'class_id' => $class->id,
            'package_months' => 1,
        ])->assertRedirect(route('registered.classes'));

        $this->assertDatabaseHas('registrations', [
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'status' => RegistrationStatus::PENDING->value,
        ]);
    }

    public function test_user_can_cancel_registration_before_attendance(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create(['start_date' => now()->addDays(3)->toDateString()]);
        $registration = Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::PENDING,
        ]);

        $this->actingAs($user)->post(route('registered.class.cancel', $registration->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => RegistrationStatus::CANCELLED->value,
        ]);
    }

    public function test_admin_cannot_create_duplicate_class_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = Teacher::factory()->create();
        YogaClass::factory()->create(['name' => 'Yoga Trùng Tên']);

        $this->actingAs($admin)->post('/admin/classes', [
            'name' => 'yoga trùng tên',
            'teacher_id' => $teacher->id,
            'lich_hoc' => 'Mon-Wed-Fri',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'quantity' => 10,
            'price' => 100000,
            'location' => 'Phong A',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('classes', 1);
    }

    public function test_admin_cannot_assign_overlapping_class_to_the_same_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = Teacher::factory()->create();
        YogaClass::factory()->create([
            'teacher_id' => $teacher->id,
            'lich_hoc' => 'Mon-Wed-Fri',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);

        $this->actingAs($admin)->post('/admin/classes', [
            'name' => 'Lop bi trung lich',
            'teacher_id' => $teacher->id,
            'lich_hoc' => 'Mon-Tue',
            'start_time' => '08:30',
            'end_time' => '09:30',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-20',
            'quantity' => 10,
            'price' => 100000,
            'location' => 'Phong A',
            'description' => 'Test',
        ])->assertSessionHasErrors('teacher_id');

        $this->assertDatabaseMissing('classes', ['name' => 'Lop bi trung lich']);
    }

    public function test_teacher_account_can_only_mark_attendance_for_owned_classes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $ownedClass = YogaClass::factory()->create([
            'teacher_id' => $teacher->id,
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $otherClass = YogaClass::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $customer = Customer::factory()->create();
        $ownedRegistration = $this->confirmedRegistration($customer, $ownedClass);
        $otherRegistration = $this->confirmedRegistration(Customer::factory()->create(), $otherClass);

        $this->actingAs($admin)->put('/admin/teachers/'.$teacher->id, [
            'name' => $teacher->name,
            'phone' => $teacher->phone,
            'email' => $teacher->email,
            'birthday' => $teacher->birthday->format('Y-m-d'),
            'exp_year' => $teacher->exp_year,
            'description' => $teacher->description,
            'account_password' => 'teacher123',
            'account_password_confirmation' => 'teacher123',
        ])->assertRedirect();

        $teacherUser = User::where('teacher_id', $teacher->id)->firstOrFail();
        $this->assertSame($teacher->email, $teacherUser->user_name);
        $this->actingAs($teacherUser)->get('/teacher/classes/'.$ownedClass->id.'/attendance')
            ->assertOk()
            ->assertSee('/teacher');

        $this->actingAs($teacherUser)->post(route('teacher.registrations.attendance.store', $ownedRegistration->id), [
            'attendance_date' => now()->toDateString(),
            'status' => 'LATE',
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'registration_id' => $ownedRegistration->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'LATE',
        ]);

        $this->actingAs($teacherUser)->get('/teacher/classes/'.$ownedClass->id.'/attendance')
            ->assertOk()
            ->assertSee('disabled', false)
            ->assertSee('Đã điểm danh')
            ->assertSee('value="LATE" selected', false);

        $this->actingAs($teacherUser, 'sanctum')->postJson('/api/attendance', [
            'registration_id' => $ownedRegistration->id,
            'attendance_date' => $ownedClass->start_date->format('Y-m-d'),
            'status' => 'PRESENT',
        ])->assertCreated();

        $this->actingAs($teacherUser, 'sanctum')->postJson('/api/attendance', [
            'registration_id' => $otherRegistration->id,
            'attendance_date' => $otherClass->start_date->format('Y-m-d'),
            'status' => 'PRESENT',
        ])->assertForbidden();
    }

    private function confirmedRegistration(Customer $customer, YogaClass $class): Registration
    {
        return Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::CONFIRMED,
        ]);
    }
}
