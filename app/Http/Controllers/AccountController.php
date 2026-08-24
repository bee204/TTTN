<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountPasswordRequest;
use App\Http\Requests\UpdateAccountProfileRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->profileData($request->user()->load('customer')),
        ]);
    }

    public function updateProfile(UpdateAccountProfileRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $customer = $user->customer
            ?: Customer::where('email', $user->email)->first();

        if (! $customer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tài khoản chưa được liên kết với hồ sơ thành viên.',
                ], 422);
            }

            return back()->with('error', 'Tài khoản chưa được liên kết với hồ sơ thành viên.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($user, $customer, $data): void {
            $customer->update([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'gender' => $data['gender'],
                'address' => $data['address'] ?? null,
            ]);

            $user->update([
                'name' => $data['name'],
                'customer_id' => $customer->id,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cập nhật hồ sơ thành công.',
                'data' => $this->profileData($user->fresh()->load('customer')),
            ]);
        }

        return redirect()->route('account.profile')->with('success', 'Thông tin tài khoản đã được cập nhật.');
    }

    public function updatePassword(UpdateAccountPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($request->validated('password')),
        ])->save();

        $user->tokens()->delete();

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đổi mật khẩu thành công. Các API token cũ đã được thu hồi.',
            ]);
        }

        return redirect()->route('account.profile')->with('success', 'Mật khẩu đã được thay đổi thành công.');
    }

    private function profileData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->customer?->phone,
            'birthday' => $user->customer?->birthday?->toDateString(),
            'gender' => $user->customer?->gender,
            'address' => $user->customer?->address,
        ];
    }
}
