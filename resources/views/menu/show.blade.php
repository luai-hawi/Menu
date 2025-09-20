<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $restaurant->name }} - Menu</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            scroll-behavior: smooth;
            padding-bottom: 100px;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%) !important;
            position: relative;
            overflow: hidden;
        }

        .header-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .restaurant-logo {
            width: 180px !important;
            height: 180px !important;
            object-fit: cover !important;
            border-radius: 50% !important;
            border: 4px solid var(--border-secondary) !important;
            box-shadow: var(--shadow-lg) !important;
            transition: all 0.4s ease !important;
        }

        .restaurant-logo-placeholder {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 4px solid var(--border-secondary);
            box-shadow: var(--shadow-lg);
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }

        .restaurant-logo:hover,
        .restaurant-logo-placeholder:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-xl);
        }

        .menu-item-card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-primary) !important;
            border-radius: var(--radius-xl) !important;
            overflow: hidden !important;
            box-shadow: var(--shadow-md) !important;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            backdrop-filter: blur(10px) !important;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .menu-item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gradient);
            z-index: 1;
        }

        .menu-item-card:hover {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: var(--shadow-xl), 0 0 40px rgba(102, 126, 234, 0.2) !important;
            border-color: var(--border-accent) !important;
        }

        .menu-image {
            width: 100% !important;
            height: 200px !important;
            object-fit: cover !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .menu-image:hover {
            transform: scale(1.05) !important;
        }

        .menu-image-placeholder {
            width: 100% !important;
            height: 200px !important;
            background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-elevated)) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .menu-item-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .menu-item-header {
            margin-bottom: 1rem;
        }

        .menu-item-title {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: var(--text-primary) !important;
            margin-bottom: 0.5rem !important;
            line-height: 1.3 !important;
        }

        .menu-item-description {
            color: var(--text-muted) !important;
            font-size: 0.95rem !important;
            line-height: 1.5 !important;
            margin-bottom: 1rem !important;
            flex-grow: 1;
        }

        .menu-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .price {
            font-size: 1.75rem !important;
            font-weight: 800 !important;
            background: var(--success-gradient) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            font-family: 'Inter', sans-serif !important;
            letter-spacing: -0.02em !important;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 12px;
            padding: 0.5rem;
            margin-left: 0.5rem;
            margin-right: 0.5rem;

        }

        .quantity-btn {
            background: var(--primary-gradient);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .quantity-input {
            min-width: 50px;
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            outline: none;
            margin-left: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .category-header {
            position: relative !important;
            padding-bottom: 1.5rem !important;
            margin-bottom: 3rem !important;
        }

        .category-header h2 {
            font-family: 'Playfair Display', serif !important;
            font-size: 3rem !important;
            font-weight: 700 !important;
            background: var(--primary-gradient) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            margin-bottom: 0.5rem !important;
        }

        .category-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 4px;
            background: var(--accent-gradient);
            border-radius: 2px;
        }

        .responsive-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
            gap: 2rem !important;
            margin-bottom: 2rem !important;
        }

        .order-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-card);
            border-top: 2px solid var(--border-secondary);
            padding: 1rem;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .order-summary.visible {
            transform: translateY(0);
        }

        .order-btn {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.visible {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-secondary);
            border-radius: var(--radius-xl);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.visible .modal-content {
            transform: scale(1);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-primary);
        }

        .modal-header h3 {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-primary);
            background: var(--bg-tertiary);
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-primary);
            color: var(--text-primary);
            padding: 1rem;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            border: 2px solid var(--border-primary);
        }

        .btn-secondary:hover {
            background: var(--bg-elevated);
            border-color: var(--border-secondary);
        }

        .btn-whatsapp {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
        }

        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }

        @media (max-width: 768px) {
            .responsive-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1rem !important;
            }
            
            .menu-item-card {
                font-size: 0.9rem;
            }
            
            .menu-item-content {
                padding: 1rem;
            }
            
            .menu-item-title {
                font-size: 1.2rem !important;
            }
            
            .price {
                font-size: 1.4rem !important;
            }
            
            .menu-image,
            .menu-image-placeholder {
                height: 150px !important;
            }
            
            .restaurant-logo,
            .restaurant-logo-placeholder {
                width: 140px !important;
                height: 140px !important;
            }

            .category-header h2 {
                font-size: 2.5rem !important;
            }

            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }

        @media (max-width: 480px) {
            .responsive-grid {
                gap: 0.75rem !important;
            }
            
            .menu-item-content {
                padding: 0.75rem;
            }
            
            .restaurant-logo,
            .restaurant-logo-placeholder {
                width: 120px !important;
                height: 120px !important;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .glass {
            background: rgba(37, 45, 86, 0.25) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.6;
        }

    </style>
</head>
<body style="background-color: #0a0e27; color: #e2e8f0;">
    <!-- Language Selector -->
    <div style="position: absolute; top: 16px; left: 16px; z-index: 100;">
        <div class="rounded-lg p-2 flex items-center space-x-2">
            <i class="fas fa-globe text-white"></i>
            <select id="language-select" class="bg-transparent text-white border-none outline-none text-sm">
                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
            </select>
        </div>
    </div>

    <!-- Company Logo - Top Right -->
    <div style="position: absolute; top: 16px; right: 16px; z-index: 100;">
        <div>
            <img src="{{ asset('images/logo.png') }}" alt="Hawi Tech" style="width: 90px; height: 90px; object-fit: contain;filter: brightness(0) invert(1);transition: all 0.3s ease;">
        </div>
    </div>

    <!-- Header -->
    <div class="header-gradient sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-10">
            <div class="text-center animate-fade-in-up">
                @if($restaurant->logo)
                    <img src="{{ asset('storage/' . $restaurant->logo) }}" 
                         alt="{{ $restaurant->name }}" 
                         class="restaurant-logo mx-auto mb-8">
                @else
                    <div class="restaurant-logo-placeholder mx-auto mb-8">
                        <span class="text-white text-5xl font-bold">{{ substr($restaurant->name, 0, 1) }}</span>
                    </div>
                @endif
                <h1 class="text-6xl md:text-7xl font-bold text-white mb-6 font-display">
                    {{ $restaurant->name }}
                </h1>
                @if($restaurant->description)
                    <p class="text-gray-300 text-xl md:text-2xl max-w-3xl mx-auto mb-8 leading-relaxed">
                        {{ $restaurant->description }}
                    </p>
                @endif
            </div>
        </div>
    </div>


    <!-- Menu Content -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        @if($restaurant->activeMenuCategories->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🍽️</div>
                <h2 class="text-4xl font-semibold text-gray-400 mb-6">{{ __('messages.menu_coming_soon') }}</h2>
                <p class="text-gray-500 text-xl">{{ __('messages.preparing_delicious') }}</p>
            </div>
        @else
            @foreach($restaurant->activeMenuCategories as $category)
                <div class="mb-20">
                    <div class="category-header animate-fade-in-up">
                        <h2>{{ $category->name }}</h2>
                    </div>
                    
                    <div class="responsive-grid">
                        @foreach($category->activeMenuItems as $item)
                            <div class="menu-item-card animate-fade-in-up" data-item-id="{{ $item->id }}">
                                <!-- Item Image -->
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" 
                                         alt="{{ $item->name }}" 
                                         class="menu-image">
                                @else
                                    <div class="menu-image-placeholder">
                                        <i class="fas fa-utensils text-white text-4xl opacity-50"></i>
                                    </div>
                                @endif
                                
                                <!-- Item Content -->
                                <div class="menu-item-content">
                                    <div class="menu-item-header">
                                        <h3 class="menu-item-title">{{ $item->name }}</h3>
                                        @if($item->description)
                                            <p class="menu-item-description">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="menu-item-footer">
                                        <div class="price">{{ __('messages.currency_symbol') }}{{ number_format($item->price, 2) }}</div>
                                        
                                        @if($restaurant->whatsapp_orders_enabled && $restaurant->whatsapp_number)
                                            <div class="quantity-control flex flex-col items-center">
                                                <button class="quantity-btn" onclick="changeQuantity({{ $item->id }}, 1)">+</button>
                                                <input type="number" class="quantity-input text-white w-full" value="0" min="0" max="999" 
                                                       id="qty-{{ $item->id }}" 
                                                       onchange="updateQuantity({{ $item->id }}, this.value)"
                                                       data-price="{{ $item->price }}"
                                                       data-name="{{ $item->name }}">
                                                <button class="quantity-btn" onclick="changeQuantity({{ $item->id }}, -1)">-</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Order Summary Bar (only show if WhatsApp ordering is enabled) -->
    @if($restaurant->whatsapp_orders_enabled && $restaurant->whatsapp_number)
        <div class="order-summary" id="orderSummary">
            <div class="max-w-md mx-auto">
                <button class="order-btn" onclick="openOrderModal()">
                    <i class="fab fa-whatsapp text-xl"></i>
                    <span>{{ __('messages.order_via_whatsapp') }}</span>
                    <span id="totalItems" class="bg-white bg-opacity-20 px-2 py-1 rounded-full text-sm">0 {{ __('messages.items') }}</span>
                </button>
            </div>
        </div>

        <!-- Order Modal -->
        <div class="modal" id="orderModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ __('messages.your_order') }}</h3>
                    <p class="text-gray-400">{{ __('messages.review_items') }}</p>
                </div>
                
                <div class="modal-body">
                    <div id="orderItems" class="mb-6"></div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.additional_notes') }}</label>
                        <textarea id="orderNotes" class="form-input" rows="3"
                                  placeholder="{{ __('messages.notes_placeholder') }}"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.your_location') }}</label>
                        <textarea id="orderLocation" class="form-input" rows="2"
                                  placeholder="{{ __('messages.location_placeholder') }}" required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">
                        <i class="fas fa-times"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" class="btn btn-whatsapp" onclick="sendWhatsAppOrder()">
                        <i class="fab fa-whatsapp"></i>
                        {{ __('messages.send_order') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <footer class="mt-24 glass border-t border-gray-700">
        <div class="max-w-6xl mx-auto px-4 py-16">
            <div class="text-center">
                <!-- Restaurant Info -->
                <h3 class="text-2xl font-bold text-white mb-4">{{ $restaurant->name }}</h3>
                <p class="text-gray-400 mb-8 text-lg">{{ __('messages.thank_you_visiting') }}</p>

                <!-- Company Info -->
                <div class="border-t border-gray-600 pt-8 mt-8">
                    <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-8">
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('images/logo.png') }}" alt="Hawi Tech" class="w-8 h-8 rounded">
                            <span class="text-white font-semibold">Hawi Tech</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fab fa-whatsapp text-green-400 text-xl"></i>
                            <a href="https://wa.me/970599647713" class="text-green-400 hover:text-green-300 transition-colors">
                                +970 599 647 713
                            </a>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm mt-6">
                     {{ __('messages.copyright', ['year' => date('Y'), 'restaurant' => 'Hawi Tech']) }}
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @if($restaurant->whatsapp_orders_enabled && $restaurant->whatsapp_number)
    <script>
        let cart = {};
        const whatsappNumber = "{{ $restaurant->whatsapp_number }}";
        const restaurantName = "{{ $restaurant->name }}";

        // Translation strings
        const translations = {
            newOrderFrom: "{{ __('messages.new_order_from', ['restaurant' => $restaurant->name]) }}",
            orderDetails: "{{ __('messages.order_details') }}",
            total: "{{ __('messages.total', ['total' => ':total']) }}",
            totalLabel: "{{ __('messages.total_label') }}",
            deliveryLocation: "{{ __('messages.delivery_location') }}",
            additionalNotesLabel: "{{ __('messages.additional_notes_label') }}",
            thankYou: "{{ __('messages.thank_you') }}",
            currencySymbol: "{{ __('messages.currency_symbol') }}"
        };

        function changeQuantity(itemId, change) {
            const input = document.getElementById(`qty-${itemId}`);
            const currentValue = parseInt(input.value) || 0;
            const newValue = Math.max(0, Math.min(99, currentValue + change));
            input.value = newValue;
            updateQuantity(itemId, newValue);
        }

        function updateQuantity(itemId, quantity) {
            const input = document.getElementById(`qty-${itemId}`);
            const price = parseFloat(input.dataset.price);
            const name = input.dataset.name;
            
            quantity = Math.max(0, Math.min(99, parseInt(quantity) || 0));
            input.value = quantity;
            
            if (quantity > 0) {
                cart[itemId] = {
                    name: name,
                    price: price,
                    quantity: quantity,
                    total: price * quantity
                };
            } else {
                delete cart[itemId];
            }
            
            updateOrderSummary();
        }

        function updateOrderSummary() {
            const totalItems = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
            const orderSummary = document.getElementById('orderSummary');
            const totalItemsSpan = document.getElementById('totalItems');
            
            if (totalItems > 0) {
                totalItemsSpan.textContent = `${totalItems} item${totalItems > 1 ? 's' : ''}`;
                orderSummary.classList.add('visible');
            } else {
                orderSummary.classList.remove('visible');
            }
        }

        function openOrderModal() {
            const orderItems = document.getElementById('orderItems');
            const modal = document.getElementById('orderModal');
            
            let html = '';
            let grandTotal = 0;
            
            Object.values(cart).forEach(item => {
                html += `
                    <div class="order-item">
                        <div>
                            <div class="font-semibold text-white">${item.name}</div>
                            <div class="text-gray-400 text-sm">${translations.currencySymbol}${item.price.toFixed(2)} x ${item.quantity}</div>
                        </div>
                        <div class="font-bold text-green-400">${translations.currencySymbol}${item.total.toFixed(2)}</div>
                    </div>
                `;
                grandTotal += item.total;
            });
            
            html += `
                <div class="order-item bg-gray-700 border-2 border-green-500">
                    <div class="font-bold text-white text-lg">${translations.totalLabel}</div>
                    <div class="font-bold text-green-400 text-xl">${translations.currencySymbol}${grandTotal.toFixed(2)}</div>
                </div>
            `;
            
            orderItems.innerHTML = html;
            modal.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }

        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            modal.classList.remove('visible');
            document.body.style.overflow = 'auto';
            
            // Clear form
            document.getElementById('orderNotes').value = '';
            document.getElementById('orderLocation').value = '';
        }

        function sendWhatsAppOrder() {
            const notes = document.getElementById('orderNotes').value;
            const location = document.getElementById('orderLocation').value;
            
            if (!location.trim()) {
                alert('Please provide your location for delivery.');
                return;
            }
            
            let message = `${translations.newOrderFrom}\n\n`;
            message += `${translations.orderDetails}\n`;
            
            let grandTotal = 0;
            Object.values(cart).forEach(item => {
                message += `• ${item.name} x${item.quantity} - ${item.total.toFixed(2)}\n`;
                grandTotal += item.total;
            });
            
            message += `\n${translations.total.replace(':total', grandTotal.toFixed(2))}\n\n`;
            message += `${translations.deliveryLocation}\n${location}\n\n`;

            if (notes.trim()) {
                message += `${translations.additionalNotesLabel}\n${notes}\n\n`;
            }

            message += `${translations.thankYou}`;
            
            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/${whatsappNumber.replace(/[^0-9]/g, '')}?text=${encodedMessage}`;
            
            // Open WhatsApp
            window.open(whatsappUrl, '_blank');
            
            // Close modal and reset cart
            closeOrderModal();
            
            // Reset all quantities
            Object.keys(cart).forEach(itemId => {
                document.getElementById(`qty-${itemId}`).value = 0;
            });
            cart = {};
            updateOrderSummary();
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });

        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.menu-item-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>

    <!-- Language switching functionality -->
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
    @endif
</body>
</html>