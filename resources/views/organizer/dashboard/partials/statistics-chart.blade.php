<div class="glass-panel rounded-[2rem] p-8 border border-white/60 dark:border-white/10"
     x-data="{
        revenue: @js($analytics['revenue']),
        volume: @js($analytics['volume']),
        tickets: @js($analytics['tickets']),
        labels: @js($analytics['labels']),
        chart: null,
        init() {
            this.$nextTick(() => this.renderChart());
        },
        renderChart() {
            if (typeof ApexCharts === 'undefined' || this.chart) {
                return;
            }

            this.chart = new ApexCharts(this.$refs.chart, {
                series: [
                    { name: 'Pendapatan', type: 'area', data: this.revenue },
                    { name: 'Pesanan', type: 'line', data: this.volume },
                    { name: 'Tiket Terjual', type: 'column', data: this.tickets }
                ],
                chart: {
                    height: 360,
                    type: 'line',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 },
                    fontFamily: 'Inter, ui-sans-serif, system-ui'
                },
                colors: ['#7c3aed', '#0ea5e9', '#10b981'],
                fill: {
                    type: ['gradient', 'solid', 'solid'],
                    gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
                },
                stroke: { width: [3, 3, 0], curve: 'smooth' },
                plotOptions: { bar: { borderRadius: 8, columnWidth: '42%' } },
                xaxis: {
                    categories: this.labels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } }
                },
                yaxis: [
                    {
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '10px' },
                            formatter: (value) => 'Rp ' + Number(value).toLocaleString('id-ID')
                        }
                    },
                    { opposite: true, labels: { style: { colors: '#94a3b8', fontSize: '10px' } } }
                ],
                grid: { borderColor: 'rgba(148, 163, 184, 0.14)', strokeDashArray: 4 },
                legend: { show: false },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: (value, context) => context.seriesIndex === 0
                            ? 'Rp ' + Number(value).toLocaleString('id-ID')
                            : Number(value).toLocaleString('id-ID')
                    }
                }
            });

            this.chart.render();
        }
     }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Analitik 30 Hari</p>
            <h3 class="text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">Grafik Kinerja Penyelenggara</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pendapatan, pesanan, dan tiket terjual berdasarkan pembayaran berhasil.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-violet-600/10 bg-violet-600/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400"><span class="h-1.5 w-1.5 rounded-full bg-violet-600"></span>Pendapatan</span>
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-sky-500/10 bg-sky-500/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-sky-500"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Pesanan</span>
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/10 bg-emerald-500/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-emerald-500"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Tiket</span>
        </div>
    </div>
    <div x-ref="chart" class="min-h-[360px]"></div>
</div>
