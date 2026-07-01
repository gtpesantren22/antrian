@extends('layouts.app')

@section('title', 'Resepsionis - Pendaftaran Antrian')

@section('content')
    <div class="flex-1 flex flex-col md:flex-row h-[calc(100vh-4rem)] overflow-hidden" x-data="resepsionisHandler({{ json_encode($antriansHariIni) }})">

        <!-- Left Panel: Search & Register -->
        <div class="flex-1 flex flex-col p-6 overflow-y-auto border-r border-slate-900 bg-slate-950">

            <div class="max-w-3xl w-full mx-auto space-y-6">
                <!-- Header section -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Pendaftaran Antrian Santri</h1>
                        <p class="text-xs text-slate-400 mt-1">Cari data santri di database untuk memasukkan mereka ke
                            antrian hari ini.</p>
                    </div>
                    <div>
                        <button @click="syncData()" :disabled="syncing"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-400 hover:text-indigo-300 font-bold text-xs transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <svg :class="syncing ? 'animate-spin' : ''" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18.2" />
                            </svg>
                            <span x-text="syncing ? 'Sinkronisasi...' : 'Sinkronisasi API'">Sinkronisasi API</span>
                        </button>
                    </div>
                </div>

                <!-- Search Panel -->
                <div
                    class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
                    <div
                        class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none">
                    </div>

                    <label for="search" class="block text-sm font-semibold text-slate-200 mb-2">Cari Nama / NIS (No.
                        Induk)</label>
                    <div class="relative">
                        <input type="text" id="search" x-model="keyword" @input.debounce.300ms="cariSantri()"
                            placeholder="Ketik minimal 2 karakter... (contoh: Ahmad)"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 pl-11 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        <div class="absolute left-4 top-3.5 text-slate-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Loading indicator inside input -->
                        <div class="absolute right-4 top-3.5" x-show="loading">
                            <svg class="animate-spin h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-1">Hasil Pencarian</h3>

                    <!-- Empty state before typing -->
                    <div x-show="keyword.length < 2"
                        class="text-center py-12 border border-dashed border-slate-800 rounded-2xl bg-slate-900/10">
                        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p class="text-sm text-slate-400 font-medium">Belum ada pencarian</p>
                        <p class="text-xs text-slate-600 mt-1">Cari nama atau NIS santri untuk mendaftarkan antrian.</p>
                    </div>

                    <!-- No results found -->
                    <div x-show="keyword.length >= 2 && santriList.length === 0 && !loading"
                        class="text-center py-12 border border-dashed border-slate-800 rounded-2xl bg-slate-900/10">
                        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-400 font-medium">Santri tidak ditemukan</p>
                        <p class="text-xs text-slate-600 mt-1">Gunakan kata kunci pencarian yang lain.</p>
                    </div>

                    <!-- Results list -->
                    <div class="grid grid-cols-1 gap-3" x-show="santriList.length > 0">
                        <template x-for="santri in santriList" :key="santri.id">
                            <div
                                class="bg-slate-900/40 border border-slate-850 hover:border-slate-800/80 rounded-2xl p-4 flex items-center justify-between gap-4 transition-all">
                                <div class="space-y-1">
                                    <h4 class="text-base font-bold text-white" x-text="santri.nama"></h4>
                                    <div class="flex items-center gap-3 text-xs text-slate-400">
                                        <span class="font-semibold text-indigo-400"
                                            x-text="'NIS: ' + santri.no_induk"></span>
                                        <span>•</span>
                                        <span x-text="'Asal: ' + (santri.asal_daerah || '-')"></span>
                                    </div>
                                </div>
                                <div>
                                    <template x-if="santri.sudah_antrian">
                                        <span
                                            class="px-3.5 py-1.5 rounded-xl bg-slate-800/60 border border-slate-750 text-slate-500 font-semibold text-xs inline-block">
                                            Sudah Antre
                                        </span>
                                    </template>
                                    <template x-if="!santri.sudah_antrian">
                                        <button @click="tambahAntrian(santri)"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-semibold text-xs transition-all cursor-pointer">
                                            Daftar Antrian
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Sidebar Queue List -->
        <div
            class="w-full md:w-80 bg-slate-900/30 border-t md:border-t-0 md:border-l border-slate-900 flex flex-col h-2/5 md:h-full overflow-hidden">
            <div class="p-4 border-b border-slate-900 bg-slate-950/80 flex items-center justify-between flex-shrink-0">
                <div>
                    <h2 class="text-sm font-bold text-white">Antrian Hari Ini</h2>
                    <p class="text-[10px] text-slate-500">Real-time update dari server</p>
                </div>
                <span class="text-xs font-bold bg-indigo-500/10 text-indigo-400 px-2.5 py-1 rounded-full"
                    x-text="antrianList.length">
                </span>
            </div>

            <!-- Queue list container -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                <template x-if="antrianList.length === 0">
                    <div class="text-center py-8 text-slate-500">
                        <p class="text-xs font-medium">Belum ada antrian terdaftar hari ini.</p>
                    </div>
                </template>

                <template x-for="antrian in antrianList" :key="antrian.id">
                    <div :class="getStatusBorder(antrian.status)"
                        class="bg-slate-900/60 border rounded-xl p-3.5 flex items-center justify-between gap-3 group hover:bg-slate-900 transition-all">

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-white" x-text="antrian.no_antrian"></span>
                                <span :class="getStatusBadge(antrian.status)"
                                    class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider"
                                    x-text="getStatusLabel(antrian.status)">
                                </span>
                            </div>
                            <h4 class="text-xs font-semibold text-slate-200 mt-1 truncate" x-text="antrian.santri.nama">
                            </h4>
                            <p class="text-[10px] text-slate-500 mt-0.5"
                                x-text="'Daftar: ' + formatWaktu(antrian.waktu_daftar)"></p>
                        </div>

                        <!-- Batal button (Hanya untuk yang statusnya 'menunggu') -->
                        <template x-if="antrian.status === 'menunggu'">
                            <button @click="batalAntrian(antrian)"
                                class="p-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-all duration-150 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Ticket Struk Modal (Popup printed simulation) -->
        <div x-show="showTicketModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="closeTicketModal()"
                class="w-full max-w-sm bg-white text-slate-950 rounded-2xl shadow-2xl p-6 border-t-8 border-indigo-600 relative overflow-hidden transform duration-300 flex flex-col"
                x-show="showTicketModal" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="scale-95 translate-y-8" x-transition:enter-end="scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="scale-100 translate-y-0" x-transition:leave-end="scale-95 translate-y-8">

                <!-- Close Button -->
                <button @click="closeTicketModal()"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-950 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Ticket details mockup -->
                <div class="text-center border-b-2 border-dashed border-slate-200 pb-5">
                    <p class="text-xs uppercase font-extrabold tracking-widest text-indigo-600">Nomor Antrian Santri</p>
                    <h3 class="text-6xl font-black mt-3 text-slate-900 tracking-tight" x-text="activeTicket?.no_antrian">
                    </h3>
                    <p class="text-[10px] text-slate-400 mt-2">Simpan struk ini untuk pemanggilan layanan.</p>
                </div>

                <div class="py-5 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nama Santri:</span>
                        <span class="font-bold text-slate-900 text-right" x-text="activeTicket?.nama"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">No. Induk (NIS):</span>
                        <span class="font-mono font-bold text-slate-900" x-text="activeTicket?.no_induk"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Tanggal:</span>
                        <span class="text-slate-800" x-text="activeTicket?.tanggal"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Jam Cetak:</span>
                        <span class="text-slate-800" x-text="activeTicket?.waktu + ' WIB'"></span>
                    </div>
                </div>

                <!-- Ticket Footer cut effect -->
                <div class="bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 rounded-xl font-bold text-xs cursor-pointer shadow-lg shadow-indigo-500/20 active:scale-98 transition-all"
                    @click="mockPrint()">
                    <span>Cetak Struk Antrian</span>
                </div>

                <p class="text-[9px] text-slate-400 text-center mt-3 font-semibold uppercase tracking-wider">Antrian Santri
                    Real-time</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
    <script>
        function resepsionisHandler(antrianHariIni) {
            return {
                keyword: '',
                loading: false,
                syncing: false,
                santriList: [],
                antrianList: antrianHariIni,

                // Ticket Modal State
                showTicketModal: false,
                activeTicket: null,

                init() {
                    // Initialize Reverb Echo listeners
                    if (window.Echo) {
                        window.Echo.channel('antrian')
                            .listen('.AntrianBaru', (e) => {
                                // Cek jika antrian belum ada di list
                                if (!this.antrianList.find(a => a.id === e.antrian.id)) {
                                    this.antrianList.push({
                                        id: e.antrian.id,
                                        no_antrian: e.antrian.no_antrian,
                                        status: e.antrian.status,
                                        waktu_daftar: e.antrian.waktu_daftar,
                                        santri: e.antrian.santri
                                    });
                                    // Sort by queue number
                                    this.antrianList.sort((a, b) => a.no_antrian.localeCompare(b.no_antrian));
                                }
                            })
                            .listen('.AntrianStatusUpdate', (e) => {
                                const antrian = this.antrianList.find(a => a.id === e.antrian.id);
                                if (antrian) {
                                    antrian.status = e.antrian.status;
                                    if (e.antrian.status === 'batal') {
                                        this.antrianList = this.antrianList.filter(a => a.id !== e.antrian.id);
                                    }
                                }
                            });
                    }
                },

                async cariSantri() {
                    if (this.keyword.length < 2) {
                        this.santriList = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(
                            `/resepsionis/cari-santri?keyword=${encodeURIComponent(this.keyword)}`);
                        if (response.ok) {
                            this.santriList = await response.json();
                        } else {
                            console.error('Failed to fetch students.');
                        }
                    } catch (error) {
                        console.error('Error fetching students:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async syncData() {
                    this.syncing = true;
                    try {
                        const response = await fetch('/resepsionis/sync-santri', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            const app = document.querySelector('body').__x?.$data || this.$root?.$data;
                            if (app && typeof app.addToast === 'function') {
                                app.addToast('Sukses', data.message, 'success');
                            }
                        } else {
                            const app = document.querySelector('body').__x?.$data || this.$root?.$data;
                            if (app && typeof app.addToast === 'function') {
                                app.addToast('Gagal', data.message || 'Gagal sinkronisasi.', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error syncing:', error);
                        const app = document.querySelector('body').__x?.$data || this.$root?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Error', 'Terjadi kesalahan koneksi saat sinkronisasi.', 'error');
                        }
                    } finally {
                        this.syncing = false;
                    }
                },

                async tambahAntrian(santri) {
                    try {
                        const response = await fetch(`/resepsionis/tambah-antrian/${santri.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Open Ticket Modal
                            this.activeTicket = data.antrian;
                            this.showTicketModal = true;

                            // Show toast
                            if (window.Alpine) {
                                // Find parent globalApp logic to push toast
                                const app = document.querySelector('body').__x?.$data || this.$root?.__x?.$data;
                                if (app && typeof app.addToast === 'function') {
                                    app.addToast('Sukses', data.message, 'success');
                                }
                            }

                            // Update search result item state
                            santri.sudah_antrian = true;

                            // Clear search field after successful queueing
                            this.keyword = '';
                            this.santriList = [];
                        } else {
                            const app = document.querySelector('body').__x?.$data || this.$root?.__x?.$data;
                            if (app && typeof app.addToast === 'function') {
                                app.addToast('Gagal', data.message || 'Gagal membuat antrian.', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error adding queue:', error);
                    }
                },

                async batalAntrian(antrian) {
                    if (!confirm(`Batalkan antrian ${antrian.no_antrian} untuk ${antrian.santri.nama}?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/resepsionis/batal-antrian/${antrian.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.antrianList = this.antrianList.filter(a => a.id !== antrian.id);

                            const app = document.querySelector('body').__x?.$data || this.$root?.__x?.$data;
                            if (app && typeof app.addToast === 'function') {
                                app.addToast('Sukses', data.message, 'success');
                            }
                        } else {
                            const app = document.querySelector('body').__x?.$data || this.$root?.__x?.$data;
                            if (app && typeof app.addToast === 'function') {
                                app.addToast('Gagal', data.message || 'Gagal membatalkan antrian.', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error canceling queue:', error);
                    }
                },

                closeTicketModal() {
                    this.showTicketModal = false;
                    this.activeTicket = null;
                },

                async mockPrint() {
                    if (!this.activeTicket) return;

                    // Tentukan nama printer thermal dari .env
                    const printerName = "{{ env('PRINTER_NAME', 'BP-LITE 80L') }}";

                    try {
                        // 1. Hubungkan ke aplikasi QZ Tray lokal
                        if (!qz.websocket.isActive()) {
                            await qz.websocket.connect();
                        }

                        // 2. Set konfigurasi printer (lebar kertas disesuaikan)
                        const config = qz.configs.create(printerName, {
                            size: {
                                width: 80
                            }, // Lebar kertas thermal 80mm
                            units: 'mm',
                            margins: 0
                        });

                        // 3. Siapkan HTML Struk (Template yang sama dengan print browser sebelumnya)
                        const ticketHtml = `
                        <div style="font-family: 'Courier New', monospace; text-align: center; font-size: 12px; width: 100%;">
                            <h3 style="margin: 0; font-size: 14px;">ANTRIAN SANTRI</h3>
                            <div style="font-size: 11px;">PENDAFTARAN SANTRI BARU 2026/2027</div>
                            <div style="font-size: 11px;">PP Darul Lughah Wal Karomah</div>
                            <div style="border-top: 1px dashed #000; margin: 6px 0;"></div>
                            <div style="font-size: 10px; uppercase">Nomor Antrian Anda</div>
                            <div style="font-size: 42px; font-weight: bold; margin: 3px 0;">${this.activeTicket.no_antrian}</div>
                            <div style="border-top: 1px dashed #000; margin: 6px 0;"></div>
                            <table style="width: 100%; font-size: 14px; text-align: left; margin: 0 auto;">
                                <tr><td style="width: 45px;">Nama:</td><td style="font-weight: bold;">${this.activeTicket.nama}</td></tr>
                                <tr><td>Tgl:</td><td>${this.activeTicket.tanggal}</td></tr>
                                <tr><td>Jam:</td><td>${this.activeTicket.waktu} WIB</td></tr>
                            </table>
                            <div style="border-top: 1px dashed #000; margin: 6px 0;"></div>
                            <div style="font-size: 8px;">
                                Simpan struk ini untuk pemanggilan.<br>
                                Harap menunggu antrian Anda dipanggil.
                            </div>
                        </div>
                    `;

                        // 4. Kirim print job dalam format HTML langsung ke printer tanpa memunculkan print dialog
                        const printData = [{
                            type: 'html',
                            format: 'plain',
                            data: ticketHtml
                        }];

                        await qz.print(config, printData);

                        // 5. Putuskan koneksi WebSocket setelah selesai cetak (opsional)
                        await qz.websocket.disconnect();

                    } catch (err) {
                        console.error("QZ Tray Error:", err);
                        alert("Gagal mencetak menggunakan QZ Tray. Pastikan aplikasi QZ Tray lokal sudah aktif di PC ini.\n\nDetail: " +
                            err.message);
                    } finally {
                        this.closeTicketModal();
                    }
                },


                // Helper Styles
                getStatusBorder(status) {
                    switch (status) {
                        case 'menunggu':
                            return 'border-indigo-800/40 hover:border-indigo-700/60';
                        case 'dipanggil_layanan':
                        case 'diproses_layanan':
                        case 'dipanggil_pembayaran':
                            return 'border-emerald-800/40 hover:border-emerald-700/60';
                        case 'menunggu_pembayaran':
                            return 'border-amber-800/40 hover:border-amber-700/60';
                        default:
                            return 'border-slate-800/40 hover:border-slate-700/60';
                    }
                },

                getStatusBadge(status) {
                    switch (status) {
                        case 'menunggu':
                            return 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20';
                        case 'dipanggil_layanan':
                            return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 animate-pulse';
                        case 'diproses_layanan':
                            return 'bg-teal-500/10 text-teal-400 border border-teal-500/20';
                        case 'menunggu_pembayaran':
                            return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                        case 'dipanggil_pembayaran':
                            return 'bg-amber-500/15 text-amber-400 border border-amber-500/20 animate-pulse';
                        case 'selesai':
                            return 'bg-slate-800 text-slate-400 border border-slate-750';
                        default:
                            return 'bg-slate-900 text-slate-500 border border-slate-950';
                    }
                },

                getStatusLabel(status) {
                    switch (status) {
                        case 'menunggu':
                            return 'Menunggu';
                        case 'dipanggil_layanan':
                            return 'Dipanggil';
                        case 'diproses_layanan':
                            return 'Diproses';
                        case 'menunggu_pembayaran':
                            return 'Ke Kasir';
                        case 'dipanggil_pembayaran':
                            return 'Dipanggil Kasir';
                        case 'selesai':
                            return 'Selesai';
                        default:
                            return status;
                    }
                },

                formatWaktu(dateStr) {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        return date.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        return dateStr;
                    }
                }
            }
        }
    </script>
@endsection
