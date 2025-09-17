<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patientId = $this->route('patient')->id;
        
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'national_id' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('patients', 'national_id')->ignore($patientId)
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->ignore($patientId)
            ],
            'phone' => 'sometimes|required|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'gender' => 'sometimes|required|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'medical_history' => 'nullable|string|max:2000',
            'allergies' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:active,inactive,blocked',
            'visit_count' => 'sometimes|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages in Arabic.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب',
            'last_name.required' => 'الاسم الأخير مطلوب',
            'national_id.required' => 'رقم الهوية مطلوب',
            'national_id.unique' => 'رقم الهوية موجود مسبقاً',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني موجود مسبقاً',
            'phone.required' => 'رقم الهاتف مطلوب',
            'date_of_birth.required' => 'تاريخ الميلاد مطلوب',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            'gender.required' => 'الجنس مطلوب',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى أو آخر',
            'status.in' => 'الحالة يجب أن تكون نشط أو غير نشط أو محظور',
            'visit_count.integer' => 'عدد الزيارات يجب أن يكون رقم صحيح',
            'visit_count.min' => 'عدد الزيارات يجب أن يكون أكبر من أو يساوي 0',
        ];
    }

    /**
     * Get custom attribute names in Arabic.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'الاسم الأول',
            'last_name' => 'الاسم الأخير',
            'national_id' => 'رقم الهوية',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'phone_2' => 'رقم الهاتف الثاني',
            'date_of_birth' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'status' => 'الحالة',
            'visit_count' => 'عدد الزيارات',
        ];
    }
}
