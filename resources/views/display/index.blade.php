@extends('layouts.app')

@section('title', 'Display Antrian Utama')

@section('content')
<div class="flex-1 bg-slate-950 text-slate-100 flex flex-col h-[calc(100vh-4rem)] overflow-hidden relative"
     x-data="displayHandler({{ json_encode($terakhirDipanggil) }})">
     
    <!-- Ambient glowing backgrounds -->
    <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-[500px] h-[500px] bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Browser Autoplay Audio Activation Modal overlay -->
    <div x-show="!audioActivated" 
         class="absolute inset-0 z-40 bg-slate-950/95 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center">
        <div class="max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl pointer-events-none"></div>
            
            <div class="w-16 h-16 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Aktifkan Suara Panggilan</h2>
            <p class="text-xs text-slate-400 mb-6">Browser membatasi suara otomatis sebelum ada interaksi. Klik tombol di bawah agar suara panggilan Text-to-Speech (TTS) berbunyi.</p>
            
            <button @click="activateAudio()" 
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 active:scale-98 text-white font-bold text-sm rounded-xl transition-all shadow-lg shadow-indigo-600/20 cursor-pointer">
                Aktifkan Audio Display
            </button>
        </div>
    </div>

    <!-- Main TV Dashboard layout -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-6 p-6 h-full overflow-hidden">
        
        <!-- Left 2 Cols: Main Now Serving Screen -->
        <div class="lg:col-span-2 flex flex-col h-full overflow-hidden justify-between space-y-6">
            
            <!-- Main Calling Panel -->
            <div class="flex-1 bg-slate-900/40 border border-slate-900 rounded-3xl p-8 backdrop-blur-xl flex flex-col items-center justify-center text-center relative overflow-hidden">
                <!-- Pulse glow when calling -->
                <div x-show="isCallingNow" class="absolute inset-0 bg-indigo-500/5 animate-pulse border border-indigo-500/25 rounded-3xl pointer-events-none"></div>
                
                <template x-if="mainAntrian">
                    <div class="space-y-6 md:space-y-10 w-full">
                        <span class="px-5 py-2.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs sm:text-sm font-extrabold uppercase tracking-widest inline-block shadow-sm">
                            SEDANG DIPANGGIL
                        </span>
                        
                        <div class="relative inline-block my-4">
                            <span x-text="mainAntrian.no_antrian" 
                                  class="text-8xl sm:text-9xl md:text-[11rem] font-black tracking-tighter bg-gradient-to-b from-white via-slate-100 to-slate-300 bg-clip-text text-transparent drop-shadow-[0_10px_10px_rgba(99,102,241,0.15)] leading-none select-none font-mono">
                            </span>
                        </div>
                        
                        <div class="space-y-2 md:space-y-4">
                            <h2 x-text="mainAntrian.santri.nama" 
                                class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight uppercase max-w-2xl mx-auto truncate px-4">
                            </h2>
                            <p class="text-slate-400 text-xs sm:text-sm" x-text="'No. Induk (NIS): ' + mainAntrian.santri.no_induk"></p>
                        </div>
                        
                        <!-- Destination Desk -->
                        <div class="mt-4 inline-flex items-center gap-3.5 bg-indigo-600 px-8 py-4 sm:py-5 rounded-2xl shadow-xl shadow-indigo-600/20 border border-indigo-500/40 transform scale-105">
                            <svg class="w-6 h-6 text-white animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                            <span class="text-lg sm:text-xl font-black text-white uppercase tracking-wide" x-text="getDestDesk()"></span>
                        </div>
                    </div>
                </template>

                <template x-if="!mainAntrian">
                    <div class="text-slate-500 space-y-3">
                        <svg class="w-16 h-16 text-slate-700 mx-auto animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
                        </svg>
                        <h2 class="text-xl font-bold text-slate-400">Menunggu Antrian Berikutnya</h2>
                        <p class="text-xs text-slate-600 max-w-xs mx-auto">Nomor antrian yang dipanggil oleh petugas layanan atau kasir akan muncul secara otomatis di layar ini.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Right 1 Col: History Logs -->
        <div class="flex flex-col h-full overflow-hidden bg-slate-900/30 border border-slate-900 rounded-3xl p-5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat Pemanggilan</span>
            </h3>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                <template x-if="history.length === 0">
                    <p class="text-xs text-slate-600 text-center py-12">Belum ada riwayat pemanggilan hari ini.</p>
                </template>
                
                <template x-for="item in history" :key="item.id">
                    <div class="bg-slate-900/60 border border-slate-850 hover:border-slate-800 rounded-2xl p-4 flex items-center justify-between gap-3 transition-colors">
                        <div>
                            <span class="text-xs font-bold text-slate-400 block truncate max-w-[130px]" x-text="item.santri.nama"></span>
                            <span class="text-[9px] font-semibold text-indigo-400 uppercase tracking-wide mt-1 block" x-text="getDestDesk(item)"></span>
                        </div>
                        <div class="bg-slate-950/80 border border-slate-850 rounded-xl px-3 py-2 flex items-center justify-center min-w-[70px]">
                            <span class="text-xl font-extrabold text-white font-mono" x-text="item.no_antrian"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Bottom: Rolling Ticker & Time Footer -->
    <footer class="h-14 border-t border-slate-900 bg-slate-950 flex items-center justify-between px-6 flex-shrink-0">
        <!-- Ticker -->
        <div class="flex-1 overflow-hidden relative h-full flex items-center mr-6">
            <div class="absolute whitespace-nowrap animate-[marquee_25s_linear_infinite] hover:[animation-play-state:paused] text-xs sm:text-sm text-slate-400 font-medium">
                Selamat Datang di Sistem Antrian Pendaftaran Santri Baru • Silakan tertib menunggu nomor antrian Anda dipanggil di ruang tunggu • Siapkan kartu identitas, berkas persyaratan, dan dokumen pendukung lainnya • Terima kasih atas kerjasamanya.
            </div>
        </div>
        
        <!-- Clock -->
        <div class="flex items-center gap-3 border-l border-slate-900 pl-6 h-full text-slate-300 font-mono text-sm sm:text-base font-bold whitespace-nowrap">
            <span x-text="clockTime">00:00:00</span>
        </div>
    </footer>
