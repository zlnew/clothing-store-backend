<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItem\StoreCartItemRequest;
use App\Http\Requests\CartItem\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    protected $cartItem;

    public function __construct(CartItem $cartItem)
    {
        $this->cartItem = $cartItem;
    }

    public function index(Request $request)
    {
        $cartItem = $this->cartItem->query()
            ->where('user_id', $request->user()->id)
            ->get();

        return new CartItemResource($cartItem);
    }

    public function store(StoreCartItemRequest $request)
    {
        $request->check();

        $validated = $request->safe();

        $cartItem = $this->cartItem->query()
            ->where([
                'user_id' => $request->user()->id,
                'product_id' => $validated->product_id,
                'size' => $validated->size
            ])
            ->first();
        
        if ($cartItem) {
            $cartItem->increment('quantity', $validated->quantity);
        } else {
            $cartItem = $this->cartItem->create([
                'user_id' => $request->user()->id,
                'product_id' => $validated->product_id,
                'quantity' => $validated->quantity,
                'size' => $validated->size
            ]);
        }

        return new CartItemResource($cartItem);
    }

    public function show(CartItem $cartItem)
    {
        return new CartItemResource($cartItem);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        $validated = $request->safe();

        $cartItem->query()
            ->where('user_id', $request->user()->id)
            ->update(['quantity' => $validated->quantity]);

        return new CartItemResource($cartItem);
    }

    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();

        return response()->noContent();
    }
}
