<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomCakeOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'cake_size' => 'required|string',
            'cake_flavor' => 'required|string',
            'cake_filling' => 'required|string',
            'cake_frosting' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,in_progress,completed,cancelled',
            'special_instructions' => 'nullable|string',
            'delivery_date' => 'required|date|after:today',
            'delivery_address' => 'required|string',
            'reference_image' => 'nullable|image|max:2048', // Optional image up to 2MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a customer.',
            'user_id.exists' => 'The selected customer is invalid.',
            'cake_size.required' => 'Please select a cake size.',
            'cake_flavor.required' => 'Please select a cake flavor.',
            'cake_filling.required' => 'Please select a cake filling.',
            'cake_frosting.required' => 'Please select a cake frosting.',
            'price.required' => 'Please enter the price.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price cannot be negative.',
            'status.required' => 'Please select an order status.',
            'status.in' => 'The selected status is invalid.',
            'delivery_date.required' => 'Please select a delivery date.',
            'delivery_date.after' => 'The delivery date must be after today.',
            'delivery_address.required' => 'Please enter the delivery address.',
            'reference_image.image' => 'The reference image must be a valid image file.',
            'reference_image.max' => 'The reference image must not be larger than 2MB.',
        ];
    }
} 