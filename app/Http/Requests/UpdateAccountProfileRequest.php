<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountProfileRequest extends FormRequest
{
    protected $errorBag = 'profile';

    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        $customerId = $this->user()?->customer_id;

        if (! $customerId && $this->user()?->email) {
            $customerId = Customer::where('email', $this->user()->email)->value('id');
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['prohibited'],
            'phone' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9,10}$/',
                'max:20',
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.prohibited' => 'Email đăng nhập không được phép thay đổi.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'birthday.required' => 'Vui lòng chọn ngày sinh.',
            'birthday.before_or_equal' => 'Ngày sinh không thể là ngày trong tương lai.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính được chọn không hợp lệ.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/[\s.-]+/', '', (string) $this->input('phone'));
        if (str_starts_with($phone, '+84')) {
            $phone = '0'.substr($phone, 3);
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $phone,
            'address' => $this->filled('address') ? trim((string) $this->input('address')) : null,
        ]);
    }
}
