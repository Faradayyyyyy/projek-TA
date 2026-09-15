<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-400 font-mono flex items-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6" autocomplete="off">
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

        <!-- Password -->
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-medium transition-colors">
                        Forgot Password?
                    </a>
                @endif
            </div>
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="block w-full pl-10 pr-11 py-3.5 bg-slate-950/60 border border-white/10 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/25 focus:border-cyan-400 transition-all font-mono text-sm"
                    placeholder="••••••••" style="background-color: rgba(2, 6, 23, 0.8);" />
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-cyan-400 transition-colors focus:outline-none">
                    <span id="eyeIconWrapper">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </span>
                </button>
            </div>
            @if ($errors->has('password'))
                <p class="mt-2 text-xs text-rose-500 font-medium flex items-center gap-1 animate-pulse">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $errors->first('password') }}
                </p>
            @endif
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const togglePasswordBtn = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('password');
                const eyeWrapper = document.getElementById('eyeIconWrapper');

                if (togglePasswordBtn && passwordInput && eyeWrapper) {
                    togglePasswordBtn.addEventListener('click', function () {
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                        
                        if (isPassword) {
                            eyeWrapper.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4 text-cyan-400"></i>';
                        } else {
                            eyeWrapper.innerHTML = '<i data-lucide="eye" class="w-4 h-4 text-slate-500"></i>';
                        }
                        
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                }
            });
        </script>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" 
                class="w-4 h-4 rounded bg-slate-950/60 border-white/10 text-cyan-500 focus:ring-cyan-500/25 focus:ring-offset-0 focus:outline-none cursor-pointer">
            <label for="remember_me" class="ms-2.5 text-xs text-slate-400 font-medium cursor-pointer hover:text-slate-300 transition-colors">
                Keep me signed in
            </label>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" 
                class="w-full py-3.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold rounded-xl shadow-[0_0_20px_rgba(6,182,212,0.3)] transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-2">
                <span>Sign In to Dashboard</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
        
        <!-- Registration Link -->
        <div class="text-center mt-6">
            <p class="text-xs text-slate-400">
                New user? 
                <a href="{{ route('register') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Create an account
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
