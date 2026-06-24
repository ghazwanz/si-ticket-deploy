{{-- Edit User Panel --}}
<x-admin.panel 
    name="edit-user-{{ $user->id }}" 
    title="Ubah Akun" 
    description="Perbarui detail profil dan hak akses untuk {{ $user->name }}."
    width="3xl"
>

    <form id="edit-user-form-{{ $user->id }}" method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Identity Section --}}
        <section id="edit-identity-{{ $user->id }}" class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Identitas Pribadi</h3>
            </div>
            
            <div class="grid gap-6">
                <div class="space-y-2">
                    <x-input-label for="edit_name_{{ $user->id }}" :value="__('Nama Lengkap')" class="text-sm font-bold text-slate-900 dark:text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="edit_name_{{ $user->id }}" name="name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('name', $user->name)" required />
                </div>

                <div class="space-y-2">
                    <x-input-label for="edit_email_{{ $user->id }}" :value="__('Alamat Pos-el')" class="text-sm font-bold text-slate-900 dark:text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="edit_email_{{ $user->id }}" name="email" type="email" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('email', $user->email)" required />
                </div>

                <div class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-3">
                        <x-heroicon-o-lock-closed class="w-6 h-6 text-amber-500" />
                        <p class="text-xs text-slate-900 dark:text-slate-400 uppercase tracking-widest">Pembaruan Keamanan</p>
                    </div>
                    <x-text-input name="password" type="password" placeholder="Kosongkan jika tidak ingin mengubah kata sandi" class="block w-full !bg-transparent rounded-xl border-slate-200 dark:border-slate-800 py-2 text-sm" />
                </div>
            </div>
        </section>

        <hr class="border-slate-100 dark:border-slate-800">

        {{-- Access Section --}}
        <section id="edit-access-{{ $user->id }}" class="space-y-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Izin dan Status</h3>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <x-input-label for="edit_role_{{ $user->id }}" :value="__('Peran')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <select id="edit_role_{{ $user->id }}" name="role" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-sm focus:ring-violet-500/20 py-3">
                        <option value="user" {{ $user->role->value === 'user' ? 'selected' : '' }}>Pengguna / Pembeli</option>
                        <option value="organizer" {{ $user->role->value === 'organizer' ? 'selected' : '' }}>Penyelenggara Acara</option>
                        <option value="admin" {{ $user->role->value === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>
                <div class="space-y-2" x-data="{ active: '{{ $user->is_active ? 1 : 0 }}' }">
                    <x-input-label for="edit_active_{{ $user->id }}" :value="__('Status Akun')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                    <select id="edit_active_{{ $user->id }}" name="is_active" x-model="active"
                            class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-sm focus:ring-violet-500/20 py-3">
                        <option value="1">Aktif</option>
                        <option value="0">Ditangguhkan</option>
                    </select>
                    
                    @if($user->hasActivePaidOrders())
                        <template x-if="active == '0'">
                            <div class="mt-3 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3 animate-fade-in">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">Peringatan</p>
                                    <p class="text-[10px] text-amber-500 mt-1 leading-relaxed">Penyelenggara ini memiliki acara aktif dengan pesanan berbayar. Penangguhan dapat mengganggu akses tiket dan penyelesaian dana.</p>
                                </div>
                            </div>
                        </template>
                    @endif
                </div>
            </div>
        </section>

        @if($user->role->value === 'organizer')
            <hr class="border-slate-100 dark:border-slate-800">

            {{-- Penyelenggara Profile Section --}}
            <section id="edit-organizer-{{ $user->id }}" class="space-y-6">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-4 bg-blue-500 rounded-full"></div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Profil Penyelenggara</h3>
                </div>

                <div class="grid gap-6">
                    <div class="space-y-2">
                        <x-input-label for="edit_org_name_{{ $user->id }}" :value="__('Nama Organisasi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="edit_org_name_{{ $user->id }}" name="organization_name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('organization_name', $user->organizerProfile?->organization_name)" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="edit_phone_{{ $user->id }}" :value="__('Nomor Kontak')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="edit_phone_{{ $user->id }}" name="phone" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('phone', $user->organizerProfile?->phone)" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <x-input-label for="edit_bank_name_{{ $user->id }}" :value="__('Nama Bank')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                            <x-text-input id="edit_bank_name_{{ $user->id }}" name="bank_name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 text-slate-400 dark:border-slate-800 py-3" :value="old('bank_name', $user->organizerProfile?->bank_name)" />
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="edit_bank_acc_{{ $user->id }}" :value="__('Nomor Rekening')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                            <x-text-input id="edit_bank_acc_{{ $user->id }}" name="bank_account_number" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('bank_account_number', $user->organizerProfile?->bank_account_number)" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="edit_bank_acc_name_{{ $user->id }}" :value="__('Nama Pemilik Rekening')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="edit_bank_acc_name_{{ $user->id }}" name="bank_account_name" type="text" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('bank_account_name', $user->organizerProfile?->bank_account_name)" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="edit_org_addr_{{ $user->id }}" :value="__('Alamat Organisasi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <textarea id="edit_org_addr_{{ $user->id }}" name="organization_address" rows="3" class="block w-full glass-panel !bg-transparent rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm p-3 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-shadow">{{ old('organization_address', $user->organizerProfile?->organization_address) }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="edit_off_contact_{{ $user->id }}" :value="__('Alamat Pos-el Resmi')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="edit_off_contact_{{ $user->id }}" name="official_contact" type="email" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 py-3" :value="old('official_contact', $user->organizerProfile?->official_contact)" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="edit_leg_doc_{{ $user->id }}" :value="__('Dokumen Legalitas')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <input id="edit_leg_doc_{{ $user->id }}" name="legality_document" type="file" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 dark:file:bg-violet-950 dark:file:text-violet-300 hover:file:bg-violet-100 dark:hover:file:bg-violet-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-2.5" />
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Format: PDF, JPG, JPEG, PNG (Maks. 5MB). Biarkan kosong jika tidak diubah.</p>
                        
                        @if($user->organizerProfile?->legality_document)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">Dokumen Saat Ini:</span>
                                <a href="{{ Storage::url($user->organizerProfile->legality_document) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                    Lihat Dokumen
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </form>

    <x-slot name="footer">
        <div class="flex items-center justify-end gap-3">
            <button x-on:click="close()" class="px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                Batalkan Perubahan
            </button>
            <x-primary-button form="edit-user-form-{{ $user->id }}" class="rounded-2xl bg-violet-600 px-8 py-3 text-xs font-bold uppercase tracking-widest shadow-lg shadow-violet-600/20">
                {{ __('Commit Changes') }}
            </x-primary-button>
        </div>
    </x-slot>
</x-admin.panel>

{{-- Termination / Suspension Modal --}}
<x-modal name="delete-user-{{ $user->id }}" maxWidth="md">
    @php
        $hasHistory = $user->role->value === 'organizer' && $user->events()->whereHas('orders')->exists();
        $isBlocked = $user->role->value === 'organizer' && ($user->hasPublishedEvents() || $user->hasPendingPayouts());
        $showSuspendInstead = $user->is_active && ($hasHistory || $isBlocked);
    @endphp

    <div class="p-8 text-center">
        @if($showSuspendInstead)
            <div class="w-16 h-16 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-6">
                <x-heroicon-o-shield-exclamation class="w-8 h-8" />
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Batasi Akun?</h2>
            <p class="text-sm text-slate-700 dark:text-slate-500 mt-2 px-4">
                Penghapusan tidak dapat dilakukan karena terdapat {{ $isBlocked ? 'acara aktif' : 'riwayat audit' }}. 
                Penangguhan <b>{{ $user->name }}</b> akan membatasi akses mereka sambil menjaga integritas data.
            </p>
        @else
            <div class="w-16 h-16 rounded-3xl bg-rose-500/10 text-rose-500 flex items-center justify-center mx-auto mb-6">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8" />
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->is_active ? 'Arsipkan Akun?' : 'Pulihkan Akses?' }}</h2>
            <p class="text-sm text-slate-500 mt-2 px-4">
                @if($user->is_active)
                    Aksi ini akan mengarsipkan <b>{{ $user->name }}</b> dan menyembunyikan akun dari tampilan admin aktif sambil menjaga integritas catatan audit.
                @else
                    Aksi ini akan memulihkan seluruh akses <b>{{ $user->name }}</b> pada Joinfest.
                @endif
            </p>
        @endif

        @if($user->role->value === 'organizer' && $user->is_active)
            <div class="mt-4 px-4">
                @if($user->hasPublishedEvents())
                    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                        <x-heroicon-o-x-circle class="w-4 h-4" />
                        Diblokir: Acara Aktif yang Dipublikasikan
                    </div>
                @elseif($user->hasPendingPayouts())
                    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                        <x-heroicon-o-x-circle class="w-4 h-4" />
                        Diblokir: Penarikan Dana Tertunda
                    </div>
                @elseif($hasHistory)
                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 text-[10px] font-bold uppercase tracking-widest flex items-start gap-2 text-left">
                        <x-heroicon-o-information-circle class="w-4 h-4 mt-0.5 shrink-0" />
                        <span>Kebutuhan Audit: Riwayat transaksi ditemukan. Gunakan penangguhan, bukan penghapusan.</span>
                    </div>
                @elseif($user->events()->exists())
                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-4 h-4" />
                        Catatan: Data riwayat acara akan tetap terhubung untuk kebutuhan audit.
                    </div>
                @endif
            </div>
        @endif

        <div class="mt-8 flex flex-col gap-3">
            @if($showSuspendInstead || !$user->is_active)
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="w-full justify-center py-4 rounded-md text-xs font-bold uppercase tracking-widest transition-all active:scale-95 shadow-lg
                            {{ $user->is_active ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-500/20' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20' }}">
                        {{ $user->is_active ? __('Konfirmasi Penangguhan') : __('Konfirmasi Pemulihan') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                    @csrf
                    @method('DELETE')
                    <x-danger-button 
                        :disabled="$isBlocked"
                        class="w-full justify-center py-4 rounded-md text-xs font-bold uppercase tracking-widest bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/20 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-500 disabled:shadow-none"
                    >
                        {{ __('Konfirmasi Arsip') }}
                    </x-danger-button>
                </form>
            @endif

            <x-secondary-button x-on:click="$dispatch('close')" class="justify-center py-4 rounded-2xl dark:text-black border-slate-200 dark:border-slate-800 text-xs font-bold uppercase tracking-widest">
                {{ __('Batalkan') }}
            </x-secondary-button>
        </div>
    </div>
</x-modal>
