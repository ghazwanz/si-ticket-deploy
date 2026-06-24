<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class ApproveAdvancePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'approved_amount' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'approved_amount.required' => 'Jumlah yang disetujui harus diisi.',
            'approved_amount.integer' => 'Jumlah yang disetujui harus berupa angka.',
            'approved_amount.min' => 'Jumlah yang disetujui minimal Rp 1.',
        ];
    }
}
