<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:patients,national_id|max:20',
            'email' => 'required|email|unique:patients,email|max:255',
            'phone' => 'required|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
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
            'first_name.string' => 'الاسم الأول يجب أن يكون نص',
            'first_name.max' => 'الاسم الأول يجب ألا يتجاوز 255 حرف',
            
            'last_name.required' => 'الاسم الأخير مطلوب',
            'last_name.string' => 'الاسم الأخير يجب أن يكون نص',
            'last_name.max' => 'الاسم الأخير يجب ألا يتجاوز 255 حرف',
            
            'national_id.required' => 'رقم الهوية مطلوب',
            'national_id.string' => 'رقم الهوية يجب أن يكون نص',
            'national_id.unique' => 'رقم الهوية موجود مسبقاً',
            'national_id.max' => 'رقم الهوية يجب ألا يتجاوز 20 حرف',
            
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني موجود مسبقاً',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرف',
            
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.string' => 'رقم الهاتف يجب أن يكون نص',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 20 حرف',
            
            'phone_2.string' => 'رقم الهاتف الثاني يجب أن يكون نص',
            'phone_2.max' => 'رقم الهاتف الثاني يجب ألا يتجاوز 20 حرف',
            
            'date_of_birth.required' => 'تاريخ الميلاد مطلوب',
            'date_of_birth.date' => 'تاريخ الميلاد غير صحيح',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            
            'gender.required' => 'الجنس مطلوب',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى أو آخر',
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
            'address' => 'العنوان',
            'city' => 'المدينة',
            'state' => 'المحافظة',
            'postal_code' => 'الرمز البريدي',
            'country' => 'البلد',
            'emergency_contact_name' => 'اسم جهة الاتصال للطوارئ',
            'emergency_contact_phone' => 'هاتف الطوارئ',
            'medical_history' => 'التاريخ المرضي',
            'allergies' => 'الحساسية',
            'current_medications' => 'الأدوية الحالية',
            'notes' => 'ملاحظات',
            'profile_image' => 'صورة الملف الشخصي',
        ];
    }
}
