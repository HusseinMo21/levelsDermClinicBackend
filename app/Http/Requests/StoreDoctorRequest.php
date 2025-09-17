<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
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
            // Basic Information
            'first_name' => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'third_name' => 'required|string|max:255',
            'fourth_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'national_id' => 'required|string|max:20|unique:users,national_id',
            'email' => 'required|email|max:255|unique:users,email',
            'address' => 'required|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',

            // Role and Permissions
            'role' => 'required|string|in:doctor',
            'permissions' => 'required|array',
            'permissions.*' => 'string|in:see_patients,add_appointment,remove_appointment,add_registration,see_patient_reports',

            // Financial Information
            'monthly_salary' => 'required|numeric|min:0',
            'detection_value' => 'required|numeric|min:0',
            'doctor_percentage' => 'required|numeric|min:0|max:100',

            // Working Days and Appointment Information
            'working_days' => 'required|array',
            'working_days.*.day' => 'required|string|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'working_days.*.from_time' => 'required|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            'working_days.*.to_time' => 'required|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
            'working_days.*.is_working' => 'required|boolean',

            // User Credentials
            'username' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',

            // Additional Doctor Information
            'license_number' => 'required|string|max:50|unique:doctors,license_number',
            'specialization' => 'required|string|max:255',
            'qualifications' => 'nullable|string|max:1000',
            'experience_years' => 'nullable|integer|min:0',
            'consultation_fee' => 'required|numeric|min:0',
            'bio' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Basic Information Messages
            'first_name.required' => 'الاسم الأول مطلوب',
            'first_name.string' => 'الاسم الأول يجب أن يكون نص',
            'first_name.max' => 'الاسم الأول لا يجب أن يتجاوز 255 حرف',
            
            'second_name.required' => 'الاسم الثاني مطلوب',
            'second_name.string' => 'الاسم الثاني يجب أن يكون نص',
            'second_name.max' => 'الاسم الثاني لا يجب أن يتجاوز 255 حرف',
            
            'third_name.required' => 'الاسم الثالث مطلوب',
            'third_name.string' => 'الاسم الثالث يجب أن يكون نص',
            'third_name.max' => 'الاسم الثالث لا يجب أن يتجاوز 255 حرف',
            
            'fourth_name.required' => 'الاسم الرابع مطلوب',
            'fourth_name.string' => 'الاسم الرابع يجب أن يكون نص',
            'fourth_name.max' => 'الاسم الرابع لا يجب أن يتجاوز 255 حرف',
            
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.string' => 'رقم الهاتف يجب أن يكون نص',
            'phone.max' => 'رقم الهاتف لا يجب أن يتجاوز 20 رقم',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
            
            'national_id.required' => 'رقم الهوية مطلوب',
            'national_id.string' => 'رقم الهوية يجب أن يكون نص',
            'national_id.max' => 'رقم الهوية لا يجب أن يتجاوز 20 رقم',
            'national_id.unique' => 'رقم الهوية مستخدم بالفعل',
            
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.max' => 'البريد الإلكتروني لا يجب أن يتجاوز 255 حرف',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            
            'address.required' => 'العنوان مطلوب',
            'address.string' => 'العنوان يجب أن يكون نص',
            'address.max' => 'العنوان لا يجب أن يتجاوز 500 حرف',

            // Role and Permissions Messages
            'role.required' => 'الدور مطلوب',
            'role.in' => 'الدور يجب أن يكون طبيب',
            'permissions.required' => 'الصلاحيات مطلوبة',
            'permissions.array' => 'الصلاحيات يجب أن تكون قائمة',
            'permissions.*.in' => 'إحدى الصلاحيات غير صحيحة',

            // Financial Information Messages
            'monthly_salary.required' => 'الراتب الشهري مطلوب',
            'monthly_salary.numeric' => 'الراتب الشهري يجب أن يكون رقم',
            'monthly_salary.min' => 'الراتب الشهري يجب أن يكون أكبر من أو يساوي 0',
            
            'detection_value.required' => 'قيمة الكشف مطلوبة',
            'detection_value.numeric' => 'قيمة الكشف يجب أن تكون رقم',
            'detection_value.min' => 'قيمة الكشف يجب أن تكون أكبر من أو تساوي 0',
            
            'doctor_percentage.required' => 'نسبة الطبيب مطلوبة',
            'doctor_percentage.numeric' => 'نسبة الطبيب يجب أن تكون رقم',
            'doctor_percentage.min' => 'نسبة الطبيب يجب أن تكون أكبر من أو تساوي 0',
            'doctor_percentage.max' => 'نسبة الطبيب يجب أن تكون أقل من أو تساوي 100',

            // Working Days Messages
            'working_days.required' => 'أيام العمل مطلوبة',
            'working_days.array' => 'أيام العمل يجب أن تكون قائمة',
            'working_days.*.day.required' => 'اليوم مطلوب',
            'working_days.*.day.in' => 'اليوم غير صحيح',
            'working_days.*.from_time.required' => 'وقت البداية مطلوب',
            'working_days.*.from_time.regex' => 'وقت البداية غير صحيح',
            'working_days.*.to_time.required' => 'وقت النهاية مطلوب',
            'working_days.*.to_time.regex' => 'وقت النهاية غير صحيح',
            'working_days.*.is_working.required' => 'حالة العمل مطلوبة',
            'working_days.*.is_working.boolean' => 'حالة العمل يجب أن تكون صحيح أو خطأ',

            // User Credentials Messages
            'username.required' => 'اسم المستخدم مطلوب',
            'username.string' => 'اسم المستخدم يجب أن يكون نص',
            'username.max' => 'اسم المستخدم لا يجب أن يتجاوز 255 حرف',
            'username.unique' => 'اسم المستخدم مستخدم بالفعل',
            
            'password.required' => 'كلمة المرور مطلوبة',
            'password.string' => 'كلمة المرور يجب أن تكون نص',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور وتأكيد كلمة المرور غير متطابقتين',
            
            'password_confirmation.required' => 'تأكيد كلمة المرور مطلوب',
            'password_confirmation.string' => 'تأكيد كلمة المرور يجب أن يكون نص',
            'password_confirmation.min' => 'تأكيد كلمة المرور يجب أن يكون 8 أحرف على الأقل',

            // Additional Doctor Information Messages
            'license_number.required' => 'رقم الترخيص مطلوب',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نص',
            'license_number.max' => 'رقم الترخيص لا يجب أن يتجاوز 50 حرف',
            'license_number.unique' => 'رقم الترخيص مستخدم بالفعل',
            
            'specialization.required' => 'التخصص مطلوب',
            'specialization.string' => 'التخصص يجب أن يكون نص',
            'specialization.max' => 'التخصص لا يجب أن يتجاوز 255 حرف',
            
            'consultation_fee.required' => 'رسوم الاستشارة مطلوبة',
            'consultation_fee.numeric' => 'رسوم الاستشارة يجب أن تكون رقم',
            'consultation_fee.min' => 'رسوم الاستشارة يجب أن تكون أكبر من أو تساوي 0',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'الاسم الأول',
            'second_name' => 'الاسم الثاني',
            'third_name' => 'الاسم الثالث',
            'fourth_name' => 'الاسم الرابع',
            'phone' => 'رقم الهاتف',
            'national_id' => 'رقم الهوية',
            'email' => 'البريد الإلكتروني',
            'address' => 'العنوان',
            'role' => 'الدور',
            'permissions' => 'الصلاحيات',
            'monthly_salary' => 'الراتب الشهري',
            'detection_value' => 'قيمة الكشف',
            'doctor_percentage' => 'نسبة الطبيب',
            'working_days' => 'أيام العمل',
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
            'license_number' => 'رقم الترخيص',
            'specialization' => 'التخصص',
            'consultation_fee' => 'رسوم الاستشارة',
        ];
    }
}
