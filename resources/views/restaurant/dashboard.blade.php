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
            <section x-show="tab === 'theme'" x-cloak role="tabpanel" class="dash-tab-panel">
                <div class="dash-card dash-card-wide">
                    <header class="dash-card-header">
                        <div class="dash-card-icon dash-icon-purple"><i class="fas fa-palette"></i></div>
                        <h3>{{ __('messages.theme_colors') }}</h3>
                    </header>
                    <p class="dash-help" style="margin-bottom: 1rem">{{ __('messages.choose_colors') }}</p>

                    <form action="{{ route('restaurant.update.settings') }}" method="POST" data-ajax>
                        @csrf
                        <div class="dash-colors-grid">
                            @foreach ([['primary_color', 'primary', '#667eea', __('messages.primary_color')], ['secondary_color', 'secondary', '#764ba2', __('messages.secondary_color')], ['accent_color', 'accent', '#4facfe', __('messages.accent_color')], ['text_color', 'text', '#ffffff', __('messages.text_color')], ['background_color', 'background', '#0a0e27', __('messages.background_color')], ['card_color', 'card', '#252d56', __('messages.card_color')], ['secondary_bg', 'secondary_bg', '#141b3c', __('messages.secondary_background')], ['tertiary_bg', 'tertiary_bg', '#1e2749', __('messages.tertiary_background')], ['secondary_text', 'secondary_text', '#e2e8f0', __('messages.secondary_text')], ['muted_text', 'muted_text', '#94a3b8', __('messages.muted_text')], ['input_bg', 'input_bg', '#1e2749', __('messages.input_background')], ['input_border', 'input_border', '#334155', __('messages.input_border')]] as [$field, $key, $default, $label])
                                <label class="dash-color-field">
                                    <span class="dash-label">{{ $label }}</span>
                                    <input type="color" name="{{ $field }}"
                                        value="{{ $restaurant->theme_colors[$key] ?? $default }}">
                                </label>
                            @endforeach
                        </div>

                        <div class="dash-button-row">
                            <button type="submit" class="dash-btn dash-btn-primary">
                                <i class="fas fa-save"></i>
                                <span>{{ __('messages.save_settings') }}</span>
                            </button>
                            <button type="button" class="dash-btn dash-btn-ghost" onclick="resetThemeColors()">
                                <i class="fas fa-undo"></i>
                                <span>{{ __('messages.reset_theme_colors') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    @endif

    <script>
        function resetThemeColors() {
            if (!confirm(@js(__('messages.confirm_reset_theme_colors')))) return;
            const defaults = {
                primary_color: '#667eea',
                secondary_color: '#764ba2',
                accent_color: '#4facfe',
                text_color: '#ffffff',
                background_color: '#0a0e27',
                card_color: '#252d56',
                secondary_bg: '#141b3c',
                tertiary_bg: '#1e2749',
                secondary_text: '#e2e8f0',
                muted_text: '#94a3b8',
                input_bg: '#1e2749',
                input_border: '#334155',
            };
            Object.entries(defaults).forEach(([name, value]) => {
                const inp = document.querySelector(`input[name="${name}"]`);
                if (inp) inp.value = value;
            });
            window.toast?.info(@js(__('messages.theme_colors_updated')));
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
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .dash-color-field {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 0.65rem;
        }

        .dash-color-field input[type="color"] {
            width: 100%;
            height: 2.25rem;
            border: 1px solid #334155;
            border-radius: 8px;
            background: transparent;
            cursor: pointer;
            padding: 2px;
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
