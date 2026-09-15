<x-guest-layout>
    <div class="mb-6 text-xs text-slate-400 font-mono leading-relaxed">
        Lupa kata sandi Anda? Masukkan alamat email terdaftar Anda, dan sistem akan mengirimkan tautan untuk mereset kata sandi Anda.
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-400 font-mono flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6" autocomplete="off">
        @csrf

        <!-- Email Address -->
        <div class="relative">
            <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="off"
                    class="block w-full pl-10 pr-4 py-3.5 bg-slate-950/60 border border-white/10 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/25 focus:border-cyan-400 transition-all font-mono text-sm"
                    placeholder="name@example.com" style="background-color: rgba(2, 6, 23, 0.8);" />
            </div>
            @if ($errors->has('email'))
                <p class="mt-2 text-xs text-rose-500 font-medium flex items-center gap-1 animate-pulse">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $errors->first('email') }}
                </p>
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" 
                class="w-full py-3.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold rounded-xl shadow-[0_0_20px_rgba(6,182,212,0.3)] transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-2">
                <span>Send Reset Link</span>
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </div>
        
        <!-- Back to Login -->
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors flex items-center justify-center gap-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Back to Sign In
            </a>
        </div>
    </form>
</x-guest-layout>
