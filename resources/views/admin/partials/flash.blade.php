@if (session('success'))
    <div class="adm-alert adm-alert-success" role="status">
        <i class="fas fa-check-circle adm-icon-gap"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="adm-alert adm-alert-error" role="alert">
        <i class="fas fa-exclamation-triangle adm-icon-gap"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if (($bag ?? null) && $errors->getBag($bag)->any())
    <div class="adm-alert adm-alert-error" role="alert">
        <i class="fas fa-exclamation-triangle adm-icon-gap"></i>
        <div>
            <p class="adm-strong">{{ __('admin.common.form_has_errors') }}</p>
            <ul class="adm-error-list">
                @foreach ($errors->getBag($bag)->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif