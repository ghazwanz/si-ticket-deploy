{{-- Create Category Panel --}}
<x-admin.panel 
    name="create-category" 
    title="Klasifikasi Baru" 
    description="Tambahkan kategori acara baru untuk memperluas taksonomi pencarian platform."
    width="2xl"
>
    <form id="create-category-form" method="POST" action="{{ route('api.admin.event-categories.store') }}" enctype="multipart/form-data" class="space-y-8" data-api-form>
        @csrf

        {{-- Identity Section --}}
        <section class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Identitas Kategori</h3>
            </div>
            
            <div class="grid gap-6">
                <div class="space-y-2">
                    <x-input-label for="new_name" :value="__('Nama Tampilan')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="new_name" name="name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('name')" placeholder="mis. Musik dan Konser" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    <p class="text-[10px] text-slate-500 font-medium italic ml-1">Sistem akan membuat slug URL unik untuk kategori ini secara otomatis.</p>
                </div>

                {{-- Image Uploader --}}
                <div x-data="{ 
                    imageUrl: null,
                    triggerFileInput() { this.$refs.fileInput.click() },
                    handleFileChange(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.imageUrl = URL.createObjectURL(file);
                        }
                    },
                    handleDrop(event) {
                        const file = event.dataTransfer.files[0];
                        if (file) {
                            this.$refs.fileInput.files = event.dataTransfer.files;
                            this.imageUrl = URL.createObjectURL(file);
                        }
                    }
                }" class="space-y-2">
                    <x-input-label for="new_image" :value="__('Gambar Kategori')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    
                    <div 
                        x-on:dragover.prevent=""
                        x-on:drop.prevent="handleDrop($event)"
                        x-on:click="triggerFileInput()"
                        class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-violet-500/50 dark:hover:border-violet-500/50 rounded-2xl p-6 text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-900/50 flex flex-col items-center justify-center min-h-[140px]"
                    >
                        <input 
                            type="file" 
                            id="new_image" 
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
                                    <button type="button" x-on:click.stop="imageUrl = null; $refs.fileInput.value = ''" class="p-2 bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition-colors">
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
                    selectedColor: 'violet',
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

        {{-- Guidance Section --}}
        <section class="p-5 bg-violet-500/5 rounded-2xl border border-violet-500/10">
            <div class="flex items-center gap-3 mb-2">
                <x-heroicon-o-information-circle class="w-5 h-5 text-violet-500" />
                <p class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-widest">Catatan Platform</p>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Kategori terlihat secara publik dan digunakan untuk pencarian acara, penyaringan, serta optimasi SEO. Gunakan nama yang deskriptif dan berbeda.
            </p>
        </section>
    </form>

    <x-slot name="footer">
        <div class="flex items-center justify-end gap-3">
            <button x-on:click="close()" class="px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                Batal
            </button>
            <x-primary-button form="create-category-form" class="rounded-2xl bg-violet-600 px-8 py-3 text-xs font-bold uppercase tracking-widest shadow-lg shadow-violet-600/20">
                {{ __('Daftarkan Kategori') }}
            </x-primary-button>
        </div>
    </x-slot>
</x-admin.panel>
