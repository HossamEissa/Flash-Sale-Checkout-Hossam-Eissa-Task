<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductListRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponder;

    /**
     * Display a listing of all products
     */
    public function index(ProductListRequest $request): JsonResponse
    {
        try {
            $products = Product::query()
                ->when($request->search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->when($request->has('available_only') && $request->available_only, function ($query) {
                    return $query->where('stock', '>', 0);
                })
                ->orderBy('name')
                ->dynamicPaginate();

            return $this->respondWithRetrieved(ProductCollection::make($products), 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorDatabase($e->getMessage());
        }
    }

    /**
     * Display the specified product
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::select(['id', 'name', 'price', 'stock', 'created_at', 'updated_at'])
                ->findOrFail($id);

            return $this->respondWithItem(new ProductResource($product), 'Product retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound('Product not found');
        } catch (\Exception $e) {
            return $this->errorDatabase($e->getMessage());
        }
    }
}
