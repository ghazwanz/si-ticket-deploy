{{-- Panel Ubah Kategori --}}
<x-admin.panel 
    name="edit-category-{{ $category->id }}" 
    title="Ubah Klasifikasi" 
    description="Perbarui nama dan properti kategori {{ $category->name }}."
    width="2xl"
>
    <form id="edit-category-form-{{ $category->id }}" method="POST" action="{{ route('api.admin.event-categories.update', $category) }}" enctype="multipart/form-data" class="space-y-8" data-api-form>
        @csrf
        @method('PUT')

        {{-- Informasi dasar --}}
        <section class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Identitas Umum</h3>
            </div>
            
            <div class="grid gap-6">
                <div class="space-y-2">
                    <x-input-label for="edit_name_{{ $category->id }}" :value="__('Nama Kategori')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="edit_name_{{ $category->id }}" name="name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('name', $category->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    <p class="text-[10px] text-slate-500 font-medium italic ml-1">Slug URL akan dibuat ulang secara otomatis berdasarkan nama ini.</p>
                </div>

                {{-- Image Uploader with Remove Option --}}
                <div x-data="{ 
                    imageUrl: '{{ $category->image ? $category->image_url : '' }}',
                    removeImage: 0,
                    triggerFileInput() { this.$refs.fileInput.click() },
                    handleFileChange(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.imageUrl = URL.createObjectURL(file);
                            this.removeImage = 0;
                        }
                    },
                    handleDrop(event) {
                        const file = event.dataTransfer.files[0];
                        if (file) {
                            this.$refs.fileInput.files = event.dataTransfer.files;
                            this.imageUrl = URL.createObjectURL(file);
                            this.removeImage = 0;
                        }
                    },
                    clearImage() {
                        this.imageUrl = '';
                        this.$refs.fileInput.value = '';
                        this.removeImage = 1;
                    }
                }" class="space-y-2">
                    <x-input-label for="edit_image_{{ $category->id }}" :value="__('Gambar Kategori')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <input type="hidden" name="remove_image" :value="removeImage">
                    
                    <div 
                        x-on:dragover.prevent=""
                        x-on:drop.prevent="handleDrop($event)"
                        x-on:click="triggerFileInput()"
                        class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-violet-500/50 dark:hover:border-violet-500/50 rounded-2xl p-6 text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-900/50 flex flex-col items-center justify-center min-h-[140px]"
                    >
                        <input 
                            type="file" 
                            id="edit_image_{{ $category->id }}" 
                            name="image" 
                            x-ref="fileInput" 
                            x-on:change="handleFileChange($event)" 
                            class="hidden" 
                            accept="image/*"
                        >
                        
                        <template x-if="!imageUrl">
                            <div class="space-y-2">
                                <x-heroicon-o-arrow-up-tray class="w-8 h-8 text-slate-400 mx-auto" />
                                <p class="text-xs text-slate-500 dark:text-slate-400"><span class="font-bold text-violet-600 dark:text-violet-400">Klik untuk unggah</span> atau seret gambar ke sini</p>
                                <p class="text-[10px] text-slate-400">PNG, JPG, JPEG, WEBP (Maks 1MB)</p>
                            </div>
                        </template>
                        
                        <template x-if="imageUrl">
                            <div class="relative w-full aspect-[2/1] rounded-xl overflow-hidden group">
                                <img :src="imageUrl" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button type="button" x-on:click.stop="clearImage()" class="p-2 bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition-colors">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                {{-- Color Palette Selector --}}
                <div x-data="{ 
                    selectedColor: '{{ old('color', $category->color ?? 'violet') }}',
                    colors: [
                        { name: 'violet', bg: 'bg-violet-500' },
                        { name: 'sky', bg: 'bg-sky-500' },
                        { name: 'emerald', bg: 'bg-emerald-500' },
                        { name: 'rose', bg: 'bg-rose-500' },
                        { name: 'amber', bg: 'bg-amber-500' },
                        { name: 'fuchsia', bg: 'bg-fuchsia-500' },
                        { name: 'cyan', bg: 'bg-cyan-500' },
                        { name: 'indigo', bg: 'bg-indigo-500' }
                    ]
                }" class="space-y-2">
                    <x-input-label :value="__('Aksen Warna')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <input type="hidden" name="color" :value="selectedColor">
                    <div class="flex flex-wrap gap-3">
                        <template x-for="color in colors" :key="color.name">
                            <button 
                                type="button" 
                                x-on:click="selectedColor = color.name"
                                class="w-8 h-8 rounded-full transition-all duration-200 active:scale-90 relative flex items-center justify-center cursor-pointer border border-black/10 dark:border-white/10 shadow-sm"
                                :class="color.bg"
                            >
                                <div 
                                    x-show="selectedColor === color.name"
                                    class="absolute inset-0 rounded-full border-2 border-white dark:border-slate-950 scale-75"
                                ></div>
                            </button>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('color')" class="mt-2" />
                </div>
            </div>
        </section>

        <hr class="border-slate-100 dark:border-slate-800">

        {{-- Metadata / statistik --}}
        <section class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-blue-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Registri Penggunaan</h3>
            </div>

            <div class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Acara Aktif</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($category->events_count ?? $category->events()->count()) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center text-slate-400">
                    <x-heroicon-o-calendar class="w-6 h-6" />
                </div>
            </div>
        </section>
    </form>

    <x-slot name="footer">
        <div class="flex items-center justify-end gap-3">
            <button x-on:click="close()" class="px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                Batalkan Perubahan
            </button>
            <x-primary-button form="edit-category-form-{{ $category->id }}" class="rounded-2xl bg-violet-600 px-8 py-3 text-xs font-bold uppercase tracking-widest shadow-lg shadow-violet-600/20">
                {{ __('Perbarui Klasifikasi') }}
            </x-primary-button>
        </div>
    </x-slot>
