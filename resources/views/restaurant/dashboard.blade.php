<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('messages.restaurant_dashboard') }}
                </h2>
                @if($restaurant)
                    <p class="text-gray-400 mt-1">{{ $restaurant->name }}</p>
                @endif

                <!-- Restaurant Selector -->
                @if($restaurants && $restaurants->count() > 1)
                    <div class="mt-3">
                        <form method="POST" action="{{ route('restaurant.select') }}" class="inline">
                            @csrf
                            <label for="restaurant-select" class="text-sm text-gray-300 mr-2">{{ __('messages.select_restaurant') }}:</label>
                            <select name="restaurant_id" id="restaurant-select" onchange="this.form.submit()" class="bg-gray-700 text-white border border-gray-600 rounded px-3 py-1 text-sm">
                                @foreach($restaurants as $r)
                                    <option value="{{ $r->id }}" {{ $r->id === $restaurant->id ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif
            </div>
            @if($restaurant)
                <a href="{{ route('menu.show', $restaurant->slug) }}" target="_blank"
                   class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-1M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    {{ __('messages.view_public_menu') }}
                </a>
            @endif
        </div>
    </x-slot>

    @if($restaurant)
        <!-- WhatsApp Settings Card -->
        <div class="card mb-6 max-w-3xl mt-6 mx-auto">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-white">{{ __('messages.whatsapp_orders') }}</h3>
                    <p class="text-gray-400">{{ __('messages.allow_whatsapp_orders') }}</p>
                </div>
                <form action="{{ route('restaurant.whatsapp.toggle') }}" method="POST">
                    @csrf
                    <label class="switch">
                        <input type="checkbox" {{ $restaurant->whatsapp_orders_enabled ? 'checked' : '' }} 
                            onchange="this.form.submit()">
                        <span class="slider"></span>
                    </label>
                </form>
            </div>
            
            @if($restaurant->whatsapp_orders_enabled)
                <form action="{{ route('restaurant.whatsapp.update') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.whatsapp_number') }}</label>
                        <input type="text" name="whatsapp_number"
                            value="{{ $restaurant->whatsapp_number }}"
                            placeholder="{{ __('messages.whatsapp_example') }}"
                            class="form-input" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        {{ __('messages.save_whatsapp_number') }}
                    </button>
                </form>
            @endif
        </div>

        <!-- Rest of existing content... -->
    @endif

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!$restaurant)
                <!-- Setup Restaurant -->
                <div class="card text-center">
                    <div class="text-6xl mb-4">🏪</div>
                    <h3 class="text-2xl font-bold text-white mb-4">{{ __('messages.setup_your_restaurant') }}</h3>
                    <p class="text-gray-300 mb-6">{{ __('messages.create_restaurant_profile') }}</p>
                    <a href="{{ route('restaurant.create') }}" class="btn btn-success">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Restaurant
                    </a>
                </div>
            @else
                <!-- Quick Actions -->
                <div class="responsive-grid mb-8">
                    <!-- Add Category Card -->
                    <div class="quick-action-card">
                        <div class="flex items-center mb-4">
                            <div class="icon-wrapper icon-blue">
                                <i class="fas fa-tags text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">{{ __('messages.add_new_category') }}</h3>
                        </div>
                        <form action="{{ route('category.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <input type="text" name="name" placeholder="{{ __('messages.category_name_placeholder') }}"
                                       class="form-input" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-plus w-4 h-4"></i>
                                {{ __('messages.add_category_btn') }}
                            </button>
                        </form>
                    </div>

                    <!-- Add Menu Item Card -->
                    <div class="quick-action-card">
                        <div class="flex items-center mb-4">
                            <div class="icon-wrapper icon-green">
                                <i class="fas fa-plus text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">{{ __('messages.add_new_menu_item') }}</h3>
                        </div>
                        <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-4">
                                <div class="form-group">
                                    <select name="category_id" class="form-input" required>
                                        <option value="">{{ __('messages.select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="{{ __('messages.item_name') }}" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <textarea name="description" placeholder="{{ __('messages.description_optional') }}"
                                              class="form-input" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <input type="number" step="0.01" name="price" placeholder="{{ __('messages.price') }}"
                                           class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.item_image_optional') }}</label>
                                    <input type="file" name="image" accept="image/*" class="form-input">
                                </div>
                                <button type="submit" class="btn btn-success w-full">
                                    <i class="fas fa-plus w-4 h-4"></i>
                                    {{ __('messages.add_menu_item_btn') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Menu Categories and Items -->
                @if($categories->isEmpty())
                    <div class="card text-center">
                        <div class="text-6xl mb-4">📋</div>
                        <h3 class="text-xl font-semibold text-white mb-2">{{ __('messages.no_categories_yet') }}</h3>
                        <p class="text-gray-400">{{ __('messages.start_adding_categories') }}</p>
                    </div>
                @else
                    @foreach($categories as $category)
                        <div class="card mb-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-white">{{ $category->name }}</h3>
                                    <p class="text-gray-400">{{ $category->menuItems->count() }} items</p>
                                </div>
                                <form action="{{ route('category.delete', $category) }}" method="POST" class="mt-4 sm:mt-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('{{ __('messages.delete_category_confirm') }}')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        {{__('messages.Delete Category')}}
                                    </button>
                                </form>
                            </div>

                            @if($category->menuItems->isEmpty())
                                <div class="text-center py-8 bg-gray-800 rounded-lg">
                                    <div class="text-4xl mb-2">🍽️</div>
                                    <p class="text-gray-400">{{ __('messages.no_items_in_category') }}</p>
                                </div>
                            @else
                                <div class="responsive-grid">
                                    @foreach($category->menuItems as $item)
                                        <div class="menu-item-card">
                                            <div class="flex justify-between items-start mb-3">
                                                <h4 class="font-semibold text-white text-lg">{{ $item->name }}</h4>
                                                <div class="flex space-x-2">
                                                    <button onclick="editItem({{ $item->id }})" 
                                                            class="text-blue-400 hover:text-blue-300 p-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <form action="{{ route('item.delete', $item) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-400 hover:text-red-300 p-1" 
                                                                onclick="return confirm('{{ __('messages.delete_item_confirm') }}')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col sm:flex-row gap-4">
                                                <div class="flex-1">
                                                    @if($item->description)
                                                        <p class="text-gray-300 text-sm mb-3">{{ $item->description }}</p>
                                                    @endif
                                                    <div class="price text-xl">{{ __('messages.currency_symbol') }}{{ number_format($item->price, 2) }}</div>
                                                </div>
                                                @if($item->image)
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ asset('storage/' . $item->image) }}" 
                                                             alt="{{ $item->name }}" 
                                                             class="menu-image">
                                                    </div>
                                                @else
                                                    <div class="flex-shrink-0">
                                                        <div class="menu-image bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center">
                                                            <i class="fas fa-utensils text-white text-2xl opacity-50"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            @endif
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editModal" class="fixed inset-0 modal hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-white">{{ __('messages.edit_menu_item') }}</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.item_name') }}</label>
                            <input type="text" id="editName" name="name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.restaurant_description') }}</label>
                            <textarea id="editDescription" name="description" class="form-input" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.price') }}</label>
                            <input type="number" step="0.01" id="editPrice" name="price" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.update_image_optional') }}</label>
                            <input type="file" name="image" accept="image/*" class="form-input">
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" class="btn btn-primary flex-1">
                                {{ __('messages.update_item') }}
                            </button>
                            <button type="button" onclick="closeEditModal()" class="btn bg-gray-600 hover:bg-gray-700 text-white">
                                {{ __('messages.cancel') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const items = @json($categories->flatMap(fn($cat) => $cat->menuItems));
        
        function editItem(itemId) {
            const item = items.find(i => i.id === itemId);
            if (item) {
                document.getElementById('editName').value = item.name;
                document.getElementById('editDescription').value = item.description || '';
                document.getElementById('editPrice').value = item.price;
                document.getElementById('editForm').action = `/menu-item/${itemId}`;
                document.getElementById('editModal').classList.remove('hidden');
            }
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            
            --bg-primary: #0a0e27;
            --bg-secondary: #141b3c;
            --bg-tertiary: #1e2749;
            --bg-card: #252d56;
            --bg-elevated: #2a3365;
            
            --text-primary: #ffffff;
            --text-secondary: #e2e8f0;
            --text-muted: #94a3b8;
            
            --border-primary: #334155;
            --border-secondary: #475569;
            --border-accent: #64748b;
            
            --shadow-md: 0 8px 25px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 25px 50px rgba(0, 0, 0, 0.25);
            
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        .card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-primary) !important;
            border-radius: var(--radius-xl) !important;
            padding: 2rem !important;
            box-shadow: var(--shadow-md) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--primary-gradient);
            opacity: 0.6;
        }

        .card:hover {
            transform: translateY(-8px) !important;
            box-shadow: var(--shadow-xl) !important;
            border-color: var(--border-secondary) !important;
        }

        .quick-action-card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-primary) !important;
            border-radius: var(--radius-lg) !important;
            padding: 1.5rem !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .quick-action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gradient);
        }

        .quick-action-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: var(--shadow-lg) !important;
            border-color: var(--border-secondary) !important;
        }

        .menu-item-card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-primary) !important;
            border-radius: var(--radius-lg) !important;
            padding: 1.5rem !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .menu-item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--success-gradient);
        }

        .menu-item-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: var(--shadow-lg) !important;
            border-color: var(--border-secondary) !important;
            background: var(--bg-elevated) !important;
        }

        .menu-image {
            width: 80px !important;
            height: 80px !important;
            object-fit: cover !important;
            border-radius: var(--radius-lg) !important;
            border: 2px solid var(--border-secondary) !important;
            transition: all 0.3s ease !important;
            box-shadow: var(--shadow-md) !important;
        }

        .menu-image:hover {
            transform: scale(1.05) !important;
            border-color: var(--border-accent) !important;
        }

        .btn {
            padding: 0.875rem 1.5rem !important;
            border-radius: var(--radius-lg) !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: none !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.5rem !important;
            text-decoration: none !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-gradient) !important;
            color: white !important;
            box-shadow: var(--shadow-md) !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-lg) !important;
        }

        .btn-success {
            background: var(--success-gradient) !important;
            color: white !important;
            box-shadow: var(--shadow-md) !important;
        }

        .btn-success:hover {
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-lg) !important;
        }

        .btn-danger {
            background: var(--warning-gradient) !important;
            color: white !important;
            box-shadow: var(--shadow-md) !important;
        }

        .btn-danger:hover {
            transform: translateY(-2px) !important;
            box-shadow: var(--shadow-lg) !important;
        }

        .form-input {
            background: var(--bg-tertiary) !important;
            border: 2px solid var(--border-primary) !important;
            color: var(--text-primary) !important;
            padding: 0.875rem 1rem !important;
            border-radius: var(--radius-lg) !important;
            transition: all 0.3s ease !important;
            font-size: 0.95rem !important;
            font-weight: 500 !important;
            width: 100% !important;
        }

        .form-input:focus {
            background: var(--bg-elevated) !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
            outline: none !important;
            transform: translateY(-1px) !important;
        }

        .form-input::placeholder {
            color: var(--text-muted) !important;
            font-weight: 400 !important;
        }

        .price {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            background: var(--success-gradient) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
        }

        .icon-wrapper {
            width: 2.5rem !important;
            height: 2.5rem !important;
            border-radius: var(--radius-lg) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-right: 0.75rem !important;
        }

        .icon-blue {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        }

        .icon-green {
            background: var(--success-gradient) !important;
        }

        .responsive-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 1.5rem !important;
            margin-bottom: 2rem !important;
        }

        .modal {
            background: rgba(10, 14, 39, 0.8) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
        }

        .modal-content {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-secondary) !important;
            border-radius: var(--radius-xl) !important;
            box-shadow: var(--shadow-xl) !important;
            backdrop-filter: blur(20px) !important;
        }

        .alert {
            padding: 1.25rem 1.5rem !important;
            border-radius: var(--radius-lg) !important;
            margin-bottom: 1.5rem !important;
            border-left: 4px solid !important;
            backdrop-filter: blur(10px) !important;
            font-weight: 500 !important;
        }

        .alert-success {
            background: rgba(67, 233, 123, 0.1) !important;
            border-left-color: #43e97b !important;
            color: #86efac !important;
        }

        .alert-error {
            background: rgba(245, 87, 108, 0.1) !important;
            border-left-color: #f5576c !important;
            color: #fca5a5 !important;
        }

        .form-label {
            display: block !important;
            margin-bottom: 8px !important;
            font-weight: 600 !important;
            color: #e2e8f0 !important;
        }

        .form-group {
            margin-bottom: 20px !important;
        }

        @media (max-width: 768px) {
            .responsive-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .card {
                padding: 1.5rem !important;
            }
            
            .quick-action-card {
                padding: 1.25rem !important;
            }
        }
    </style>
</x-app-layout>