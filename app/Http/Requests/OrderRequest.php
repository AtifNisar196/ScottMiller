<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class OrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable',
            'name' => 'nullable',
            'company_name' => 'nullable',
            'country' => 'required',
            'city' => 'required',
            'state' => 'required',
            'post_code' => 'required',
            'phone' => 'required',
            'email_address' => 'nullable',
            'address' => 'required',
            'appartment_suite' => 'nullable',
            'note' => 'nullable',
            'subtotal' => 'required',
            'total' => 'required',

            //order items
            'product_id' => 'required|array',
            'qty' => 'required|array',
            'price' => 'required|array',

            // 'card_no' => 'required',
            // 'exp_month' => 'required',
            // 'exp_year' => 'required',
            // 'cvc' => 'required',
            'card_id' => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
