<x-guest-layout>
    <div class="auth-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">{{ __('messages.welcome_back') }}</h2>
        <p class="text-gray-400">{{ __('messages.sign_in_to_continue') }}</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <!-- Email Address -->
        <div class="form-field">
            <label for="email" class="form-label">
                <i class="fas fa-envelope mr-2"></i>
                {{ __('messages.email_address') }}
            </label>
            <input id="email" 
                   class="form-input @error('email') input-error @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   placeholder="{{ __('messages.enter_your_email') }}"
                   required 
                   autofocus 
                   autocomplete="username">
            @error('email')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-field">
            <label for="password" class="form-label">
                <i class="fas fa-lock mr-2"></i>
                {{ __('messages.password') }}
            </label>
            <div class="password-wrapper">
                <input id="password" 
                       class="form-input @error('password') input-error @enderror" 
                       type="password" 
                       name="password" 
                       placeholder="{{ __('messages.enter_your_password') }}"
                       required 
                       autocomplete="current-password">
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i id="password-icon" class="fas fa-eye-slash"></i>
                </button>
            </div>
            @error('password')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-field">
            <label for="remember_me" class="checkbox-label">
                <input id="remember_me" 
                       type="checkbox" 
                       class="checkbox-input" 
                       name="remember">
                <span class="checkbox-custom"></span>
                <span class="checkbox-text">{{ __('messages.remember_me') }}</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-sign-in-alt mr-2"></i>
                {{ __('messages.sign_in') }}
            </button>
        </div>

        <div class="auth-links">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">
                    <i class="fas fa-key mr-2"></i>
                    {{ __('messages.forgot_password') }}
                </a>
            @endif
            
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye-slash';
            }
        }

        // Add floating label effect
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>

    <style>
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .logo-image {
            max-width: 120px;
            max-height: 120px;
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: brightness(0) invert(1);
            transition: all 0.3s ease;
        }

        .logo-image:hover {
            transform: scale(1.05);
            filter: brightness(0) invert(1) drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
        }

        .auth-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-field {
            display: grid;
            gap: 0.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .form-input {
            background: var(--bg-tertiary);
            border: 2px solid var(--border-primary);
            color: var(--text-primary);
            padding: 1rem;
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

        .input-error {
            border-color: #ef4444;
        }

        .input-error:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .form-error {
            display: flex;
            align-items: center;
            color: #fca5a5;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-lg);
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--text-primary);
            background: var(--bg-elevated);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .checkbox-custom {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid var(--border-primary);
            border-radius: 0.375rem;
            margin-right: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
            background: var(--bg-tertiary);
        }

        .checkbox-input:checked + .checkbox-custom {
            background: var(--primary-gradient);
            border-color: #667eea;
        }

        .checkbox-input:checked + .checkbox-custom::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
        }

        .checkbox-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-actions {
            margin-top: 1rem;
        }

        .btn-full {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
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

        .auth-links {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
            text-align: center;
        }

        .auth-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.5rem;
            border-radius: var(--radius-lg);
            transition: all 0.3s ease;
        }

        .auth-link:hover {
            color: var(--text-primary);
            background: var(--bg-elevated);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-primary);
        }

        .auth-divider span {
            padding: 0 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            border-left: 4px solid;
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        .alert-success {
            background: rgba(67, 233, 123, 0.1);
            border-left-color: #43e97b;
            color: #86efac;
        }
    </style>
</x-guest-layout>