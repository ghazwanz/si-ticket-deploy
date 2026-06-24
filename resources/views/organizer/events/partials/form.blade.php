@php
    $event ??= null;
    $method ??= 'POST';
    $submitLabel ??= 'Simpan Acara';

    $isPublished = $event?->status?->value === 'published';
    $hasSales = $event ? $event->hasSales() : false;
    
    // Lock specific fields if published with sales
    $isPartiallyLocked = $isPublished && $hasSales;
    
    // Fully lock everything if the event is over or in cancellation process
    $isFullyLocked = in_array($event?->status?->value, ['completed', 'cancelled', 'awaiting_cancellation']);
    
    $isLocked = $isPartiallyLocked || $isFullyLocked;
    
    $soldCount = $event ? $event->ticketCategories->sum('sold_count') : 0;
    
    $isHardCutoffPassed = false;
    if ($event && $event->event_date && $event->start_time) {
        $eventDateTime = \Carbon\Carbon::parse($event->event_date->format('Y-m-d').' '.$event->start_time);
        $isHardCutoffPassed = now()->greaterThanOrEqualTo($eventDateTime);
    }
    
    $lockedClass = 'bg-slate-100 cursor-not-allowed opacity-70 focus:ring-0';
    
    $ticketRows = old('tickets')
        ?? ($event?->ticketCategories?->map(fn ($ticket) => [
            'id' => (string) $ticket->id,
            'name' => $ticket->name,
            'price' => $ticket->price,
            'quota' => $ticket->quota,
            'max_per_user' => $ticket->max_per_user,
        ])->values()->all() ?: [[
            'id' => 'ticket-1',
            'name' => '',
            'price' => 0,
            'quota' => 100,
            'max_per_user' => null,
        ]]);

    $merchRows = old('merchandise')
        ?? ($event?->merchandiseItems?->map(fn ($item) => [
            'id' => (string) $item->id,
            'name' => $item->name,
            'base_price' => $item->base_price,
            'description' => $item->description,
            'is_available' => $item->is_available,
            'image_url' => $item->image ? Storage::disk('public')->url($item->image) : null,
            'variants' => $item->variants?->map(fn ($v) => [
                'id' => (string) $v->id,
                'group' => $v->variant_group,
                'value' => $v->variant_value,
                'stock' => $v->stock,
                'price_adjustment' => $v->price_adjustment,
            ])->values()->all() ?: [],
        ])->values()->all() ?: []);

    $inputClass = 'w-full rounded-2xl border-slate-200 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition-all placeholder:text-slate-400 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-white';
    $labelClass = 'mb-2 block text-[13px] font-bold uppercase tracking-widest text-slate-500';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{
    step: 1,
    tickets: @js($ticketRows),
    merchandise: @js($merchRows),
    bannerPreview: '{{ $event?->banner_image ? Storage::disk('public')->url($event->banner_image) : '' }}',
    addTicket() {
        this.tickets.push({ id: `ticket-${Date.now()}`, name: '', price: 0, quota: 100, max_per_user: null });
    },
    removeTicket(index) {
        if (this.tickets.length > 1) {
            this.tickets.splice(index, 1);
        }
    },
    addMerchandise() {
        this.merchandise.push({ id: `merch-${Date.now()}`, name: '', base_price: 0, description: '', is_available: true, image_url: null, variants: [] });
    },
    removeMerchandise(index) {
        this.merchandise.splice(index, 1);
    },
    addVariant(index) {
        this.merchandise[index].variants.push({ id: `var-${Date.now()}`, group: 'Size', value: '', stock: 0, price_adjustment: 0 });
    },
    removeVariant(mIndex, vIndex) {
        this.merchandise[mIndex].variants.splice(vIndex, 1);
    },
    handleBannerChange(e) {
        const file = e.target.files[0];
        if (file) {
            this.bannerPreview = URL.createObjectURL(file);
        }
    },
    handleMerchImageChange(e, index) {
        const file = e.target.files[0];
        if (file) {
            this.merchandise[index].image_url = URL.createObjectURL(file);
        }
    }
}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-8">
            
            @if($event && $event->rejection_message)
                <div class="glass-panel p-5 rounded-3xl mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-850 dark:text-rose-300">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-400">
                            <x-heroicon-o-x-circle class="w-6 h-6 animate-pulse" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-extrabold uppercase tracking-widest text-rose-600 dark:text-rose-450 mb-1">
                                {{ $event->status->value === 'cancelled' ? 'Acara Dibatalkan oleh Admin' : 'Acara Ditolak oleh Admin' }}
                            </h3>
                            <p class="text-sm font-semibold mb-3 text-rose-750 dark:text-rose-400">
                                {{ $event->status->value === 'cancelled' ? 'Acara Anda telah dibatalkan oleh Admin dengan alasan sebagai berikut:' : 'Acara Anda dikembalikan ke status draf dengan alasan penolakan sebagai berikut:' }}
                            </p>
                            <div class="bg-white/50 dark:bg-slate-900/50 rounded-xl p-4 text-sm italic border border-rose-500/10 text-slate-800 dark:text-slate-200">
                                "{{ $event->rejection_message }}"
                            </div>
                            @if($event->status->value !== 'cancelled')
                            <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-3">
                                * Harap perbaiki konten acara sesuai masukan di atas sebelum mengajukan kembali untuk ditinjau.
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            @if(isset($pendingCancellation) && $pendingCancellation)
                <div class="glass-panel p-5 rounded-3xl mb-6 bg-orange-500/10 border border-orange-500/20 text-orange-800 dark:text-orange-300">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-orange-500/20 text-orange-600 dark:text-orange-400">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold uppercase tracking-widest text-orange-600 dark:text-orange-400 mb-1">
                                Menunggu Persetujuan Pembatalan
                            </h3>
                            <p class="text-sm font-medium mb-3">
                                Permintaan pembatalan diajukan pada {{ $pendingCancellation->created_at->translatedFormat('d M Y, H:i') }}. Acara Anda sedang ditinjau oleh Admin.
                            </p>
                            <div class="bg-white/50 dark:bg-slate-900/50 rounded-xl p-4 text-sm italic border border-orange-500/10">
                                "{{ $pendingCancellation->reason }}"
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Step Indicator --}}
            <div class="glass-panel p-4 rounded-3xl mb-6 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <button type="button" @click="step = 1" class="flex flex-col items-center flex-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 transition-all" 
                             :class="step >= 1 ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/30' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'">
                            1
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest" :class="step >= 1 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500'">Info Acara</span>
                    </button>
                    <div class="h-1 flex-1 bg-slate-100 dark:bg-slate-800 rounded-full mx-2 overflow-hidden relative">
                        <div class="absolute inset-y-0 left-0 bg-violet-600 transition-all" :class="step >= 2 ? 'w-full' : 'w-0'"></div>
                    </div>
                    <button type="button" @click="step = 2" class="flex flex-col items-center flex-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 transition-all" 
                             :class="step >= 2 ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/30' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'">
                            2
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest" :class="step >= 2 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500'">Tiket</span>
                    </button>
                    <div class="h-1 flex-1 bg-slate-100 dark:bg-slate-800 rounded-full mx-2 overflow-hidden relative">
                        <div class="absolute inset-y-0 left-0 bg-violet-600 transition-all" :class="step >= 3 ? 'w-full' : 'w-0'"></div>
                    </div>
                    <button type="button" @click="step = 3" class="flex flex-col items-center flex-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 transition-all" 
                             :class="step >= 3 ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/30' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'">
                            3
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest" :class="step >= 3 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500'">Suvenir</span>
                    </button>
                </div>
            </div>

            {{-- Step 1: Informasi Dasar & Lokasi --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <fieldset @disabled($isFullyLocked)>
                <x-organizer.form-section
                    icon="document-text"
                    title="Informasi Dasar"
                    description="Gunakan judul, kategori, dan deskripsi yang jelas agar calon peserta memahami nilai acara Anda.">
                    
                    <div class="mb-5">
                        <label class="{{ $labelClass }}">Banner Acara</label>
                        <div class="relative group rounded-[2rem] border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:hover:bg-slate-900 overflow-hidden transition-all text-center aspect-video flex flex-col items-center justify-center">
                            <input type="file" name="banner_image" accept="image/jpeg,image/png,image/webp" @change="handleBannerChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            
                            <template x-if="bannerPreview">
                                <img :src="bannerPreview" class="absolute inset-0 w-full h-full object-cover">
                            </template>
                            
                            <div class="relative z-0 flex flex-col items-center p-6" :class="bannerPreview ? 'opacity-0 group-hover:opacity-100 bg-slate-900/60 backdrop-blur-sm w-full h-full justify-center transition-all' : ''">
                                <div class="w-12 h-12 rounded-full bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 flex items-center justify-center mb-3">
                                    <x-heroicon-o-photo class="w-6 h-6" />
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="bannerPreview ? 'Klik atau seret untuk mengganti gambar' : 'Klik atau seret gambar ke sini'"></p>
                                <p class="text-xs text-slate-500 mt-1">Format JPG, PNG, WEBP (Maks. 2MB)</p>
                            </div>
                        </div>
                        <x-organizer.field-error name="banner_image" />
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="event-name" class="{{ $labelClass }}">Nama Acara</label>
                            <input id="event-name" type="text" name="name" value="{{ old('name', $event?->name) }}" required class="{{ $inputClass }}" placeholder="Misal: Konser Senja Jakarta">
                            <x-organizer.field-error name="name" />
                        </div>
                        <div>
                            <label for="category-id" class="{{ $labelClass }}">Kategori Acara</label>
                            <select id="category-id" name="category_id" required class="{{ $inputClass }}">
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $event?->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-organizer.field-error name="category_id" />
                        </div>
                    </div>
                    <div class="mt-5">
                        <label for="event-description" class="{{ $labelClass }}">Deskripsi Acara</label>
                        <textarea id="event-description" name="description" rows="5" required class="{{ $inputClass }}" placeholder="Jelaskan konsep acara, pengalaman peserta, dan informasi penting lainnya.">{{ old('description', $event?->description) }}</textarea>
                        <x-organizer.field-error name="description" />
                    </div>
                </x-organizer.form-section>

                <x-organizer.form-section
                    icon="map-pin"
                    title="Lokasi dan Jadwal"
                    description="Pastikan tempat, kota, alamat, dan waktu pelaksanaan sesuai dengan informasi publik acara.">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="venue-name" class="{{ $labelClass }}">Nama Tempat</label>
                            <input id="venue-name" type="text" name="venue_name" value="{{ old('venue_name', $event?->venue_name) }}" {{ $isLocked ? 'disabled' : 'required' }} class="{{ $inputClass }} {{ $isLocked ? $lockedClass : '' }}" placeholder="Misal: GBK Hall A">
                            <x-organizer.field-error name="venue_name" />
                        </div>
                        <div>
                            <label for="event-city" class="{{ $labelClass }}">Kota</label>
                            <input id="event-city" type="text" name="city" value="{{ old('city', $event?->city) }}" {{ $isLocked ? 'disabled' : 'required' }} class="{{ $inputClass }} {{ $isLocked ? $lockedClass : '' }}" placeholder="Misal: Jakarta Pusat">
                            <x-organizer.field-error name="city" />
                        </div>
                    </div>
                    <div class="mt-5">
                        <label for="event-address" class="{{ $labelClass }}">Alamat Lengkap Tempat</label>
                        <input id="event-address" type="text" name="address" value="{{ old('address', $event?->address) }}" {{ $isLocked ? 'disabled' : 'required' }} class="{{ $inputClass }} {{ $isLocked ? $lockedClass : '' }}" placeholder="Masukkan alamat lengkap lokasi acara">
                        <x-organizer.field-error name="address" />
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div>
                            <label for="event-date" class="{{ $labelClass }}">Tanggal Acara</label>
                            <input id="event-date" type="date" name="event_date" value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}" {{ $isLocked ? 'disabled' : 'required' }} class="{{ $inputClass }} {{ $isLocked ? $lockedClass : '' }}">
                            <x-organizer.field-error name="event_date" />
                        </div>
                        <div>
                            <label for="start-time" class="{{ $labelClass }}">Waktu Mulai</label>
                            <input id="start-time" type="time" name="start_time" value="{{ old('start_time', $event?->start_time ? substr((string) $event->start_time, 0, 5) : null) }}" {{ $isLocked ? 'disabled' : 'required' }} class="{{ $inputClass }} {{ $isLocked ? $lockedClass : '' }}">
                            <x-organizer.field-error name="start_time" />
                        </div>
                        <div>
                            <label for="end-time" class="{{ $labelClass }}">Waktu Selesai</label>
                            <input id="end-time" type="time" name="end_time" value="{{ old('end_time', $event?->end_time ? substr((string) $event->end_time, 0, 5) : null) }}" {{ $isLocked ? 'disabled' : 'required' }} class="{{ $inputClass }} {{ $isLocked ? $lockedClass : '' }}">
                            <x-organizer.field-error name="end_time" />
                        </div>
                    </div>
                </x-organizer.form-section>
                </fieldset>
            </div>

            {{-- Step 2: Kategori Tiket --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" x-cloak>
                <fieldset @disabled($isFullyLocked)>
                <x-organizer.form-section
                    icon="ticket"
                    title="Kategori Tiket"
                    description="Atur nama, harga, dan kuota setiap kategori tiket. Gunakan harga 0 untuk tiket gratis.">
                    <div class="mb-5 flex justify-end">
                        <button type="button" @click="addTicket()" class="inline-flex items-center gap-2 rounded-2xl bg-violet-600/10 px-4 py-2 text-xs font-extrabold uppercase tracking-widest text-violet-600 transition-all hover:bg-violet-600 hover:text-white dark:text-violet-400 dark:hover:text-white">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Tambah Tiket
                        </button>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(ticket, index) in tickets" :key="ticket.id">
                            <div class="rounded-[1.5rem] border border-slate-200/80 bg-white/70 p-4 shadow-sm transition-all hover:border-violet-500/30 dark:border-slate-800 dark:bg-white/5">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:bg-slate-900 dark:text-slate-400" x-text="`Kategori ${index + 1}`"></span>
                                    <button type="button" @click="removeTicket(index)" x-show="tickets.length > 1" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold text-rose-500 transition-all hover:bg-rose-500/10">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                        Hapus
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                    <div class="md:col-span-4">
                                        <label class="{{ $labelClass }}">Nama Kategori</label>
                                        <input type="hidden" :name="`tickets[${index}][id]`" x-model="ticket.id">
                                        <input type="text" :name="`tickets[${index}][name]`" x-model="ticket.name" required class="{{ $inputClass }}" placeholder="Misal: VIP, Festival">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="{{ $labelClass }}">Harga (Rp)</label>
                                        <input type="number" min="0" :name="`tickets[${index}][price]`" x-model="ticket.price" :required="!(ticket.id && !ticket.id.startsWith('ticket-') && {{ $isLocked ? 'true' : 'false' }})" class="{{ $inputClass }}" :class="(ticket.id && !ticket.id.startsWith('ticket-') && {{ $isLocked ? 'true' : 'false' }}) ? '{{ $lockedClass }}' : ''" placeholder="0 untuk gratis" :disabled="ticket.id && !ticket.id.startsWith('ticket-') && {{ $isLocked ? 'true' : 'false' }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="{{ $labelClass }}">Kuota</label>
                                        <input type="number" min="1" :name="`tickets[${index}][quota]`" x-model="ticket.quota" required class="{{ $inputClass }}" placeholder="100">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="{{ $labelClass }}">Maks. Pembelian</label>
                                        <input type="number" min="1" :name="`tickets[${index}][max_per_user]`" x-model="ticket.max_per_user" class="{{ $inputClass }}" placeholder="Opsional (misal: 4)">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <x-organizer.field-error name="tickets" />
                        <x-organizer.field-error name="tickets.*.name" />
                        <x-organizer.field-error name="tickets.*.price" />
                        <x-organizer.field-error name="tickets.*.quota" />
                        <x-organizer.field-error name="tickets.*.max_per_user" />
                    </div>
                </x-organizer.form-section>
                </fieldset>
            </div>

            {{-- Step 3: Merchandise --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" x-cloak>
                <fieldset @disabled($isFullyLocked)>
                <x-organizer.form-section
                    icon="shopping-bag"
                    title="Suvenir (Opsional)"
                    description="Tambahkan suvenir khusus untuk acara ini. Peserta dapat membelinya saat pembelian tiket.">
                    <div class="mb-5 flex justify-end">
                        <button type="button" @click="addMerchandise()" class="inline-flex items-center gap-2 rounded-2xl bg-violet-600/10 px-4 py-2 text-xs font-extrabold uppercase tracking-widest text-violet-600 transition-all hover:bg-violet-600 hover:text-white dark:text-violet-400 dark:hover:text-white">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Tambah Suvenir
                        </button>
                    </div>

                    <div x-show="merchandise.length === 0" class="text-center py-12 px-4 rounded-[1.5rem] border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                        <div class="w-16 h-16 bg-slate-200 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <x-heroicon-o-shopping-bag class="w-8 h-8" />
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Belum ada Suvenir</h4>
                        <p class="text-sm text-slate-500 mt-1 mb-6 max-w-sm mx-auto">Anda tidak wajib menambahkan suvenir. Lewati langkah ini jika tidak diperlukan.</p>
                    </div>

                    <div class="space-y-4" x-show="merchandise.length > 0">
                        <template x-for="(item, index) in merchandise" :key="item.id">
                            <div class="rounded-[1.5rem] border border-slate-200/80 bg-white/70 p-4 shadow-sm transition-all hover:border-violet-500/30 dark:border-slate-800 dark:bg-white/5">
                                <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:bg-slate-900 dark:text-slate-400" x-text="`Item ${index + 1}`"></span>
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" :name="`merchandise[${index}][is_available]`" value="0">
                                            <input type="checkbox" :id="`merch-avail-${index}`" :name="`merchandise[${index}][is_available]`" value="1" x-model="item.is_available" class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-600">
                                            <label :for="`merch-avail-${index}`" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">Tersedia</label>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeMerchandise(index)" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold text-rose-500 transition-all hover:bg-rose-500/10">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                        Hapus
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
                                    <div class="md:col-span-3">
                                        <label class="{{ $labelClass }}">Gambar</label>
                                        <div class="relative group rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:hover:bg-slate-900 overflow-hidden aspect-square flex flex-col items-center justify-center cursor-pointer">
                                            <input type="file" :name="`merchandise[${index}][image]`" accept="image/jpeg,image/png,image/webp" @change="handleMerchImageChange($event, index)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            
                                            <template x-if="item.image_url">
                                                <img :src="item.image_url" class="absolute inset-0 w-full h-full object-cover">
                                            </template>
                                            
                                            <div class="relative z-0 flex flex-col items-center p-2 text-center" :class="item.image_url ? 'opacity-0 group-hover:opacity-100 bg-slate-900/60 backdrop-blur-sm w-full h-full justify-center transition-all' : ''">
                                                <x-heroicon-o-photo class="w-5 h-5 text-slate-400 mb-1" x-bind:class="item.image_url ? 'text-white' : ''" />
                                                <p class="text-[10px] font-medium" :class="item.image_url ? 'text-white' : 'text-slate-500'">Upload</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="md:col-span-9 space-y-4">
                                        <input type="hidden" :name="`merchandise[${index}][id]`" x-model="item.id">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="{{ $labelClass }}">Nama Suvenir</label>
                                                <input type="text" :name="`merchandise[${index}][name]`" x-model="item.name" required class="{{ $inputClass }}" placeholder="Misal: T-Shirt Official">
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">Harga Dasar (Rp)</label>
                                                <input type="number" min="0" :name="`merchandise[${index}][base_price]`" x-model="item.base_price" required class="{{ $inputClass }}" placeholder="150000">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }}">Deskripsi</label>
                                            <textarea :name="`merchandise[${index}][description]`" x-model="item.description" rows="2" class="{{ $inputClass }}" placeholder="Detail bahan, ukuran, dll..."></textarea>
                                        </div>
                                        
                                        <!-- Variants Section -->
                                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                            <div class="flex items-center justify-between mb-3">
                                                <label class="{{ $labelClass }} !mb-0">Varian (Opsional)</label>
                                                <button type="button" @click="addVariant(index)" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                                    <x-heroicon-o-plus class="w-3 h-3" />
                                                    Tambah Varian
                                                </button>
                                            </div>
                                            
                                            <div class="space-y-3" x-show="item.variants.length > 0">
                                                <template x-for="(variant, vIndex) in item.variants" :key="variant.id">
                                                    <div class="flex flex-wrap sm:flex-nowrap items-start gap-3 p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800 relative">
                                                        <input type="hidden" :name="`merchandise[${index}][variants][${vIndex}][id]`" x-model="variant.id">
                                                        
                                                        <div class="w-full sm:w-1/4">
                                                            <label class="{{ $labelClass }}">Grup</label>
                                                            <input type="text" :name="`merchandise[${index}][variants][${vIndex}][group]`" x-model="variant.group" required class="{{ $inputClass }} !py-2 !px-3" placeholder="Grup (mis: Ukuran)">
                                                        </div>
                                                        <div class="w-full sm:w-1/4">
                                                            <label class="{{ $labelClass }}">Nilai</label>
                                                            <input type="text" :name="`merchandise[${index}][variants][${vIndex}][value]`" x-model="variant.value" required class="{{ $inputClass }} !py-2 !px-3" placeholder="Nilai (mis: XL)">
                                                        </div>
                                                        <div class="w-full sm:w-1/4">
                                                            <div class="relative">
                                                                <label class="{{ $labelClass }}">Harga (+/-)</label>
                                                                <input type="number" :name="`merchandise[${index}][variants][${vIndex}][price_adjustment]`" x-model.number="variant.price_adjustment" required class="{{ $inputClass }} !py-2 !px-3" placeholder="+ / - Harga">
                                                                <p x-show="item.base_price + variant.price_adjustment < 0" class="absolute -bottom-5 left-0 text-[10px] font-bold text-rose-500 whitespace-nowrap">Harga akhir < 0!</p>
                                                            </div>
                                                        </div>
                                                        <div class="w-full sm:w-1/4">
                                                            <label class="{{ $labelClass }}">Stok</label>
                                                            <div class="flex items-center gap-2">
                                                                <input type="number" min="0" :name="`merchandise[${index}][variants][${vIndex}][stock]`" x-model="variant.stock" required class="{{ $inputClass }} !py-2 !px-3" placeholder="Stok">
                                                                <button type="button" @click="removeVariant(index, vIndex)" class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors shrink-0">
                                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </template>
                        <x-organizer.field-error name="merchandise" />
                    </div>
                </x-organizer.form-section>
            </div>
        </div>

        <aside class="space-y-6 lg:col-span-4">
            <div class="glass-panel sticky top-24 rounded-[2rem] border border-white/60 p-6 shadow-sm dark:border-white/10">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-500">
                        <x-heroicon-o-paper-airplane class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Publikasi</p>
                        <h3 class="font-extrabold tracking-tight text-slate-950 dark:text-white">Status Acara</h3>
                    </div>
                </div>

                <label for="event-status" class="{{ $labelClass }}">Tindakan</label>
                <fieldset @disabled($isFullyLocked)>
                <select id="event-status" name="status" class="{{ $inputClass }}">
                    @if($isPublished)
                        <option value="published" selected>Dipublikasikan</option>
                    @else
                        <option value="draft" {{ old('status', $event?->status?->value ?? 'draft') === 'draft' ? 'selected' : '' }}>Simpan sebagai draf</option>
                        <option value="awaiting_approval" {{ old('status', $event?->status?->value) === 'awaiting_approval' ? 'selected' : '' }}>Ajukan untuk Ditinjau</option>
                    @endif
                </select>
                </fieldset>
                <x-organizer.field-error name="status" />

                <div class="mt-6 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm leading-6 text-amber-700 dark:text-amber-300">
                    <div class="mb-2 flex items-center gap-2 font-extrabold">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                        Periksa sebelum menyimpan
                    </div>
                    Pastikan informasi acara, jadwal, lokasi, tiket, dan suvenir sudah akurat sebelum dipublikasikan.
                </div>

                <div class="mt-6 grid gap-3">
                    {{-- Navigation Buttons --}}
                    <button type="button" x-show="step < 3" @click="step++" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-violet-600/20 transition-all hover:-translate-y-0.5 hover:shadow-violet-600/30">
                        Lanjutkan
                        <x-heroicon-o-arrow-right class="h-5 w-5" />
                    </button>
                    
                    @if(!$isFullyLocked)
                    <button type="submit" x-show="step === 3" x-cloak class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-emerald-500/20 transition-all hover:-translate-y-0.5 hover:shadow-emerald-500/30">
                        <x-heroicon-o-check-circle class="h-5 w-5" />
                        {{ $submitLabel }}
                    </button>
                    @endif

                    <button type="button" x-show="step > 1" @click="step--" x-cloak class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-extrabold text-slate-600 transition-all hover:bg-slate-100 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-white/5">
                        <x-heroicon-o-arrow-left class="h-5 w-5" />
                        Kembali
                    </button>

                    <a href="{{ route('organizer.events.index') }}" x-show="step === 1" data-link class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-extrabold text-slate-600 transition-all hover:bg-slate-100 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-white/5">
                        Batal
                    </a>
                </div>

                @if($event && in_array($event->status->value, ['published', 'completed', 'cancelled']))
                    <div class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-800">
                        <div class="mb-4 flex items-center gap-2 font-extrabold text-rose-500">
                            <x-heroicon-o-exclamation-circle class="h-5 w-5" />
                            Zona Berbahaya
                        </div>
                        
                        @if($event->status->value === 'published')
                            @if(!$isHardCutoffPassed)
                                @if(!$hasSales)
                                    <button type="button" x-data @click="$dispatch('open-panel', 'cancel-event-modal')" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-500/10 px-5 py-3 text-sm font-extrabold text-rose-600 transition-all hover:bg-rose-500 hover:text-white dark:text-rose-400">
                                        <x-heroicon-o-x-circle class="h-5 w-5" />
                                        Batalkan Acara
                                    </button>
                                @else
                                    <button type="button" x-data @click="$dispatch('open-panel', 'request-cancellation-modal')" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500/10 px-5 py-3 text-sm font-extrabold text-amber-600 transition-all hover:bg-amber-500 hover:text-white dark:text-amber-400">
                                        <x-heroicon-o-document-minus class="h-5 w-5" />
                                        Ajukan Pembatalan
                                    </button>
                                @endif
                            @else
                                <div class="text-xs text-rose-500/80 mb-2">Acara sudah dimulai. Pembatalan tidak lagi tersedia.</div>
                            @endif
                        @endif

                        @can('delete', $event)
                            <button type="button" x-data @click="$dispatch('open-panel', 'delete-event-modal')" class="w-full mt-3 inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-500/10 px-5 py-3 text-sm font-extrabold text-rose-600 transition-all hover:bg-rose-500 hover:text-white dark:text-rose-400">
                                <x-heroicon-o-trash class="h-5 w-5" />
                                Hapus dari Dashboard
                            </button>
                        @endcan
                    </div>
                @endif
            </div>
        </aside>
    </div>
</form>

@if($event)
    @push('modals')
        {{-- Tier 1: Cancel Event Modal --}}
        @if($event->status->value === 'published' && !$isHardCutoffPassed && !$hasSales)
        <template x-teleport="body">
        <div x-data="{ show: false }" @open-panel.window="if ($event.detail === 'cancel-event-modal') show = true" @close-panel.window="if ($event.detail === 'cancel-event-modal') show = false" x-show="show" class="relative z-[100]" x-cloak>
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="show" x-transition.opacity.scale.95 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-lg">
                        <form action="{{ route('organizer.events.cancel', $event) }}" method="POST">
                            @csrf
                            <div class="p-8 flex flex-col items-center text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-500/10 text-rose-500 mb-6">
                                    <x-heroicon-o-x-circle class="h-8 w-8" />
                                </div>
                                <h3 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-2">Batalkan Acara?</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Anda yakin ingin membatalkan acara ini? Karena belum ada tiket yang terjual, acara akan langsung dibatalkan dan ditarik dari peredaran publik. Tindakan ini tidak dapat diurungkan.
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950/50 px-8 py-5 flex flex-col-reverse sm:flex-row sm:justify-center gap-3">
                                <button type="button" @click="$dispatch('close-panel', 'cancel-event-modal')" class="inline-flex w-full sm:w-auto justify-center rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Kembali
                                </button>
                                <button type="submit" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-rose-500/30 transition-all hover:bg-rose-500">
                                    Ya, Batalkan Acara
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </template>
        @endif

        {{-- Tier 2: Request Cancellation Modal --}}
        @if($event->status->value === 'published' && !$isHardCutoffPassed && $hasSales)
        <template x-teleport="body">
        <div x-data="{ show: false, reason: '' }" @open-panel.window="if ($event.detail === 'request-cancellation-modal') show = true" @close-panel.window="if ($event.detail === 'request-cancellation-modal') show = false" x-show="show" class="relative z-[100]" x-cloak>
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="show" x-transition.opacity.scale.95 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-xl">
                        <form action="{{ route('organizer.events.request-cancellation', $event) }}" method="POST">
                            @csrf
                            <div class="p-8 flex flex-col items-center text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-6">
                                    <x-heroicon-o-document-minus class="h-8 w-8" />
                                </div>
                                <h3 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-2">Ajukan Pembatalan</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                                    @if($soldCount > 0)
                                        Acara ini sudah memiliki <strong class="text-amber-500">{{ $soldCount }} tiket terjual</strong>. Pengajuan pembatalan membutuhkan persetujuan Admin karena melibatkan pengembalian dana ke pembeli.
                                    @else
                                        Acara ini sudah memiliki transaksi suvenir. Pengajuan pembatalan membutuhkan persetujuan Admin karena melibatkan pengembalian dana ke pembeli.
                                    @endif
                                </p>
                                
                                <div class="w-full text-left">
                                    <label for="cancellation_reason" class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Alasan Pembatalan</label>
                                    <textarea id="cancellation_reason" name="reason" x-model="reason" rows="4" minlength="20" required class="w-full rounded-2xl border-slate-200 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition-all placeholder:text-slate-400 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-800 dark:bg-slate-950/60 dark:text-white" placeholder="Jelaskan alasan detail mengapa acara ini harus dibatalkan (minimal 20 karakter)..."></textarea>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950/50 px-8 py-5 flex flex-col-reverse sm:flex-row sm:justify-center gap-3">
                                <button type="button" @click="$dispatch('close-panel', 'request-cancellation-modal')" class="inline-flex w-full sm:w-auto justify-center rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Kembali
                                </button>
                                <button type="submit" :disabled="reason.length < 20" :class="reason.length < 20 ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5'" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-amber-500/30 transition-all hover:bg-amber-400">
                                    Kirim Pengajuan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </template>
        @endif

        {{-- Delete Event Modal --}}
        @can('delete', $event)
        <template x-teleport="body">
        <div x-data="{ show: false }" @open-panel.window="if ($event.detail === 'delete-event-modal') show = true" @close-panel.window="if ($event.detail === 'delete-event-modal') show = false" x-show="show" class="relative z-[100]" x-cloak>
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="show" x-transition.opacity.scale.95 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-lg">
                        <form action="{{ route('organizer.events.destroy', $event) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="p-8 flex flex-col items-center text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-500/10 text-rose-500 mb-6">
                                    <x-heroicon-o-trash class="h-8 w-8" />
                                </div>
                                <h3 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-2">Hapus dari Dashboard?</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Acara ini akan dihapus dari tampilan dashboard Anda agar lebih rapi. Data analitik dan riwayat transaksi akan tetap tersimpan dalam sistem.
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950/50 px-8 py-5 flex flex-col-reverse sm:flex-row sm:justify-center gap-3">
                                <button type="button" @click="$dispatch('close-panel', 'delete-event-modal')" class="inline-flex w-full sm:w-auto justify-center rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-rose-500/30 transition-all hover:bg-rose-500">
                                    Ya, Hapus
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </template>
        @endcan
    @endpush
@endif
