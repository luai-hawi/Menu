@php
    $ownerMethod = old('owner_method', $owners->isEmpty() ? 'new' : 'existing');
    $invalid = fn (string $field) => $errors->has($field) ? 'aria-invalid=true' : '';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="adm-header">
            <div>
                <h2 class="adm-title">{{ __('admin.create.title') }}</h2>
                <p class="adm-muted">{{ __('admin.create.subtitle') }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="adm-btn adm-btn-secondary">
                <i class="fas fa-arrow-left adm-flip adm-icon-gap"></i>{{ __('admin.common.back') }}
            </a>
        </div>
    </x-slot>

    @include('admin.partials.styles')

    <div class="adm-page">
        <div class="adm-container-narrow">
            @include('admin.partials.flash', ['bag' => 'default'])

            <form method="POST" action="{{ route('admin.restaurant.store') }}" enctype="multipart/form-data" class="adm-card adm-form" novalidate>
                @csrf

                <section class="adm-section">
                    <h3 class="adm-section-title">{{ __('admin.create.owner_section') }}</h3>
                    <p class="adm-help">{{ __('admin.create.owner_section_help') }}</p>
                    @include('admin.partials.field-error', ['field' => 'owner'])
                    @include('admin.partials.field-error', ['field' => 'owner_method'])

                    <div class="adm-radio-group" role="radiogroup">
                        <label class="adm-radio">
                            <input type="radio" name="owner_method" value="existing" @checked($ownerMethod === 'existing') @disabled($owners->isEmpty())>
                            <span>{{ __('admin.create.use_existing') }}</span>
                        </label>
                        <label class="adm-radio">
                            <input type="radio" name="owner_method" value="new" @checked($ownerMethod === 'new')>
                            <span>{{ __('admin.create.create_new') }}</span>
                        </label>
                    </div>

                    <div id="owner-existing" @if ($ownerMethod !== 'existing') hidden @endif>
                        @if ($owners->isEmpty())
                            <p class="adm-help">{{ __('admin.create.no_owners') }}</p>
                        @else
                            <div class="adm-field">
                                <label for="user_id" class="adm-label">{{ __('admin.create.select_owner') }}</label>
                                <select id="user_id" name="user_id" class="adm-input" {{ $invalid('user_id') }}>
                                    <option value="">{{ __('admin.create.choose_owner') }}</option>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}" @selected((string) old('user_id') === (string) $owner->id)>
                                            {{ __('admin.create.owner_option', ['name' => $owner->name, 'email' => $owner->email, 'count' => $owner->restaurants_count]) }}
                                        </option>
                                    @endforeach
                                </select>
                                @include('admin.partials.field-error', ['field' => 'user_id'])
                            </div>
                        @endif
                    </div>

                    <div id="owner-new" @if ($ownerMethod !== 'new') hidden @endif>
                        <div class="adm-field">
                            <label for="owner_name" class="adm-label">{{ __('admin.create.owner_name') }}</label>
                            <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" maxlength="255" class="adm-input" {{ $invalid('owner_name') }}>
                            <p class="adm-help">{{ __('admin.create.owner_name_help') }}</p>
                            @include('admin.partials.field-error', ['field' => 'owner_name'])
                        </div>
                        <div class="adm-field">
                            <label for="owner_email" class="adm-label">{{ __('admin.create.owner_email') }}</label>
                            <input type="email" id="owner_email" name="owner_email" value="{{ old('owner_email') }}" maxlength="255" autocomplete="off" class="adm-input adm-input-ltr" {{ $invalid('owner_email') }}>
                            <p class="adm-help">{{ __('admin.create.owner_email_help') }}</p>
                            @include('admin.partials.field-error', ['field' => 'owner_email'])
                        </div>
                        <div class="adm-field">
                            <label for="phone" class="adm-label">{{ __('admin.create.phone') }}</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20" class="adm-input adm-input-ltr" {{ $invalid('phone') }}>
                            @include('admin.partials.field-error', ['field' => 'phone'])
                        </div>
                        <div class="adm-field">
                            <label for="password" class="adm-label">{{ __('admin.create.password') }}</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" class="adm-input adm-input-ltr" {{ $invalid('password') }}>
                            <p class="adm-help">{{ __('admin.create.password_help') }}</p>
                            @include('admin.partials.field-error', ['field' => 'password'])
                        </div>
                        <div class="adm-field">
                            <label for="password_confirmation" class="adm-label">{{ __('admin.create.password_confirmation') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="adm-input adm-input-ltr">
                        </div>
                    </div>
                </section>

                <section class="adm-section">
                    <h3 class="adm-section-title">{{ __('admin.create.restaurant_section') }}</h3>

                    <div class="adm-field">
                        <label for="name" class="adm-label">{{ __('admin.create.name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255" class="adm-input" {{ $invalid('name') }}>
                        @include('admin.partials.field-error', ['field' => 'name'])
                    </div>

                    <div class="adm-field">
                        <label for="slug" class="adm-label">{{ __('admin.create.slug') }}</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required maxlength="100" pattern="[a-z0-9]+(-[a-z0-9]+)*"
                            class="adm-input adm-input-ltr" {{ $invalid('slug') }} @if (old('slug')) data-touched="1" @endif>
                        <p class="adm-help">{{ __('admin.create.slug_help') }} <span class="adm-code adm-ltr">{{ url('/') }}/<span id="slug-preview">{{ old('slug') }}</span></span></p>
                        @include('admin.partials.field-error', ['field' => 'slug'])
                    </div>

                    <div class="adm-field">
                        <label for="description" class="adm-label">{{ __('admin.create.description') }}</label>
                        <textarea id="description" name="description" rows="3" maxlength="5000" class="adm-input" {{ $invalid('description') }}>{{ old('description') }}</textarea>
                        @include('admin.partials.field-error', ['field' => 'description'])
                    </div>

                    <div class="adm-field">
                        <label for="subscription_amount" class="adm-label">{{ __('admin.create.subscription_amount') }}</label>
                        <input type="number" id="subscription_amount" name="subscription_amount" value="{{ old('subscription_amount', '100') }}" min="0" max="999999.99" step="0.01"
                            class="adm-input adm-input-ltr" {{ $invalid('subscription_amount') }}>
                        <p class="adm-help">{{ __('admin.create.subscription_amount_help') }}</p>
                        @include('admin.partials.field-error', ['field' => 'subscription_amount'])
                    </div>

                    <div class="adm-field">
                        <label for="logo" class="adm-label">{{ __('admin.create.logo') }}</label>
                        <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="adm-input" {{ $invalid('logo') }}>
                        <p class="adm-help">{{ __('admin.create.logo_help') }}</p>
                        @include('admin.partials.field-error', ['field' => 'logo'])
                    </div>

                    <div class="adm-field">
                        <label for="admin_notes" class="adm-label">{{ __('admin.create.admin_notes') }}</label>
                        <textarea id="admin_notes" name="admin_notes" rows="3" maxlength="5000" class="adm-input"
                            placeholder="{{ __('admin.notes.placeholder') }}" {{ $invalid('admin_notes') }}>{{ old('admin_notes') }}</textarea>
                        <p class="adm-help">{{ __('admin.notes.private_hint') }}</p>
                        @include('admin.partials.field-error', ['field' => 'admin_notes'])
                    </div>
                </section>

                <div class="adm-actions">
                    <a href="{{ route('dashboard') }}" class="adm-btn adm-btn-secondary">{{ __('admin.common.cancel') }}</a>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="fas fa-plus adm-icon-gap"></i>{{ __('admin.create.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const radios = document.querySelectorAll('input[name="owner_method"]');
            const existing = document.getElementById('owner-existing');
            const fresh = document.getElementById('owner-new');
            radios.forEach((radio) => radio.addEventListener('change', () => {
                const method = (document.querySelector('input[name="owner_method"]:checked') || {}).value;
                existing.hidden = method !== 'existing';
                fresh.hidden = method !== 'new';
            }));

            const name = document.getElementById('name');
            const slug = document.getElementById('slug');
            const preview = document.getElementById('slug-preview');
            const slugify = (value) => value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 100);

            slug.addEventListener('input', () => {
                slug.dataset.touched = slug.value === '' ? '' : '1';
                preview.textContent = slug.value;
            });
            name.addEventListener('input', () => {
                if (slug.dataset.touched === '1') return;
                slug.value = slugify(name.value);
                preview.textContent = slug.value;
            });
        })();
    </script>
</x-app-layout>