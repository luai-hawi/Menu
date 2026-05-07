@props(['name', 'value', 'label', 'hint' => ''])
<label class="dash-color-field" x-data="{ color: '{{ $value }}' }" title="{{ $hint }}">
    <div class="dash-color-field-preview" :style="'background:' + color"></div>
    <input type="color" name="{{ $name }}" :value="color"
        class="dash-color-input"
        @input="color = $event.target.value; $el.closest('.dash-color-field').querySelector('.dash-color-field-preview').style.background = color; $el.closest('.dash-color-field').querySelector('.dash-color-hex').textContent = color;">
    <div class="dash-color-field-info">
        <span class="dash-color-label">{{ $label }}</span>
        @if ($hint)
            <span class="dash-color-hint">{{ $hint }}</span>
        @endif
        <span class="dash-color-hex">{{ $value }}</span>
    </div>
</label>
