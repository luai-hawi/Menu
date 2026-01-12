<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('messages.edit_restaurant') }}
                </h2>
                <p class="text-gray-400 mt-1">{{ __('messages.modify_restaurant_details') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="create-form-card">
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-edit text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">{{ __('messages.edit_restaurant_details') }}</h3>
                    <p class="text-gray-400">{{ __('messages.update_restaurant_information') }}</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-error mb-6">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.restaurant.update', $restaurant) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="name" class="form-label">
                                <i class="fas fa-store mr-2"></i>
                                {{ __('messages.restaurant_name') }}
                            </label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', $restaurant->name) }}"
                                placeholder="{{ __('messages.enter_restaurant_name') }}" class="form-input" required>
                        </div>

                        <div class="form-field">
                            <label for="slug" class="form-label">
                                <i class="fas fa-link mr-2"></i>
                                {{ __('messages.url_slug') }}
                            </label>
                            <input type="text" id="slug" name="slug"
                                value="{{ old('slug', $restaurant->slug) }}"
                                placeholder="{{ __('messages.restaurant_url_slug') }}" class="form-input" required>
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.url_slug_help') }}<strong>yourdomain.com/<span
                                        id="slug-preview">{{ $restaurant->slug }}</span></strong>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left mr-2"></i>
                                {{ __('messages.restaurant_description') }}
                            </label>
                            <textarea id="description" name="description" rows="4"
                                placeholder="{{ __('messages.describe_restaurant_optional') }}" class="form-input">{{ old('description', $restaurant->description) }}</textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('messages.update_restaurant') }}
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

    <script>
        // Auto-generate slug from restaurant name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();

            document.getElementById('slug').value = slug;
            document.getElementById('slug-preview').textContent = slug || 'slug';
        });

        // Update slug preview when manually edited
        document.getElementById('slug').addEventListener('input', function() {
            document.getElementById('slug-preview').textContent = this.value || 'slug';
        });
    </script>

    <style>
        .create-form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .create-form-card::before {
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

        @media (max-width: 640px) {
            .create-form-card {
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
    </style>
</x-app-layout>
