<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Models\ClassReview;
use App\Models\Customer;
use App\Models\Registration;
use App\Models\Teacher;
use App\Models\User;
use App\Models\YogaClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CN01Cn02Test extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_student_attendance_can_be_recorded_and_summarized(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $registration = $this->confirmedRegistration();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
            'registration_id' => $registration->id,
            'attendance_date' => '2026-08-20',
            'status' => 'PRESENT',
        ]);

        $response->assertCreated()->assertJsonPath('status', 'PRESENT');
        $this->assertDatabaseHas('attendances', [
            'registration_id' => $registration->id,
            'attendance_date' => '2026-08-20',
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/attendance/summary?class_id='.$registration->class_id)
            ->assertOk()
            ->assertJsonPath('attendance_rate', 100)
            ->assertJsonPath('attended_sessions', 1);
    }

    public function test_confirmed_student_can_review_once_and_class_ranking_is_calculated(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'PRESENT',
        ]);
        $payload = [
            'class_id' => $registration->class_id,
            'rating' => 5,
            'comment' => 'Lop hoc rat tot.',
        ];

        $this->actingAs($user, 'sanctum')->postJson('/api/class-reviews', $payload)
            ->assertCreated()
            ->assertJsonPath('rating', 5);

        $this->actingAs($user, 'sanctum')->postJson('/api/class-reviews', [
            ...$payload,
            'rating' => 4,
        ])->assertStatus(409);

        $review = ClassReview::where('customer_id', $customer->id)->where('class_id', $class->id)->firstOrFail();
        $this->actingAs($user, 'sanctum')->putJson('/api/class-reviews/'.$review->id, [
            'rating' => 4,
            'comment' => 'Cap nhat review',
        ])->assertOk()->assertJsonPath('rating', 4);

        $this->travel(8)->days();
        $this->actingAs($user, 'sanctum')->putJson('/api/class-reviews/'.$review->id, [
            'rating' => 3,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('class_reviews', 1);
        $this->assertDatabaseHas('class_reviews', ['id' => $review->id, 'rating' => 4]);
        $this->actingAs($user, 'sanctum')->getJson('/api/class-reviews/ranking')
            ->assertOk()
            ->assertJsonPath('0.average_rating', 4)
            ->assertJsonPath('0.review_count', 1);
    }

    public function test_api_review_cannot_be_created_or_updated_for_another_customer(): void
    {
        $owner = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $ownerUser = User::factory()->create(['role' => 'customer', 'customer_id' => $owner->id]);
        $otherUser = User::factory()->create(['role' => 'customer', 'customer_id' => $otherCustomer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($owner, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'PRESENT',
        ]);
        $review = ClassReview::create([
            'customer_id' => $owner->id,
            'class_id' => $class->id,
            'rating' => 5,
        ]);

        $this->actingAs($otherUser, 'sanctum')->postJson('/api/class-reviews', [
            'class_id' => $class->id,
            'rating' => 4,
            'customer_id' => $owner->id,
        ])->assertNotFound();

        $this->actingAs($otherUser, 'sanctum')->putJson('/api/class-reviews/'.$review->id, [
            'rating' => 1,
        ])->assertForbidden();

        $this->actingAs($ownerUser, 'sanctum')->deleteJson('/api/class-reviews/'.$review->id)
            ->assertForbidden();

        $this->assertDatabaseHas('class_reviews', ['id' => $review->id, 'rating' => 5]);
    }

    public function test_admin_can_delete_review_from_admin_class_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();
        $class = YogaClass::factory()->create();
        $review = ClassReview::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'rating' => 4,
            'comment' => 'Can kiem duyet',
        ]);
        $otherClass = YogaClass::factory()->create();
        $otherReview = ClassReview::create([
            'customer_id' => $customer->id,
            'class_id' => $otherClass->id,
            'rating' => 3,
        ]);
        $teacher = Teacher::factory()->create();
        $teacherUser = User::factory()->create(['role' => 'teacher', 'teacher_id' => $teacher->id]);

        $this->actingAs($admin)->get(route('admin.classes.reviews', $class->id))
            ->assertOk()
            ->assertSee('Xóa đánh giá');

        $this->actingAs($teacherUser)->delete(route('admin.classes.reviews.destroy', [$class->id, $review->id]))
            ->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.classes.reviews.destroy', [$class->id, $otherReview->id]))
            ->assertNotFound();
        $this->assertDatabaseHas('class_reviews', ['id' => $review->id]);
        $this->assertDatabaseHas('class_reviews', ['id' => $otherReview->id]);

        $this->actingAs($admin)->delete(route('admin.classes.reviews.destroy', [$class->id, $review->id]))
            ->assertRedirect(route('admin.classes.reviews', $class->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('class_reviews', ['id' => $review->id]);
    }

    public function test_api_review_requires_present_or_late_attendance(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'ABSENT',
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/class-reviews', [
            'class_id' => $class->id,
            'rating' => 5,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('class_reviews', 0);
    }

    public function test_api_review_cannot_be_submitted_after_thirty_day_window(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDays(60)->toDateString(),
            'end_date' => now()->subDays(31)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->subDays(45)->toDateString(),
            'status' => 'PRESENT',
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/class-reviews', [
            'class_id' => $class->id,
            'rating' => 5,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('class_reviews', 0);
    }

    public function test_attendance_cannot_be_recorded_for_a_future_date(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $registration = $this->confirmedRegistration();

        $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
            'registration_id' => $registration->id,
            'attendance_date' => now()->addDay()->toDateString(),
            'status' => 'PRESENT',
        ])->assertStatus(422);
    }

    public function test_user_cannot_submit_a_review_twice(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'PRESENT',
        ]);
        ClassReview::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'rating' => 5,
            'comment' => 'Tot',
        ]);

        $this->actingAs($user)->post(route('registered.class.review', $class->id), [
            'rating' => 3,
            'comment' => 'Sua doi',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('class_reviews', [
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'rating' => 5,
        ]);
    }

    public function test_confirmed_student_can_review_after_attendance(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
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
            'comment' => 'Lop hoc rat tot.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('class_reviews', [
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'rating' => 5,
        ]);
    }

    public function test_student_cannot_review_without_present_or_late_attendance(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'ABSENT',
        ]);

        $this->actingAs($user)->get(route('registered.class.detail', $class->id))
            ->assertOk()
            ->assertSee('Bạn cần điểm danh có mặt hoặc đi muộn');

        $this->actingAs($user)->post(route('registered.class.review', $class->id), [
            'rating' => 5,
            'comment' => 'Chua du dieu kien',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('class_reviews', 0);
    }

    public function test_student_can_edit_review_once_within_seven_days(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'LATE',
        ]);

        $this->actingAs($user)->post(route('registered.class.review', $class->id), [
            'rating' => 3,
            'comment' => 'Ban dau',
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($user)->get(route('registered.class.detail', $class->id))
            ->assertOk()
            ->assertSee('Sửa đánh giá')
            ->assertSee('value="3" checked', false);

        $this->actingAs($user)->put(route('registered.class.review.update', $class->id), [
            'rating' => 5,
            'comment' => 'Da cap nhat',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('class_reviews', 1);
        $this->assertDatabaseHas('class_reviews', [
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'rating' => 5,
            'comment' => 'Da cap nhat',
        ]);

        $this->travel(8)->days();
        $this->actingAs($user)->put(route('registered.class.review.update', $class->id), [
            'rating' => 4,
            'comment' => 'Qua han',
        ])->assertRedirect()->assertSessionHas('error');
        $this->actingAs($user)->delete(route('registered.class.review.destroy', $class->id))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('class_reviews', ['class_id' => $class->id, 'rating' => 5]);
    }

    public function test_student_can_delete_own_review_within_seven_days(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->toDateString(),
            'status' => 'PRESENT',
        ]);
        ClassReview::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'rating' => 4,
            'comment' => 'Can xoa',
        ]);

        $this->actingAs($user)->delete(route('registered.class.review.destroy', $class->id))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('class_reviews', 0);
        $this->actingAs($user)->get(route('registered.class.detail', $class->id))
            ->assertOk()
            ->assertSee('Gửi đánh giá');
    }

    public function test_student_cannot_submit_review_more_than_thirty_days_after_class(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create(['role' => 'customer', 'customer_id' => $customer->id]);
        $class = YogaClass::factory()->create([
            'start_date' => now()->subDays(60)->toDateString(),
            'end_date' => now()->subDays(31)->toDateString(),
        ]);
        $registration = $this->confirmedRegistration($customer, $class);
        $registration->attendances()->create([
            'attendance_date' => now()->subDays(45)->toDateString(),
            'status' => 'PRESENT',
        ]);

        $this->actingAs($user)->post(route('registered.class.review', $class->id), [
            'rating' => 5,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('class_reviews', 0);
    }

    private function confirmedRegistration(?Customer $customer = null, ?YogaClass $class = null): Registration
    {
        $customer ??= Customer::factory()->create();
        $class ??= YogaClass::factory()->create([
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);

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
