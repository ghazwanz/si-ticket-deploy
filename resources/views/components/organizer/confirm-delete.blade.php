@props([
    'id',
    'title' => 'Hapus Acara',
    'action',
    'name' => null,
])

<div x-data="{ open: false }" class="inline-flex">
    <button type="button" @click="open = true" class="inline-flex items-center justify-end gap-1 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 font-bold">
        <x-heroicon-o-trash class="w-4 h-4" />
        Hapus
    </button>

    <template x-teleport="body">
        <div x-cloak x-show="open" class="relative z-[100]">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div @click.outside="open = false" x-show="open" x-transition.opacity.scale.95 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-md">
                        <div class="p-8 flex flex-col items-center text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-500/10 text-rose-500 mb-6">
                                <x-heroicon-o-trash class="h-8 w-8" />
                            </div>
                            <h3 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-2">{{ $title }}</h3>
                            <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                                Tindakan ini akan menghapus acara{{ $name ? ' “'.$name.'”' : '' }} dari daftar aktif. Data tetap tersimpan sebagai arsip sesuai kebijakan sistem.
                            </p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-950/50 px-8 py-5 flex flex-col-reverse sm:flex-row sm:justify-center gap-3">
                            <button type="button" @click="open = false" class="inline-flex w-full sm:w-auto justify-center rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800">
                                Batal
                            </button>
                            <form method="POST" action="{{ $action }}" class="w-full sm:w-auto m-0 p-0 flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-rose-500/30 transition-all hover:bg-rose-500">
                                    Ya, Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
