<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)->withoutTrashed()],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,organizer,user',
            'is_active' => 'required|boolean',
            'organization_name' => 'required_if:role,organizer|nullable|string|max:255',
            'phone' => 'required_if:role,organizer|nullable|string|max:20',
            'bank_name' => 'required_if:role,organizer|nullable|string|max:255',
            'bank_account_number' => 'required_if:role,organizer|nullable|string|max:50',
            'bank_account_name' => 'required_if:role,organizer|nullable|string|max:255',
            'organization_address' => 'required_if:role,organizer|nullable|string',
            'official_contact' => 'required_if:role,organizer|nullable|string|email|max:255',
            'legality_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }
}