</x-admin.panel>

{{-- Modal Hapus Kategori --}}
<x-modal name="delete-category-{{ $category->id }}" maxWidth="md">
    <div class="p-8 text-center">
        @php
            $hasEvents = $category->events()->exists();
        @endphp

        <div class="w-16 h-16 rounded-3xl bg-rose-500/10 text-rose-500 flex items-center justify-center mx-auto mb-6">
            <x-heroicon-o-exclamation-triangle class="w-8 h-8" />
        </div>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Arsipkan Kategori?</h2>
        <p class="text-sm text-slate-500 mt-2 px-4 leading-relaxed">
            Konfirmasikan pengarsipan <b>{{ $category->name }}</b>. Kategori ini tetap mempertahankan registri aktif dan dipindahkan ke filter audit terhapus.
        </p>

        @if($hasEvents)
            <div class="mt-6 p-4 rounded-2xl bg-rose-500/5 border border-rose-500/20 text-rose-600 text-[10px] font-bold flex flex-col gap-2 text-left uppercase tracking-widest">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-lock-closed class="w-4 h-4" />
                    Arsip Diblokir: Terdapat Dependensi
                </div>
                <p class="font-medium normal-case tracking-normal text-xs text-rose-500">Kategori ini masih dipetakan ke acara yang ada. Alihkan taksonomi acara sebelum mengarsipkannya.</p>
            </div>
        @endif

        <div class="mt-8 flex flex-col gap-3">
            <form method="POST" action="{{ route('api.admin.event-categories.destroy', $category) }}" data-api-form>
                @csrf
                @method('DELETE')
                <x-danger-button 
                    :disabled="$hasEvents"
                    class="w-full justify-center py-4 rounded-2xl text-xs font-bold uppercase tracking-widest bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/20 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-500 disabled:shadow-none"
                >
                    {{ __('Konfirmasi Arsip') }}
                </x-danger-button>
            </form>
            <x-secondary-button x-on:click="$dispatch('close')" class="justify-center py-4 rounded-2xl dark:text-black border-slate-200 dark:border-slate-800 text-xs font-bold uppercase tracking-widest">
                {{ __('Batalkan Permintaan') }}
            </x-secondary-button>
        </div>
    </div>
</x-modal>
