<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function index(Request $request)
    {
        if (isset($request->category) && $request->category !== 'all') {
            $this->product = $this->product
                ->where('category', $request->category);
        }

        if (isset($request->search)) {
            $this->product = $this->product
                ->where('name', 'LIKE', '%'.$request->search.'%');
        }

        if (isset($request->new_release) && $request->new_release == 'true') {
            $this->product = $this->product
                ->where('stock', '>', 0)
                ->whereDate('created_at', '>', Carbon::now()->subDays(30))
                ->limit(8);
        }

        if (isset($request->on_sale) && $request->on_sale == 'true') {
            $this->product = $this->product
                ->where('stock', '>', 0)
                ->where('discount_percentage', '>', 0)
                ->limit(8);
        }

        return new ProductResource($this->product->latest()->get());
    }

    public function store(StoreProductRequest $request)
    {
        $this->product->fill($request->validated());
        $this->product->save();

        return response([
            'data' => new ProductResource($this->product),
            'message' => 'Product created successfully'
        ]);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->fill($request->validated());
        $product->save();

        return response([
            'data' => new ProductResource($product),
            'message' => 'Product updated successfully'
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response([
            'message' => 'Product deleted successfully'
        ]);
    }
}
