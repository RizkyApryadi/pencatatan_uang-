<div class="p-4">
    <div class="p-6 bg-gray-50 min-h-screen">

        <!-- Header + Tombol Tambah -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Catatan Keuangan</h2>
                <p class="text-gray-600 mt-1">
                    Ringkasan keuangan Anda
                </p>
            </div>

            <button type="button" wire:click="openForm"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow">
                + Tambah Transaksi
            </button>
        </div>

        <!-- Summary Grid -->
        <div class="flex justify-between gap-6 mb-6">

            <!-- Pemasukan -->
            <div
                class="flex-1 bg-green-500 text-black rounded-xl p-6 shadow-lg flex flex-col items-center justify-center">
                <h3 class="text-sm font-semibold uppercase tracking-wide">Pemasukan</h3>
                <p class="text-2xl font-bold mt-2">@currency($totalIncome)</p>
            </div>

            <!-- Pengeluaran -->
            <div
                class="flex-1 bg-red-500 text-black rounded-xl p-6 shadow-lg flex flex-col items-center justify-center">
                <h3 class="text-sm font-semibold uppercase tracking-wide">Pengeluaran</h3>
                <p class="text-2xl font-bold mt-2">@currency($totalExpense)</p>
            </div>

            <!-- Total Saldo -->
            <div
                class="flex-1 bg-blue-500 text-black rounded-xl p-6 shadow-lg flex flex-col items-center justify-center">
                <h3 class="text-sm font-semibold uppercase tracking-wide">Total Saldo</h3>
                <p class="text-2xl font-bold mt-2">@currency($totalIncome - $totalExpense)</p>
            </div>

        </div>

        <!-- Statistik Transaksi -->

        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold mb-4">Statistik Transaksi</h3>
            <div id="transactionsChart" wire:ignore style="min-height:320px;"></div>

            <div id="chart-data" style="display:none;">
                {!! json_encode([
                'labels' => (array) $chartLabels,
                'income' => (array) $chartIncome,
                'expense' => (array) $chartExpense,
                ]) !!}
            </div>
        </div>


      <!-- Filter + Search -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

        <!-- Search -->
        <div class="relative w-full md:w-1/3">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari transaksi (judul, tanggal, jumlah)..." 
                class="w-full pl-10 pr-10 py-2 rounded-full border focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm"
                autocomplete="off"
            >
            @if($search)
                <button 
                    wire:click="$set('search', '')" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Filter + Tanggal -->
        <div class="flex flex-wrap items-center gap-3 mt-3 md:mt-0">
            <select 
                wire:model.live="filterType"
                class="border p-2 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none"
            >
                <option value="all">Semua</option>
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
            </select>

            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Dari</label>
                <input 
                    wire:model.live="fromDate" 
                    type="date"
                    class="border p-2 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none"
                >

                <label class="text-sm text-gray-600">Sampai</label>
                <input 
                    wire:model.live="toDate" 
                    type="date"
                    class="border p-2 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none"
                >
            </div>
        </div>
    </div>
