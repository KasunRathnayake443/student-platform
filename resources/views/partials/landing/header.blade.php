<header id="landing-header" class="fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-20">
            <a href="/" class="flex items-center gap-2.5 group">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-lg shadow-sm">🎓</span>
                <span class="font-display font-semibold text-lg text-white tracking-tight hidden sm:block">StudentPlatform</span>
            </a>

            <nav class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-sm font-medium text-white/80 hover:text-white transition-colors duration-200">Features</a>
                <a href="#portals" class="text-sm font-medium text-white/80 hover:text-white transition-colors duration-200">Portals</a>
                <a href="#social-proof" class="text-sm font-medium text-white/80 hover:text-white transition-colors duration-200">About</a>
            </nav>

            <div class="hidden md:flex items-center gap-3">
                <a href="/student/login" class="text-sm font-medium text-white/80 hover:text-teal-300 transition-colors duration-200">Sign In</a>
                <a href="#portals" class="px-5 py-2.5 bg-teal-500 text-ink font-semibold text-sm rounded-lg hover:bg-teal-400 transition-all duration-200">Get Started</a>
            </div>

            <button id="menu-btn" class="md:hidden flex flex-col gap-1.5 p-2 text-white" aria-label="Open menu" aria-expanded="false">
                <span class="block w-6 h-0.5 bg-white rounded"></span>
                <span class="block w-6 h-0.5 bg-white rounded"></span>
                <span class="block w-6 h-0.5 bg-white rounded"></span>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="md:hidden fixed inset-0 top-20 backdrop-blur-xl flex flex-col items-center justify-center gap-8 px-6">
        <a href="#features" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">Features</a>
        <a href="#portals" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">Portals</a>
        <a href="#social-proof" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">About</a>
        <div class="flex flex-col gap-4 w-full max-w-xs mt-4">
            <a href="/student/login" class="text-center py-3.5 border border-white/30 rounded-lg text-white font-medium text-lg hover:border-teal-300 hover:text-teal-300 transition-colors duration-200">Sign In</a>
            <a href="#portals" class="text-center py-3.5 bg-teal-500 rounded-lg text-ink font-semibold text-lg hover:bg-teal-400 transition-colors duration-200">Get Started</a>
        </div>
    </div>
</header>