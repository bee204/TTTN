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
        $ownedClass = YogaClass::factory()->create(['teacher_id' => $teacher->id]);
        $otherClass = YogaClass::factory()->create(['teacher_id' => $otherTeacher->id]);
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
