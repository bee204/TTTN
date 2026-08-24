<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_profile_without_changing_login_email(): void
    {
        [$user, $customer] = $this->customerAccount();

        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => 'Nguyễn Minh Anh',
            'phone' => '0912345678',
            'birthday' => '1995-05-20',
            'gender' => 'female',
            'address' => 'Quận Đống Đa, Hà Nội',
        ])->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nguyễn Minh Anh',
            'email' => 'member@example.com',
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nguyễn Minh Anh',
            'phone' => '0912345678',
        ]);
    }

    public function test_profile_api_rejects_email_changes(): void
    {
        [$user] = $this->customerAccount();
        Sanctum::actingAs($user);

        $this->putJson('/api/account/profile', [
            'name' => 'Nguyễn Minh Anh',
            'email' => 'changed@example.com',
            'phone' => '0912345678',
            'birthday' => '1995-05-20',
            'gender' => 'female',
            'address' => 'Hà Nội',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'member@example.com',
        ]);
    }

    public function test_customer_must_supply_the_current_password(): void
    {
        [$user] = $this->customerAccount();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'HealthyPass9',
            'password_confirmation' => 'HealthyPass9',
        ])->assertSessionHasErrors(['current_password'], null, 'password');

        $this->assertTrue(Hash::check('123456', $user->fresh()->password));
    }

    public function test_customer_can_change_password_and_existing_api_tokens_are_revoked(): void
    {
        [$user] = $this->customerAccount();
        $tokenId = $user->createToken('old-device')->accessToken->id;

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => '123456',
            'password' => 'HealthyPass9',
            'password_confirmation' => 'HealthyPass9',
        ])->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('HealthyPass9', $user->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_authenticated_customer_can_read_profile_api(): void
    {
        [$user, $customer] = $this->customerAccount();
        Sanctum::actingAs($user);

        $this->getJson('/api/account/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.phone', $customer->phone);
    }

    private function customerAccount(): array
    {
        $customer = Customer::factory()->create([
            'name' => 'Thành viên VITA',
            'email' => 'member@example.com',
            'phone' => '0901234567',
        ]);

        $user = User::factory()->create([
            'name' => $customer->name,
            'email' => $customer->email,
            'role' => 'customer',
            'customer_id' => $customer->id,
            'password' => Hash::make('123456'),
        ]);

        return [$user, $customer];
    }
}
