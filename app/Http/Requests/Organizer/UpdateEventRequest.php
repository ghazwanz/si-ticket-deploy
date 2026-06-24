<?php

namespace App\Http\Requests\Organizer;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $eventId = $this->route('event');
        $event = Event::find($eventId);

        if ($event && $event->status === EventStatus::Completed) {
            throw new HttpResponseException(
                redirect()->route('organizer.events.index')
                    ->withErrors(['error' => 'Acara yang telah selesai tidak dapat diubah kembali.'])
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        $eventId = $this->route('event');
        $event = Event::with('ticketCategories')->find($eventId);

        $isLocked = false;
        $statusRule = 'required|in:draft,awaiting_approval';

        if ($event) {
            $isLocked = ($event->status === EventStatus::Published || $event->status->value === 'published') && $event->ticketCategories->sum('sold_count') > 0;

            if ($event->status === EventStatus::Published || $event->status->value === 'published') {
                $statusRule = 'required|in:published';
            }
        }

        $lockableRule = $isLocked ? 'sometimes|required' : 'required';

        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:event_categories,id',
            'description' => 'required|string',
            'venue_name' => "$lockableRule|string|max:255",
            'address' => "$lockableRule|string",
            'city' => "$lockableRule|string|max:255",
            'event_date' => "$lockableRule|date",
            'start_time' => "$lockableRule|date_format:H:i",
            'end_time' => "$lockableRule|date_format:H:i|after:start_time",
            'status' => $statusRule,
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tickets' => 'required|array|min:1',
            'tickets.*.id' => 'nullable|string',
            'tickets.*.name' => 'required|string|max:255',
            'tickets.*.price' => 'sometimes|required|numeric|min:0',
            'tickets.*.quota' => 'required|integer|min:1',
            'tickets.*.max_per_user' => 'nullable|integer|min:1',
            'merchandise' => 'nullable|array',
            'merchandise.*.id' => 'nullable|uuid',
            'merchandise.*.name' => 'required|string|max:255',
            'merchandise.*.base_price' => 'required|integer|min:0',
            'merchandise.*.description' => 'nullable|string',
            'merchandise.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'merchandise.*.is_available' => 'nullable|boolean',
            'merchandise.*.variants' => 'nullable|array',
            'merchandise.*.variants.*.id' => 'nullable|uuid',
            'merchandise.*.variants.*.group' => 'required_with:merchandise.*.variants|string|max:255',
            'merchandise.*.variants.*.value' => 'required_with:merchandise.*.variants|string|max:255',
            'merchandise.*.variants.*.stock' => 'required_with:merchandise.*.variants|integer|min:0',
            'merchandise.*.variants.*.price_adjustment' => 'required_with:merchandise.*.variants|integer',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $merchandise = $this->input('merchandise', []);
            if (! is_array($merchandise)) {
                return;
            }

            foreach ($merchandise as $index => $item) {
                if (! isset($item['base_price']) || ! is_numeric($item['base_price'])) {
                    continue;
                }

                $basePrice = (int) $item['base_price'];
                $variants = $item['variants'] ?? [];

                if (is_array($variants)) {
                    foreach ($variants as $vIndex => $variant) {
                        if (isset($variant['price_adjustment']) && is_numeric($variant['price_adjustment'])) {
                            $adjustment = (int) $variant['price_adjustment'];
                            if ($basePrice + $adjustment < 0) {
                                $validator->errors()->add(
                                    "merchandise.{$index}.variants.{$vIndex}.price_adjustment",
                                    'Harga akhir varian (Base Price + Adjustment) tidak boleh kurang dari 0.'
                                );
                            }
                        }
                    }
                }
            }
        });
    }
}
