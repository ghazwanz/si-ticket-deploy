<?php

namespace App\Http\Requests\Organizer;

use App\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;

class RequestPayoutRequest extends FormRequest
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
        $event = $this->route('event');

        // Only enforce validation rules for amount and reason if requesting advance payout (event is published)
        if ($event && ($event->status === EventStatus::Published || $event->status === 'published')) {
            return [
                'amount' => ['required', 'integer', 'min:1'],
                'reason' => ['required', 'string', 'min:20'],
            ];
        }

        return [];
    }

    /**
     * Get the validation messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah penarikan harus diisi.',
            'amount.integer' => 'Jumlah penarikan harus berupa angka.',
            'amount.min' => 'Jumlah penarikan minimal Rp 1.',
            'reason.required' => 'Alasan penarikan harus diisi.',
            'reason.min' => 'Alasan penarikan minimal harus terdiri dari :min karakter.',
        ];
    }
}
