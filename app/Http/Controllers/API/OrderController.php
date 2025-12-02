<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Exception;

class OrderController extends Controller
{
    use ApiResponder;

    /**
     * Create an order from a valid hold
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        DB::beginTransaction();

        try {
            $holdId = $data['hold_id'];
            $paymentStatus = PaymentStatus::from($data['payment_status']);

            $hold = Order::with(['orderItems'])
                ->activeHolds()
                ->lockForUpdate()
                ->findOrFail($holdId);


            $hold->update([
                'is_hold' => false,
                'status' => OrderStatus::Confirmed,
                'payment_status' => $paymentStatus,
                'expires_at' => null,
                'consumed_at' => Carbon::now(),
            ]);

            DB::commit();


            return $this->respondWithCreated(new OrderResource($hold), 'Order created successfully from hold');

        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound('Hold not found or expired');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->setStatusCode(500)->errorInternalError('Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Get order details
     */
    public function show(int $orderId): JsonResponse
    {
        $order = Order::with(['orderItems.product'])
            ->where('is_hold', false)
            ->find($orderId);

        if (!$order) {
            return $this->setStatusCode(404)->errorNotFound('Order not found');
        }

        return $this->respondWithRetrieved(new OrderResource($order), 'Order retrieved successfully');
    }
}