</div>


        {{--
        <!-- Flash Message -->
        @if(session()->has('message'))
        <div class="mb-4 p-3 rounded bg-green-100 border border-green-300 text-green-800 shadow">
            {{ session('message') }}
        </div>
        @endif --}}

        {{-- Session-based SweetAlert removed. Notifications are handled via Livewire events and the global listener in
        layouts/app.blade.php --}}


        <!-- History Transaksi (Tabel) -->
        <div class="bg-white p-4 rounded-lg shadow">
            <table class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-4 py-2 border">Tanggal</th>
                        <th class="px-4 py-2 border">Judul</th>
                        <th class="px-4 py-2 border">Jenis</th>
                        <th class="px-4 py-2 border">Jumlah</th>
                        <th class="px-4 py-2 border">Bukti</th>
                        <th class="px-4 py-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2 border">{{ $tx->date->format('d M Y') }}</td>
                        <td class="px-4 py-2 border">{{ $tx->title }}</td>
                        <td class="px-4 py-2 border">
                            <span
                                class="px-2 py-1 rounded-full text-white {{ $tx->type == 'income' ? 'bg-green-600' : 'bg-red-600' }}">
                                {{ $tx->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border">
                            {{ $tx->type == 'income' ? '+' : '-' }} @currency($tx->amount)
                        </td>
                        <td class="px-4 py-2 border">
                            @if($tx->image)
                            <a target="_blank" href="{{ route('bukti.show', ['filename' => $tx->image]) }}"
                                class="text-blue-600 underline hover:text-blue-800">
                                Lihat
                            </a>
                            @endif


                        </td>
                        <td class="px-4 py-2 border flex gap-2">
                            <button wire:click="edit({{ $tx->id }})"
                                class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded shadow text-sm">
                                Edit
                            </button>
                            <button wire:click="delete({{ $tx->id }})"
                                onclick="confirm('Hapus transaksi?') || event.stopImmediatePropagation()"
                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded shadow text-sm">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">Belum ada transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>

    </div>

    <!-- Modal Transaction Form -->
    @if($isOpen)
    <div x-data class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-2xl rounded-lg p-5 shadow-lg" @click.away="Livewire.emit('closeForm')">
            <h3 class="font-semibold text-lg mb-3">
                {{ $transactionId ? 'Edit Transaksi' : 'Tambah Transaksi' }}
            </h3>
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>Jenis Transaksi</label>
                        <select wire:model="type" class="border p-2 w-full rounded">
                            <option value="">Pilih jenis</option>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                        @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Tanggal</label>
                        <input type="date" wire:model="date" class="border p-2 w-full rounded">
                        @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label>Judul</label>
                    <input type="text" wire:model="title" class="border p-2 w-full rounded">
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label>Jumlah</label>
                    <input type="number" wire:model="amount" class="border p-2 w-full rounded">
                    @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label>Deskripsi</label>
                    <textarea wire:model="description" class="border p-2 w-full rounded"></textarea>
                </div>

                <div>
                    <label>Bukti (opsional)</label>
                    <input type="file" wire:model="image" class="border p-2 w-full rounded">
                    @if($existingImage)
                    <div class="mt-1 flex items-center gap-2">
                        <a target="_blank" href="{{ asset('storage/'.$existingImage) }}" class="underline text-sm">Lihat
                            bukti</a>
                        <button type="button" wire:click="removeImage" class="text-red-500 text-sm">Hapus</button>
                    </div>
                    @endif
                    @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" wire:click="closeForm" class="px-4 py-2 border rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    (function () {
        let chart = null;

        function renderOrUpdateChart(payload) {
            const labels = payload.labels || [];
            // Coerce series values to numbers (ApexCharts expects numbers)
            const income = (payload.income || []).map(v => {
                const n = parseFloat(String(v).replace(/,/g, ''));
                return Number.isFinite(n) ? n : 0;
            });
            const expense = (payload.expense || []).map(v => {
                const n = parseFloat(String(v).replace(/,/g, ''));
                return Number.isFinite(n) ? n : 0;
            });

            const container = document.querySelector('#transactionsChart');

            // No data placeholder
            if (!labels.length) {
                if (chart) {
                    try { chart.destroy(); } catch (e) {}
                    chart = null;
                }
                container.innerHTML = '<div class="p-6 text-center text-gray-500">Belum ada data untuk ditampilkan.</div>';
                return;
            }

            // Clear placeholder
            container.innerHTML = '';

            const options = {
                chart: { type: 'bar', height: 350 },
                series: [
                    { name: 'Pemasukan', data: income },
                    { name: 'Pengeluaran', data: expense }
                ],
                xaxis: { categories: labels },
                colors: ['#22c55e', '#ef4444'],
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: function (val) { return new Intl.NumberFormat().format(val); } } }
            };

            if (!chart) {
                try {
                    chart = new ApexCharts(container, options);
                    chart.render();
                } catch (err) {
                    console.error('[TransactionIndex] error creating/rendering chart:', err, { container, options, labels, income, expense });
                    try { if (chart && typeof chart.destroy === 'function') chart.destroy(); } catch (e) {}
                    chart = null;
                }
            } else {
                try {
                    chart.updateOptions({ xaxis: { categories: labels } });
                    chart.updateSeries([ { name: 'Pemasukan', data: income }, { name: 'Pengeluaran', data: expense } ]);
                } catch (e) {
                    // if update fails, destroy and recreate
                    console.warn('[TransactionIndex] update failed, will recreate chart', e);
                    try { chart.destroy(); } catch (e) { console.warn('[TransactionIndex] destroy failed', e); }
                    try {
                        chart = new ApexCharts(container, options);
                        chart.render();
                    } catch (err) {
                        console.error('[TransactionIndex] error recreating chart:', err, { container, options, labels, income, expense });
                        try { if (chart && typeof chart.destroy === 'function') chart.destroy(); } catch (e) {}
                        chart = null;
                    }
                }
            }
        }

        document.addEventListener('livewire:init', function () {
            console.info('[TransactionIndex] livewire:load event');

            // Read initial payload from hidden element
            function readPayloadFromDom() {
                const raw = document.getElementById('chart-data')?.textContent || '{}';
                try { return JSON.parse(raw); } catch (e) { console.error('[TransactionIndex] invalid JSON in #chart-data', e); return {}; }
            }

            const initial = readPayloadFromDom();
            console.debug('[TransactionIndex] initial payload from DOM:', initial);
            renderOrUpdateChart(initial);

            // Update chart after Livewire updates by re-reading the hidden payload
            if (window.Livewire && typeof Livewire.hook === 'function') {
                Livewire.hook('message.processed', (message, component) => {
                    const payload = readPayloadFromDom();
                    console.debug('[TransactionIndex] message.processed payload:', payload);
                    renderOrUpdateChart(payload);
                });
            }

            // Also check a safety case: if ApexCharts isn't loaded yet, log it.
            if (typeof ApexCharts === 'undefined') {
                console.error('[TransactionIndex] ApexCharts is not loaded.');
            }
        });
    })();
</script>
@endpush