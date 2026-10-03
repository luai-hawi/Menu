@props(['name', 'value', 'label', 'hint' => ''])
<label class="dash-color-field" x-data="{ color: @js($value) }" title="{{ $hint }}">
    <div class="dash-color-field-preview" :style="'background:' + color"></div>
    <input type="color" name="{{ $name }}" value="{{ $value }}" x-model="color" class="dash-color-input"
        aria-label="{{ $label }}">
    <div class="dash-color-field-info">
        <span class="dash-color-label">{{ $label }}</span>
        @if ($hint)
            <span class="dash-color-hint">{{ $hint }}</span>
        @endif
        <span class="dash-color-hex" x-text="color">{{ $value }}</span>
    </div>
</label>
