@error($field, $bag ?? 'default')
    <p class="adm-field-error" id="{{ $field }}-error">{{ $message }}</p>
@enderror