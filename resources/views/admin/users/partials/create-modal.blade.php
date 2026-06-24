<x-admin.panel 
    name="create-user" 
    title="Buat Akun Baru" 
    description="Buat identity baru pada JoinFest."
    width="3xl"
>

    <form id="create-user-form" x-data="{ role: 'user' }" method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- Identity Section --}}
        <section id="general" class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Identitas Pribadi</h3>
            </div>
            
            <div class="grid gap-6">
                <div class="space-y-2">
                    <x-input-label for="create_name" :value="__('Nama Lengkap')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="create_name" name="name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('name')" required placeholder="mis. Budi Santoso" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="create_email" :value="__('Alamat Pos-el')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="create_email" name="email" type="email" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('email')" required placeholder="john@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="create_password" :value="__('Kata Sandi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="create_password" name="password" type="password" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" required placeholder="Minimal 8 karakter" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            </div>
        </section>

        <hr class="border-slate-100 dark:border-slate-800">

        {{-- Access Section --}}
        <section id="access" class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Izin dan Status</h3>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <x-input-label for="create_role" :value="__('Peran')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <select id="create_role" name="role" x-model="role" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-sm focus:ring-violet-500/20 py-3">
                        <option value="user">Pengguna / Pembeli</option>
                        <option value="organizer">Penyelenggara Acara</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <x-input-label for="create_active" :value="__('Status Akses')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <select id="create_active" name="is_active" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-sm focus:ring-violet-500/20 py-3">
                        <option value="1">Akun Aktif</option>
                        <option value="0">Ditangguhkan / Restricted</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- Penyelenggara Profile Section --}}
        <section id="organizer-info" x-show="role === 'organizer'" x-transition class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-blue-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Penyelenggara Profil</h3>
            </div>

            <div class="grid gap-6">
                <div class="space-y-2">
                    <x-input-label for="organization_name" :value="__('Nama Organisasi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="organization_name" name="organization_name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('organization_name')" placeholder="mis. Produksi Festival Nusantara" />
                    <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="phone" :value="__('Nomor Kontak')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="phone" name="phone" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('phone')" placeholder="+62..." />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <x-input-label for="bank_name" :value="__('Nama Bank')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="bank_name" name="bank_name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('bank_name')" placeholder="e.g. BCA" />
                    </div>
                    <div class="space-y-2">
                        <x-input-label for="bank_account_number" :value="__('Nomor Rekening')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('bank_account_number')" placeholder="00000000" />
                    </div>
                </div>

                <div class="space-y-2">
                    <x-input-label for="bank_account_name" :value="__('Nama Pemilik Rekening')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="bank_account_name" name="bank_account_name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('bank_account_name')" placeholder="Nama pada buku tabungan" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="organization_address" :value="__('Alamat Organisasi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <textarea id="organization_address" name="organization_address" rows="3" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm p-3 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-shadow" placeholder="e.g. Jl. Jend. Sudirman No. 123">{{ old('organization_address') }}</textarea>
                    <x-input-error :messages="$errors->get('organization_address')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="official_contact" :value="__('Alamat Pos-el Resmi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="official_contact" name="official_contact" type="email" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('official_contact')" placeholder="official@org.com" />
                    <x-input-error :messages="$errors->get('official_contact')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="legality_document" :value="__('Dokumen Legalitas')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <input id="legality_document" name="legality_document" type="file" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 dark:file:bg-violet-950 dark:file:text-violet-300 hover:file:bg-violet-100 dark:hover:file:bg-violet-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-2.5" />
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Format: PDF, JPG, JPEG, PNG (Maks. 5MB)</p>
                    <x-input-error :messages="$errors->get('legality_document')" class="mt-2" />
                </div>
            </div>
        </section>
    </form>

    <x-slot name="footer">
        <div class="flex items-center justify-end gap-3">
            <button x-on:click="close()" class="px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                Batalkan Perubahan
            </button>
            <x-primary-button form="create-user-form" class="rounded-2xl bg-violet-600 px-8 py-3 text-xs font-bold uppercase tracking-widest shadow-lg shadow-violet-600/20">
                {{ __('Buat Akun') }}
            </x-primary-button>
        </div>
    </x-slot>
</x-admin.panel>
