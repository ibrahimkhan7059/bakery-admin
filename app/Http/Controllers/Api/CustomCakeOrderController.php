<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomCakeOrder;
use App\Models\CakeSize;
use App\Models\CakeOptionGroup;
use App\Models\CakeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomCakeOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = CustomCakeOrder::with('user')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        // Resolve group ids for validation and pricing
        $flavorGroupId = CakeOptionGroup::where('key', 'flavor')->value('id');
        $fillingGroupId = CakeOptionGroup::where('key', 'filling')->value('id');
        $frostingGroupId = CakeOptionGroup::where('key', 'frosting')->value('id');

        $validator = Validator::make($request->all(), [
            'cake_size' => ['required','string', Rule::exists('cake_sizes', 'name')],
            'cake_flavor' => [
                'required','string',
                Rule::exists('cake_options', 'name')->where(function($q) use ($flavorGroupId){
                    $q->where('cake_option_group_id', $flavorGroupId);
                })
            ],
            'cake_filling' => [
                'required','string',
                Rule::exists('cake_options', 'name')->where(function($q) use ($fillingGroupId){
                    $q->where('cake_option_group_id', $fillingGroupId);
                })
            ],
            'cake_frosting' => [
                'required','string',
                Rule::exists('cake_options', 'name')->where(function($q) use ($frostingGroupId){
                    $q->where('cake_option_group_id', $frostingGroupId);
                })
            ],
            'special_instructions' => 'nullable|string|max:500',
            'delivery_date' => 'required|date|after:today',
            'delivery_address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $totalPrice = $this->calculateOrderPrice(
                $request->cake_size,
                $request->cake_flavor,
                $request->cake_filling,
                $request->cake_frosting
            );

        $order = CustomCakeOrder::create([
            'user_id' => $request->user()->id,
                'cake_size' => $request->cake_size,
                'cake_flavor' => $request->cake_flavor,
                'cake_filling' => $request->cake_filling,
                'cake_frosting' => $request->cake_frosting,
                'special_instructions' => $request->special_instructions,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
            'status' => 'pending',
                'price' => $totalPrice,
        ]);

        return response()->json([
                'success' => true,
                'message' => 'Custom cake order placed successfully',
                'order' => $order
        ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, CustomCakeOrder $customCakeOrder)
    {
        // Check if user owns this order
        if ($customCakeOrder->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($customCakeOrder->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomCakeOrder $customCakeOrder)
    {
        // Check if user owns this order
        if ($customCakeOrder->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow updates if order is still pending
        if ($customCakeOrder->status !== 'pending') {
            return response()->json(['message' => 'Cannot update order that is not pending'], 400);
        }

        // Resolve group ids for validation and pricing
        $flavorGroupId = CakeOptionGroup::where('key', 'flavor')->value('id');
        $fillingGroupId = CakeOptionGroup::where('key', 'filling')->value('id');
        $frostingGroupId = CakeOptionGroup::where('key', 'frosting')->value('id');

        $validator = Validator::make($request->all(), [
            'cake_size' => ['required','string', Rule::exists('cake_sizes', 'name')],
            'cake_flavor' => [
                'required','string',
                Rule::exists('cake_options', 'name')->where(function($q) use ($flavorGroupId){
                    $q->where('cake_option_group_id', $flavorGroupId);
                })
            ],
            'cake_filling' => [
                'required','string',
                Rule::exists('cake_options', 'name')->where(function($q) use ($fillingGroupId){
                    $q->where('cake_option_group_id', $fillingGroupId);
                })
            ],
            'cake_frosting' => [
                'required','string',
                Rule::exists('cake_options', 'name')->where(function($q) use ($frostingGroupId){
                    $q->where('cake_option_group_id', $frostingGroupId);
                })
            ],
            'special_instructions' => 'nullable|string|max:500',
            'delivery_date' => 'required|date|after:today',
            'delivery_address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $totalPrice = $this->calculateOrderPrice(
                $request->cake_size,
                $request->cake_flavor,
                $request->cake_filling,
                $request->cake_frosting
            );

            $customCakeOrder->update([
                'cake_size' => $request->cake_size,
                'cake_flavor' => $request->cake_flavor,
                'cake_filling' => $request->cake_filling,
                'cake_frosting' => $request->cake_frosting,
                'special_instructions' => $request->special_instructions,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'price' => $totalPrice,
        ]);

        return response()->json([
                'success' => true,
                'message' => 'Custom cake order updated successfully',
            'order' => $customCakeOrder->load('user')
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CustomCakeOrder $customCakeOrder)
    {
        // Check if user owns this order
        if ($customCakeOrder->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow deletion if order is still pending
        if ($customCakeOrder->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete order that is not pending'], 400);
        }

        $customCakeOrder->delete();

        return response()->json(['message' => 'Custom cake order deleted successfully.']);
    }

    /**
     * Calculate order price using DB-configured prices
     */
    private function calculateOrderPrice(string $sizeName, string $flavorName, string $fillingName, string $frostingName): float
    {
        $sizePrice = (float) (CakeSize::where('name', $sizeName)->value('base_price') ?? 0);

        $flavorGroupId = CakeOptionGroup::where('key', 'flavor')->value('id');
        $fillingGroupId = CakeOptionGroup::where('key', 'filling')->value('id');
        $frostingGroupId = CakeOptionGroup::where('key', 'frosting')->value('id');

        $flavorPrice = (float) (CakeOption::where('cake_option_group_id', $flavorGroupId)
            ->where('name', $flavorName)->value('price') ?? 0);
        $fillingPrice = (float) (CakeOption::where('cake_option_group_id', $fillingGroupId)
            ->where('name', $fillingName)->value('price') ?? 0);
        $frostingPrice = (float) (CakeOption::where('cake_option_group_id', $frostingGroupId)
            ->where('name', $frostingName)->value('price') ?? 0);

        return $sizePrice + $flavorPrice + $fillingPrice + $frostingPrice;
    }
}
