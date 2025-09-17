<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'supplier' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الأداة مطلوب',
            'name.string' => 'اسم الأداة يجب أن يكون نص',
            'name.max' => 'اسم الأداة لا يجب أن يتجاوز 255 حرف',
            
            'category.required' => 'الفئة مطلوبة',
            'category.string' => 'الفئة يجب أن تكون نص',
            'category.max' => 'الفئة لا يجب أن تتجاوز 255 حرف',
            
            'quantity.required' => 'الكمية مطلوبة',
            'quantity.integer' => 'الكمية يجب أن تكون رقم صحيح',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من 0',
            
            'date.required' => 'التاريخ مطلوب',
            'date.date' => 'التاريخ غير صحيح',
            
            'supplier.required' => 'المورد مطلوب',
            'supplier.string' => 'المورد يجب أن يكون نص',
            'supplier.max' => 'المورد لا يجب أن يتجاوز 255 حرف',
            
            'notes.string' => 'الملاحظات يجب أن تكون نص',
            'notes.max' => 'الملاحظات لا يجب أن تتجاوز 1000 حرف',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الأداة',
            'category' => 'الفئة',
            'quantity' => 'الكمية',
            'date' => 'التاريخ',
            'supplier' => 'المورد',
            'notes' => 'الملاحظات',
        ];
    }
}