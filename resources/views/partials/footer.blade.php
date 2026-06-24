<footer class="mt-6 rounded-[18px] border border-slate-200 bg-white/80 p-6 shadow-[0_10px_30px_rgba(15,23,42,0.06)] backdrop-blur-md sm:p-8" aria-label="JoinFest footer">
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="grid gap-3 content-start">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-lg font-bold tracking-tight text-slate-900">
                <img src="{{ asset('favicon.svg') }}" alt="JoinFest logo" class="h-9 w-9 object-contain">
                <span>JoinFest</span>
            </a>
            <p class="text-sm leading-7 text-slate-500">
                Platform tiket, festival, dan suvenir resmi untuk pengalaman acara yang cepat, aman, dan mudah diakses.
            </p>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Menu</h3>
            <div class="grid gap-2 text-sm">
                <a href="{{ url('/') }}" class="text-slate-500 transition hover:text-violet-600">Home</a>
                <a href="{{ route('checkout.index') }}" class="text-slate-500 transition hover:text-violet-600">Event</a>
                <a href="{{ route('pesanan.index') }}" class="text-slate-500 transition hover:text-violet-600">Pesanan</a>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Bantuan</h3>
            <div class="grid gap-2 text-sm">
                <a href="{{ route('login') }}" class="text-slate-500 transition hover:text-violet-600">Login</a>
                <a href="{{ route('register') }}" class="text-slate-500 transition hover:text-violet-600">Register</a>
                <a href="{{ route('checkout.index') }}" class="text-slate-500 transition hover:text-violet-600">Checkout</a>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Newsletter</h3>
            <p class="mb-3 text-sm leading-7 text-slate-500">Dapatkan info event dan promo terbaru JoinFest langsung ke inbox kamu.</p>
            <form class="grid gap-2" action="#" method="get">
                <input type="email" name="email" placeholder="Alamat email kamu" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/15">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-violet-500 px-4 text-sm font-semibold text-white transition hover:brightness-95">Langganan</button>
            </form>
        </div>
    </div>

    <div class="mt-6 flex flex-col gap-2 border-t border-slate-200 pt-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <span>© {{ date('Y') }} JoinFest. All rights reserved.</span>
        <span>Built for concerts, festivals, and creator events.</span>
    </div>
</footer>
