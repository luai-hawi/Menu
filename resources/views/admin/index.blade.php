<!-- Fixed admin index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('messages.admin_dashboard') }}
                </h2>
                <p class="text-gray-400 mt-1">{{ __('messages.manage_restaurants_overview') }}</p>
            </div>
            <a href="{{ route('admin.restaurant.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-2"></i>
                {{ __('messages.add_restaurant') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="responsive-grid mb-8">
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-blue-500 to-blue-600">
                        <i class="fas fa-store text-white text-2xl"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ __('messages.total_restaurants') }}</h3>
                        <p class="text-3xl font-bold text-blue-400">{{ $restaurants->count() }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-green-500 to-green-600">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ __('messages.active_restaurants') }}</h3>
                        <p class="text-3xl font-bold text-green-400">{{ $restaurants->where('is_active', true)->count() }}</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-purple-500 to-purple-600">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ __('messages.restaurant_owners') }}</h3>
                        <p class="text-3xl font-bold text-purple-400">{{ $users->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Restaurants Table -->
            <div class="card">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">{{ __('messages.all_restaurants') }}</h3>
                        <p class="text-gray-400 mt-1">{{ __('messages.manage_accounts_settings') }}</p>
                    </div>
                </div>
                
                @if($restaurants->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-store text-6xl text-gray-500 mb-4"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-white mb-4">{{ __('messages.no_restaurants_yet') }}</h3>
                        <p class="text-gray-400 mb-8 text-lg">{{ __('messages.start_adding_first_restaurant') }}</p>
                        <a href="{{ route('admin.restaurant.create') }}" class="btn btn-success">
                            <i class="fas fa-plus mr-2"></i>
                            {{ __('messages.add_first_restaurant') }}
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-600">
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.restaurant') }}</th>
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.url') }}</th>
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.owner') }}</th>
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.phone_number') }}</th>
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.created') }}</th>
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.status') }}</th>
                                    <th class="text-left py-4 px-4 text-gray-300 font-semibold">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($restaurants as $restaurant)
                                    <tr class="table-row">
                                        <td class="py-4 px-4">
                                            <div class="flex items-center">
                                                @if($restaurant->logo)
                                                    <img src="{{ asset('storage/' . $restaurant->logo) }}" 
                                                         alt="{{ $restaurant->name }}" 
                                                         class="restaurant-avatar">
                                                @else
                                                    <div class="restaurant-avatar-placeholder">
                                                        <span class="text-white font-semibold text-lg">{{ substr($restaurant->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                                <div class="ml-4">
                                                    <div class="text-white font-semibold text-lg">{{ $restaurant->name }}</div>
                                                    @if($restaurant->description)
                                                        <div class="text-gray-400 text-sm mt-1">{{ Str::limit($restaurant->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <code class="url-badge">{{ $restaurant->slug }}</code>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-white font-medium">{{ $restaurant->user->name }}</div>
                                            <div class="text-gray-400 text-sm">{{ $restaurant->user->email }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-white font-medium">
                                                @if($restaurant->user->phone)
                                                    <i class="fas fa-phone text-green-400 mr-2"></i>{{ $restaurant->user->phone }}
                                                @else
                                                    <span class="text-gray-500">{{ __('messages.not_provided') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-white font-medium">
                                                <i class="fas fa-calendar text-blue-400 mr-2"></i>
                                                {{ $restaurant->created_at->format('M j, Y') }}
                                            </div>
                                            <div class="text-gray-400 text-sm">
                                                {{ $restaurant->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="status-badge {{ $restaurant->is_active ? 'status-active' : 'status-inactive' }}">
                                                <i class="fas {{ $restaurant->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                                {{ $restaurant->is_active ? __('messages.active') : __('messages.inactive') }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center space-x-3">
                                                <a href="{{ route('menu.show', $restaurant->slug) }}"
                                                    target="_blank"
                                                    class="action-btn action-view"
                                                    title="{{ __('messages.view_menu') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        class="w-5 h-5">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14 3h7m0 0v7m0-7L10 14m-4 7h12a2 2 0 002-2V10a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </a>

                                                <a href="{{ route('admin.user.edit', $restaurant->user) }}"
                                                    class="action-btn action-edit"
                                                    title="{{ __('messages.edit_user') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        class="w-5 h-5">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>

                                                <form action="{{ route('admin.restaurant.toggle', $restaurant) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="action-btn {{ $restaurant->is_active ? 'action-disable' : 'action-enable' }}"
                                                            title="{{ $restaurant->is_active ? __('messages.disable') : __('messages.enable') }} {{ __('messages.restaurant') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" 
                                                            viewBox="0 0 24 24" 
                                                            fill="none" 
                                                            stroke="currentColor" 
                                                            stroke-width="2" 
                                                            stroke-linecap="round" 
                                                            stroke-linejoin="round" 
                                                            class="w-6 h-6 text-red-600">
                                                        <!-- User head -->
                                                        <circle cx="12" cy="7" r="4" />
                                                        <!-- User shoulders -->
                                                        <path d="M5.5 21a8.38 8.38 0 0 1 13 0" />
                                                        <!-- Disable slash -->
                                                        <line x1="4" y1="4" x2="20" y2="20" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                <button type="button" 
                                                        class="action-btn action-delete"
                                                        title="{{ __('messages.delete_restaurant') }}"
                                                        onclick="confirmDelete('{{ route('admin.restaurant.delete', $restaurant) }}', '{{ $restaurant->name }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                                        viewBox="0 0 24 24" 
                                                        fill="none" 
                                                        stroke="currentColor" 
                                                        stroke-width="2" 
                                                        stroke-linecap="round" 
                                                        stroke-linejoin="round" 
                                                        class="w-6 h-6 text-red-600">
                                                    <!-- Trash can -->
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2" />
                                                    <!-- Inside lines -->
                                                    <line x1="10" y1="11" x2="10" y2="17" />
                                                    <line x1="14" y1="11" x2="14" y2="17" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Unpaid Subscriptions -->
            @if($unpaidSubscriptions->isNotEmpty())
            <div class="card mt-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Unpaid Subscriptions</h3>
                        <p class="text-gray-400 mt-1">Restaurant owners who need to pay their subscription</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-600">
                                <th class="text-left py-4 px-4 text-gray-300 font-semibold">Owner</th>
                                <th class="text-left py-4 px-4 text-gray-300 font-semibold">Amount</th>
                                <th class="text-left py-4 px-4 text-gray-300 font-semibold">Status</th>
                                <th class="text-left py-4 px-4 text-gray-300 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unpaidSubscriptions as $subscription)
                                <tr class="table-row">
                                    <td class="py-4 px-4">
                                        <div class="text-white font-medium">{{ $subscription->user->name }}</div>
                                        <div class="text-gray-400 text-sm">{{ $subscription->user->email }}</div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-white font-medium">${{ number_format($subscription->amount, 2) }}</div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            {{ $subscription->paid_at ? 'Expired' : 'Unpaid' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.subscription.edit', $subscription) }}"
                                               class="action-btn action-edit"
                                               title="Edit Cost">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.subscription.mark-paid', $subscription) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="action-btn action-enable" title="Mark as Paid">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                <h3 class="text-2xl font-bold text-white mb-2">{{ __('messages.delete_restaurant_title') }}</h3>
                <p class="text-gray-300">{{ __('messages.action_cannot_be_undone') }}</p>
            </div>
            
            <div class="modal-body">
                <div class="warning-box">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-exclamation-circle text-red-400 text-lg mt-1"></i>
                        <div>
                            <p class="text-gray-300 font-medium mb-2">{{ __('messages.following_will_be_deleted') }}</p>
                            <ul class="text-gray-400 space-y-1 text-sm">
                                <li>• {{ __('messages.restaurant_profile_settings') }}</li>
                                <li>• {{ __('messages.all_menu_categories_items') }}</li>
                                <li>• {{ __('messages.all_uploaded_images') }}</li>
                                <li>• {{ __('messages.all_associated_data') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <p class="text-gray-300 mt-6">
                    {{ __('messages.confirm_delete_restaurant') }} <strong><span id="restaurant-name"></span></strong>?
                </p>
            </div>
            
            <div class="modal-footer">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.cancel') }}
                </button>
                <form id="delete-form" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('messages.delete_restaurant') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(deleteUrl, restaurantName) {
            document.getElementById('restaurant-name').textContent = restaurantName;
            document.getElementById('delete-form').action = deleteUrl;
            document.getElementById('delete-modal').classList.remove('hidden');
            document.getElementById('delete-modal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            document.getElementById('delete-modal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('delete-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>

    <!-- Include your existing CSS styles here -->
    <style>
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-xl);
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--border-secondary);
        }

        .stat-icon {
            width: 4rem;
            height: 4rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-content {
            flex: 1;
        }

        .restaurant-avatar {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-secondary);
            box-shadow: var(--shadow-md);
        }

        .restaurant-avatar-placeholder {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border-secondary);
            box-shadow: var(--shadow-md);
        }

        .url-badge {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-lg);
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.875rem;
            border: 1px solid var(--border-primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .action-btn {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .action-view {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .action-view:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: scale(1.1);
        }

        .action-edit {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .action-edit:hover {
            background: rgba(245, 158, 11, 0.2);
            transform: scale(1.1);
        }

        .action-enable {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .action-enable:hover {
            background: rgba(34, 197, 94, 0.2);
            transform: scale(1.1);
        }

        .action-disable {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .action-disable:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: scale(1.1);
        }

        .action-delete {
            background: rgba(220, 38, 38, 0.1);
            color: #f87171;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .action-delete:hover {
            background: rgba(220, 38, 38, 0.2);
            transform: scale(1.1);
        }

        .table-row {
            border-bottom: 1px solid var(--border-primary);
            transition: all 0.3s ease;
        }

        .table-row:hover {
            background: var(--bg-elevated);
            transform: translateX(4px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            margin-bottom: 2rem;
            opacity: 0.6;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.hidden {
            display: none;
        }

        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-secondary);
            border-radius: var(--radius-xl);
            padding: 0;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            text-align: center;
            padding: 2rem 2rem 1rem;
        }

        .modal-body {
            padding: 0 2rem 1rem;
        }

        .modal-footer {
            padding: 2rem;
            border-top: 1px solid var(--border-primary);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .warning-box {
            background: rgba(220, 38, 38, 0.05);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: 1px solid #dc2626;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        }
    </style>
</x-app-layout>