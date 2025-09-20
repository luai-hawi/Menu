<x-guest-layout>
    <div class="auth-header">
        <div class="auth-icon">
            <i class="fas fa-user-plus text-white text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Create Account</h2>
        <p class="text-gray-400">Join our platform to manage your restaurant</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <!-- Name -->
        <div class="form-field">
            <label for="name" class="form-label">
                <i class="fas fa-user mr-2"></i>
                Full Name
            </label>
            <input id="name" 
                   class="form-input @error('name') input-error @enderror" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   placeholder="Enter your full name"
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="form-field">
            <label for="email" class="form-label">
                <i class="fas fa-envelope mr-2"></i>
                Email Address
            </label>
            <input id="email" 
                   class="form-input @error('email') input-error @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   placeholder="Enter your email address"
                   required 
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
                Password
            </label>
            <div class="password-wrapper">
                <input id="password" 
                       class="form-input @error('password') input-error @enderror" 
                       type="password" 
                       name="password" 
                       placeholder="Create a strong password"
                       required 
                       autocomplete="new-password"
                       onkeyup="checkPasswordStrength(this.value)">
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                    <i id="password-icon" class="fas fa-eye-slash"></i>
                </button>
            </div>
            <div class="password-strength">
                <div class="strength-bar">
                    <div id="strength-fill" class="strength-fill"></div>
                </div>
                <div id="strength-text" class="strength-text">Password strength</div>
            </div>
            @error('password')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-field">
            <label for="password_confirmation" class="form-label">
                <i class="fas fa-lock mr-2"></i>
                Confirm Password
            </label>
            <div class="password-wrapper">
                <input id="password_confirmation" 
                       class="form-input @error('password_confirmation') input-error @enderror" 
                       type="password" 
                       name="password_confirmation" 
                       placeholder="Confirm your password"
                       required 
                       autocomplete="new-password"
                       onkeyup="checkPasswordMatch()">
                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                    <i id="password-confirmation-icon" class="fas fa-eye-slash"></i>
                </button>
            </div>
            <div id="password-match" class="password-match hidden">
                <i class="fas fa-check-circle mr-2"></i>
                Passwords match
            </div>
            @error('password_confirmation')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus mr-2"></i>
                Create Account
            </button>
        </div>

        <div class="auth-links">
            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>
            
            <a href="{{ route('login') }}" class="btn btn-secondary btn-full">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In Instead
            </a>
        </div>
    </form>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const passwordIcon = document.getElementById(fieldId + '-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye-slash';
            }
        }

        function checkPasswordStrength(password) {
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    text = 'Weak password';
                    color = '#ef4444';
                    break;
                case 2:
                case 3:
                    text = 'Medium password';
                    color = '#f59e0b';
                    break;
                case 4:
                case 5:
                    text = 'Strong password';
                    color = '#10b981';
                    break;
            }
            
            strengthFill.style.width = (strength * 20) + '%';
            strengthFill.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchIndicator = document.getElementById('password-match');
            
            if (confirmation && password === confirmation) {
                matchIndicator.classList.remove('hidden');
                matchIndicator.classList.add('match-success');
            } else {
                matchIndicator.classList.add('hidden');
                matchIndicator.classList.remove('match-success');
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

        .auth-icon {
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

        .password-strength {
            margin-top: 0.75rem;
        }

        .strength-bar {
            width: 100%;
            height: 4px;
            background: var(--bg-tertiary);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-text {
            font-size: 0.875rem;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .password-match {
            display: flex;
            align-items: center;
            color: #10b981;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .password-match.hidden {
            display: none;
        }

        .match-success {
            color: #10b981;
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
    </style>
</x-guest-layout>