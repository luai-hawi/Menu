<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ __('messages.add_new_restaurant') }}
                </h2>
                <p class="text-gray-400 mt-1">{{ __('messages.create_new_restaurant_account') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="create-form-card">
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-store text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">{{ __('messages.restaurant_setup') }}</h3>
                    <p class="text-gray-400">{{ __('messages.fill_details_restaurant_account') }}</p>
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

                <form action="{{ route('admin.restaurant.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Owner Selection Section -->
                    <div class="owner-selection-section">
                        <h4 class="section-title">{{ __('messages.select_restaurant_owner') }}</h4>
                        <p class="section-description">{{ __('messages.choose_owner_method') }}</p>

                        <!-- Radio buttons for selection method -->
                        <div class="selection-method">
                            <div class="radio-option">
                                <input type="radio" id="method_existing" name="owner_method" value="existing" {{ old('owner_method', 'existing') === 'existing' ? 'checked' : '' }}>
                                <label for="method_existing">
                                    <i class="fas fa-user-check mr-2"></i>
                                    {{ __('messages.use_existing_owner') }}
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="method_new" name="owner_method" value="new" {{ old('owner_method') === 'new' ? 'checked' : '' }}>
                                <label for="method_new">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    {{ __('messages.create_new_owner') }}
                                </label>
                            </div>
                        </div>

                        <!-- Existing Owner Selection -->
                        <div class="form-field" id="existing-owner-field">
                            <label for="user_id" class="form-label">
                                <i class="fas fa-user mr-2"></i>
                                {{ __('messages.select_existing_owner') }}
                            </label>
                            <select id="user_id" name="user_id" class="form-input">
                                <option value="">{{ __('messages.choose_owner_from_list') }}</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}" {{ old('user_id') == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->name }} - {{ $owner->email }} ({{ $owner->restaurants_count }} {{ __('messages.restaurants') }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.existing_owner_help') }}
                            </div>
                        </div>

                        <!-- New Owner Creation -->
                        <div class="form-field" id="new-owner-field" style="display: none;">
                            <label for="owner_email" class="form-label">
                                <i class="fas fa-envelope mr-2"></i>
                                {{ __('messages.owner_email_address') }}
                            </label>
                            <input type="email"
                                   id="owner_email"
                                   name="owner_email"
                                   value="{{ old('owner_email') }}"
                                   placeholder="{{ __('messages.enter_owner_email') }}"
                                   class="form-input">
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.new_owner_help') }}
                            </div>
                        </div>

                        <div class="form-field" id="phone-field" style="display: none;">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone mr-2"></i>
                                {{ __('messages.phone_number') }}
                            </label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="{{ __('messages.enter_phone_number') }}"
                                   class="form-input">
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.phone_optional') }}
                            </div>
                        </div>

                        <div class="form-field" id="password-field" style="display: none;">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock mr-2"></i>
                                {{ __('messages.account_password') }}
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="{{ __('messages.enter_password_new_account') }}"
                                   class="form-input"
                                   minlength="8">
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.password_min_8_chars') }}
                            </div>
                        </div>

                        <div class="form-field" id="password-confirm-field" style="display: none;">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock mr-2"></i>
                                {{ __('messages.confirm_password') }}
                            </label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="{{ __('messages.confirm_password') }}"
                                   class="form-input"
                                   minlength="8">
                        </div>
                    </div>

                    <div class="form-grid">

                        <div class="form-field">
                            <label for="name" class="form-label">
                                <i class="fas fa-store mr-2"></i>
                                {{ __('messages.restaurant_name') }}
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="{{ __('messages.enter_restaurant_name') }}"
                                   class="form-input"
                                   required>
                        </div>

                        <div class="form-field">
                            <label for="slug" class="form-label">
                                <i class="fas fa-link mr-2"></i>
                                {{ __('messages.url_slug') }}
                            </label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug') }}"
                                   placeholder="{{ __('messages.restaurant_url_slug') }}"
                                   class="form-input"
                                   required>
                            <div class="form-help">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('messages.url_slug_help') }}<strong>yourdomain.com/<span id="slug-preview">slug</span></strong>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left mr-2"></i>
                                {{ __('messages.restaurant_description') }}
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="{{ __('messages.describe_restaurant_optional') }}"
                                      class="form-input">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-field">
                            <label for="logo" class="form-label">
                                <i class="fas fa-image mr-2"></i>
                                {{ __('messages.restaurant_logo') }}
                            </label>
                            <div class="file-upload-wrapper">
                                <input type="file"
                                       id="logo"
                                       name="logo"
                                       accept="image/*"
                                       class="file-input"
                                       onchange="previewImage(this)">
                                <div class="file-upload-area" onclick="document.getElementById('logo').click()">
                                    <div class="file-upload-content">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                        <p class="text-gray-300 font-medium mb-2">{{ __('messages.click_to_upload_logo') }}</p>
                                        <p class="text-gray-500 text-sm">{{ __('messages.supported_formats') }}</p>
                                    </div>
                                    <div id="image-preview" class="hidden">
                                        <img id="preview-img" src="" alt="Preview" class="preview-image">
                                        <p class="text-gray-300 text-sm mt-2">{{ __('messages.click_to_change_image') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('messages.create_restaurant') }}
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

        // Handle radio button changes for owner selection method
        document.querySelectorAll('input[name="owner_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const existingField = document.getElementById('existing-owner-field');
                const newField = document.getElementById('new-owner-field');
                const passwordField = document.getElementById('password-field');
                const passwordConfirmField = document.getElementById('password-confirm-field');
                const phoneField = document.getElementById('phone-field');
                const passwordInput = document.getElementById('password');
                const passwordConfirmInput = document.getElementById('password_confirmation');
                const phoneInput = document.getElementById('phone');
                const userIdSelect = document.getElementById('user_id');
                const emailInput = document.getElementById('owner_email');

                if (this.value === 'existing') {
                    // Show existing owner selection, hide new owner fields
                    existingField.style.display = 'grid';
                    newField.style.display = 'none';
                    passwordField.style.display = 'none';
                    passwordConfirmField.style.display = 'none';
                    phoneField.style.display = 'none';

                    // Clear new owner fields and make them not required
                    emailInput.value = '';
                    passwordInput.value = '';
                    passwordConfirmInput.value = '';
                    phoneInput.value = '';
                    emailInput.required = false;
                    passwordInput.required = false;
                    passwordConfirmInput.required = false;

                    // Make user_id required
                    userIdSelect.required = true;
                } else {
                    // Show new owner fields, hide existing owner selection
                    existingField.style.display = 'none';
                    newField.style.display = 'grid';
                    passwordField.style.display = 'grid';
                    passwordConfirmField.style.display = 'grid';
                    phoneField.style.display = 'grid';

                    // Clear existing owner selection and make it not required
                    userIdSelect.value = '';
                    userIdSelect.required = false;

                    // Make new owner fields required
                    emailInput.required = true;
                    passwordInput.required = true;
                    passwordConfirmInput.required = true;
                }
            });
        });

        // Image preview functionality
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.querySelector('.file-upload-content').classList.add('hidden');
                    document.getElementById('image-preview').classList.remove('hidden');
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        // On page load, trigger the radio button change to set initial state
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="owner_method"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
            } else {
                // Default to existing owner if none checked
                document.getElementById('method_existing').checked = true;
                document.getElementById('method_existing').dispatchEvent(new Event('change'));
            }
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

        .file-upload-wrapper {
            position: relative;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .file-upload-area {
            background: var(--bg-tertiary);
            border: 2px dashed var(--border-primary);
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            min-height: 12rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-upload-area:hover {
            border-color: var(--border-secondary);
            background: var(--bg-elevated);
            transform: translateY(-2px);
        }

        .preview-image {
            max-width: 8rem;
            max-height: 8rem;
            object-fit: cover;
            border-radius: var(--radius-lg);
            border: 2px solid var(--border-secondary);
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

        /* Owner Selection Styles */
        .owner-selection-section {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
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

        .selection-method {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-lg);
            transition: all 0.3s ease;
            cursor: pointer;
            flex: 1;
            min-width: 200px;
        }

        .radio-option:hover {
            border-color: var(--border-secondary);
            background: var(--bg-elevated);
        }

        .radio-option input[type="radio"] {
            display: none;
        }

        .radio-option input[type="radio"]:checked + label {
            color: #667eea;
            font-weight: 600;
        }

        .radio-option input[type="radio"]:checked ~ * {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .radio-option label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }

        .radio-option input[type="radio"]:checked + label::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            margin-right: 0.5rem;
            display: inline-block;
        }
    </style>
</x-app-layout>