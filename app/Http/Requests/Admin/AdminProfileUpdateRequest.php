<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminProfileUpdateRequest extends FormRequest
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
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $this->user()->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email'    => 'Email must be valid.',
            'email.unique'   => 'This email is already in use.',
            'photo.image'    => 'The photo must be an image.',
            'photo.mimes'    => 'Allowed formats: jpg, jpeg, png.',
            'photo.max'      => 'The maximum photo size is 2MB.',
        ];
    }
}
