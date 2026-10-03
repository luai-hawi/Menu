@php
    $invalid = fn (string $field) => $errors->has($field) ? 'aria-invalid=true' : '';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="adm-header">
            <div>
                <h2 class="adm-title">{{ __('admin.edit_user.title') }}</h2>
                <p class="adm-muted">{{ $user->name }} — <span class="adm-ltr">{{ $user->email }}</span></p>
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

            <form method="POST" action="{{ route('admin.user.update', $user) }}" class="adm-card adm-form" novalidate>
                @csrf
                @method('PUT')

                <section class="adm-section">
                    <h3 class="adm-section-title">{{ __('admin.edit_user.details') }}</h3>
                    <p class="adm-help">
                        {{ __('admin.edit_user.role', ['role' => __('admin.roles.'.($user->role ?: 'user'))]) }}
                        · {{ __('admin.edit_user.owns_restaurants', ['count' => $user->restaurants_count]) }}
                    </p>

                    <div class="adm-field">
                        <label for="name" class="adm-label">{{ __('admin.edit_user.name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="adm-input" {{ $invalid('name') }}>
                        @include('admin.partials.field-error', ['field' => 'name'])
                    </div>
                    <div class="adm-field">
                        <label for="email" class="adm-label">{{ __('admin.edit_user.email') }}</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="adm-input adm-input-ltr" {{ $invalid('email') }}>
                        @include('admin.partials.field-error', ['field' => 'email'])
                    </div>
                    <div class="adm-field">
                        <label for="phone" class="adm-label">{{ __('admin.edit_user.phone') }}</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" class="adm-input adm-input-ltr" {{ $invalid('phone') }}>
                        @include('admin.partials.field-error', ['field' => 'phone'])
                    </div>
                </section>

                <section class="adm-section">
                    <h3 class="adm-section-title">{{ __('admin.edit_user.password_section') }}</h3>
                    <p class="adm-help">{{ __('admin.edit_user.password_help') }}</p>
                    <div class="adm-field">
                        <label for="password" class="adm-label">{{ __('admin.edit_user.password') }}</label>
                        <input type="password" id="password" name="password" autocomplete="new-password" class="adm-input adm-input-ltr" {{ $invalid('password') }}>
                        @include('admin.partials.field-error', ['field' => 'password'])
                    </div>
                    <div class="adm-field">
                        <label for="password_confirmation" class="adm-label">{{ __('admin.edit_user.password_confirmation') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="adm-input adm-input-ltr">
                    </div>
                </section>

                <section class="adm-section">
                    <h3 class="adm-section-title">{{ __('admin.edit_user.subscription_section') }}</h3>
                    @if ($user->isAdmin())
                        <p class="adm-help">{{ __('admin.edit_user.admin_no_subscription') }}</p>
                    @else
                        @if ($subscription)
                            <p class="adm-help">
                                {{ __('admin.edit_user.current_amount', ['amount' => number_format((float) $subscription->amount, 2)]) }}
                                · {{ $subscription->paid_at ? __('admin.subscription.last_paid', ['date' => $subscription->paid_at->translatedFormat('j M Y')]) : __('admin.subscription.never_paid') }}
                                · <a href="{{ route('admin.subscription.edit', $subscription) }}">{{ __('admin.subscription.edit') }}</a>
                            </p>
                        @else
                            <p class="adm-help">{{ __('admin.edit_user.no_subscription') }}</p>
                        @endif
                        <div class="adm-field">
                            <label for="expires_at" class="adm-label">{{ __('admin.edit_user.next_payment_date') }}</label>
                            <input type="date" id="expires_at" name="expires_at" value="{{ old('expires_at', $subscription?->expires_at?->format('Y-m-d')) }}" class="adm-input adm-input-ltr" {{ $invalid('expires_at') }}>
                            <p class="adm-help">{{ __('admin.edit_user.next_payment_help') }}</p>
                            @include('admin.partials.field-error', ['field' => 'expires_at'])
                        </div>
                    @endif
                </section>

                <div class="adm-actions">
                    <a href="{{ route('dashboard') }}" class="adm-btn adm-btn-secondary">{{ __('admin.common.cancel') }}</a>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="fas fa-save adm-icon-gap"></i>{{ __('admin.edit_user.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>