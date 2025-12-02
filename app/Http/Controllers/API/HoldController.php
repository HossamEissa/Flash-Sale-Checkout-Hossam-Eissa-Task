<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHoldRequest;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Jobs\ExpireOrderHoldJob;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Exception;

class HoldController extends Controller
{
    use ApiResponder;

    public function store(CreateHoldRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $productId = $data['product_id'];
            $requestedQty = $data['qty'];

            $product = Product::where('id', $productId)->lockForUpdate()->findOrFail($productId);

            $availableStock = $product->available_stock;

            if ($requestedQty > $availableStock) {
                return $this->errorWrongArgs("Insufficient stock. Available: {$availableStock}, Requested: {$requestedQty}");
            }

            $expiresAt = Carbon::now()->addMinutes(2);

            // and if we have here multiple products the total will be changed
            $order = Order::create([
                'is_hold' => true,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'expires_at' => $expiresAt,
                'total' => $product->price * $requestedQty,
            ]);

            // and also here if we have multiple products the creation process will be changed
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'qty' => $requestedQty,
                'unit_price' => $product->price,
                'subtotal' => $product->price * $requestedQty,
            ]);

            ExpireOrderHoldJob::dispatch($order->id)->delay($expiresAt);

            DB::commit();

            $finalResult = [
                'hold_id' => $order->id,
                'expires_at' => $expiresAt->toISOString(),
            ];
            return $this->respondWithCreated($finalResult, 'Hold created successfully');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound('Product not found');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->setStatusCode(500)->errorInternalError('Failed to create hold: ' . $e->getMessage());
        }
    }


    public function show(int $holdId)
    {
        $order = Order::with(['orderItems.product'])
            ->where('is_hold', true)
            ->findOrFail($holdId);


        if ($order->status === OrderStatus::Pending && $order->expires_at && Carbon::now()->isAfter($order->expires_at)) {
            $order->update(['status' => OrderStatus::Cancelled]);
        }

        $isExpired = $order->status === OrderStatus::Cancelled || ($order->expires_at && Carbon::now()->isAfter($order->expires_at));

        $data = [
            'hold_id' => $order->id,
            'status' => $order->status->value,
            'expires_at' => $order->expires_at?->toISOString(),
            'is_expired' => $isExpired,
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->qty,
                    'price_per_unit' => $item->unit_price,
                    'total_price' => $item->subtotal,
                ];
            }),
        ];

        return $this->respondWithRetrieved($data, 'Hold retrieved successfully');
    }


}
