<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Antrian Santri')</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased" x-data="globalApp()">
    
    <!-- Alpine global Toast notifications -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl bg-slate-900/90 border border-slate-800/80 p-4 shadow-2xl backdrop-blur-xl flex items-start gap-3">
                
                <div class="flex-shrink-0">
                    <!-- Icon based on type -->
                    <template x-if="toast.type === 'success'">
                        <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <div class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <div class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </template>
                </div>
                
                <div class="flex-1 pt-0.5">
                    <p class="text-sm font-semibold text-white" x-text="toast.title"></p>
                    <p class="mt-1 text-xs text-slate-400" x-text="toast.message"></p>
                </div>

                <button @click="dismissToast(toast.id)" class="flex-shrink-0 text-slate-500 hover:text-slate-300 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Main Page Structure -->
    <div class="min-h-screen flex flex-col">
        
        <!-- Header / Navbar (Hanya muncul jika user login / punya sesiMeja) -->
        @if(isset($sesiMeja) && isset($sesiUser))
        <header class="border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: Brand / Logo -->
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-base font-bold bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">Antrian Santri</span>
                            <span class="text-xs block text-slate-500 font-medium">Layanan Real-time</span>
                        </div>
                    </div>

                    <!-- Center: Current Session Desk Status -->
                    <div class="hidden md:flex items-center gap-3 bg-slate-900/60 border border-slate-800/80 px-4 py-1.5 rounded-full">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <p class="text-xs text-slate-300 font-medium">
                            Aktif di <span class="text-indigo-400 font-semibold">{{ $sesiMeja->nama_meja }}</span> 
                            <span class="text-slate-500">|</span> 
                            Petugas: <span class="text-white font-semibold">{{ $sesiUser->nama }}</span>
                        </p>
                    </div>

                    <!-- Right: Logout / Action -->
                    <div class="flex items-center gap-4">
                        <div class="md:hidden flex items-center gap-1 bg-slate-900 border border-slate-800 px-3 py-1 rounded-lg">
                            <span class="text-xs font-semibold text-indigo-400">{{ $sesiMeja->nama_meja }}</span>
                        </div>

                        <form action="{{ route('sesi.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-900 border border-transparent hover:border-slate-800 transition-all duration-200">
                                <span>Keluar</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        @endif

        <!-- Main Content -->
        <main class="flex-1 flex flex-col">
            @yield('content')
        </main>
    </div>

    <!-- Script setup for Alpine.js Toast system -->
    <script>
        function globalApp() {
            return {
                toasts: [],
                init() {
                    // Check if Laravel session has success or error status, and trigger toasts
                    @if(session('success'))
                        this.addToast('Sukses', '{{ session('success') }}', 'success');
                    @endif
                    @if(session('error'))
                        this.addToast('Error', '{{ session('error') }}', 'error');
                    @endif
                },
                addToast(title, message, type = 'info') {
                    const id = Date.now() + Math.random().toString(36).substr(2, 9);
                    this.toasts.push({
                        id,
                        title,
                        message,
                        type,
                        show: true
                    });

                    // Auto dismiss after 4 seconds
                    setTimeout(() => {
                        this.dismissToast(id);
                    }, 4000);
                },
                dismissToast(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        toast.show = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            }
        }
    </script>
    
    @yield('scripts')
</body>
</html>
