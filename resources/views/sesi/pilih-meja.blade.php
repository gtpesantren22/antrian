@extends('layouts.app')

@section('title', 'Pilih Meja - Sistem Antrian')

@section('content')
<div class="flex-1 flex items-center justify-center p-6 bg-radial from-slate-900 to-slate-950" 
     x-data="pilihMejaHandler({{ json_encode($mejas) }})">
     
    <div :class="step === 1 ? 'max-w-4xl' : 'max-w-lg'"
         class="w-full bg-slate-900/60 border border-slate-800/80 rounded-3xl shadow-2xl backdrop-blur-xl p-8 relative overflow-hidden transition-all duration-300">
        
        <!-- Background glows -->
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-violet-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <!-- Step 1: Pilih Meja -->
        <div x-show="step === 1" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Pilih Meja Tugas</h1>
                <p class="text-slate-400 mt-2 text-sm">Silakan pilih meja operasional Anda hari ini untuk memulai aktivitas.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 max-h-[550px] overflow-y-auto pr-1">
                <template x-for="meja in mejas" :key="meja.id">
                    <button @click="selectMeja(meja)" 
                            :disabled="meja.sedang_ditempati"
                            :class="meja.sedang_ditempati 
                                    ? 'bg-slate-900/40 border-slate-950 text-slate-600 cursor-not-allowed opacity-50' 
                                    : 'bg-slate-900/80 hover:bg-slate-800/90 border-slate-800 hover:border-indigo-500/60 text-white cursor-pointer'"
                            class="flex flex-col items-start p-5 rounded-2xl border text-left group transition-all duration-200 hover:-translate-y-1 hover:shadow-lg relative">
                        
                        <div class="flex items-center justify-between w-full">
                            <span :class="getTypeColor(meja.tipe)" 
                                  class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold tracking-wider uppercase" 
                                  x-text="meja.tipe">
                            </span>
                            <template x-if="meja.sedang_ditempati">
                                <span class="text-[10px] text-rose-500 font-semibold bg-rose-500/10 px-2 py-0.5 rounded-md">Ditempati</span>
                            </template>
                        </div>
                        
                        <h3 class="text-base font-bold mt-3 group-hover:text-indigo-400 transition-colors" x-text="meja.nama_meja"></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-snug">Klik untuk masuk sesi petugas meja ini.</p>
                    </button>
                </template>
            </div>

            <!-- TV Display shortcut -->
            <div class="mt-8 pt-6 border-t border-slate-800/60 text-center">
                <a href="{{ route('display') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                    <span>Menuju Layar TV Utama (Display)</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Step 2: Input PIN -->
        <div x-show="step === 2" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4">
            
            <button @click="backToStep1()" class="absolute top-8 left-8 text-slate-400 hover:text-white transition-colors flex items-center gap-1.5 text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </button>
            
            <div class="text-center mb-6 pt-6">
                <span :class="getTypeColor(selectedMeja?.tipe)" 
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold tracking-wider uppercase inline-block mb-3" 
                      x-text="selectedMeja?.tipe">
                </span>
                <h2 class="text-2xl font-bold text-white" x-text="selectedMeja?.nama_meja"></h2>
                <p class="text-xs text-slate-400 mt-1">Masukkan 4-digit PIN keamanan Anda.</p>
            </div>
            
            <!-- Code representation dots -->
            <div class="flex justify-center gap-4 my-6">
                <template x-for="i in [0, 1, 2, 3]">
                    <div class="w-4 h-4 rounded-full border transition-all duration-150"
                         :class="pin.length > i ? 'bg-indigo-500 border-indigo-500 scale-110 shadow-lg shadow-indigo-500/50' : 'bg-transparent border-slate-700'">
                    </div>
                </template>
            </div>

            <!-- Keypad -->
            <div class="grid grid-cols-3 gap-3 max-w-[280px] mx-auto">
                <template x-for="num in ['1', '2', '3', '4', '5', '6', '7', '8', '9']">
                    <button @click="pressKey(num)" 
                            class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 text-xl font-bold text-white flex items-center justify-center hover:bg-indigo-600 hover:border-indigo-500 active:scale-95 transition-all duration-100 cursor-pointer mx-auto">
                        <span x-text="num"></span>
                    </button>
                </template>
                <button @click="clearPin()" 
                        class="w-16 h-16 rounded-full text-xs font-semibold text-slate-400 flex items-center justify-center hover:text-white transition-colors cursor-pointer mx-auto">
                    Reset
                </button>
                <button @click="pressKey('0')" 
                        class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 text-xl font-bold text-white flex items-center justify-center hover:bg-indigo-600 hover:border-indigo-500 active:scale-95 transition-all duration-100 cursor-pointer mx-auto">
                    0
                </button>
                <button @click="backspace()" 
                        class="w-16 h-16 rounded-full text-slate-400 flex items-center justify-center hover:text-white transition-colors cursor-pointer mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414A2 2 0 0010.828 19H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                    </svg>
                </button>
            </div>

            <!-- Hidden Submit Form -->
            <form action="{{ route('sesi.login') }}" method="POST" x-ref="loginForm" class="hidden">
                @csrf
                <input type="hidden" name="meja_id" :value="selectedMeja?.id">
                <input type="hidden" name="pin" :value="pin">
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function pilihMejaHandler(mejas) {
        return {
            mejas: mejas,
            step: 1,
            selectedMeja: null,
            pin: '',
            
            selectMeja(meja) {
                if (meja.sedang_ditempati) return;
                this.selectedMeja = meja;
                this.step = 2;
            },
            
            backToStep1() {
                this.step = 1;
                this.pin = '';
                this.selectedMeja = null;
            },
            
            pressKey(num) {
                if (this.pin.length < 4) {
                    this.pin += num;
                    if (this.pin.length === 4) {
                        // Submit automatically
                        setTimeout(() => {
                            this.$refs.loginForm.submit();
                        }, 250);
                    }
                }
            },
            
            backspace() {
                this.pin = this.pin.slice(0, -1);
            },
            
            clearPin() {
                this.pin = '';
            },
            
            getTypeColor(tipe) {
                switch(tipe) {
                    case 'resepsionis':
                        return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                    case 'layanan':
                        return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
                    case 'pembayaran':
                        return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                    default:
                        return 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
                }
            }
        }
    }
</script>
@endsection
