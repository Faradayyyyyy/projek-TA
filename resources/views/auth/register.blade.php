<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div class="relative">
            <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all font-mono text-sm"
                    placeholder="Viggo" />
            </div>
            @if ($errors->has('name'))
                <p class="mt-2 text-xs text-rose-500 font-medium flex items-center gap-1 animate-pulse">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $errors->first('name') }}
                </p>
            @endif
        </div>

        <!-- Email Address -->
        <div class="relative">
            <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                    class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all font-mono text-sm"
                    placeholder="name@example.com" />
            </div>
            @if ($errors->has('email'))
                <p class="mt-2 text-xs text-rose-500 font-medium flex items-center gap-1 animate-pulse">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $errors->first('email') }}
                </p>
            @endif
        </div>

        <!-- Password -->
        <div class="relative">
            <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all font-mono text-sm"
                    placeholder="••••••••" />
            </div>
            @if ($errors->has('password'))
                <p class="mt-2 text-xs text-rose-500 font-medium flex items-center gap-1 animate-pulse">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $errors->first('password') }}
                </p>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="relative">
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Confirm Password</label>
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all font-mono text-sm"
                    placeholder="••••••••" />
            </div>
            @if ($errors->has('password_confirmation'))
                <p class="mt-2 text-xs text-rose-500 font-medium flex items-center gap-1 animate-pulse">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $errors->first('password_confirmation') }}
                </p>
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" 
                class="w-full py-3.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold rounded-xl shadow-[0_0_20px_rgba(6,182,212,0.3)] transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-2">
                <span>Create Account</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
        
        <!-- Login Link -->
        <div class="text-center mt-6">
            <p class="text-xs text-slate-400">
                Already registered? 
                <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Sign In
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
