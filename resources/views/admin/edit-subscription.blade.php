@php
    use App\Services\Admin\SubscriptionState;

    $invalid = fn (string $field) => $errors->has($field) ? 'aria-invalid=true' : '';
    $state = SubscriptionState::for($subscription);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="adm-header">
            <div>
                <h2 class="adm-title">{{ __('admin.edit_subscription.title') }}</h2>
                <p class="adm-muted">{{ __('admin.edit_subscription.subtitle', ['name' => $subscription->user->name, 'email' => $subscription->user->email]) }}</p>
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

            <form method="POST" action="{{ route('admin.subscription.update', $subscription) }}" class="adm-card adm-form" novalidate>
                @csrf
                @method('PUT')

                <p class="adm-help">
                    <span class="adm-badge {{ ['expired' => 'adm-badge-red', 'unpaid' => 'adm-badge-amber', 'active' => 'adm-badge-green', 'no_due_date' => 'adm-badge-gray'][$state] }}">{{ __('admin.subscription.status.'.$state) }}</span>
                    {{ $subscription->paid_at ? __('admin.subscription.last_paid', ['date' => $subscription->paid_at->translatedFormat('j M Y')]) : __('admin.subscription.never_paid') }}
                </p>

                <div class="adm-field">
                    <label for="amount" class="adm-label">{{ __('admin.edit_subscription.amount') }}</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount', $subscription->amount) }}" required min="0" max="999999.99" step="0.01" class="adm-input adm-input-ltr" {{ $invalid('amount') }}>
                    @include('admin.partials.field-error', ['field' => 'amount'])
                </div>

                <div class="adm-field">
                    <label for="next_payment_date" class="adm-label">{{ __('admin.edit_subscription.next_payment_date') }}</label>
                    <input type="date" id="next_payment_date" name="next_payment_date" value="{{ old('next_payment_date', $subscription->expires_at?->format('Y-m-d')) }}" class="adm-input adm-input-ltr" {{ $invalid('next_payment_date') }}>
                    <p class="adm-help">{{ __('admin.edit_subscription.next_payment_help') }}</p>
                    @include('admin.partials.field-error', ['field' => 'next_payment_date'])
                </div>

                <div class="adm-actions">
                    <a href="{{ route('dashboard') }}" class="adm-btn adm-btn-secondary">{{ __('admin.common.cancel') }}</a>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="fas fa-save adm-icon-gap"></i>{{ __('admin.edit_subscription.submit') }}
                    </button>
                </div>
            </form>

            @if (SubscriptionState::isDue($subscription))
                <form method="POST" action="{{ route('admin.subscription.mark-paid', $subscription) }}" class="adm-actions" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="adm-btn adm-btn-secondary">
                        <i class="fas fa-check adm-icon-gap"></i>{{ __('admin.subscription.mark_paid') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>