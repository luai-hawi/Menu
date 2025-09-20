<nav x-data="{ open: false, profileOpen: false }" class="nav-bar sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Hawi Tech" style="width: 90px; height: 90px; border-radius: 12px; background: transparent;">
                        <span class="ml-4 text-2xl font-bold text-white">Hawi Tech</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    
                    @if(auth()->check())
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.index') }}"
                               class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                {{ __('messages.admin_panel') }}
                            </a>
                        @endif
                        
                        @if(auth()->user()->isRestaurantOwner() || auth()->user()->restaurants()->exists())
                            <a href="{{ route('restaurant.dashboard') }}"
                               class="nav-link {{ request()->routeIs('restaurant.*') ? 'active' : '' }}">
                                {{ __('messages.restaurant_dashboard') }}
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="profile-button">
                            <div class="flex items-center">
                                <span class="text-gray-300 font-medium">{{ Auth::user()->name }}</span>
                                <svg class="ml-2 h-4 w-4 transition-transform duration-200" 
                                     :class="{ 'rotate-180': open }"
                                     xmlns="http://www.w3.org/2000/svg" 
                                     viewBox="0 0 20 20" 
                                     fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="dropdown-menu"
                             style="display: none;">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="fas fa-user mr-3"></i>
                                {{ __('messages.profile') }}
                            </a>

                            <!-- Language Switcher -->
                            <div class="dropdown-item">
                                <i class="fas fa-globe mr-3"></i>
                                {{ __('messages.language') }}:
                                <select id="language-select" class="ml-2 bg-transparent text-white border-none outline-none">
                                    <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>{{ __('messages.english') }}</option>
                                    <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>{{ __('messages.arabic') }}</option>
                                </select>
                            </div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item w-full text-left">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    {{ __('messages.log_out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="space-x-4">
                        <a href="{{ route('login') }}" class="nav-link">{{ __('messages.login') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">{{ __('messages.register') }}</a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="hamburger-button">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            
            @if(auth()->check())
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.index') }}"
                       class="mobile-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        {{ __('messages.admin_panel') }}
                    </a>
                @endif
                
                @if(auth()->user()->isRestaurantOwner() || auth()->user()->restaurants()->exists())
                    <a href="{{ route('restaurant.dashboard') }}"
                       class="mobile-nav-link {{ request()->routeIs('restaurant.*') ? 'active' : '' }}">
                        {{ __('messages.restaurant_dashboard') }}
                    </a>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <a href="{{ route('profile.edit') }}" class="mobile-nav-link">
                        <i class="fas fa-user mr-3"></i>
                        {{ __('messages.profile') }}
                    </a>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mobile-nav-link w-full text-left">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            {{ __('messages.log_out') }}
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>

    <style>
        .nav-bar {
            background: var(--bg-secondary) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid var(--border-primary) !important;
        }

        /* Remove any background from logo image */
        .nav-bar img {
            background: none !important;
            background-color: transparent !important;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            text-decoration: none !important;
            padding: 0.5rem 1rem !important;
            border-radius: var(--radius-md) !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            font-weight: 500 !important;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            background: var(--bg-elevated) !important;
        }

        .nav-link.active {
            color: var(--text-primary) !important;
            background: var(--bg-elevated) !important;
            border-bottom: 2px solid #667eea !important;
        }

        .profile-button {
            background: var(--bg-tertiary) !important;
            color: var(--text-secondary) !important;
            padding: 0.5rem 1rem !important;
            border-radius: var(--radius-md) !important;
            border: 1px solid var(--border-primary) !important;
            transition: all 0.3s ease !important;
            cursor: pointer !important;
        }

        .profile-button:hover {
            background: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-secondary) !important;
        }

        .dropdown-menu {
            position: absolute !important;
            right: 0 !important;
            top: 100% !important;
            margin-top: 0.5rem !important;
            width: 200px !important;
            background: var(--bg-card) !important;
            border: 1px solid var(--border-primary) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: var(--shadow-lg) !important;
            backdrop-filter: blur(20px) !important;
            z-index: 50 !important;
            overflow: hidden !important;
        }

        .dropdown-item {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            padding: 0.75rem 1rem !important;
            color: var(--text-secondary) !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            border: none !important;
            background: none !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            cursor: pointer !important;
        }

        .dropdown-item:hover {
            background: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
        }

        .hamburger-button {
            background: var(--bg-tertiary) !important;
            color: var(--text-secondary) !important;
            padding: 0.5rem !important;
            border-radius: var(--radius-md) !important;
            border: 1px solid var(--border-primary) !important;
            transition: all 0.3s ease !important;
        }

        .hamburger-button:hover {
            background: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
        }

        .mobile-menu {
            background: var(--bg-secondary) !important;
            border-top: 1px solid var(--border-primary) !important;
        }

        .mobile-nav-link {
            display: flex !important;
            align-items: center !important;
            padding: 1rem 1.5rem !important;
            color: var(--text-secondary) !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            border: none !important;
            background: none !important;
            width: 100% !important;
            text-align: left !important;
            font-weight: 500 !important;
        }

        .mobile-nav-link:hover {
            background: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
        }

        .mobile-nav-link.active {
            background: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
            border-left: 4px solid #667eea !important;
        }
    </style>

    <script>
        // Language switching functionality
        document.getElementById('language-select').addEventListener('change', function() {
            const selectedLang = this.value;

            // Set cookie to remember language preference
            document.cookie = "app_locale=" + selectedLang + "; path=/; max-age=31536000"; // 1 year

            // Reload the page to apply the new language
            window.location.reload();
        });

        // Set initial language from cookie or default
        document.addEventListener('DOMContentLoaded', function() {
            const cookies = document.cookie.split(';');
            let appLocale = 'ar'; // default

            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'app_locale') {
                    appLocale = value;
                    break;
                }
            }

            // Update the select element
            const select = document.getElementById('language-select');
            if (select) {
                select.value = appLocale;
            }
        });
    </script>
</nav>