<x-app-layout>
    <x-slot name="header">
        <div class="dash-header">
            <div class="dash-header-main">
                <h2 class="dash-title">
                    <i class="fas fa-store"></i>
                    {{ __('messages.restaurant_dashboard') }}
                </h2>
                @if ($restaurant)
                    <p class="dash-subtitle">{{ $restaurant->name }}</p>
                @endif
            </div>

            @if ($restaurants && $restaurants->count() > 1)
                <form method="POST" action="{{ route('restaurant.select') }}" class="dash-restaurant-switcher" data-ajax
                    data-ajax-reload>
                    @csrf
                    <label for="restaurant-select" class="sr-only">{{ __('messages.select_restaurant') }}</label>
                    <select name="restaurant_id" id="restaurant-select" onchange="this.form.requestSubmit()">
                        @foreach ($restaurants as $r)
                            <option value="{{ $r->id }}" {{ $r->id === $restaurant->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            @if ($restaurant)
                <a href="{{ route('menu.show', $restaurant->slug) }}" target="_blank" class="dash-view-menu">
                    <i class="fas fa-external-link-alt"></i>
                    <span>{{ __('messages.view_public_menu') }}</span>
                </a>
            @endif
        </div>
    </x-slot>

    @if (!$restaurant)
        <div class="dash-wrap">
            <div class="dash-setup-card">
                <div class="dash-setup-icon">🏪</div>
                <h3>{{ __('messages.setup_your_restaurant') }}</h3>
                <p>{{ __('messages.create_restaurant_profile') }}</p>
                <a href="{{ route('restaurant.create') }}" class="dash-btn dash-btn-success">
                    <i class="fas fa-plus"></i>
                    {{ __('messages.create_restaurant') }}
                </a>
            </div>
        </div>
    @else
        <div class="dash-wrap" x-data="{ tab: localStorage.getItem('dashTab') || 'menu' }" x-init="$watch('tab', v => localStorage.setItem('dashTab', v))">

            {{-- ==================== TAB BAR ==================== --}}
            <nav class="dash-tabs" role="tablist" aria-label="{{ __('messages.restaurant_dashboard') }}">
                <button type="button" role="tab" class="dash-tab"
                    :class="{ 'dash-tab-active': tab === 'menu' }" @click="tab = 'menu'">
                    <i class="fas fa-utensils"></i>
                    <span>{{ __('messages.products.menu_settings') }}</span>
                </button>
                <button type="button" role="tab" class="dash-tab"
                    :class="{ 'dash-tab-active': tab === 'profile' }" @click="tab = 'profile'">
                    <i class="fas fa-id-card"></i>
                    <span>{{ __('messages.restaurant_profile') }}</span>
                </button>
                <button type="button" role="tab" class="dash-tab"
                    :class="{ 'dash-tab-active': tab === 'whatsapp' }" @click="tab = 'whatsapp'">
                    <i class="fab fa-whatsapp"></i>
                    <span>{{ __('messages.whatsapp_orders') }}</span>
                </button>
                <button type="button" role="tab" class="dash-tab"
                    :class="{ 'dash-tab-active': tab === 'theme' }" @click="tab = 'theme'">
                    <i class="fas fa-palette"></i>
                    <span>{{ __('messages.theme_colors') }}</span>
                </button>
            </nav>

            {{-- ==================== MENU TAB ==================== --}}
            <section x-show="tab === 'menu'" x-cloak role="tabpanel" class="dash-tab-panel">

                {{-- Add category + Add item — mobile-friendly 2-column, stacks on mobile --}}
                <div class="dash-grid-two">
                    <div class="dash-card">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-tags"></i></div>
                            <h3>{{ __('messages.add_new_category') }}</h3>
                        </header>
                        <form action="{{ route('category.store') }}" method="POST" data-ajax data-ajax-reload>
                            @csrf
                            <div class="dash-field">
                                <input type="text" name="name" required
                                    placeholder="{{ __('messages.category_name_placeholder') }}" class="dash-input">
                            </div>
                            <button type="submit" class="dash-btn dash-btn-primary dash-btn-block">
                                <i class="fas fa-plus"></i>
                                <span>{{ __('messages.add_category_btn') }}</span>
                            </button>
                        </form>
                    </div>

                    <div class="dash-card">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-green"><i class="fas fa-plus"></i></div>
                            <h3>{{ __('messages.add_new_menu_item') }}</h3>
                        </header>

                        <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data" data-ajax
                            data-ajax-reload x-data="{ showAdvanced: false }">
                            @csrf

                            <div class="dash-field">
                                <label class="dash-label">{{ __('messages.select_category') }}</label>
                                <select name="category_id" class="dash-input" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dash-field">
                                <label class="dash-label">{{ __('messages.item_name') }}</label>
                                <input type="text" name="name" class="dash-input" required dir="rtl">
                            </div>

                            <div class="dash-field-row">
                                <div class="dash-field" style="flex:2">
                                    <label class="dash-label">{{ __('messages.price') }}</label>
                                    <input type="number" step="0.01" min="0" name="price"
                                        class="dash-input" required placeholder="0.00">
                                </div>
                                <div class="dash-field" style="flex:3">
                                    <label class="dash-label">{{ __('messages.item_image_optional') }}</label>
                                    <input type="file" name="image" accept="image/*"
                                        class="dash-input dash-input-file">
                                </div>
                            </div>

                            <div class="dash-field">
                                <label class="dash-label">{{ __('messages.description_optional') }}</label>
                                <textarea name="description" class="dash-input" rows="2" dir="rtl"></textarea>
                            </div>

                            <button type="button" class="dash-collapse-toggle" @click="showAdvanced = !showAdvanced"
                                :aria-expanded="showAdvanced.toString()">
                                <i class="fas" :class="showAdvanced ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                <span
                                    x-text="showAdvanced ? '{{ __('messages.products.hide_options') }}' : '{{ __('messages.products.show_options') }}'"></span>
                            </button>

                            <div x-show="showAdvanced" x-transition x-cloak class="dash-collapsible">
                                @include('restaurant.partials.option-groups-editor', ['groups' => []])
                            </div>

                            <button type="submit" class="dash-btn dash-btn-success dash-btn-block">
                                <i class="fas fa-plus"></i>
                                <span>{{ __('messages.add_menu_item_btn') }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Categories & items list --}}
                @if ($categories->isEmpty())
                    <div class="dash-empty">
                        <div class="dash-empty-icon">📋</div>
                        <h3>{{ __('messages.no_categories_yet') }}</h3>
                        <p>{{ __('messages.start_adding_categories') }}</p>
                    </div>
                @else
                    <div class="dash-categories">
                        @foreach ($categories as $category)
                            <details class="dash-category" open>
                                <summary class="dash-category-head">
                                    <div class="dash-category-title">
                                        <i class="fas fa-folder-open"></i>
                                        <span class="dash-category-name">{{ $category->name }}</span>
                                        <span class="dash-count-badge">
                                            {{ __('messages.products.items_count', ['count' => $category->menuItems->count()]) }}
                                        </span>
                                    </div>
                                    <button type="button" class="dash-btn dash-btn-danger dash-btn-sm"
                                        data-ajax-action data-url="{{ route('category.delete', $category) }}"
                                        data-method="DELETE"
                                        data-confirm="{{ __('messages.delete_category_confirm') }}"
                                        data-on-success-reload>
                                        <i class="fas fa-trash"></i>
                                        <span>{{ __('messages.delete_category') }}</span>
                                    </button>
                                </summary>

                                @if ($category->menuItems->isEmpty())
                                    <div class="dash-empty-sm">
                                        <span>🍽️</span>
                                        <p>{{ __('messages.products.no_items_hint') }}</p>
                                    </div>
                                @else
                                    <div class="dash-items-grid">
                                        @foreach ($category->menuItems as $item)
                                            @php
                                                $itemGroupsPayload = $item->optionGroups
                                                    ->map(function ($g) {
                                                        return [
                                                            'id' => $g->id,
                                                            'group_type' => $g->group_type,
                                                            'group_name_ar' => $g->group_name_ar,
                                                            'min_choices' => $g->min_choices,
                                                            'max_choices' => $g->max_choices,
                                                            'is_required' => (bool) $g->is_required,
                                                            'position' => $g->position,
                                                            'options' => $g->options
                                                                ->map(
                                                                    fn($o) => [
                                                                        'id' => $o->id,
                                                                        'option_name_ar' => $o->option_name_ar,
                                                                        'price_delta' => (float) $o->price_delta,
                                                                        'option_note_ar' => $o->option_note_ar,
                                                                        'position' => $o->position,
                                                                        'is_active' => (bool) $o->is_active,
                                                                    ],
                                                                )
                                                                ->values()
                                                                ->all(),
                                                        ];
                                                    })
                                                    ->values()
                                                    ->all();
                                            @endphp

                                            <article class="dash-item" x-data="{ editing: false }" data-item-row>
                                                <div class="dash-item-media">
                                                    @if ($item->image)
                                                        <img src="{{ asset('storage/' . $item->image) }}"
                                                            alt="{{ $item->name }}">
                                                    @else
                                                        <div class="dash-item-placeholder"><i
                                                                class="fas fa-utensils"></i></div>
                                                    @endif
                                                </div>
                                                <div class="dash-item-body">
                                                    <h4 class="dash-item-title">{{ $item->name }}</h4>
                                                    @if ($item->description)
                                                        <p class="dash-item-desc">{{ $item->description }}</p>
                                                    @endif
                                                    <div class="dash-item-price">
                                                        {{ __('messages.currency_symbol') }}{{ number_format($item->price, 2) }}
                                                    </div>

                                                    @if ($item->optionGroups->count() > 0)
                                                        <div class="dash-item-groups">
                                                            @foreach ($item->optionGroups as $g)
                                                                <span class="dash-group-badge"
                                                                    title="{{ $g->options->count() }} {{ __('messages.options.title') }}">
                                                                    <i
                                                                        class="fas fa-{{ $g->group_type === 'SINGLE' ? 'dot-circle' : 'check-square' }}"></i>
                                                                    {{ $g->group_name_ar }}
                                                                    <small>({{ $g->options->count() }})</small>
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="dash-item-actions">
                                                    <button type="button"
                                                        @click.stop="
                                                                document.getElementById('editItemForm').action = `{{ route('item.update', $item) }}`;
                                                                window.dispatchEvent(new CustomEvent('edititem', {
                                                                    detail: {{ json_encode(['id' => $item->id, 'name' => $item->name, 'price' => $item->price, 'description' => $item->description ?? '', 'image' => $item->image ?? '', 'category_id' => $item->category_id ?? null, 'optionGroups' => $itemGroupsPayload]) }}
                                                                }))
                                                            "
                                                        class="dash-btn-icon dash-btn-icon-blue"
                                                        :title="'{{ __('messages.edit_menu_item') }}'">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="dash-btn-icon dash-btn-icon-red"
                                                        data-ajax-action data-url="{{ route('item.delete', $item) }}"
                                                        data-method="DELETE"
                                                        data-confirm="{{ __('messages.delete_item_confirm') }}"
                                                        data-on-success-remove="[data-item-row]"
                                                        :title="'{{ __('messages.delete_item') }}'">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </details>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Global Edit Modal --}}
            <div x-data="{ showEditModal: false, currentItem: { id: null, name: '', price: 0, description: '', image: '', category_id: null, optionGroups: [] } }" @edititem.window="showEditModal = true; currentItem = $event.detail">
                <div x-show="showEditModal" x-cloak @keydown.escape.window="showEditModal = false" class="dash-modal"
                    @click="showEditModal = false">
                    <div class="dash-modal-card" @click.stop>
                        <header class="dash-modal-head">
                            <h3>{{ __('messages.edit_menu_item') }}</h3>
                            <button type="button" @click="showEditModal = false" class="dash-btn-icon">
                                <i class="fas fa-times"></i>
                            </button>
                        </header>

                        <form id="editItemForm" method="POST" enctype="multipart/form-data" data-ajax
                            data-ajax-reload>
                            @csrf
                            @method('PUT')
                            <div class="dash-modal-body">
                                <div class="dash-field">
                                    <label class="dash-label">{{ __('messages.item_name') }}</label>
                                    <input type="text" name="name" x-model="currentItem.name"
                                        class="dash-input" required dir="rtl">
                                </div>
                                <div class="dash-field">
                                    <label class="dash-label">{{ __('messages.description_optional') }}</label>
                                    <textarea name="description" x-model="currentItem.description" class="dash-input" rows="2" dir="rtl"></textarea>
                                </div>
                                <div class="dash-field-row">
                                    <div class="dash-field" style="flex:2">
                                        <label class="dash-label">{{ __('messages.price') }}</label>
                                        <input type="number" step="0.01" min="0" name="price"
                                            x-model="currentItem.price" class="dash-input" required>
                                    </div>
                                    <div class="dash-field" style="flex:3">
                                        <label class="dash-label">{{ __('messages.update_image_optional') }}</label>
                                        <input type="file" name="image" accept="image/*"
                                            class="dash-input dash-input-file">
                                    </div>
                                </div>

                                {{-- Option Groups Editor --}}
                                @include('restaurant.partials.option-groups-editor', ['groups' => []])
                            </div>
                            <footer class="dash-modal-foot">
                                <button type="button" @click="showEditModal = false"
                                    class="dash-btn dash-btn-ghost">
                                    {{ __('messages.cancel') }}
                                </button>
                                <button type="submit" class="dash-btn dash-btn-primary">
                                    <i class="fas fa-save"></i>
                                    <span>{{ __('messages.update_item') }}</span>
                                </button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ==================== PROFILE TAB ==================== --}}
            <section x-show="tab === 'profile'" x-cloak role="tabpanel" class="dash-tab-panel">
                <div class="dash-card dash-card-wide">
                    <header class="dash-card-header">
                        <div class="dash-card-icon dash-icon-purple"><i class="fas fa-id-card"></i></div>
                        <h3>{{ __('messages.restaurant_profile') }}</h3>
                    </header>

                    <form action="{{ route('restaurant.update.profile') }}" method="POST"
                        enctype="multipart/form-data" data-ajax>
                        @csrf

                        <div class="dash-field">
                            <label class="dash-label">{{ __('messages.restaurant_name') }}</label>
                            <input type="text" name="name" value="{{ $restaurant->name }}" class="dash-input"
                                required>
                            <small class="dash-help">{{ __('messages.restaurant_name_help') }}</small>
                        </div>

                        <div class="dash-field">
                            <label class="dash-label">{{ __('messages.restaurant_description') }}</label>
                            <textarea name="description" rows="3" class="dash-input"
                                placeholder="{{ __('messages.restaurant_description_placeholder') }}">{{ $restaurant->description }}</textarea>
                            <small class="dash-help">{{ __('messages.restaurant_description_help') }}</small>
                        </div>

                        <div class="dash-field">
                            <label class="dash-label">{{ __('messages.restaurant_logo') }}</label>
                            <input type="file" name="logo" accept="image/*"
                                class="dash-input dash-input-file">
                            <small class="dash-help">{{ __('messages.logo_upload_help') }}</small>
                            @if ($restaurant->logo)
                                <div class="dash-current-image">
                                    <p>{{ __('messages.current_logo') }}:</p>
                                    <img src="{{ asset('storage/' . $restaurant->logo) }}" alt=""
                                        class="dash-logo-preview">
                                    <label class="dash-toggle">
                                        <input type="checkbox" name="remove_logo" value="1">
                                        <span>{{ __('messages.remove_logo') }}</span>
                                    </label>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="dash-btn dash-btn-primary">
                            <i class="fas fa-save"></i>
                            <span>{{ __('messages.save_profile') }}</span>
                        </button>
                    </form>
                </div>

                {{-- Background image (separate form because it's optional & heavier) --}}
                <div class="dash-card dash-card-wide">
                    <header class="dash-card-header">
                        <div class="dash-card-icon dash-icon-blue"><i class="fas fa-image"></i></div>
                        <h3>{{ __('messages.restaurant_header') }}</h3>
                    </header>
                    <form action="{{ route('restaurant.update.settings') }}" method="POST"
                        enctype="multipart/form-data" data-ajax>
                        @csrf
                        <div class="dash-field">
                            <label class="dash-label">{{ __('messages.background_image_optional') }}</label>
                            <input type="file" name="background_image" accept="image/*"
                                class="dash-input dash-input-file">
                            <small class="dash-help">{{ __('messages.background_image_help') }}</small>
                            @if ($restaurant->background_image)
                                <div class="dash-current-image">
                                    <p>{{ __('messages.current_background') }}:</p>
                                    <img src="{{ asset('storage/' . $restaurant->background_image) }}" alt=""
                                        class="dash-bg-preview">
                                    <label class="dash-toggle">
                                        <input type="checkbox" name="remove_background" value="1">
                                        <span>{{ __('messages.remove_background') }}</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                        <button type="submit" class="dash-btn dash-btn-primary">
                            <i class="fas fa-save"></i>
                            <span>{{ __('messages.save_background_image') }}</span>
                        </button>
                    </form>
                </div>

                {{-- Social links --}}
                <div class="dash-card dash-card-wide">
                    <header class="dash-card-header">
                        <div class="dash-card-icon dash-icon-pink"><i class="fas fa-share-alt"></i></div>
                        <h3>{{ __('messages.social_media_links') }}</h3>
                    </header>
                    <form action="{{ route('restaurant.update.settings') }}" method="POST" data-ajax>
                        @csrf
                        <div class="dash-social-grid">
                            @foreach ([['facebook_url', 'fab fa-facebook text-blue-500', 'Facebook', 'https://facebook.com/yourpage'], ['instagram_url', 'fab fa-instagram text-pink-500', 'Instagram', 'https://instagram.com/yourprofile'], ['snapchat_url', 'fab fa-snapchat text-yellow-400', 'Snapchat', 'https://snapchat.com/add/username'], ['twitter_url', 'fab fa-twitter text-blue-400', 'Twitter', 'https://twitter.com/username'], ['tiktok_url', 'fab fa-tiktok', 'TikTok', 'https://tiktok.com/@username'], ['whatsapp_url', 'fab fa-whatsapp text-green-500', 'WhatsApp Business', 'https://wa.me/1234567890']] as [$name, $icon, $label, $ph])
                                <div class="dash-field">
                                    <label class="dash-label"><i class="{{ $icon }}"></i>
                                        {{ $label }}</label>
                                    <input type="url" name="{{ $name }}"
                                        value="{{ $restaurant->$name }}" placeholder="{{ $ph }}"
                                        class="dash-input">
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="dash-btn dash-btn-primary">
                            <i class="fas fa-save"></i>
                            <span>{{ __('messages.save_settings') }}</span>
                        </button>
                    </form>
                </div>
            </section>

            {{-- ==================== WHATSAPP TAB ==================== --}}
            <section x-show="tab === 'whatsapp'" x-cloak role="tabpanel" class="dash-tab-panel">
                <div class="dash-card dash-card-wide" x-data="{ waEnabled: {{ $restaurant->whatsapp_orders_enabled ? 'true' : 'false' }} }">
                    <header class="dash-card-header">
                        <div class="dash-card-icon dash-icon-green"><i class="fab fa-whatsapp"></i></div>
                        <h3>{{ __('messages.whatsapp_orders') }}</h3>
                    </header>

                    <form action="{{ route('restaurant.whatsapp.toggle') }}" method="POST" data-ajax
                        class="dash-inline-form">
                        @csrf
                        <div>
                            <p class="dash-label">{{ __('messages.whatsapp_orders') }}</p>
                            <small class="dash-help">{{ __('messages.allow_whatsapp_orders') }}</small>
                        </div>
                        <label class="dash-switch">
                            <input type="checkbox" {{ $restaurant->whatsapp_orders_enabled ? 'checked' : '' }}
                                @change="waEnabled = $event.target.checked; $el.form.requestSubmit()">
                            <span class="dash-switch-slider"></span>
                        </label>
                    </form>

                    <form x-show="waEnabled" action="{{ route('restaurant.whatsapp.update') }}" method="POST"
                        data-ajax class="dash-mt">
                        @csrf
                        <div class="dash-field">
                            <label class="dash-label">{{ __('messages.whatsapp_number') }}</label>
                            <input type="text" name="whatsapp_number" value="{{ $restaurant->whatsapp_number }}"
                                placeholder="{{ __('messages.whatsapp_example') }}" class="dash-input" required>
                        </div>
                        <button type="submit" class="dash-btn dash-btn-primary">
                            <i class="fas fa-save"></i>
                            <span>{{ __('messages.save_whatsapp_number') }}</span>
                        </button>
                    </form>
                </div>
            </section>

            {{-- ==================== THEME TAB ==================== --}}
            <section x-show="tab === 'theme'" x-cloak role="tabpanel" class="dash-tab-panel"
                x-data="themeEditor(@js($restaurant->theme_colors ?: []))">

                {{-- ── PRESET THEMES ── --}}
                <div class="dash-card dash-card-wide">
                    <header class="dash-card-header">
                        <div class="dash-card-icon dash-icon-purple"><i class="fas fa-magic"></i></div>
                        <h3>{{ __('messages.theme.preset_themes_title') }}</h3>
                    </header>
                    <p class="dash-help" style="margin-bottom:1.25rem;">
                        {{ __('messages.theme.preset_themes_desc') }}</p>

                    <div class="theme-presets-grid">
                        @php
                            $presets = [
                                'midnight_galaxy' => [
                                    'label_en' => 'Midnight Galaxy',
                                    'label_ar' => 'مجرة منتصف الليل',
                                    'type' => 'dark',
                                    'preview' => ['#0a0e27', '#6366f1', '#22d3ee'],
                                    'colors' => [
                                        'page_bg' => '#0a0e27',
                                        'page_bg_2' => '#141b3c',
                                        'page_bg_3' => '#1e2749',
                                        'header_bg_start' => '#0a0e27',
                                        'header_bg_end' => '#1e2749',
                                        'restaurant_name' => '#ffffff',
                                        'restaurant_tagline' => '#e2e8f0',
                                        'text_primary' => '#ffffff',
                                        'text_secondary' => '#e2e8f0',
                                        'text_muted' => '#94a3b8',
                                        'text_price' => '#22d3ee',
                                        'text_option_price' => '#86efac',
                                        'card_bg' => '#1a2254',
                                        'card_border' => '#334155',
                                        'card_border_hover' => '#6366f1',
                                        'card_accent_bar' => '#6366f1',
                                        'card_accent_bar_end' => '#8b5cf6',
                                        'btn_primary' => '#6366f1',
                                        'btn_primary_end' => '#8b5cf6',
                                        'btn_qty' => '#6366f1',
                                        'btn_qty_end' => '#8b5cf6',
                                        'btn_order' => '#22c55e',
                                        'btn_order_end' => '#16a34a',
                                        'pill_bg' => '#1a2254',
                                        'pill_border' => '#334155',
                                        'pill_text' => '#94a3b8',
                                        'pill_active' => '#6366f1',
                                        'pill_active_end' => '#8b5cf6',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#0f1631',
                                        'option_selected_bg' => '#2e3a8c',
                                        'option_input_accent' => '#6366f1',
                                        'input_bg' => '#1e2749',
                                        'input_border' => '#334155',
                                        'input_focus' => '#6366f1',
                                        'input_text' => '#ffffff',
                                        'footer_bg' => '#070b1e',
                                        'footer_text' => '#94a3b8',
                                        'footer_heading' => '#ffffff',
                                        'border' => '#334155',
                                        'border_secondary' => '#475569',
                                    ],
                                ],
                                'emerald_night' => [
                                    'label_en' => 'Emerald Night',
                                    'label_ar' => 'ليلة الزمرد',
                                    'type' => 'dark',
                                    'preview' => ['#071a10', '#10b981', '#34d399'],
                                    'colors' => [
                                        'page_bg' => '#071a10',
                                        'page_bg_2' => '#0d2b1a',
                                        'page_bg_3' => '#143d27',
                                        'header_bg_start' => '#071a10',
                                        'header_bg_end' => '#143d27',
                                        'restaurant_name' => '#ffffff',
                                        'restaurant_tagline' => '#d1fae5',
                                        'text_primary' => '#ecfdf5',
                                        'text_secondary' => '#d1fae5',
                                        'text_muted' => '#6ee7b7',
                                        'text_price' => '#34d399',
                                        'text_option_price' => '#6ee7b7',
                                        'card_bg' => '#0d2b1a',
                                        'card_border' => '#1a5c35',
                                        'card_border_hover' => '#10b981',
                                        'card_accent_bar' => '#10b981',
                                        'card_accent_bar_end' => '#059669',
                                        'btn_primary' => '#10b981',
                                        'btn_primary_end' => '#059669',
                                        'btn_qty' => '#10b981',
                                        'btn_qty_end' => '#059669',
                                        'btn_order' => '#25d366',
                                        'btn_order_end' => '#128c7e',
                                        'pill_bg' => '#0d2b1a',
                                        'pill_border' => '#1a5c35',
                                        'pill_text' => '#6ee7b7',
                                        'pill_active' => '#10b981',
                                        'pill_active_end' => '#059669',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#071a10',
                                        'option_selected_bg' => '#1a5c35',
                                        'option_input_accent' => '#10b981',
                                        'input_bg' => '#143d27',
                                        'input_border' => '#1a5c35',
                                        'input_focus' => '#10b981',
                                        'input_text' => '#ecfdf5',
                                        'footer_bg' => '#040d08',
                                        'footer_text' => '#6ee7b7',
                                        'footer_heading' => '#ecfdf5',
                                        'border' => '#1a5c35',
                                        'border_secondary' => '#2d7a50',
                                    ],
                                ],
                                'crimson_dark' => [
                                    'label_en' => 'Crimson Dark',
                                    'label_ar' => 'القرمزي الداكن',
                                    'type' => 'dark',
                                    'preview' => ['#0f0a0a', '#ef4444', '#fca5a5'],
                                    'colors' => [
                                        'page_bg' => '#0f0a0a',
                                        'page_bg_2' => '#1a1010',
                                        'page_bg_3' => '#241818',
                                        'header_bg_start' => '#0f0a0a',
                                        'header_bg_end' => '#241818',
                                        'restaurant_name' => '#ffffff',
                                        'restaurant_tagline' => '#fee2e2',
                                        'text_primary' => '#fff1f1',
                                        'text_secondary' => '#fee2e2',
                                        'text_muted' => '#fca5a5',
                                        'text_price' => '#f87171',
                                        'text_option_price' => '#fca5a5',
                                        'card_bg' => '#1a1010',
                                        'card_border' => '#4b1c1c',
                                        'card_border_hover' => '#ef4444',
                                        'card_accent_bar' => '#ef4444',
                                        'card_accent_bar_end' => '#b91c1c',
                                        'btn_primary' => '#ef4444',
                                        'btn_primary_end' => '#b91c1c',
                                        'btn_qty' => '#ef4444',
                                        'btn_qty_end' => '#b91c1c',
                                        'btn_order' => '#ef4444',
                                        'btn_order_end' => '#b91c1c',
                                        'pill_bg' => '#1a1010',
                                        'pill_border' => '#4b1c1c',
                                        'pill_text' => '#fca5a5',
                                        'pill_active' => '#ef4444',
                                        'pill_active_end' => '#b91c1c',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#0f0a0a',
                                        'option_selected_bg' => '#4b1c1c',
                                        'option_input_accent' => '#ef4444',
                                        'input_bg' => '#241818',
                                        'input_border' => '#4b1c1c',
                                        'input_focus' => '#ef4444',
                                        'input_text' => '#fff1f1',
                                        'footer_bg' => '#080505',
                                        'footer_text' => '#fca5a5',
                                        'footer_heading' => '#ffffff',
                                        'border' => '#4b1c1c',
                                        'border_secondary' => '#7f1d1d',
                                    ],
                                ],
                                'golden_dusk' => [
                                    'label_en' => 'Golden Dusk',
                                    'label_ar' => 'الغسق الذهبي',
                                    'type' => 'dark',
                                    'preview' => ['#12100a', '#f59e0b', '#fcd34d'],
                                    'colors' => [
                                        'page_bg' => '#12100a',
                                        'page_bg_2' => '#1e1a0f',
                                        'page_bg_3' => '#2b2516',
                                        'header_bg_start' => '#12100a',
                                        'header_bg_end' => '#2b2516',
                                        'restaurant_name' => '#fef3c7',
                                        'restaurant_tagline' => '#fde68a',
                                        'text_primary' => '#fef3c7',
                                        'text_secondary' => '#fde68a',
                                        'text_muted' => '#d97706',
                                        'text_price' => '#f59e0b',
                                        'text_option_price' => '#fcd34d',
                                        'card_bg' => '#1e1a0f',
                                        'card_border' => '#44350e',
                                        'card_border_hover' => '#f59e0b',
                                        'card_accent_bar' => '#f59e0b',
                                        'card_accent_bar_end' => '#d97706',
                                        'btn_primary' => '#f59e0b',
                                        'btn_primary_end' => '#d97706',
                                        'btn_qty' => '#f59e0b',
                                        'btn_qty_end' => '#d97706',
                                        'btn_order' => '#f59e0b',
                                        'btn_order_end' => '#d97706',
                                        'pill_bg' => '#1e1a0f',
                                        'pill_border' => '#44350e',
                                        'pill_text' => '#d97706',
                                        'pill_active' => '#f59e0b',
                                        'pill_active_end' => '#d97706',
                                        'pill_active_text' => '#12100a',
                                        'option_group_bg' => '#12100a',
                                        'option_selected_bg' => '#44350e',
                                        'option_input_accent' => '#f59e0b',
                                        'input_bg' => '#2b2516',
                                        'input_border' => '#44350e',
                                        'input_focus' => '#f59e0b',
                                        'input_text' => '#fef3c7',
                                        'footer_bg' => '#0a0906',
                                        'footer_text' => '#d97706',
                                        'footer_heading' => '#fef3c7',
                                        'border' => '#44350e',
                                        'border_secondary' => '#78521f',
                                    ],
                                ],
                                'ocean_depths' => [
                                    'label_en' => 'Ocean Depths',
                                    'label_ar' => 'أعماق المحيط',
                                    'type' => 'dark',
                                    'preview' => ['#061929', '#06b6d4', '#67e8f9'],
                                    'colors' => [
                                        'page_bg' => '#061929',
                                        'page_bg_2' => '#0a253d',
                                        'page_bg_3' => '#0f3050',
                                        'header_bg_start' => '#061929',
                                        'header_bg_end' => '#0f3050',
                                        'restaurant_name' => '#ffffff',
                                        'restaurant_tagline' => '#cffafe',
                                        'text_primary' => '#f0f9ff',
                                        'text_secondary' => '#bae6fd',
                                        'text_muted' => '#38bdf8',
                                        'text_price' => '#06b6d4',
                                        'text_option_price' => '#67e8f9',
                                        'card_bg' => '#0a253d',
                                        'card_border' => '#164e72',
                                        'card_border_hover' => '#06b6d4',
                                        'card_accent_bar' => '#06b6d4',
                                        'card_accent_bar_end' => '#0284c7',
                                        'btn_primary' => '#06b6d4',
                                        'btn_primary_end' => '#0284c7',
                                        'btn_qty' => '#06b6d4',
                                        'btn_qty_end' => '#0284c7',
                                        'btn_order' => '#06b6d4',
                                        'btn_order_end' => '#0284c7',
                                        'pill_bg' => '#0a253d',
                                        'pill_border' => '#164e72',
                                        'pill_text' => '#38bdf8',
                                        'pill_active' => '#06b6d4',
                                        'pill_active_end' => '#0284c7',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#061929',
                                        'option_selected_bg' => '#164e72',
                                        'option_input_accent' => '#06b6d4',
                                        'input_bg' => '#0f3050',
                                        'input_border' => '#164e72',
                                        'input_focus' => '#06b6d4',
                                        'input_text' => '#f0f9ff',
                                        'footer_bg' => '#030d17',
                                        'footer_text' => '#38bdf8',
                                        'footer_heading' => '#f0f9ff',
                                        'border' => '#164e72',
                                        'border_secondary' => '#0369a1',
                                    ],
                                ],
                                'rose_garden' => [
                                    'label_en' => 'Rose Garden',
                                    'label_ar' => 'حديقة الورود',
                                    'type' => 'light',
                                    'preview' => ['#fff5f7', '#e11d48', '#be185d'],
                                    'colors' => [
                                        'page_bg' => '#fff5f7',
                                        'page_bg_2' => '#fce7ef',
                                        'page_bg_3' => '#fbd3e0',
                                        'header_bg_start' => '#fff5f7',
                                        'header_bg_end' => '#fce7ef',
                                        'restaurant_name' => '#881337',
                                        'restaurant_tagline' => '#be185d',
                                        'text_primary' => '#1f0a12',
                                        'text_secondary' => '#4c0519',
                                        'text_muted' => '#9f1239',
                                        'text_price' => '#e11d48',
                                        'text_option_price' => '#be185d',
                                        'card_bg' => '#ffffff',
                                        'card_border' => '#fda4af',
                                        'card_border_hover' => '#e11d48',
                                        'card_accent_bar' => '#e11d48',
                                        'card_accent_bar_end' => '#be185d',
                                        'btn_primary' => '#e11d48',
                                        'btn_primary_end' => '#be185d',
                                        'btn_qty' => '#e11d48',
                                        'btn_qty_end' => '#be185d',
                                        'btn_order' => '#e11d48',
                                        'btn_order_end' => '#be185d',
                                        'pill_bg' => '#fff5f7',
                                        'pill_border' => '#fda4af',
                                        'pill_text' => '#9f1239',
                                        'pill_active' => '#e11d48',
                                        'pill_active_end' => '#be185d',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#fff5f7',
                                        'option_selected_bg' => '#fce7ef',
                                        'option_input_accent' => '#e11d48',
                                        'input_bg' => '#ffffff',
                                        'input_border' => '#fda4af',
                                        'input_focus' => '#e11d48',
                                        'input_text' => '#1f0a12',
                                        'footer_bg' => '#fff5f7',
                                        'footer_text' => '#9f1239',
                                        'footer_heading' => '#881337',
                                        'border' => '#fda4af',
                                        'border_secondary' => '#f9a8b4',
                                    ],
                                ],
                                'fresh_mint' => [
                                    'label_en' => 'Fresh Mint',
                                    'label_ar' => 'النعناع الطازج',
                                    'type' => 'light',
                                    'preview' => ['#f0fdf4', '#16a34a', '#22c55e'],
                                    'colors' => [
                                        'page_bg' => '#f0fdf4',
                                        'page_bg_2' => '#dcfce7',
                                        'page_bg_3' => '#bbf7d0',
                                        'header_bg_start' => '#f0fdf4',
                                        'header_bg_end' => '#dcfce7',
                                        'restaurant_name' => '#14532d',
                                        'restaurant_tagline' => '#166534',
                                        'text_primary' => '#052e16',
                                        'text_secondary' => '#14532d',
                                        'text_muted' => '#15803d',
                                        'text_price' => '#16a34a',
                                        'text_option_price' => '#22c55e',
                                        'card_bg' => '#ffffff',
                                        'card_border' => '#86efac',
                                        'card_border_hover' => '#16a34a',
                                        'card_accent_bar' => '#16a34a',
                                        'card_accent_bar_end' => '#15803d',
                                        'btn_primary' => '#16a34a',
                                        'btn_primary_end' => '#15803d',
                                        'btn_qty' => '#16a34a',
                                        'btn_qty_end' => '#15803d',
                                        'btn_order' => '#16a34a',
                                        'btn_order_end' => '#15803d',
                                        'pill_bg' => '#f0fdf4',
                                        'pill_border' => '#86efac',
                                        'pill_text' => '#166534',
                                        'pill_active' => '#16a34a',
                                        'pill_active_end' => '#15803d',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#f0fdf4',
                                        'option_selected_bg' => '#dcfce7',
                                        'option_input_accent' => '#16a34a',
                                        'input_bg' => '#ffffff',
                                        'input_border' => '#86efac',
                                        'input_focus' => '#16a34a',
                                        'input_text' => '#052e16',
                                        'footer_bg' => '#f0fdf4',
                                        'footer_text' => '#166534',
                                        'footer_heading' => '#14532d',
                                        'border' => '#86efac',
                                        'border_secondary' => '#4ade80',
                                    ],
                                ],
                                'lavender_cloud' => [
                                    'label_en' => 'Lavender Cloud',
                                    'label_ar' => 'سحابة اللافندر',
                                    'type' => 'light',
                                    'preview' => ['#faf5ff', '#7c3aed', '#a78bfa'],
                                    'colors' => [
                                        'page_bg' => '#faf5ff',
                                        'page_bg_2' => '#f3e8ff',
                                        'page_bg_3' => '#e9d5ff',
                                        'header_bg_start' => '#faf5ff',
                                        'header_bg_end' => '#f3e8ff',
                                        'restaurant_name' => '#4c1d95',
                                        'restaurant_tagline' => '#5b21b6',
                                        'text_primary' => '#1e0540',
                                        'text_secondary' => '#4c1d95',
                                        'text_muted' => '#7c3aed',
                                        'text_price' => '#7c3aed',
                                        'text_option_price' => '#a78bfa',
                                        'card_bg' => '#ffffff',
                                        'card_border' => '#d8b4fe',
                                        'card_border_hover' => '#7c3aed',
                                        'card_accent_bar' => '#7c3aed',
                                        'card_accent_bar_end' => '#6d28d9',
                                        'btn_primary' => '#7c3aed',
                                        'btn_primary_end' => '#6d28d9',
                                        'btn_qty' => '#7c3aed',
                                        'btn_qty_end' => '#6d28d9',
                                        'btn_order' => '#7c3aed',
                                        'btn_order_end' => '#6d28d9',
                                        'pill_bg' => '#faf5ff',
                                        'pill_border' => '#d8b4fe',
                                        'pill_text' => '#5b21b6',
                                        'pill_active' => '#7c3aed',
                                        'pill_active_end' => '#6d28d9',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#faf5ff',
                                        'option_selected_bg' => '#f3e8ff',
                                        'option_input_accent' => '#7c3aed',
                                        'input_bg' => '#ffffff',
                                        'input_border' => '#d8b4fe',
                                        'input_focus' => '#7c3aed',
                                        'input_text' => '#1e0540',
                                        'footer_bg' => '#faf5ff',
                                        'footer_text' => '#5b21b6',
                                        'footer_heading' => '#4c1d95',
                                        'border' => '#d8b4fe',
                                        'border_secondary' => '#c4b5fd',
                                    ],
                                ],
                                'sunset_coral' => [
                                    'label_en' => 'Sunset Coral',
                                    'label_ar' => 'مرجان الغروب',
                                    'type' => 'light',
                                    'preview' => ['#fff8f5', '#ea580c', '#fb923c'],
                                    'colors' => [
                                        'page_bg' => '#fff8f5',
                                        'page_bg_2' => '#fff1eb',
                                        'page_bg_3' => '#ffe4d6',
                                        'header_bg_start' => '#fff8f5',
                                        'header_bg_end' => '#fff1eb',
                                        'restaurant_name' => '#7c2d12',
                                        'restaurant_tagline' => '#9a3412',
                                        'text_primary' => '#431407',
                                        'text_secondary' => '#7c2d12',
                                        'text_muted' => '#ea580c',
                                        'text_price' => '#ea580c',
                                        'text_option_price' => '#fb923c',
                                        'card_bg' => '#ffffff',
                                        'card_border' => '#fed7aa',
                                        'card_border_hover' => '#ea580c',
                                        'card_accent_bar' => '#ea580c',
                                        'card_accent_bar_end' => '#c2410c',
                                        'btn_primary' => '#ea580c',
                                        'btn_primary_end' => '#c2410c',
                                        'btn_qty' => '#ea580c',
                                        'btn_qty_end' => '#c2410c',
                                        'btn_order' => '#ea580c',
                                        'btn_order_end' => '#c2410c',
                                        'pill_bg' => '#fff8f5',
                                        'pill_border' => '#fed7aa',
                                        'pill_text' => '#9a3412',
                                        'pill_active' => '#ea580c',
                                        'pill_active_end' => '#c2410c',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#fff8f5',
                                        'option_selected_bg' => '#fff1eb',
                                        'option_input_accent' => '#ea580c',
                                        'input_bg' => '#ffffff',
                                        'input_border' => '#fed7aa',
                                        'input_focus' => '#ea580c',
                                        'input_text' => '#431407',
                                        'footer_bg' => '#fff8f5',
                                        'footer_text' => '#9a3412',
                                        'footer_heading' => '#7c2d12',
                                        'border' => '#fed7aa',
                                        'border_secondary' => '#fdba74',
                                    ],
                                ],
                                'arctic_frost' => [
                                    'label_en' => 'Arctic Frost',
                                    'label_ar' => 'صقيع القطب',
                                    'type' => 'light',
                                    'preview' => ['#f0f9ff', '#0284c7', '#38bdf8'],
                                    'colors' => [
                                        'page_bg' => '#f0f9ff',
                                        'page_bg_2' => '#e0f2fe',
                                        'page_bg_3' => '#bae6fd',
                                        'header_bg_start' => '#f0f9ff',
                                        'header_bg_end' => '#e0f2fe',
                                        'restaurant_name' => '#0c4a6e',
                                        'restaurant_tagline' => '#075985',
                                        'text_primary' => '#082f49',
                                        'text_secondary' => '#0c4a6e',
                                        'text_muted' => '#0284c7',
                                        'text_price' => '#0284c7',
                                        'text_option_price' => '#38bdf8',
                                        'card_bg' => '#ffffff',
                                        'card_border' => '#7dd3fc',
                                        'card_border_hover' => '#0284c7',
                                        'card_accent_bar' => '#0284c7',
                                        'card_accent_bar_end' => '#0369a1',
                                        'btn_primary' => '#0284c7',
                                        'btn_primary_end' => '#0369a1',
                                        'btn_qty' => '#0284c7',
                                        'btn_qty_end' => '#0369a1',
                                        'btn_order' => '#0284c7',
                                        'btn_order_end' => '#0369a1',
                                        'pill_bg' => '#f0f9ff',
                                        'pill_border' => '#7dd3fc',
                                        'pill_text' => '#075985',
                                        'pill_active' => '#0284c7',
                                        'pill_active_end' => '#0369a1',
                                        'pill_active_text' => '#ffffff',
                                        'option_group_bg' => '#f0f9ff',
                                        'option_selected_bg' => '#e0f2fe',
                                        'option_input_accent' => '#0284c7',
                                        'input_bg' => '#ffffff',
                                        'input_border' => '#7dd3fc',
                                        'input_focus' => '#0284c7',
                                        'input_text' => '#082f49',
                                        'footer_bg' => '#f0f9ff',
                                        'footer_text' => '#075985',
                                        'footer_heading' => '#0c4a6e',
                                        'border' => '#7dd3fc',
                                        'border_secondary' => '#38bdf8',
                                    ],
                                ],
                            ];
                        @endphp

                        @foreach ($presets as $presetKey => $preset)
                            <button type="button" class="theme-preset-btn"
                                :class="{ 'theme-preset-active': activePreset === '{{ $presetKey }}' }"
                                @click="applyPreset(@js($preset['colors']), '{{ $presetKey }}')"
                                title="{{ app()->getLocale() === 'ar' ? $preset['label_ar'] : $preset['label_en'] }}">
                                <div class="theme-preset-swatches">
                                    @foreach ($preset['preview'] as $swatch)
                                        <span class="theme-swatch" style="background: {{ $swatch }};"></span>
                                    @endforeach
                                </div>
                                <div class="theme-preset-info">
                                    <span
                                        class="theme-preset-name">{{ app()->getLocale() === 'ar' ? $preset['label_ar'] : $preset['label_en'] }}</span>
                                    <span class="theme-preset-type theme-preset-type--{{ $preset['type'] }}">
                                        {{ $preset['type'] === 'dark' ? __('messages.theme.dark') : __('messages.theme.light') }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- ── COLOR CONTROLS FORM ── --}}
                <form action="{{ route('restaurant.update.settings') }}" method="POST" data-ajax
                    id="themeColorsForm">
                    @csrf

                    @php
                        $tc = $restaurant->theme_colors ?: [];
                        $cv = fn($k, $d) => $tc[$k] ?? $d;
                    @endphp

                    {{-- GROUP 1: Page Background --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-layer-group"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_page_bg') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_page_bg_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="page_bg" :value="$cv('page_bg', '#0a0e27')" :label="__('messages.theme.page_bg')"
                                :hint="__('messages.theme.page_bg_hint')" />
                            <x-theme-color-field name="page_bg_2" :value="$cv('page_bg_2', '#141b3c')" :label="__('messages.theme.page_bg_2')"
                                :hint="__('messages.theme.page_bg_2_hint')" />
                            <x-theme-color-field name="page_bg_3" :value="$cv('page_bg_3', '#1e2749')" :label="__('messages.theme.page_bg_3')"
                                :hint="__('messages.theme.page_bg_3_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 2: Header / Hero --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-purple"><i class="fas fa-store"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_header') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_header_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="header_bg_start" :value="$cv('header_bg_start', '#0a0e27')" :label="__('messages.theme.header_bg_start')"
                                :hint="__('messages.theme.header_bg_start_hint')" />
                            <x-theme-color-field name="header_bg_end" :value="$cv('header_bg_end', '#1e2749')" :label="__('messages.theme.header_bg_end')"
                                :hint="__('messages.theme.header_bg_end_hint')" />
                            <x-theme-color-field name="restaurant_name" :value="$cv('restaurant_name', '#ffffff')" :label="__('messages.theme.restaurant_name')"
                                :hint="__('messages.theme.restaurant_name_hint')" />
                            <x-theme-color-field name="restaurant_tagline" :value="$cv('restaurant_tagline', '#e2e8f0')" :label="__('messages.theme.restaurant_tagline')"
                                :hint="__('messages.theme.restaurant_tagline_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 3: Body Text --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-font"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_text') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_text_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="text_primary" :value="$cv('text_primary', '#ffffff')" :label="__('messages.theme.text_primary')"
                                :hint="__('messages.theme.text_primary_hint')" />
                            <x-theme-color-field name="text_secondary" :value="$cv('text_secondary', '#e2e8f0')" :label="__('messages.theme.text_secondary')"
                                :hint="__('messages.theme.text_secondary_hint')" />
                            <x-theme-color-field name="text_muted" :value="$cv('text_muted', '#94a3b8')" :label="__('messages.theme.text_muted')"
                                :hint="__('messages.theme.text_muted_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 4: Prices --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-green"><i class="fas fa-tag"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_prices') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_prices_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="text_price" :value="$cv('text_price', '#22d3ee')" :label="__('messages.theme.text_price')"
                                :hint="__('messages.theme.text_price_hint')" />
                            <x-theme-color-field name="text_option_price" :value="$cv('text_option_price', '#86efac')" :label="__('messages.theme.text_option_price')"
                                :hint="__('messages.theme.text_option_price_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 5: Product Cards --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-th-large"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_cards') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_cards_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="card_bg" :value="$cv('card_bg', '#1a2254')" :label="__('messages.theme.card_bg')"
                                :hint="__('messages.theme.card_bg_hint')" />
                            <x-theme-color-field name="card_border" :value="$cv('card_border', '#334155')" :label="__('messages.theme.card_border')"
                                :hint="__('messages.theme.card_border_hint')" />
                            <x-theme-color-field name="card_border_hover" :value="$cv('card_border_hover', '#6366f1')" :label="__('messages.theme.card_border_hover')"
                                :hint="__('messages.theme.card_border_hover_hint')" />
                            <x-theme-color-field name="card_accent_bar" :value="$cv('card_accent_bar', '#6366f1')" :label="__('messages.theme.card_accent_bar')"
                                :hint="__('messages.theme.card_accent_bar_hint')" />
                            <x-theme-color-field name="card_accent_bar_end" :value="$cv('card_accent_bar_end', '#8b5cf6')" :label="__('messages.theme.card_accent_bar_end')"
                                :hint="__('messages.theme.card_accent_bar_end_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 6: Buttons --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-purple"><i class="fas fa-hand-pointer"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_buttons') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_buttons_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="btn_primary" :value="$cv('btn_primary', '#6366f1')" :label="__('messages.theme.btn_primary')"
                                :hint="__('messages.theme.btn_primary_hint')" />
                            <x-theme-color-field name="btn_primary_end" :value="$cv('btn_primary_end', '#8b5cf6')" :label="__('messages.theme.btn_primary_end')"
                                :hint="__('messages.theme.btn_primary_end_hint')" />
                            <x-theme-color-field name="btn_qty" :value="$cv('btn_qty', '#6366f1')" :label="__('messages.theme.btn_qty')"
                                :hint="__('messages.theme.btn_qty_hint')" />
                            <x-theme-color-field name="btn_qty_end" :value="$cv('btn_qty_end', '#8b5cf6')" :label="__('messages.theme.btn_qty_end')"
                                :hint="__('messages.theme.btn_qty_end_hint')" />
                            <x-theme-color-field name="btn_order" :value="$cv('btn_order', '#22c55e')" :label="__('messages.theme.btn_order')"
                                :hint="__('messages.theme.btn_order_hint')" />
                            <x-theme-color-field name="btn_order_end" :value="$cv('btn_order_end', '#16a34a')" :label="__('messages.theme.btn_order_end')"
                                :hint="__('messages.theme.btn_order_end_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 7: Category Pills --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-pills"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_pills') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_pills_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="pill_bg" :value="$cv('pill_bg', '#1a2254')" :label="__('messages.theme.pill_bg')"
                                :hint="__('messages.theme.pill_bg_hint')" />
                            <x-theme-color-field name="pill_border" :value="$cv('pill_border', '#334155')" :label="__('messages.theme.pill_border')"
                                :hint="__('messages.theme.pill_border_hint')" />
                            <x-theme-color-field name="pill_text" :value="$cv('pill_text', '#94a3b8')" :label="__('messages.theme.pill_text')"
                                :hint="__('messages.theme.pill_text_hint')" />
                            <x-theme-color-field name="pill_active" :value="$cv('pill_active', '#6366f1')" :label="__('messages.theme.pill_active')"
                                :hint="__('messages.theme.pill_active_hint')" />
                            <x-theme-color-field name="pill_active_end" :value="$cv('pill_active_end', '#8b5cf6')" :label="__('messages.theme.pill_active_end')"
                                :hint="__('messages.theme.pill_active_end_hint')" />
                            <x-theme-color-field name="pill_active_text" :value="$cv('pill_active_text', '#ffffff')" :label="__('messages.theme.pill_active_text')"
                                :hint="__('messages.theme.pill_active_text_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 8: Product Options --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-green"><i class="fas fa-list-ul"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_options') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_options_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="option_group_bg" :value="$cv('option_group_bg', '#0f1631')" :label="__('messages.theme.option_group_bg')"
                                :hint="__('messages.theme.option_group_bg_hint')" />
                            <x-theme-color-field name="option_selected_bg" :value="$cv('option_selected_bg', '#2e3a8c')" :label="__('messages.theme.option_selected_bg')"
                                :hint="__('messages.theme.option_selected_bg_hint')" />
                            <x-theme-color-field name="option_input_accent" :value="$cv('option_input_accent', '#6366f1')" :label="__('messages.theme.option_input_accent')"
                                :hint="__('messages.theme.option_input_accent_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 9: Search & Inputs --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-search"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_inputs') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_inputs_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="input_bg" :value="$cv('input_bg', '#1e2749')" :label="__('messages.theme.input_bg')"
                                :hint="__('messages.theme.input_bg_hint')" />
                            <x-theme-color-field name="input_border" :value="$cv('input_border', '#334155')" :label="__('messages.theme.input_border')"
                                :hint="__('messages.theme.input_border_hint')" />
                            <x-theme-color-field name="input_focus" :value="$cv('input_focus', '#6366f1')" :label="__('messages.theme.input_focus')"
                                :hint="__('messages.theme.input_focus_hint')" />
                            <x-theme-color-field name="input_text" :value="$cv('input_text', '#ffffff')" :label="__('messages.theme.input_text')"
                                :hint="__('messages.theme.input_text_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 10: Footer --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-purple"><i class="fas fa-shoe-prints"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_footer') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_footer_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="footer_bg" :value="$cv('footer_bg', '#070b1e')" :label="__('messages.theme.footer_bg')"
                                :hint="__('messages.theme.footer_bg_hint')" />
                            <x-theme-color-field name="footer_text" :value="$cv('footer_text', '#94a3b8')" :label="__('messages.theme.footer_text')"
                                :hint="__('messages.theme.footer_text_hint')" />
                            <x-theme-color-field name="footer_heading" :value="$cv('footer_heading', '#ffffff')" :label="__('messages.theme.footer_heading')"
                                :hint="__('messages.theme.footer_heading_hint')" />
                        </div>
                    </div>

                    {{-- GROUP 11: Borders & Dividers --}}
                    <div class="dash-card dash-card-wide">
                        <header class="dash-card-header">
                            <div class="dash-card-icon dash-icon-blue"><i class="fas fa-border-style"></i></div>
                            <div>
                                <h3>{{ __('messages.theme.group_borders') }}</h3>
                                <p class="dash-help">{{ __('messages.theme.group_borders_desc') }}</p>
                            </div>
                        </header>
                        <div class="dash-colors-grid">
                            <x-theme-color-field name="border" :value="$cv('border', '#334155')" :label="__('messages.theme.border')"
                                :hint="__('messages.theme.border_hint')" />
                            <x-theme-color-field name="border_secondary" :value="$cv('border_secondary', '#475569')" :label="__('messages.theme.border_secondary')"
                                :hint="__('messages.theme.border_secondary_hint')" />
                        </div>
                    </div>

                    <div class="dash-card dash-card-wide">
                        <div class="dash-button-row">
                            <button type="submit" class="dash-btn dash-btn-primary">
                                <i class="fas fa-save"></i>
                                <span>{{ __('messages.theme.save_colors') }}</span>
                            </button>
                            <button type="button" class="dash-btn dash-btn-ghost" @click="resetToDefault()">
                                <i class="fas fa-undo"></i>
                                <span>{{ __('messages.theme.reset_to_default') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    @endif

    <script>
        function themeEditor(savedColors) {
            // Default Midnight Galaxy colors
            const DEFAULTS = {
                page_bg: '#0a0e27',
                page_bg_2: '#141b3c',
                page_bg_3: '#1e2749',
                header_bg_start: '#0a0e27',
                header_bg_end: '#1e2749',
                restaurant_name: '#ffffff',
                restaurant_tagline: '#e2e8f0',
                text_primary: '#ffffff',
                text_secondary: '#e2e8f0',
                text_muted: '#94a3b8',
                text_price: '#22d3ee',
                text_option_price: '#86efac',
                card_bg: '#1a2254',
                card_border: '#334155',
                card_border_hover: '#6366f1',
                card_accent_bar: '#6366f1',
                card_accent_bar_end: '#8b5cf6',
                btn_primary: '#6366f1',
                btn_primary_end: '#8b5cf6',
                btn_qty: '#6366f1',
                btn_qty_end: '#8b5cf6',
                btn_order: '#22c55e',
                btn_order_end: '#16a34a',
                pill_bg: '#1a2254',
                pill_border: '#334155',
                pill_text: '#94a3b8',
                pill_active: '#6366f1',
                pill_active_end: '#8b5cf6',
                pill_active_text: '#ffffff',
                option_group_bg: '#0f1631',
                option_selected_bg: '#2e3a8c',
                option_input_accent: '#6366f1',
                input_bg: '#1e2749',
                input_border: '#334155',
                input_focus: '#6366f1',
                input_text: '#ffffff',
                footer_bg: '#070b1e',
                footer_text: '#94a3b8',
                footer_heading: '#ffffff',
                border: '#334155',
                border_secondary: '#475569',
            };

            return {
                activePreset: null,

                applyPreset(colors, presetKey) {
                    this.activePreset = presetKey;
                    Object.entries(colors).forEach(([name, value]) => {
                        const label = document.querySelector(`#themeColorsForm label[x-data*="'${name}'"]`) ||
                            document.querySelector(`#themeColorsForm input[name="${name}"]`)?.closest('label');
                        const inp = document.querySelector(`#themeColorsForm input[name="${name}"]`);
                        if (inp) {
                            inp.value = value;
                            // Update Alpine data
                            if (label && label._x_dataStack) {
                                label._x_dataStack[0].color = value;
                            }
                            // Update preview swatch directly
                            const preview = inp.closest('.dash-color-field')?.querySelector(
                                '.dash-color-field-preview');
                            if (preview) preview.style.background = value;
                            const hex = inp.closest('.dash-color-field')?.querySelector('.dash-color-hex');
                            if (hex) hex.textContent = value;
                        }
                    });
                    window.toast?.success(@js(__('messages.theme.preset_applied')));
                },

                resetToDefault() {
                    if (!confirm(@js(__('messages.theme.confirm_reset')))) return;
                    this.activePreset = 'midnight_galaxy';
                    Object.entries(DEFAULTS).forEach(([name, value]) => {
                        const inp = document.querySelector(`#themeColorsForm input[name="${name}"]`);
                        if (inp) inp.value = value;
                    });
                    window.toast?.info(@js(__('messages.theme.reset_done')));
                },
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* ---- Header ---- */
        .dash-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dash-header-main {
            flex: 1;
            min-width: 0;
        }

        .dash-title {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.35rem;
            font-weight: 700;
            color: #f1f5f9;
            margin: 0;
        }

        .dash-title i {
            color: #a5b4fc;
        }

        .dash-subtitle {
            color: #94a3b8;
            margin: 0.25rem 0 0;
            font-size: 0.9rem;
        }

        .dash-restaurant-switcher select {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border: 1px solid #334155 !important;
            border-radius: 8px;
            padding: 0.4rem 0.7rem;
            font-size: 0.85rem;
        }

        .dash-view-menu {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff !important;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: transform .15s;
        }

        .dash-view-menu:hover {
            transform: translateY(-1px);
        }

        /* ---- Wrapper ---- */
        .dash-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.25rem 1rem 4rem;
        }

        /* ---- Tabs ---- */
        .dash-tabs {
            display: flex;
            gap: 0.25rem;
            background: #1e293b;
            padding: 0.35rem;
            border-radius: 14px;
            border: 1px solid #334155;
            overflow-x: auto;
            scrollbar-width: none;
            position: sticky;
            top: 0.75rem;
            z-index: 20;
            margin-bottom: 1.25rem;
        }

        .dash-tabs::-webkit-scrollbar {
            display: none;
        }

        .dash-tab {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.65rem 1rem;
            background: transparent;
            border: 0;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 10px;
            cursor: pointer;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }

        .dash-tab:hover {
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.04);
        }

        .dash-tab-active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: #fff !important;
        }

        /* ---- Cards ---- */
        .dash-tab-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .dash-grid-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dash-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 1.15rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .dash-card-wide {}

        .dash-card-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }

        .dash-card-header h3 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #f1f5f9;
        }

        .dash-card-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.95rem;
        }

        .dash-icon-blue {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .dash-icon-green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .dash-icon-pink {
            background: linear-gradient(135deg, #ec4899, #db2777);
        }

        .dash-icon-purple {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        /* ---- Fields ---- */
        .dash-field {
            margin-bottom: 0.85rem;
        }

        .dash-field-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .dash-field-row>.dash-field {
            min-width: 0;
        }

        .dash-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 0.3rem;
            letter-spacing: 0.02em;
        }

        .dash-input {
            width: 100%;
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #f1f5f9 !important;
            border-radius: 10px !important;
            padding: 0.65rem 0.85rem !important;
            font-size: 0.9rem !important;
            transition: border-color .15s, box-shadow .15s;
        }

        .dash-input:focus {
            border-color: #6366f1 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        }

        .dash-input-file {
            padding: 0.45rem !important;
        }

        .dash-help {
            display: block;
            color: #94a3b8;
            font-size: 0.72rem;
            margin-top: 0.3rem;
        }

        /* ---- Buttons ---- */
        .dash-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.15rem;
            border-radius: 10px;
            border: 0;
            font-weight: 600;
            font-size: 0.9rem;
            color: #fff !important;
            cursor: pointer;
            transition: transform .15s, opacity .15s, box-shadow .15s;
        }

        .dash-btn:hover {
            transform: translateY(-1px);
        }

        .dash-btn-block {
            width: 100%;
            justify-content: center;
        }

        .dash-btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .dash-btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .dash-btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
        }

        .dash-btn-ghost {
            background: transparent !important;
            border: 1px solid #334155 !important;
            color: #e2e8f0 !important;
        }

        .dash-btn-ghost:hover {
            background: rgba(255, 255, 255, 0.04) !important;
        }

        .dash-btn-sm {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }

        .dash-btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 8px;
            border: 0;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .dash-btn-icon:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .dash-btn-icon-blue {
            background: rgba(59, 130, 246, 0.12);
            color: #93c5fd;
        }

        .dash-btn-icon-red {
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5;
        }

        .dash-collapse-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: transparent;
            border: 1px dashed #334155;
            color: #cbd5e1;
            border-radius: 10px;
            padding: 0.5rem 0.9rem;
            font-size: 0.85rem;
            cursor: pointer;
            margin-bottom: 0.75rem;
            transition: border-color .15s, color .15s;
        }

        .dash-collapse-toggle:hover {
            border-color: #64748b;
            color: #fff;
        }

        .dash-collapsible {
            margin-bottom: 0.85rem;
        }

        .dash-button-row {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* ---- Setup (no restaurant) ---- */
        .dash-setup-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 18px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            max-width: 32rem;
            margin: 2rem auto;
        }

        .dash-setup-icon {
            font-size: 3rem;
        }

        .dash-setup-card h3 {
            color: #f1f5f9;
            margin: 0.75rem 0 0.5rem;
        }

        .dash-setup-card p {
            color: #94a3b8;
            margin-bottom: 1.25rem;
        }

        /* ---- Empty states ---- */
        .dash-empty,
        .dash-empty-sm {
            background: rgba(15, 23, 42, 0.5);
            border: 1px dashed #334155;
            border-radius: 14px;
            text-align: center;
            padding: 2rem;
            color: #94a3b8;
        }

        .dash-empty-icon {
            font-size: 2.25rem;
        }

        .dash-empty h3 {
            color: #e2e8f0;
            margin: 0.5rem 0 0.25rem;
        }

        .dash-empty-sm {
            padding: 1rem;
            font-size: 0.85rem;
        }

        /* ---- Categories ---- */
        .dash-categories {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .dash-category {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 0.5rem 1rem 1rem;
        }

        .dash-category[open] {
            padding-bottom: 1rem;
        }

        .dash-category-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            padding: 0.75rem 0;
            list-style: none;
            gap: 0.75rem;
        }

        .dash-category-head::-webkit-details-marker {
            display: none;
        }

        .dash-category-title {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
        }

        .dash-category-title i {
            color: #fbbf24;
        }

        .dash-category-name {
            font-weight: 600;
            font-size: 1rem;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dash-count-badge {
            font-size: 0.7rem;
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            padding: 2px 8px;
            border-radius: 999px;
        }

        /* ---- Items grid ---- */
        .dash-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .dash-item {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 0.75rem;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.75rem;
            align-items: start;
            transition: transform .15s, border-color .15s;
        }

        .dash-item:hover {
            border-color: #475569;
            transform: translateY(-1px);
        }

        .dash-item-media {
            flex-shrink: 0;
        }

        .dash-item-media img,
        .dash-item-placeholder {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            object-fit: cover;
        }

        .dash-item-placeholder {
            background: linear-gradient(135deg, #334155, #475569);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.25rem;
        }

        .dash-item-body {
            min-width: 0;
        }

        .dash-item-title {
            font-weight: 600;
            color: #f1f5f9;
            font-size: 0.95rem;
            margin: 0;
        }

        .dash-item-desc {
            color: #94a3b8;
            font-size: 0.8rem;
            margin: 0.25rem 0;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .dash-item-price {
            font-weight: 700;
            font-size: 1rem;
            background: linear-gradient(135deg, #34d399, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-top: 0.35rem;
        }

        .dash-item-groups {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 0.35rem;
        }

        .dash-group-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 0.7rem;
            padding: 2px 7px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            border-radius: 999px;
        }

        .dash-group-badge small {
            opacity: 0.7;
        }

        .dash-item-actions {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        /* ---- Modal ---- */
        .dash-modal {
            position: fixed;
            inset: 0;
            background: rgba(10, 14, 39, 0.85);
            backdrop-filter: blur(8px);
            z-index: 100;
            overflow-y: auto;
            padding: 2rem 1rem;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .dash-modal-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 18px;
            width: 100%;
            max-width: 44rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .dash-modal-head,
        .dash-modal-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #334155;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .dash-modal-foot {
            border-top: 1px solid #334155;
            border-bottom: 0;
            justify-content: flex-end;
        }

        .dash-modal-head h3 {
            margin: 0;
            color: #f1f5f9;
            font-size: 1.05rem;
        }

        .dash-modal-body {
            padding: 1.25rem;
            overflow-y: auto;
        }

        /* ---- Switch ---- */
        .dash-switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
        }

        .dash-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .dash-switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #475569;
            transition: .3s;
            border-radius: 28px;
        }

        .dash-switch-slider::before {
            content: '';
            position: absolute;
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background: #fff;
            transition: .3s;
            border-radius: 50%;
        }

        html[dir="rtl"] .dash-switch-slider::before {
            left: auto;
            right: 4px;
        }

        .dash-switch input:checked+.dash-switch-slider {
            background: #10b981;
        }

        .dash-switch input:checked+.dash-switch-slider::before {
            transform: translateX(24px);
        }

        html[dir="rtl"] .dash-switch input:checked+.dash-switch-slider::before {
            transform: translateX(-24px);
        }

        .dash-inline-form {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dash-mt {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #334155;
        }

        /* ---- Social grid ---- */
        .dash-social-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        /* ---- Theme colors grid ---- */
        .dash-colors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .dash-color-field {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            cursor: pointer;
            transition: border-color 0.2s;
            position: relative;
            /* contain the absolute-positioned color input */
        }

        .dash-color-field:hover {
            border-color: #6366f1;
        }

        .dash-color-field-preview {
            width: 100%;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #1e293b;
        }

        .dash-color-input {
            /* Visually hidden but in the render tree so the native color dialog can open.
               pointer-events left enabled so the label→input activation works in all browsers. */
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
            padding: 0;
            border: 0;
            margin: 0;
            overflow: hidden;
        }

        /* Prevent app.css generic input:focus from shifting the hidden color swatch */
        .dash-color-input:focus {
            transform: none !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .dash-color-field-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .dash-color-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #e2e8f0;
        }

        .dash-color-hint {
            font-size: 0.7rem;
            color: #64748b;
            line-height: 1.3;
        }

        /* ---- Preset themes grid ---- */
        .theme-presets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
            gap: 0.85rem;
        }

        .theme-preset-btn {
            background: #0f172a;
            border: 2px solid #334155;
            border-radius: 14px;
            padding: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            text-align: left;
        }

        .theme-preset-btn:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }

        .theme-preset-active {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25) !important;
        }

        .theme-preset-swatches {
            display: flex;
            gap: 4px;
        }

        .theme-swatch {
            flex: 1;
            height: 28px;
            border-radius: 6px;
            display: block;
        }

        .theme-preset-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.4rem;
        }

        .theme-preset-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #e2e8f0;
        }

        .theme-preset-type {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .theme-preset-type--dark {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
        }

        .theme-preset-type--light {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
        }

        /* ---- Current image preview ---- */
        .dash-current-image {
            margin-top: 0.75rem;
        }

        .dash-current-image p {
            color: #94a3b8;
            font-size: 0.75rem;
            margin: 0 0 0.4rem;
        }

        .dash-logo-preview {
            max-height: 4rem;
            border-radius: 8px;
            border: 1px solid #334155;
            background: #fff;
            padding: 4px;
        }

        .dash-bg-preview {
            max-height: 8rem;
            border-radius: 8px;
            border: 1px solid #334155;
            object-fit: cover;
        }

        .dash-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.4rem;
            color: #cbd5e1;
            font-size: 0.8rem;
        }

        /* ============================= MOBILE ============================= */
        @media (max-width: 768px) {
            .dash-grid-two {
                grid-template-columns: 1fr;
            }

            .dash-social-grid {
                grid-template-columns: 1fr;
            }

            .dash-items-grid {
                grid-template-columns: 1fr;
            }

            .dash-item {
                grid-template-columns: auto 1fr;
            }

            .dash-item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: flex-end;
            }

            .dash-view-menu {
                width: 100%;
                justify-content: center;
            }

            .dash-header {
                flex-direction: column;
                align-items: stretch;
            }

            .dash-modal-card {
                max-height: 100vh;
                border-radius: 0;
            }

            .dash-modal {
                padding: 0;
            }

            .dash-tab span {
                display: none;
            }

            .dash-tab {
                padding: 0.7rem 0.85rem;
            }

            .dash-tab i {
                font-size: 1.05rem;
            }

            .dash-tabs {
                gap: 0.15rem;
            }
        }

        @media (max-width: 480px) {
            .dash-wrap {
                padding: 1rem 0.75rem 4rem;
            }

            .dash-card {
                padding: 1rem;
            }

            .dash-field-row {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* Respect RTL for grid/flex alignment */
        html[dir="rtl"] .dash-item-actions {
            align-items: flex-start;
        }
    </style>
</x-app-layout>