</div>

<style>
    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
</style>
@endsection

@section('scripts')
<script>
    function displayHandler(terakhirDipanggil) {
        return {
            audioActivated: false,
            mainAntrian: terakhirDipanggil.length > 0 ? terakhirDipanggil[0] : null,
            history: terakhirDipanggil.length > 1 ? terakhirDipanggil.slice(1) : [],
            isCallingNow: false,
            
            // Clock state
            clockTime: '',
            
            // TTS configuration
            speechQueue: [],
            isSpeaking: false,
            
            init() {
                // Initialize clock
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
                
                // Echo Listeners
                if (window.Echo) {
                    window.Echo.channel('antrian')
                        .listen('.AntrianDipanggil', (e) => {
                            this.handleNewCall(e.antrian, e.meja);
                        });
                }
            },
            
            activateAudio() {
                this.audioActivated = true;
                
                // Play dummy silent sound to activate context
                if ('speechSynthesis' in window) {
                    const utter = new SpeechSynthesisUtterance('');
                    window.speechSynthesis.speak(utter);
                }
                
                // Play a brief chime to verify AudioContext works
                this.playChime();
            },
            
            updateClock() {
                const now = new Date();
                this.clockTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            },
            
            getDestDesk(item = null) {
                const antrian = item || this.mainAntrian;
                if (!antrian) return '';
                
                // Check if called from payment or services
                if (antrian.status === 'dipanggil_pembayaran') {
                    return antrian.meja_pembayaran?.nama_meja || 'Meja Pembayaran';
                }
                return antrian.meja_layanan?.nama_meja || 'Meja Layanan';
            },
            
            handleNewCall(antrian, meja) {
                // If there's an active main queue, push it to history (if it's not the same id)
                if (this.mainAntrian && this.mainAntrian.id !== antrian.id) {
                    this.history.unshift(this.mainAntrian);
                    if (this.history.length > 5) this.history.pop();
                }
                
                // Set active models
                this.mainAntrian = {
                    ...antrian,
                    meja_layanan: antrian.status !== 'dipanggil_pembayaran' ? meja : null,
                    meja_pembayaran: antrian.status === 'dipanggil_pembayaran' ? meja : null
                };
                
                this.isCallingNow = true;
                
                // Trigger voice call (TTS)
                this.speak(antrian.no_antrian, antrian.santri.nama, meja.nama_meja, antrian.id);
            },
            
            playChime() {
                if (!window.AudioContext && !window.webkitAudioContext) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const playTone = (freq, start, duration) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = freq;
                        osc.type = 'sine';
                        gain.gain.setValueAtTime(0, start);
                        gain.gain.linearRampToValueAtTime(0.25, start + 0.05);
                        gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
                        osc.start(start);
                        osc.stop(start + duration);
                    };
                    // Professional dual tone chime (Ding-Dong: C#5 and A4)
                    playTone(554.37, ctx.currentTime, 0.4); 
                    playTone(440.00, ctx.currentTime + 0.25, 0.6); 
                } catch(e) {
                    console.error('Failed to play audio chime:', e);
                }
            },

            speak(noAntrian, nama, namaMeja, antrianId) {
                if (!('speechSynthesis' in window)) {
                    // Fallback if not supported
                    this.releaseLock(antrianId);
                    return;
                }
                
                // Play dual-tone chime sound first
                this.playChime();
                
                // Cancel previous speech if any
                window.speechSynthesis.cancel();
                
                // Format spelling for ticket number e.g. A-005 -> "A, 5" (skip leading zeros)
                const parts = noAntrian.split('-');
                let spelledNo = noAntrian;
                if (parts.length === 2) {
                    const letter = parts[0];
                    const num = parseInt(parts[1], 10);
                    spelledNo = `${letter}, ${num}`;
                }
                
                // Destination prefix
                const destText = namaMeja.toLowerCase().includes('pembayaran') || namaMeja.toLowerCase().includes('kasir')
                    ? `menuju ${namaMeja} untuk pembayaran`
                    : `menuju ${namaMeja}`;
                    
                const fullText = `Nomor antrean, ${spelledNo}, ${nama}, silakan ${destText}.`;
                
                const utterance = new SpeechSynthesisUtterance(fullText);
                utterance.lang = 'id-ID';
                utterance.rate = 0.95; // slightly slower for clarity
                utterance.pitch = 1;
                
                // Find Indonesian voice if possible
                const voices = window.speechSynthesis.getVoices();
                const idVoice = voices.find(voice => voice.lang.includes('id') || voice.lang.includes('ID'));
                if (idVoice) utterance.voice = idVoice;
                
                utterance.onend = () => {
                    this.isCallingNow = false;
                    this.releaseLock(antrianId);
                };
                
                utterance.onerror = (err) => {
                    console.error('TTS synthesis error:', err);
                    this.isCallingNow = false;
                    this.releaseLock(antrianId);
                };
                
                // Play audio with a slight delay to allow the chime to finish playing
                setTimeout(() => {
                    window.speechSynthesis.speak(utterance);
                }, 900);
                
                // Safety net: in case TTS gets stuck or blocked by browser policies
                // release lock after 8 seconds anyway
                setTimeout(() => {
                    if (this.isCallingNow) {
                        this.isCallingNow = false;
                        this.releaseLock(antrianId);
                    }
                }, 9000);
            },
            
            async releaseLock(antrianId) {
                try {
                    await fetch('/antrian/lepas-lock', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                } catch(e) {
                    console.error('Failed to release call lock.', e);
                }
            }
        }
    }
</script>
@endsection
