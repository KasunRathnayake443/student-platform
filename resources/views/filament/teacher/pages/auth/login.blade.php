<x-filament-panels::page.simple>
    <div class="relative overflow-hidden w-full max-w-md mx-auto">
        <!-- Abstract Background Decoration -->
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-gradient-to-br from-indigo-100 to-emerald-50 blur-3xl opacity-60"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-gradient-to-br from-emerald-50 to-indigo-100 blur-3xl opacity-60"></div>
        
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 p-8 sm:p-10 relative z-10 transition-all">
            
            <!-- Logo / Brand -->
            <div class="flex flex-col items-center justify-center space-y-3 mb-10">
                <div class="h-14 w-14 bg-gradient-to-br from-indigo-500 to-emerald-400 rounded-2xl shadow-lg flex items-center justify-center text-white text-2xl font-bold tracking-tighter">
                    SP
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Teacher Portal</h1>
                <p class="text-sm text-gray-500 font-medium">Sign in to manage your classes</p>
            </div>

            <!-- Form -->
            <form wire:submit="authenticate">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit" class="w-full" size="lg">
                        Sign in
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page.simple>
