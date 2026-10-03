@php
    use App\Services\Admin\AccountCleanupService;
    use App\Services\Admin\SubscriptionState;

    $badgeFor = [
        SubscriptionState::EXPIRED => 'adm-badge-red',
        SubscriptionState::UNPAID => 'adm-badge-amber',
        SubscriptionState::ACTIVE => 'adm-badge-green',
        SubscriptionState::NO_DUE_DATE => 'adm-badge-gray',
    ];
    $hasFilters = $filters['q'] !== '' || $filters['status'] !== '' || $filters['billing'] !== '';
    $restaurantDeletionErrors = $errors->getBag('restaurantDeletion');
    $accountDeletionErrors = $errors->getBag('accountDeletion');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="adm-header">
            <div>
                <h2 class="adm-title">{{ __('admin.dashboard.title') }}</h2>
                <p class="adm-muted">{{ __('admin.dashboard.subtitle') }}</p>
            </div>
            <a href="{{ route('admin.restaurant.create') }}" class="adm-btn adm-btn-primary">
                <i class="fas fa-plus"></i>
                {{ __('admin.dashboard.add_restaurant') }}
            </a>
        </div>
    </x-slot>

    @include('admin.partials.styles')

    <div class="adm-page">
        <div class="adm-container">
            @include('admin.partials.flash', ['bag' => 'restaurantNotes'])

            <div class="adm-metrics">
                @foreach (['restaurants', 'active_restaurants', 'owners', 'due_subscriptions', 'unused_accounts'] as $metric)
                    @php $anchor = ['due_subscriptions' => '#due-subscriptions', 'unused_accounts' => '#unused-accounts'][$metric] ?? null; @endphp
                    @if ($anchor)
                        <a class="adm-metric" href="{{ $anchor }}" data-metric="{{ $metric }}">
                    @else
                        <div class="adm-metric" data-metric="{{ $metric }}">
                    @endif
                        <div class="adm-metric-value">{{ $metrics[$metric] }}</div>
                        <div class="adm-metric-label">{{ __('admin.dashboard.metrics.'.$metric) }}</div>
                    @if ($anchor) </a> @else </div> @endif
                @endforeach
            </div>

            {{-- Restaurants --}}
            <section class="adm-card" id="restaurants">
                <div class="adm-card-head">
                    <div>
                        <h3 class="adm-card-title">{{ __('admin.dashboard.restaurants_title') }}</h3>
                        <p class="adm-muted adm-small">{{ __('admin.dashboard.results', ['count' => $restaurants->total()]) }}</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="adm-filters" role="search">
                    <div class="adm-field adm-field-wide">
                        <label for="filter-q" class="adm-label">{{ __('admin.dashboard.filters.search') }}</label>
                        <input type="search" id="filter-q" name="q" value="{{ $filters['q'] }}" class="adm-input"
                            placeholder="{{ __('admin.dashboard.filters.search_placeholder') }}">
                    </div>
                    <div class="adm-field">
                        <label for="filter-status" class="adm-label">{{ __('admin.dashboard.filters.status') }}</label>
                        <select id="filter-status" name="status" class="adm-input">
                            <option value="">{{ __('admin.dashboard.filters.status_any') }}</option>
                            <option value="active" @selected($filters['status'] === 'active')>{{ __('admin.common.active') }}</option>
                            <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('admin.common.inactive') }}</option>
                        </select>
                    </div>
                    <div class="adm-field">
                        <label for="filter-billing" class="adm-label">{{ __('admin.dashboard.filters.billing') }}</label>
                        <select id="filter-billing" name="billing" class="adm-input">
                            <option value="">{{ __('admin.dashboard.filters.billing_any') }}</option>
                            @foreach (['due', 'ok', 'none'] as $billing)
                                <option value="{{ $billing }}" @selected($filters['billing'] === $billing)>{{ __('admin.dashboard.filters.billing_'.$billing) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="adm-actions">
                        <button type="submit" class="adm-btn adm-btn-primary">
                            <i class="fas fa-search adm-icon-gap"></i>{{ __('admin.dashboard.filters.apply') }}
                        </button>
                        @if ($hasFilters)
                            <a href="{{ route('dashboard') }}" class="adm-btn adm-btn-secondary">{{ __('admin.dashboard.filters.reset') }}</a>
                        @endif
                    </div>
                </form>

                @if ($restaurants->isEmpty())
                    <div class="adm-empty">
                        <i class="fas fa-store" style="font-size: 3rem; opacity: .5;"></i>
                        @if ($hasFilters)
                            <p class="adm-strong" style="margin-top: 1rem;">{{ __('admin.dashboard.empty_filtered') }}</p>
                        @else
                            <p class="adm-strong" style="margin-top: 1rem;">{{ __('admin.dashboard.empty') }}</p>
                            <p>{{ __('admin.dashboard.empty_help') }}</p>
                        @endif
                    </div>
                @else
                    <div class="adm-table-wrap" style="margin-top: 1.25rem;">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.dashboard.table.restaurant') }}</th>
                                    <th>{{ __('admin.dashboard.table.owner') }}</th>
                                    <th>{{ __('admin.dashboard.table.next_payment') }}</th>
                                    <th>{{ __('admin.common.status') }}</th>
                                    <th>{{ __('admin.dashboard.table.notes') }}</th>
                                    <th>{{ __('admin.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>                                @foreach ($restaurants as $restaurant)
                                    @php
                                        $owner = $restaurant->user;
                                        $sub = $owner?->subscriptions->first();
                                        $subState = SubscriptionState::for($sub);
                                        $ownerStatus = $restaurant->owner_deletion_status;
                                        $notesOpen = $errors->restaurantNotes->any() && old('notes_target') === $restaurant->slug;
                                    @endphp
                                    <tr class="{{ $restaurant->is_active ? '' : 'adm-row-inactive' }}" data-restaurant="{{ $restaurant->slug }}">
                                        <td>
                                            <div class="adm-cell-flex">
                                                @if ($restaurant->logo)
                                                    <img src="{{ asset('storage/'.$restaurant->logo) }}" alt="" class="adm-avatar">
                                                @else
                                                    <span class="adm-avatar-placeholder">{{ mb_substr($restaurant->name, 0, 1) }}</span>
                                                @endif
                                                <div>
                                                    <div class="adm-strong">{{ $restaurant->name }}</div>
                                                    <a href="{{ route('menu.show', $restaurant->slug) }}" target="_blank" rel="noopener" class="adm-code adm-ltr">/{{ $restaurant->slug }}</a>
                                                    <div class="adm-muted adm-small">
                                                        {{ __('admin.dashboard.table.created') }}: {{ $restaurant->created_at?->translatedFormat('j M Y') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($owner)
                                                <div>{{ $owner->name }}</div>
                                                <div class="adm-muted adm-small adm-ltr">{{ $owner->email }}</div>
                                                <div class="adm-muted adm-small adm-ltr">{{ $owner->phone ?: __('admin.common.not_provided') }}</div>
                                                <div class="adm-muted adm-small">{{ __('admin.dashboard.owner_restaurants', ['count' => $owner->restaurants_count]) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($sub)
                                                <span class="adm-badge {{ $badgeFor[$subState] }}">{{ __('admin.subscription.status.'.$subState) }}</span>
                                                <div class="adm-small">
                                                    @if ($sub->expires_at)
                                                        {{ __('admin.subscription.due_on', ['date' => $sub->expires_at->translatedFormat('j M Y')]) }}
                                                    @else
                                                        <span class="adm-muted">{{ __('admin.subscription.no_date') }}</span>
                                                    @endif
                                                </div>
                                                <a href="{{ route('admin.subscription.edit', $sub) }}" class="adm-small">{{ __('admin.subscription.edit') }}</a>
                                            @else
                                                <span class="adm-badge adm-badge-gray">{{ __('admin.subscription.status.none') }}</span>
                                                @if ($owner && ! $owner->isAdmin())
                                                    <div><a href="{{ route('admin.user.edit', $owner) }}" class="adm-small">{{ __('admin.subscription.set_date') }}</a></div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="adm-badge {{ $restaurant->is_active ? 'adm-badge-green' : 'adm-badge-red' }}">
                                                {{ $restaurant->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="adm-notes" data-notes>{{ $restaurant->admin_notes ?: __('admin.notes.empty') }}</div>
                                            <details class="adm-notes-details" @if ($notesOpen) open @endif>
                                                <summary>{{ __('admin.notes.edit') }}</summary>
                                                <form method="POST" action="{{ route('admin.restaurant.notes', $restaurant) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="notes_target" value="{{ $restaurant->slug }}">
                                                    <label for="notes-{{ $restaurant->id }}" class="adm-help">{{ __('admin.notes.private_hint') }}</label>
                                                    <textarea id="notes-{{ $restaurant->id }}" name="admin_notes" rows="3" maxlength="5000" class="adm-input"
                                                        placeholder="{{ __('admin.notes.placeholder') }}">{{ $notesOpen ? old('admin_notes') : $restaurant->admin_notes }}</textarea>
                                                    <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">{{ __('admin.notes.save') }}</button>
                                                </form>
                                            </details>
                                        </td>
                                        <td>
                                            @php $toggleLabel = $restaurant->is_active ? __('admin.dashboard.deactivate') : __('admin.dashboard.activate'); @endphp
                                            <div class="adm-icon-actions">
                                                <a href="{{ route('menu.show', $restaurant->slug) }}" target="_blank" rel="noopener" class="adm-icon-btn"
                                                    title="{{ __('admin.dashboard.view_menu') }}" aria-label="{{ __('admin.dashboard.view_menu') }}">
                                                    <i class="fas fa-external-link-alt adm-flip"></i>
                                                </a>
                                                <a href="{{ route('admin.restaurant.edit', $restaurant) }}" class="adm-icon-btn adm-warn"
                                                    title="{{ __('admin.dashboard.edit_restaurant') }}" aria-label="{{ __('admin.dashboard.edit_restaurant') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if ($owner)
                                                    <a href="{{ route('admin.user.edit', $owner) }}" class="adm-icon-btn"
                                                        title="{{ __('admin.dashboard.edit_owner') }}" aria-label="{{ __('admin.dashboard.edit_owner') }}">
                                                        <i class="fas fa-user-edit"></i>
                                                    </a>
                                                @endif
                                                <form method="POST" action="{{ route('admin.restaurant.toggle', $restaurant) }}">
                                                    @csrf
                                                    <button type="submit" class="adm-icon-btn {{ $restaurant->is_active ? 'adm-warn' : 'adm-ok' }}"
                                                        title="{{ $toggleLabel }}" aria-label="{{ $toggleLabel }}">
                                                        <i class="fas {{ $restaurant->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="adm-icon-btn adm-danger js-delete-restaurant"
                                                    title="{{ __('admin.dashboard.delete_restaurant') }}" aria-label="{{ __('admin.dashboard.delete_restaurant') }}"
                                                    data-url="{{ route('admin.restaurant.delete', $restaurant) }}"
                                                    data-slug="{{ $restaurant->slug }}"
                                                    data-name="{{ $restaurant->name }}"
                                                    data-owner-deletable="{{ $ownerStatus === AccountCleanupService::OWNER_DELETABLE ? '1' : '0' }}"
                                                    data-owner-note="{{ __('admin.delete_restaurant.owner_status.'.$ownerStatus, ['email' => $owner?->email]) }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="adm-pagination">{{ $restaurants->fragment('restaurants')->links() }}</div>
                @endif
            </section>

            {{-- Subscriptions needing payment --}}
            <section class="adm-card" id="due-subscriptions">
                <div class="adm-card-head">
                    <div>
                        <h3 class="adm-card-title">{{ __('admin.due_section.title') }}</h3>
                        <p class="adm-muted adm-small">{{ __('admin.due_section.subtitle') }}</p>
                    </div>
                </div>

                @if ($dueSubscriptions->isEmpty())
                    <p class="adm-empty">{{ __('admin.due_section.empty') }}</p>
                @else
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.due_section.owner') }}</th>
                                    <th>{{ __('admin.due_section.amount') }}</th>
                                    <th>{{ __('admin.due_section.due') }}</th>
                                    <th>{{ __('admin.common.status') }}</th>
                                    <th>{{ __('admin.due_section.restaurants') }}</th>
                                    <th>{{ __('admin.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dueSubscriptions as $subscription)
                                    @php $state = SubscriptionState::for($subscription); @endphp
                                    <tr data-subscription="{{ $subscription->id }}">
                                        <td>
                                            <div>{{ $subscription->user->name }}</div>
                                            <div class="adm-muted adm-small adm-ltr">{{ $subscription->user->email }}</div>
                                        </td>
                                        <td class="adm-ltr">${{ number_format((float) $subscription->amount, 2) }}</td>
                                        <td>
                                            {{ $subscription->expires_at ? $subscription->expires_at->translatedFormat('j M Y') : __('admin.subscription.no_date') }}
                                            <div class="adm-muted adm-small">
                                                {{ $subscription->paid_at ? __('admin.subscription.last_paid', ['date' => $subscription->paid_at->translatedFormat('j M Y')]) : __('admin.subscription.never_paid') }}
                                            </div>
                                        </td>
                                        <td><span class="adm-badge {{ $badgeFor[$state] }}">{{ __('admin.subscription.status.'.$state) }}</span></td>
                                        <td>{{ $subscription->user->restaurants_count }}</td>
                                        <td>
                                            <div class="adm-icon-actions">
                                                <a href="{{ route('admin.subscription.edit', $subscription) }}" class="adm-icon-btn adm-warn"
                                                    title="{{ __('admin.subscription.edit') }}" aria-label="{{ __('admin.subscription.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.subscription.mark-paid', $subscription) }}">
                                                    @csrf
                                                    <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm">
                                                        <i class="fas fa-check adm-icon-gap"></i>{{ __('admin.subscription.mark_paid') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="adm-pagination">{{ $dueSubscriptions->fragment('due-subscriptions')->links() }}</div>
                @endif
            </section>
            {{-- Accounts without restaurants --}}
            <section class="adm-card" id="unused-accounts">
                <div class="adm-card-head">
                    <div>
                        <h3 class="adm-card-title">{{ __('admin.unused_section.title') }}</h3>
                        <p class="adm-muted adm-small">{{ __('admin.unused_section.subtitle') }}</p>
                    </div>
                </div>

                @if ($unusedAccounts->isEmpty())
                    <p class="adm-empty">{{ __('admin.unused_section.empty') }}</p>
                @else
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.unused_section.account') }}</th>
                                    <th>{{ __('admin.unused_section.role') }}</th>
                                    <th>{{ __('admin.unused_section.created') }}</th>
                                    <th>{{ __('admin.unused_section.subscription') }}</th>
                                    <th>{{ __('admin.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unusedAccounts as $account)
                                    @php $accountState = SubscriptionState::for($account->subscriptions->first()); @endphp
                                    <tr data-account="{{ $account->id }}">
                                        <td>
                                            <div>{{ $account->name }}</div>
                                            <div class="adm-muted adm-small adm-ltr">{{ $account->email }}</div>
                                        </td>
                                        <td>{{ __('admin.roles.'.($account->role ?: 'user')) }}</td>
                                        <td>{{ $account->created_at?->translatedFormat('j M Y') }}</td>
                                        <td>
                                            @if ($accountState)
                                                <span class="adm-badge {{ $badgeFor[$accountState] }}">{{ __('admin.subscription.status.'.$accountState) }}</span>
                                            @else
                                                <span class="adm-badge adm-badge-gray">{{ __('admin.subscription.status.none') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="adm-icon-actions">
                                                <a href="{{ route('admin.user.edit', $account) }}" class="adm-icon-btn"
                                                    title="{{ __('admin.dashboard.edit_owner') }}" aria-label="{{ __('admin.dashboard.edit_owner') }}">
                                                    <i class="fas fa-user-edit"></i>
                                                </a>
                                                @if ($account->id === auth()->id())
                                                    <span class="adm-muted adm-small">{{ __('admin.unused_section.self') }}</span>
                                                @else
                                                    <button type="button" class="adm-btn adm-btn-danger adm-btn-sm js-delete-account"
                                                        data-url="{{ route('admin.user.destroy', $account) }}"
                                                        data-id="{{ $account->id }}"
                                                        data-email="{{ $account->email }}"
                                                        data-explain="{{ __('admin.delete_account.explain', ['email' => $account->email]) }}">
                                                        <i class="fas fa-user-times adm-icon-gap"></i>{{ __('admin.unused_section.delete') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="adm-pagination">{{ $unusedAccounts->fragment('unused-accounts')->links() }}</div>
                @endif
            </section>
        </div>
    </div>

    {{-- Delete restaurant modal --}}
    <div id="delete-restaurant-modal" class="adm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-restaurant-title">
        <div class="adm-modal-box">
            <form id="delete-restaurant-form" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="delete_target" id="dr-target">
                <input type="hidden" name="delete_target_name" id="dr-name-hidden">
                <input type="hidden" name="delete_target_owner_note" id="dr-owner-note-hidden">
                <input type="hidden" name="delete_target_owner_deletable" id="dr-owner-deletable">

                <div class="adm-modal-head">
                    <h3 id="delete-restaurant-title" class="adm-card-title">
                        <i class="fas fa-exclamation-triangle adm-icon-gap" style="color: #ef4444;"></i>{{ __('admin.delete_restaurant.title') }}: <span id="dr-name"></span>
                    </h3>
                    <p class="adm-muted">{{ __('admin.delete_restaurant.irreversible') }}</p>
                </div>
                <div class="adm-modal-body">
                    <div class="adm-warning-box">
                        <p class="adm-strong">{{ __('admin.delete_restaurant.will_delete') }}</p>
                        <ul>
                            <li>{{ __('admin.delete_restaurant.item_profile') }}</li>
                            <li>{{ __('admin.delete_restaurant.item_menu') }}</li>
                            <li>{{ __('admin.delete_restaurant.item_media') }}</li>
                        </ul>
                    </div>

                    <input type="hidden" name="delete_owner" value="0">
                    <label class="adm-check">
                        <input type="checkbox" name="delete_owner" value="1" id="dr-delete-owner" checked>
                        <span>
                            <span class="adm-strong">{{ __('admin.delete_restaurant.delete_owner_label') }}</span>
                            <span class="adm-help" style="display: block;">{{ __('admin.delete_restaurant.delete_owner_help') }}</span>
                        </span>
                    </label>
                    <p id="dr-owner-note" class="adm-owner-note" aria-live="polite"></p>

                    <div class="adm-field">
                        <label for="dr-confirm" class="adm-label">{{ __('admin.delete_restaurant.type_name') }}</label>
                        <input type="text" id="dr-confirm" name="confirm_name" autocomplete="off" class="adm-input"
                            placeholder="{{ __('admin.delete_restaurant.name_placeholder') }}"
                            @if ($restaurantDeletionErrors->has('confirm_name')) aria-invalid="true" @endif>
                        <p id="dr-confirm-error" class="adm-field-error" @unless ($restaurantDeletionErrors->any()) hidden @endunless>
                            {{ $restaurantDeletionErrors->first() ?: __('admin.delete_restaurant.name_mismatch') }}
                        </p>
                    </div>
                </div>
                <div class="adm-modal-foot">
                    <button type="button" class="adm-btn adm-btn-secondary js-close-modal">{{ __('admin.common.cancel') }}</button>
                    <button type="submit" id="dr-submit" class="adm-btn adm-btn-danger" disabled>
                        <i class="fas fa-trash adm-icon-gap"></i>{{ __('admin.delete_restaurant.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete unused account modal --}}
    <div id="delete-account-modal" class="adm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-account-title">
        <div class="adm-modal-box">
            <form id="delete-account-form" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="delete_account_id" id="da-id">
                <input type="hidden" name="delete_account_email" id="da-email-hidden">
                <input type="hidden" name="delete_account_explain" id="da-explain-hidden">
                <div class="adm-modal-head">
                    <h3 id="delete-account-title" class="adm-card-title">
                        <i class="fas fa-user-times adm-icon-gap" style="color: #ef4444;"></i>{{ __('admin.delete_account.title') }}
                    </h3>
                </div>
                <div class="adm-modal-body">
                    <div class="adm-warning-box"><p id="da-explain"></p></div>
                    <div class="adm-field">
                        <label for="da-confirm" class="adm-label">{{ __('admin.delete_account.type_email') }} <span id="da-email" class="adm-code adm-ltr"></span></label>
                        <input type="text" id="da-confirm" name="confirm_email" autocomplete="off" class="adm-input adm-input-ltr"
                            @if ($accountDeletionErrors->has('confirm_email')) aria-invalid="true" @endif>
                        <p id="da-confirm-error" class="adm-field-error" @unless ($accountDeletionErrors->has('confirm_email')) hidden @endunless>
                            {{ $accountDeletionErrors->first('confirm_email') ?: __('admin.delete_account.email_mismatch') }}
                        </p>
                    </div>
                </div>
                <div class="adm-modal-foot">
                    <button type="button" class="adm-btn adm-btn-secondary js-close-modal">{{ __('admin.common.cancel') }}</button>
                    <button type="submit" id="da-submit" class="adm-btn adm-btn-danger" disabled>
                        <i class="fas fa-trash adm-icon-gap"></i>{{ __('admin.delete_account.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const byId = (id) => document.getElementById(id);
            const drModal = byId('delete-restaurant-modal');
            const daModal = byId('delete-account-modal');
            const nameMismatch = @js(__('admin.delete_restaurant.name_mismatch'));
            const emailMismatch = @js(__('admin.delete_account.email_mismatch'));
            let expectedName = '';
            let expectedEmail = '';

            function closeModals() {
                drModal.classList.remove('is-open');
                daModal.classList.remove('is-open');
            }

            function updateOwnerNote() {
                const deleting = byId('dr-delete-owner').checked;
                byId('dr-owner-note').className = 'adm-owner-note ' + (deleting ? 'adm-owner-note-delete' : 'adm-owner-note-keep');
            }

            function syncRestaurantSubmit() {
                const value = byId('dr-confirm').value;
                const match = value === expectedName;
                byId('dr-submit').disabled = !match;
                const error = byId('dr-confirm-error');
                if (value.length > 0 && !match) {
                    error.textContent = nameMismatch;
                    error.hidden = false;
                } else if (match) {
                    error.hidden = true;
                }
            }

            function openRestaurantModal(data, showServerError) {
                expectedName = data.name;
                byId('delete-restaurant-form').action = data.url;
                byId('dr-target').value = data.slug;
                byId('dr-name').textContent = data.name;
                byId('dr-name-hidden').value = data.name;
                byId('dr-owner-note-hidden').value = data.ownerNote;
                byId('dr-owner-deletable').value = data.ownerDeletable ? '1' : '0';
                byId('dr-owner-note').textContent = data.ownerNote;

                const checkbox = byId('dr-delete-owner');
                checkbox.disabled = !data.ownerDeletable;
                checkbox.checked = data.ownerDeletable && data.deleteOwner;
                updateOwnerNote();

                byId('dr-confirm').value = '';
                byId('dr-confirm-error').hidden = !showServerError;
                syncRestaurantSubmit();
                drModal.classList.add('is-open');
                setTimeout(() => byId('dr-confirm').focus(), 50);
            }

            function syncAccountSubmit() {
                const value = byId('da-confirm').value.trim().toLowerCase();
                const match = value === expectedEmail;
                byId('da-submit').disabled = !match;
                const error = byId('da-confirm-error');
                if (value.length > 0 && !match) {
                    error.textContent = emailMismatch;
                    error.hidden = false;
                } else if (match) {
                    error.hidden = true;
                }
            }

            function openAccountModal(data, showServerError) {
                expectedEmail = String(data.email).toLowerCase();
                byId('delete-account-form').action = data.url;
                byId('da-id').value = data.id;
                byId('da-email').textContent = data.email;
                byId('da-email-hidden').value = data.email;
                byId('da-explain').textContent = data.explain;
                byId('da-explain-hidden').value = data.explain;
                byId('da-confirm').value = '';
                byId('da-confirm-error').hidden = !showServerError;
                syncAccountSubmit();
                daModal.classList.add('is-open');
                setTimeout(() => byId('da-confirm').focus(), 50);
            }

            document.querySelectorAll('.js-delete-restaurant').forEach((btn) => {
                btn.addEventListener('click', () => openRestaurantModal({
                    url: btn.dataset.url,
                    slug: btn.dataset.slug,
                    name: btn.dataset.name,
                    ownerNote: btn.dataset.ownerNote,
                    ownerDeletable: btn.dataset.ownerDeletable === '1',
                    deleteOwner: true,
                }, false));
            });

            document.querySelectorAll('.js-delete-account').forEach((btn) => {
                btn.addEventListener('click', () => openAccountModal({
                    url: btn.dataset.url,
                    id: btn.dataset.id,
                    email: btn.dataset.email,
                    explain: btn.dataset.explain,
                }, false));
            });

            byId('dr-confirm').addEventListener('input', syncRestaurantSubmit);
            byId('dr-delete-owner').addEventListener('change', updateOwnerNote);
            byId('da-confirm').addEventListener('input', syncAccountSubmit);
            document.querySelectorAll('.js-close-modal').forEach((b) => b.addEventListener('click', closeModals));
            [drModal, daModal].forEach((m) => m.addEventListener('click', (e) => { if (e.target === m) closeModals(); }));
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModals(); });

            @if ($restaurantDeletionErrors->any() && is_string(old('delete_target')) && old('delete_target') !== '')
                // Re-open after a failed confirmation, preserving the owner-deletion choice.
                openRestaurantModal({
                    url: @js(route('admin.restaurant.delete', ['restaurant' => old('delete_target')])),
                    slug: @js(old('delete_target')),
                    name: @js(old('delete_target_name')),
                    ownerNote: @js(old('delete_target_owner_note')),
                    ownerDeletable: @js(old('delete_target_owner_deletable') === '1'),
                    deleteOwner: @js(old('delete_owner') === '1'),
                }, true);
            @endif

            @if ($accountDeletionErrors->any() && ctype_digit((string) old('delete_account_id')))
                openAccountModal({
                    url: @js(route('admin.user.destroy', ['user' => (int) old('delete_account_id')])),
                    id: @js((string) old('delete_account_id')),
                    email: @js(old('delete_account_email')),
                    explain: @js(old('delete_account_explain')),
                }, true);
            @endif
        })();
    </script>
</x-app-layout>