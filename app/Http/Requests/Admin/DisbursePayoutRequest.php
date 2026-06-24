<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DisbursePayoutRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'proof_photo' => ['required', 'image', 'max:5120'],
            'transfer_reference' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get the validation messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof_photo.required' => 'Bukti transfer harus diunggah.',
            'proof_photo.image' => 'Bukti transfer harus berupa gambar.',
            'proof_photo.max' => 'Ukuran bukti transfer maksimal 5 MB.',
            'transfer_reference.required' => 'Nomor referensi transfer harus diisi.',
            'transfer_reference.string' => 'Nomor referensi transfer harus berupa teks.',
            'transfer_reference.max' => 'Nomor referensi transfer maksimal 255 karakter.',
        ];
    }
}
