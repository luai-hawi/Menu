<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('messages.edit_user') }}
                </h2>
                <p class="text-gray-400 mt-1">{{ $user->name }} - {{ $user->email }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="edit-form-card">
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-user-edit text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">{{ __('messages.edit_user_details') }}</h3>
                    <p class="text-gray-400">{{ __('messages.update_user_information') }}</p>
                </div>

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

                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success mb-6">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.user.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <!-- Name -->
                        <div class="form-field">
                            <label for="name" class="form-label">
                                <i class="fas fa-user mr-2"></i>
                                {{ __('messages.full_name') }}
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   placeholder="{{ __('messages.enter_full_name') }}"
                                   class="form-input"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="form-field">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope mr-2"></i>
                                {{ __('messages.email_address') }}
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="{{ __('messages.enter_your_email') }}"
                                   class="form-input"
                                   required>
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.email_change_warning') }}
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="form-field">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone mr-2"></i>
                                {{ __('messages.phone_number') }}
                            </label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="{{ __('messages.enter_phone_number') }}"
                                   class="form-input">
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.phone_optional') }}
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="password-section">
                            <h4 class="section-title">{{ __('messages.change_password_optional') }}</h4>
                            <p class="section-description">{{ __('messages.leave_blank_keep_password') }}</p>

                            <!-- New Password -->
                            <div class="form-field">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock mr-2"></i>
                                    {{ __('messages.new_password') }}
                                </label>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       placeholder="{{ __('messages.enter_new_password') }}"
                                       class="form-input"
                                       minlength="8">
                                <div class="form-help">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    {{ __('messages.password_min_8_chars') }}
                                </div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="form-field">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock mr-2"></i>
                                    {{ __('messages.confirm_new_password') }}
                                </label>
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="{{ __('messages.confirm_new_password') }}"
                                       class="form-input"
                                       minlength="8">
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('messages.update_user') }}
                            </button>
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .edit-form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .edit-form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .form-icon {
            width: 4rem;
            height: 4rem;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .form-grid {
            display: grid;
            gap: 2rem;
        }

        .form-field {
            display: grid;
            gap: 0.75rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
        }

        .form-input {
            background: var(--bg-tertiary);
            border: 2px solid var(--border-primary);
            color: var(--text-primary);
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
        }

        .form-input:focus {
            background: var(--bg-elevated);
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .form-help {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .password-section {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-top: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .section-description {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            flex: 1;
            min-width: 200px;
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            border: 2px solid var(--border-primary);
        }

        .btn-secondary:hover {
            background: var(--bg-elevated);
            border-color: var(--border-secondary);
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-lg);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: 1px solid #10b981;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        @media (max-width: 640px) {
            .edit-form-card {
                padding: 2rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-lg {
                flex: none;
                min-width: auto;
            }
        }
    </style>
</x-app-layout>