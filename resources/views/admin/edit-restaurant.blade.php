@php
    $invalid = fn (string $field) => $errors->has($field) ? 'aria-invalid=true' : '';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="adm-header">
            <div>
                <h2 class="adm-title">{{ __('admin.edit_restaurant.title') }}</h2>
                <p class="adm-muted">{{ __('admin.edit_restaurant.subtitle', ['name' => $restaurant->name]) }}</p>
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

            <form method="POST" action="{{ route('admin.restaurant.update', $restaurant) }}" class="adm-card adm-form" novalidate>
                @csrf
                @method('PUT')

                @if ($restaurant->user)
                    <p class="adm-help">
                        {{ __('admin.edit_restaurant.owner', ['name' => $restaurant->user->name, 'email' => $restaurant->user->email]) }}
                        — <a href="{{ route('admin.user.edit', $restaurant->user) }}">{{ __('admin.dashboard.edit_owner') }}</a>
                    </p>
                @endif

                <div class="adm-field">
                    <label for="name" class="adm-label">{{ __('admin.create.name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $restaurant->name) }}" required maxlength="255" class="adm-input" {{ $invalid('name') }}>
                    @include('admin.partials.field-error', ['field' => 'name'])
                </div>

                <div class="adm-field">
                    <label for="slug" class="adm-label">{{ __('admin.create.slug') }}</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $restaurant->slug) }}" required maxlength="100" class="adm-input adm-input-ltr" {{ $invalid('slug') }}>
                    <p class="adm-help">{{ __('admin.create.slug_help') }} <span class="adm-code adm-ltr">{{ url('/') }}/<span id="slug-preview">{{ old('slug', $restaurant->slug) }}</span></span></p>
                    @include('admin.partials.field-error', ['field' => 'slug'])
                </div>

                <div class="adm-field">
                    <label for="description" class="adm-label">{{ __('admin.create.description') }}</label>
                    <textarea id="description" name="description" rows="4" maxlength="5000" class="adm-input" {{ $invalid('description') }}>{{ old('description', $restaurant->description) }}</textarea>
                    @include('admin.partials.field-error', ['field' => 'description'])
                </div>

                <div class="adm-field">
                    <label for="admin_notes" class="adm-label">{{ __('admin.notes.label') }}</label>
                    <textarea id="admin_notes" name="admin_notes" rows="4" maxlength="5000" class="adm-input"
                        placeholder="{{ __('admin.notes.placeholder') }}" {{ $invalid('admin_notes') }}>{{ old('admin_notes', $restaurant->admin_notes) }}</textarea>
                    <p class="adm-help">{{ __('admin.notes.private_hint') }}</p>
                    @include('admin.partials.field-error', ['field' => 'admin_notes'])
                </div>

                <div class="adm-actions">
                    <a href="{{ route('dashboard') }}" class="adm-btn adm-btn-secondary">{{ __('admin.common.cancel') }}</a>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="fas fa-save adm-icon-gap"></i>{{ __('admin.edit_restaurant.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('slug').addEventListener('input', function () {
            document.getElementById('slug-preview').textContent = this.value;
        });
    </script>
</x-app-layout>