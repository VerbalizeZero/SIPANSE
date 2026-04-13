<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $role = auth()->user()?->role;
        $dashboardRoute = match ($role) {
            'tu' => 'tu.dashboard',
            'bendahara' => 'bendahara.dashboard',
            'orang_tua', 'ortu' => 'ortu.dashboard',
            default => 'dashboard',
        };
    @endphp

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/50 sm:hidden"
        style="display: none;"
    ></div>

    {{-- Sidebar mobile --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed top-0 left-0 bottom-0 z-50 w-64 bg-white shadow-xl sm:hidden"
        style="display: none;"
    >
        <div class="flex h-16 items-center justify-between border-b border-gray-100 px-4">
            <span class="text-lg font-semibold text-gray-800">Menu</span>
            <button @click="open = false" class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-1 px-2 py-3">
            <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (auth()->user()?->role === 'bendahara')
                <x-responsive-nav-link :href="route('bendahara.master-faktur.index')" :active="request()->routeIs('bendahara.master-faktur.*')">
                    {{ __('Membuat Faktur') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bendahara.arsip.index')" :active="request()->routeIs('bendahara.arsip.*')">
                    {{ __('Arsip Faktur') }}
                </x-responsive-nav-link>
            @endif

            @if (auth()->user()?->role === 'tu')
                <x-responsive-nav-link :href="route('tu.siswa.index')" :active="request()->routeIs('tu.siswa.*')">
                    {{ __('Data Siswa') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tu.kelas.index')" :active="request()->routeIs('tu.kelas.*')">
                    {{ __('Data Kelas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tu.faktur.index')" :active="request()->routeIs('tu.faktur.*')">
                    {{ __('Faktur') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tu.verifikasi.index')" :active="request()->routeIs('tu.verifikasi.*')">
                    {{ __('Verifikasi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tu.arsip.index')" :active="request()->routeIs('tu.arsip.*')">
                    {{ __('Arsip') }}
                </x-responsive-nav-link>
            @endif

            @if (in_array(auth()->user()?->role, ['orang_tua', 'ortu'], true))
                <x-responsive-nav-link :href="route('ortu.faktur.index')" :active="request()->routeIs('ortu.faktur.*')">
                    {{ __('Faktur') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t border-gray-200 px-4 py-4">
            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                @if (in_array(auth()->user()?->role, ['orang_tua', 'ortu'], true))
                    <form method="POST" action="{{ route('ortu.logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('ortu.logout')" class="text-rose-700"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                @else
                    @php
                        $profileRoute = auth()->user()?->role === 'tu' ? 'tu.profile.edit' : 'bendahara.profile.edit';
                    @endphp
                    <x-responsive-nav-link :href="route($profileRoute)">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Top navbar --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route($dashboardRoute) }}">
                        {{-- Logo aplikasi dapat diganti di components/application-logo.blade.php --}}
                        {{-- <x-application-logo class="block h-9 w-auto fill-current text-gray-800" /> --}}
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (auth()->user()?->role === 'bendahara')
                        <x-nav-link :href="route('bendahara.master-faktur.index')" :active="request()->routeIs('bendahara.master-faktur.*')">
                            {{ __('Membuat Faktur') }}
                        </x-nav-link>
                        <x-nav-link :href="route('bendahara.arsip.index')" :active="request()->routeIs('bendahara.arsip.*')">
                            {{ __('Arsip Faktur') }}
                        </x-nav-link>
                    @endif

                    @if (auth()->user()?->role === 'tu')
                        <x-nav-link :href="route('tu.siswa.index')" :active="request()->routeIs('tu.siswa.*')">
                            {{ __('Data Siswa') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tu.kelas.index')" :active="request()->routeIs('tu.kelas.*')">
                            {{ __('Data Kelas') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tu.faktur.index')" :active="request()->routeIs('tu.faktur.*')">
                            {{ __('Faktur') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tu.verifikasi.index')" :active="request()->routeIs('tu.verifikasi.*')">
                            {{ __('Verifikasi') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tu.arsip.index')" :active="request()->routeIs('tu.arsip.*')">
                            {{ __('Arsip') }}
                        </x-nav-link>
                    @endif

                    @if (in_array(auth()->user()?->role, ['orang_tua', 'ortu'], true))
                        <x-nav-link :href="route('ortu.faktur.index')" :active="request()->routeIs('ortu.faktur.*')">
                            {{ __('Faktur') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if (in_array(auth()->user()?->role, ['orang_tua', 'ortu'], true))
                    <div class="inline-flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('ortu.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-rose-300 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-100">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                @else
                    @php
                        $profileRoute = auth()->user()?->role === 'tu' ? 'tu.profile.edit' : 'bendahara.profile.edit';
                    @endphp
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route($profileRoute)">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endif
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>
