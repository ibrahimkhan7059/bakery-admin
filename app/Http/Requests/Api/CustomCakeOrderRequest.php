<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        $rules = [
            'cake_size' => 'required|string|max:255',
            'cake_flavor' => 'required|string|max:255',
            'cake_filling' => 'required|string|max:255',
            'cake_frosting' => 'required|string|max:255',
            'special_instructions' => 'nullable|string|max:1000',
            'delivery_date' => 'required|date|after:today',
            'delivery_address' => 'required|string|max:500',
        ];

        // Add price validation only for store requests
        if ($this->isMethod('POST')) {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'cake_size.required' => 'Please select a cake size.',
            'cake_flavor.required' => 'Please select a cake flavor.',
            'cake_filling.required' => 'Please select a cake filling.',
            'cake_frosting.required' => 'Please select a cake frosting.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price cannot be negative.',
            'delivery_date.required' => 'Please select a delivery date.',
            'delivery_date.after' => 'The delivery date must be after today.',
            'delivery_address.required' => 'Please enter the delivery address.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
} 