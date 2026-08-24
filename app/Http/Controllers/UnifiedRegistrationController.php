<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Http\Requests\UnifiedRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Models\Customer;
use App\Models\YogaClass;
use App\Models\Registration;
//use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * @deprecated Không được giao diện hiện tại sử dụng.
 *
 * POST /api/registrations hiện được route admin apiResource xử lý. Không kích hoạt
 * controller này thành public trước khi sửa ownership, schema và idempotency.
 * Xem docs/LEGACY_UNUSED.md.
 */
class UnifiedRegistrationController extends Controller
{
    public function store(UnifiedRegistrationRequest $request)
    {
    $data = $request->validated();
    // Nếu validation request vẫn require package_months thì bỏ qua, tự set mặc định hoặc không dùng
    unset($data['package_months']);

        return DB::transaction(function () use ($data) {

            if (!empty($data['customer_id'])) {
                $customer = Customer::findOrFail($data['customer_id']);
            } else {
                $customerPayload = [
                    'name'      => $data['customer']['name'],
                    'phone'     => $data['customer']['phone'] ?? null,
                    'email'     => $data['customer']['email'] ?? null,
                    'birthday'  => $data['customer']['birthday'] ?? null,
                    'gender' => $data['customer']['gender'] ?? null,
                    'address'   => $data['customer']['address'] ?? null,
                    'note'      => $data['customer']['note'] ?? null,
                ];


                $q = Customer::query();
                if (!empty($customerPayload['phone'])) {
                    $q->where('phone', $customerPayload['phone']);
                } elseif (!empty($customerPayload['email'])) {
                    $q->where('email', $customerPayload['email']);
                }
                $customer = $q->first();

                $customer = $customer
                    ? tap($customer)->update($customerPayload)
                    : Customer::create($customerPayload);
            }


            $class   = YogaClass::lockForUpdate()->findOrFail($data['class_id']);
            // Không còn package_months, discount, final_price. Giá lấy trực tiếp từ lớp học


            if (!empty($data['idempotency_key'])) {
                $existing = Registration::where('idempotency_key', $data['idempotency_key'])->first();
                if ($existing) {
                    return new RegistrationResource($existing->load(['customer','class']));
                }
            }

            $alreadyRegistered = Registration::where('customer_id', $customer->id)
                ->where('class_id', $class->id)
                ->whereIn('status', [
                    RegistrationStatus::PENDING->value,
                    RegistrationStatus::CONFIRMED->value,
                ])
                ->exists();
            if ($alreadyRegistered) {
                return response()->json([
                    'message' => 'Customer has already registered for this class.',
                ], 422);
            }

            $reg = Registration::create([
                'customer_id'    => $customer->id,
                'class_id'       => $class->id,
                // Không còn package_months, discount, final_price
                'status'         => RegistrationStatus::PENDING->value,
                'note'           => $data['note'] ?? null,
                'idempotency_key'=> $data['idempotency_key'] ?? null,
            ]);

            return (new RegistrationResource($reg->load(['customer','class'])))
                ->response()->setStatusCode(201);
        });
    }
}

