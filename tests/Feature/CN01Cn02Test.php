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
        $user = User::factory()->create(['role' => 'admin']);
        $registration = $this->confirmedRegistration();
        $payload = [
            'customer_id' => $registration->customer_id,
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
        ])->assertCreated();

        $this->assertDatabaseCount('class_reviews', 1);
        $this->actingAs($user, 'sanctum')->getJson('/api/class-reviews/ranking')
            ->assertOk()
            ->assertJsonPath('0.average_rating', 4)
            ->assertJsonPath('0.review_count', 1);
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
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);
        Registration::create([
            'customer_id' => $customer->id,
            'class_id' => $class->id,
            'package_months' => 1,
            'discount' => 0,
            'final_price' => $class->price,
            'status' => RegistrationStatus::CONFIRMED,
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
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
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
            'attendance_date' => '2026-08-20',
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

    private function confirmedRegistration(): Registration
    {
        $customer = Customer::factory()->create();
        $class = YogaClass::factory()->create([
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
