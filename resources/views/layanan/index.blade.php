@extends('layouts.app')

@section('title', $meja->nama_meja . ' - Dashboard Operator')

@section('content')
<div class="flex-1 p-6 bg-radial from-slate-900 to-slate-950" 
     x-data="layananHandler({{ json_encode($antrianAktif) }}, {{ $totalMenunggu }}, {{ $lockAktif ? 'true' : 'false' }}, {{ json_encode($meja) }})">
     
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Welcome desk banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 backdrop-blur-xl relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white" x-text="meja.nama_meja"></h1>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau dan layani administrasi santri secara real-time.</p>
                </div>
            </div>
            
            <!-- Quick stats bar -->
            <div class="flex gap-4 w-full sm:w-auto">
                <div class="bg-slate-950/60 border border-slate-850 px-5 py-2.5 rounded-2xl flex-1 sm:flex-none">
                    <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Menunggu Layanan</span>
                    <span class="text-xl font-extrabold text-indigo-400 mt-1 block" x-text="totalMenunggu">0</span>
                </div>
                <div class="bg-slate-950/60 border border-slate-850 px-5 py-2.5 rounded-2xl flex-1 sm:flex-none">
                    <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Status Meja</span>
                    <span class="text-xs font-bold mt-1.5 flex items-center gap-1.5"
                          :class="antrianAktif ? 'text-emerald-400' : 'text-slate-400'">
                        <span class="w-2 h-2 rounded-full animate-pulse" :class="antrianAktif ? 'bg-emerald-500' : 'bg-slate-500'"></span>
                        <span x-text="antrianAktif ? 'Sedang Melayani' : 'Siap Melayani'">Siap</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Grid (Col-Span 2): Active Service Workspace -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Main Workspace Card -->
                <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-8 backdrop-blur-xl relative min-h-[350px] flex flex-col justify-between overflow-hidden">
                    <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-500/5 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- State 1: No Active Queue -->
                    <template x-if="!antrianAktif">
                        <div class="flex-1 flex flex-col items-center justify-center text-center py-12">
                            <div class="w-16 h-16 rounded-full bg-slate-950 flex items-center justify-center border border-slate-850 text-slate-500 mb-5">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white">Tidak Ada Antrian Aktif</h3>
                            <p class="text-xs text-slate-400 max-w-sm mt-1.5">Klik tombol di bawah untuk mengambil nomor antrian berikutnya dari antrean menunggu.</p>
                            
                            <button @click="ambilAntrian()" 
                                    :disabled="loading"
                                    class="mt-6 px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 active:scale-98 text-white font-bold text-sm rounded-xl transition-all shadow-lg shadow-indigo-600/20 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Memproses...' : 'Ambil Antrian Berikutnya'">Ambil Antrian Berikutnya</span>
                            </button>
                        </div>
                    </template>

                    <!-- State 2: Active Queue in Workspace -->
                    <template x-if="antrianAktif">
                        <div class="flex-1 flex flex-col justify-between h-full space-y-8">
                            <!-- Card Header -->
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-slate-800/60">
                                <div>
                                    <span :class="getStatusBadge(antrianAktif.status)" 
                                          class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" 
                                          x-text="getStatusLabel(antrianAktif.status)">
                                    </span>
                                    <h2 class="text-2xl font-extrabold text-white mt-2" x-text="antrianAktif.santri.nama"></h2>
                                    <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                        <span class="font-bold text-indigo-400 font-mono" x-text="'NIS: ' + antrianAktif.santri.no_induk"></span>
                                        <span>•</span>
                                        <span x-text="'Wali/Ayah: ' + (antrianAktif.santri.nama_ayah || '-')"></span>
                                        <span>•</span>
                                        <span x-text="'Asal: ' + (antrianAktif.santri.asal_daerah || '-')"></span>
                                    </p>
                                </div>
                                <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl px-6 py-4 flex flex-col items-center justify-center self-start sm:self-auto min-w-[120px]">
                                    <span class="text-[9px] text-indigo-400 font-extrabold uppercase tracking-wider">No. Antrian</span>
                                    <span class="text-4xl font-black text-white mt-1" x-text="antrianAktif.no_antrian"></span>
                                </div>
                            </div>

                            <!-- Card Body / Timers & Processing -->
                            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 py-4">
                                <div class="space-y-1">
                                    <h4 class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Durasi Pelayanan</h4>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-black text-white font-mono" x-text="formatTimer(timerSeconds)">00:00</span>
                                        <span class="text-xs text-slate-500" x-show="antrianAktif.status === 'diproses_layanan'">(sedang diproses)</span>
                                        <span class="text-xs text-slate-500" x-show="antrianAktif.status === 'dipanggil_layanan'">(menunggu santri datang)</span>
                                    </div>
                                </di                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <!-- Re-call TTS Button (Hanya jika status 'dipanggil_layanan') -->
                                    <template x-if="antrianAktif.status === 'dipanggil_layanan'">
                                        <button @click="panggil()" 
                                                :disabled="lockAktif || loading"
                                                :class="lockAktif ? 'bg-slate-800 border-slate-750 text-slate-500 cursor-not-allowed' : 'bg-slate-950 border-slate-800 hover:border-slate-700 text-white cursor-pointer'"
                                                class="px-5 py-2.5 rounded-xl border font-semibold text-xs transition-all flex items-center gap-1.5">
                                            <svg x-show="loading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                            </svg>
                                            <span x-text="loading ? 'Memproses...' : (lockAktif ? 'Suara Sedang Mengantre' : 'Panggil Ulang')">Panggil Ulang</span>
                                        </button>
                                    </template>
 
                                    <!-- Start Processing Button (Hanya jika status 'dipanggil_layanan') -->
                                    <template x-if="antrianAktif.status === 'dipanggil_layanan'">
                                        <button @click="mulaiProses()" 
                                                :disabled="loading"
                                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/10 transition-all cursor-pointer flex items-center gap-1.5">
                                            <svg x-show="loading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span x-text="loading ? 'Memproses...' : 'Mulai Proses'">Mulai Proses</span>
                                        </button>
                                    </template>
 
                                    <!-- Complete Button (Hanya jika status 'diproses_layanan') -->
                                    <template x-if="antrianAktif.status === 'diproses_layanan'">
                                        <button @click="selesai()" 
                                                :disabled="loading"
                                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/10 transition-all cursor-pointer flex items-center gap-1.5">
                                            <svg x-show="loading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span x-text="loading ? 'Memproses...' : 'Selesai & Arahkan ke Kasir'">Selesai & Arahkan ke Kasir</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right Grid (Col-Span 1): Desk Information & Live Events -->
            <div class="space-y-6">
                <!-- Desk Profile -->
                <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 backdrop-blur-xl space-y-4">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Informasi Meja</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-slate-850 pb-2.5">
                            <span class="text-xs text-slate-500">Nama Meja</span>
                            <span class="text-xs font-bold text-white" x-text="meja.nama_meja"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-850 pb-2.5">
                            <span class="text-xs text-slate-500">Petugas</span>
                            <span class="text-xs font-bold text-white">{{ $sesiUser->nama }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Tipe Akses</span>
                            <span class="text-xs font-semibold text-indigo-400 capitalize" x-text="meja.tipe"></span>
                        </div>
                    </div>
                </div>

                <!-- Live Broadcast logs -->
                <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 backdrop-blur-xl flex flex-col h-[230px] overflow-hidden">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Aktivitas Real-time</h3>
                    
                    <div class="flex-1 overflow-y-auto space-y-2 pr-1 font-mono text-[10px]">
                        <template x-if="logs.length === 0">
                            <p class="text-slate-600 text-center py-8">Mendengarkan event Reverb...</p>
                        </template>
                        <template x-for="log in logs" :key="log.time">
                            <div class="border-l-2 border-slate-700 pl-2 py-0.5">
                                <span class="text-slate-500" x-text="log.time"></span>
                                <span class="text-slate-300 ml-1.5" x-text="log.message"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function layananHandler(antrianAktif, totalMenunggu, lockAktif, meja) {
        return {
            antrianAktif: antrianAktif,
            totalMenunggu: totalMenunggu,
            lockAktif: lockAktif,
            meja: meja,
            loading: false,
            
            // Timer state
            timerSeconds: 0,
            timerInterval: null,
            
            // Event logs
            logs: [],
            
            init() {
                // Initialize timer if queue is already active
                if (this.antrianAktif) {
                    if (this.antrianAktif.status === 'diproses_layanan') {
                        // Calculate elapsed time from waktu_mulai_layanan
                        const startTime = new Date(this.antrianAktif.waktu_mulai_layanan).getTime();
                        this.timerSeconds = Math.max(0, Math.floor((Date.now() - startTime) / 1000));
                        this.startTimer();
                    } else if (this.antrianAktif.status === 'dipanggil_layanan') {
                        const startTime = new Date(this.antrianAktif.waktu_dipanggil_layanan).getTime();
                        this.timerSeconds = Math.max(0, Math.floor((Date.now() - startTime) / 1000));
                        this.startTimer();
                    }
                }
                
                // Echo Listeners
                if (window.Echo) {
                    this.pushLog('Terhubung ke Reverb WebSocket.');
                    
                    window.Echo.channel('antrian')
                        .listen('.AntrianBaru', (e) => {
                            this.pushLog(`Antrian baru terdaftar: ${e.antrian.no_antrian}`);
                            this.fetchStatus();
                        })
                        .listen('.AntrianDipanggil', (e) => {
                            this.pushLog(`Pemanggilan: ${e.antrian.no_antrian} menuju ${e.meja.nama_meja}`);
                            this.lockAktif = true;
                            
                            // Highlight calling status on operators if it's not this desk
                            if (e.meja.id !== this.meja.id) {
                                const app = document.querySelector('body').__x?.$data;
                                if (app && typeof app.addToast === 'function') {
                                    app.addToast('Pemanggilan Suara', `${e.meja.nama_meja} sedang memanggil antrian ${e.antrian.no_antrian}`, 'info');
                                }
                            }
                        })
                        .listen('.LockDilepas', (e) => {
                            this.pushLog('Mikrofon/Suara bebas (Lock dilepas).');
                            this.lockAktif = false;
                        })
                        .listen('.AntrianStatusUpdate', (e) => {
                            this.pushLog(`Update status antrian ${e.antrian.no_antrian} -> ${e.antrian.status}`);
                            this.fetchStatus();
                            
                            // If this queue is updated by someone else (e.g. cancelled by receptionist)
                            if (this.antrianAktif && this.antrianAktif.id === e.antrian.id) {
                                if (e.antrian.status === 'batal') {
                                    this.stopTimer();
                                    this.antrianAktif = null;
                                    this.timerSeconds = 0;
                                    
                                    const app = document.querySelector('body').__x?.$data;
                                    if (app && typeof app.addToast === 'function') {
                                        app.addToast('Antrian Dibatalkan', 'Antrian yang sedang aktif dibatalkan oleh resepsionis.', 'error');
                                    }
                                }
                            }
                        });
                }
            },
            
            async fetchStatus() {
                try {
                    const response = await fetch('/antrian/status');
                    if (response.ok) {
                        const data = await response.json();
                        this.totalMenunggu = data.menunggu;
                        this.lockAktif = data.lock_aktif;
                    }
                } catch(e) {
                    console.error('Failed to sync statuses.', e);
                }
            },
            
            async ambilAntrian() {
                this.loading = true;
                try {
                    const response = await fetch('/layanan/ambil-antrian', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.antrianAktif = data.antrian;
                        this.timerSeconds = 0;
                        this.startTimer();
                        
                        // Automatically call TTS when taken
                        this.panggil();
                        
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Sukses', data.message, 'success');
                        }
                    } else {
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Info', data.message || 'Gagal mengambil antrian.', 'info');
                        }
                    }
                } catch(error) {
                    console.error('Error fetching next queue:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            async panggil() {
                if (!this.antrianAktif) return;
                
                this.loading = true;
                try {
                    const response = await fetch(`/layanan/panggil/${this.antrianAktif.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        // local lock
                        this.lockAktif = true;
                        this.pushLog('Mengirim permintaan pemanggilan suara...');
                    } else {
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Gagal', data.message || 'Gagal memanggil.', 'error');
                        }
                    }
                } catch(error) {
                    console.error('Error calling TTS:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            async mulaiProses() {
                if (!this.antrianAktif) return;
                
                this.loading = true;
                try {
                    const response = await fetch(`/layanan/mulai/${this.antrianAktif.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.antrianAktif.status = 'diproses_layanan';
                        this.timerSeconds = 0; // reset timer for processing duration
                        
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Sukses', data.message, 'success');
                        }
                    } else {
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Gagal', data.message || 'Gagal memulai.', 'error');
                        }
                    }
                } catch(error) {
                    console.error('Error starting process:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            async selesai() {
                if (!this.antrianAktif) return;
                
                this.loading = true;
                try {
                    const response = await fetch(`/layanan/selesai/${this.antrianAktif.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.stopTimer();
                        this.antrianAktif = null;
                        this.timerSeconds = 0;
                        
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Sukses', data.message, 'success');
                        }
                    } else {
                        const app = document.querySelector('body').__x?.$data;
                        if (app && typeof app.addToast === 'function') {
                            app.addToast('Gagal', data.message || 'Gagal menyelesaikan.', 'error');
                        }
                    }
                } catch(error) {
                    console.error('Error completing process:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            // Timer Helpers
            startTimer() {
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    this.timerSeconds++;
                }, 1000);
            },
            
            stopTimer() {
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            },
            
            formatTimer(sec) {
                const m = Math.floor(sec / 60).toString().padStart(2, '0');
                const s = (sec % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
            },
            
            getStatusBadge(status) {
                switch(status) {
                    case 'dipanggil_layanan':
                        return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                    case 'diproses_layanan':
                        return 'bg-blue-500/10 text-blue-400 border border-blue-500/20 animate-pulse';
                    default:
                        return 'bg-slate-800 text-slate-400 border border-slate-750';
                }
            },
            
            getStatusLabel(status) {
                switch(status) {
                    case 'dipanggil_layanan': return 'Dipanggil';
                    case 'diproses_layanan': return 'Proses Admin';
                    default: return status;
                }
            },
            
            pushLog(msg) {
                const now = new Date();
                const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.logs.unshift({ time, message: msg });
                if (this.logs.length > 30) this.logs.pop();
            }
        }
    }
</script>
@endsection
