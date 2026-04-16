{{--
    Option Groups Editor (Arabic-only, mobile-friendly)
    ===================================================
    Nested form UI for editing an item's option groups and options.
    All labels, placeholders and helpers pull from messages.optionGroups.*
    and messages.options.* so the whole UI speaks the current locale.

    Props:
      $groups        array|[]    existing groups
      $fieldPrefix   string      input name prefix (default "option_groups")
--}}
@php
    $groups      = $groups      ?? [];
    $fieldPrefix = $fieldPrefix ?? 'option_groups';
@endphp

<div
    x-data="optionGroupsEditor(@js($groups))"
    x-cloak
    class="og-editor"
>
    <div class="og-header">
        <h4 class="og-heading">
            <i class="fas fa-sliders-h"></i>
            {{ __('messages.optionGroups.title') }}
        </h4>
        <button type="button"
                @click="addGroup()"
                class="og-btn og-btn-primary">
            <i class="fas fa-plus"></i>
            <span>{{ __('messages.optionGroups.add') }}</span>
        </button>
    </div>

    <template x-if="groups.length === 0">
        <div class="og-empty">
            <i class="fas fa-layer-group"></i>
            <p>{{ __('messages.optionGroups.empty_state') }}</p>
        </div>
    </template>

    <div x-ref="groupsList"
         x-init="initGroupsSortable()"
         class="og-groups">
        <template x-for="(group, gIdx) in groups" :key="group._key">
            <div :data-id="group._key" class="og-group">
                <!-- Group header: drag handle + name + type selector -->
                <div class="og-group-top">
                    <span class="og-handle" :title="'{{ __('messages.optionGroups.drag_to_reorder') }}'">
                        <i class="fas fa-grip-vertical"></i>
                    </span>

                    <div class="og-group-name">
                        <label class="og-label">{{ __('messages.optionGroups.name_ar') }}</label>
                        <input type="text"
                               :name="`{{ $fieldPrefix }}[${gIdx}][group_name_ar]`"
                               x-model="group.group_name_ar"
                               dir="rtl"
                               placeholder="{{ __('messages.optionGroups.name_placeholder') }}"
                               class="og-input" required>
                    </div>

                    <button type="button"
                            @click="removeGroup(gIdx)"
                            class="og-btn-icon og-btn-danger"
                            :title="'{{ __('messages.optionGroups.remove') }}'">
                        <i class="fas fa-trash"></i>
                    </button>

                    <input type="hidden" :name="`{{ $fieldPrefix }}[${gIdx}][id]`" :value="group.id ?? ''">
                    <input type="hidden"
                           :name="`{{ $fieldPrefix }}[${gIdx}][position]`"
                           :value="gIdx"
                           data-position-input>
                </div>

                <!-- Type switch (segmented buttons) -->
                <div class="og-row">
                    <label class="og-label">{{ __('messages.optionGroups.type') }}</label>
                    <div class="og-segmented">
                        <button type="button"
                                class="og-seg"
                                :class="{ 'og-seg-active': group.group_type === 'SINGLE' }"
                                @click="group.group_type = 'SINGLE'; onTypeChange(gIdx)">
                            <i class="fas fa-dot-circle"></i>
                            <span>{{ __('messages.optionGroups.type_single') }}</span>
                        </button>
                        <button type="button"
                                class="og-seg"
                                :class="{ 'og-seg-active': group.group_type === 'MULTIPLE' }"
                                @click="group.group_type = 'MULTIPLE'; onTypeChange(gIdx)">
                            <i class="fas fa-check-square"></i>
                            <span>{{ __('messages.optionGroups.type_multiple') }}</span>
                        </button>
                    </div>
                    <input type="hidden"
                           :name="`{{ $fieldPrefix }}[${gIdx}][group_type]`"
                           :value="group.group_type">
                </div>

                <!-- Required toggle + (MULTIPLE only) min/max -->
                <div class="og-row og-row-inline">
                    <label class="og-toggle">
                        <input type="checkbox"
                               :name="`{{ $fieldPrefix }}[${gIdx}][is_required]`"
                               value="1"
                               x-model="group.is_required">
                        <span>{{ __('messages.optionGroups.required') }}</span>
                    </label>

                    <template x-if="group.group_type === 'MULTIPLE'">
                        <div class="og-minmax">
                            <div>
                                <label class="og-label">{{ __('messages.optionGroups.min_choices') }}</label>
                                <input type="number" min="0"
                                       :name="`{{ $fieldPrefix }}[${gIdx}][min_choices]`"
                                       x-model.number="group.min_choices"
                                       class="og-input og-input-sm">
                            </div>
                            <div>
                                <label class="og-label">{{ __('messages.optionGroups.max_choices') }}</label>
                                <input type="number" min="1"
                                       :name="`{{ $fieldPrefix }}[${gIdx}][max_choices]`"
                                       x-model.number="group.max_choices"
                                       class="og-input og-input-sm">
                            </div>
                        </div>
                    </template>
                </div>

                <p class="og-help" x-show="group.group_type === 'SINGLE'">
                    <i class="fas fa-info-circle"></i>
                    {{ __('messages.optionGroups.single_help') }}
                </p>
                <p class="og-help" x-show="group.group_type === 'MULTIPLE'">
                    <i class="fas fa-info-circle"></i>
                    {{ __('messages.optionGroups.multiple_help') }}
                </p>

                <template x-if="validateGroup(group)">
                    <div class="og-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span x-text="validateGroup(group)"></span>
                    </div>
                </template>

                <!-- Options -->
                <div class="og-options">
                    <div class="og-options-header">
                        <span class="og-label">{{ __('messages.options.title') }}</span>
                        <button type="button"
                                @click="addOption(gIdx)"
                                class="og-btn og-btn-success og-btn-sm">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('messages.options.add') }}</span>
                        </button>
                    </div>

                    <template x-if="group.options.length === 0">
                        <p class="og-empty-sm">{{ __('messages.options.empty_state') }}</p>
                    </template>

                    <div :x-ref="`options-${gIdx}`"
                         x-init="initOptionsSortable(gIdx)"
                         class="og-options-list">
                        <template x-for="(opt, oIdx) in group.options" :key="opt._key">
                            <div :data-id="opt._key" class="og-option">
                                <span class="og-handle og-handle-opt"
                                      :title="'{{ __('messages.options.drag_to_reorder') }}'">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>

                                <div class="og-option-fields">
                                    <input type="text"
                                           :name="`{{ $fieldPrefix }}[${gIdx}][options][${oIdx}][option_name_ar]`"
                                           x-model="opt.option_name_ar"
                                           dir="rtl"
                                           placeholder="{{ __('messages.options.name_placeholder') }}"
                                           class="og-input og-option-name" required>

                                    <div class="og-option-price">
                                        <input type="number" step="0.01"
                                               :name="`{{ $fieldPrefix }}[${gIdx}][options][${oIdx}][price_delta]`"
                                               x-model.number="opt.price_delta"
                                               placeholder="0.00"
                                               class="og-input og-input-sm">
                                        <span class="og-price-hint">{{ __('messages.currency_symbol') }}</span>
                                    </div>

                                    <div class="og-option-actions">
                                        <button type="button"
                                                @click="opt._notesOpen = !opt._notesOpen"
                                                class="og-btn-link"
                                                :title="opt._notesOpen ? '{{ __('messages.options.hide_note') }}' : '{{ __('messages.options.add_note') }}'">
                                            <i class="fas fa-sticky-note"></i>
                                        </button>
                                        <label class="og-active-toggle"
                                               :title="'{{ __('messages.options.is_active') }}'">
                                            <input type="checkbox"
                                                   :name="`{{ $fieldPrefix }}[${gIdx}][options][${oIdx}][is_active]`"
                                                   value="1"
                                                   x-model="opt.is_active">
                                            <i :class="opt.is_active ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
                                        </label>
                                        <button type="button"
                                                @click="removeOption(gIdx, oIdx)"
                                                class="og-btn-icon og-btn-danger og-btn-sm">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Note (collapsible) -->
                                    <div class="og-note" x-show="opt._notesOpen" x-transition x-cloak>
                                        <input type="text" maxlength="160" dir="rtl"
                                               :name="`{{ $fieldPrefix }}[${gIdx}][options][${oIdx}][option_note_ar]`"
                                               x-model="opt.option_note_ar"
                                               placeholder="{{ __('messages.options.note_placeholder') }}"
                                               class="og-input og-input-sm">
                                        <small class="og-help-sm"
                                               x-text="`{{ __('messages.common.characters_left', ['count' => ':c']) }}`.replace(':c', 160 - (opt.option_note_ar?.length || 0))">
                                        </small>
                                        <small class="og-help-sm og-help-muted">
                                            <i class="fas fa-info-circle"></i>
                                            {{ __('messages.options.note_help') }}
                                        </small>
                                    </div>

                                    <input type="hidden" :name="`{{ $fieldPrefix }}[${gIdx}][options][${oIdx}][id]`" :value="opt.id ?? ''">
                                    <input type="hidden"
                                           :name="`{{ $fieldPrefix }}[${gIdx}][options][${oIdx}][position]`"
                                           :value="oIdx"
                                           data-position-input>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    /*
     * Alpine component for the option-groups editor.
     * Kept global so multiple editor instances share the same factory.
     */
    function optionGroupsEditor(initial = []) {
        let uid = 0;
        const makeKey = () => `k_${++uid}_${Date.now()}`;

        const hydrate = (groups) => (groups || []).map((g) => ({
            _key: makeKey(),
            id: g.id ?? null,
            group_type:   g.group_type   || 'SINGLE',
            group_name_ar: g.group_name_ar || '',
            min_choices: Number(g.min_choices ?? 0),
            max_choices: Number(g.max_choices ?? 1),
            is_required: !!g.is_required,
            position: Number(g.position ?? 0),
            options: (g.options || []).map((o) => ({
                _key: makeKey(),
                _notesOpen: !!o.option_note_ar,
                id: o.id ?? null,
                option_name_ar: o.option_name_ar || '',
                price_delta: Number(o.price_delta ?? 0),
                option_note_ar: o.option_note_ar || '',
                position: Number(o.position ?? 0),
                is_active: o.is_active !== false,
            })),
        }));

        return {
            groups: hydrate(initial),

            addGroup() {
                this.groups.push({
                    _key: makeKey(),
                    id: null,
                    group_type: 'SINGLE',
                    group_name_ar: '',
                    min_choices: 0,
                    max_choices: 1,
                    is_required: false,
                    position: this.groups.length,
                    options: [],
                });
                this.addOption(this.groups.length - 1);
            },

            removeGroup(gIdx) {
                this.groups.splice(gIdx, 1);
            },

            addOption(gIdx) {
                const g = this.groups[gIdx];
                if (!g) return;
                g.options.push({
                    _key: makeKey(),
                    _notesOpen: false,
                    id: null,
                    option_name_ar: '',
                    price_delta: 0,
                    option_note_ar: '',
                    position: g.options.length,
                    is_active: true,
                });
            },

            removeOption(gIdx, oIdx) {
                this.groups[gIdx].options.splice(oIdx, 1);
            },

            onTypeChange(gIdx) {
                const g = this.groups[gIdx];
                if (g.group_type === 'SINGLE') {
                    g.max_choices = 1;
                    g.min_choices = g.is_required ? 1 : 0;
                }
            },

            validateGroup(g) {
                const optCount = g.options.length;
                if (optCount === 0 && g.group_name_ar) {
                    return @js(__('messages.errors.group_needs_options'));
                }
                if (g.group_type === 'MULTIPLE') {
                    if (g.max_choices > 0 && g.min_choices > g.max_choices) {
                        return @js(__('messages.errors.min_greater_than_max'));
                    }
                    if (g.max_choices > optCount) {
                        return @js(__('messages.errors.max_exceeds_options', ['count' => ':n'])).replace(':n', optCount);
                    }
                }
                return null;
            },

            initGroupsSortable() {
                this.$nextTick(() => {
                    if (!this.$refs.groupsList || !window.Sortable) return;
                    window.Sortable.create(this.$refs.groupsList, {
                        animation: 150,
                        handle: '.og-handle:not(.og-handle-opt)',
                        ghostClass: 'sortable-ghost',
                        onEnd: (e) => {
                            if (e.oldIndex === e.newIndex) return;
                            const moved = this.groups.splice(e.oldIndex, 1)[0];
                            this.groups.splice(e.newIndex, 0, moved);
                        },
                    });
                });
            },

            initOptionsSortable(gIdx) {
                this.$nextTick(() => {
                    const ref = this.$refs[`options-${gIdx}`];
                    if (!ref || !window.Sortable) return;
                    window.Sortable.create(ref, {
                        animation: 150,
                        handle: '.og-handle-opt',
                        ghostClass: 'sortable-ghost',
                        onEnd: (e) => {
                            if (e.oldIndex === e.newIndex) return;
                            const moved = this.groups[gIdx].options.splice(e.oldIndex, 1)[0];
                            this.groups[gIdx].options.splice(e.newIndex, 0, moved);
                        },
                    });
                });
            },
        };
    }
    window.optionGroupsEditor = optionGroupsEditor;
</script>

<style>
    [x-cloak] { display: none !important; }

    .og-editor {
        border: 1px dashed rgba(148, 163, 184, 0.3);
        border-radius: 14px;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.4);
    }
    .og-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
        flex-wrap: wrap;
    }
    .og-heading {
        color: #e2e8f0;
        font-weight: 600;
        font-size: 1.05rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }
    .og-heading i { color: #a5b4fc; }

    .og-groups { display: flex; flex-direction: column; gap: 0.85rem; }

    .og-group {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 0.9rem;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
    .og-group-top {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .og-group-name { flex: 1; }

    .og-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 0.25rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .og-input {
        width: 100%;
        background: #0f172a !important;
        border: 1px solid #334155 !important;
        border-radius: 8px !important;
        color: #f1f5f9 !important;
        padding: 0.55rem 0.7rem !important;
        font-size: 0.9rem !important;
        transition: border-color .15s ease;
    }
    .og-input:focus {
        border-color: #6366f1 !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }
    .og-input-sm { padding: 0.45rem 0.6rem !important; font-size: 0.85rem !important; }

    .og-row {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .og-row-inline {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 1rem;
    }
    .og-minmax {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.6rem;
        flex: 1;
        min-width: 12rem;
    }

    .og-segmented {
        display: inline-flex;
        border: 1px solid #334155;
        border-radius: 10px;
        overflow: hidden;
        background: #0f172a;
    }
    .og-seg {
        flex: 1;
        padding: 0.55rem 0.85rem;
        background: transparent;
        border: 0;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        font-size: 0.85rem;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .og-seg:hover { background: rgba(255,255,255,0.04); color: #e2e8f0; }
    .og-seg-active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff !important;
    }

    .og-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #e2e8f0;
        font-size: 0.9rem;
        cursor: pointer;
    }
    .og-toggle input[type="checkbox"] {
        accent-color: #6366f1;
        width: 1rem; height: 1rem;
    }

    .og-help {
        color: #94a3b8;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
    }
    .og-help-sm {
        display: block;
        color: #94a3b8;
        font-size: 0.72rem;
        margin-top: 0.25rem;
    }
    .og-help-muted { opacity: 0.8; }

    .og-error {
        background: rgba(239, 68, 68, 0.12);
        border-inline-start: 3px solid #ef4444;
        color: #fecaca;
        padding: 0.4rem 0.7rem;
        border-radius: 8px;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .og-handle {
        cursor: grab;
        padding: 0.5rem;
        color: #64748b;
        background: rgba(255,255,255,0.04);
        border-radius: 6px;
        user-select: none;
        flex-shrink: 0;
        transition: color .15s, background .15s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .og-handle:hover { color: #e2e8f0; background: rgba(255,255,255,0.08); }
    .og-handle:active { cursor: grabbing; }

    .og-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border: 0;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: transform .15s, opacity .15s, box-shadow .15s;
        font-size: 0.85rem;
        color: #fff !important;
    }
    .og-btn:hover { transform: translateY(-1px); }
    .og-btn-sm { padding: 0.4rem 0.7rem; font-size: 0.78rem; }
    .og-btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .og-btn-success { background: linear-gradient(135deg, #10b981, #059669); }

    .og-btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.6rem;
        border-radius: 8px;
        border: 0;
        cursor: pointer;
        background: rgba(239, 68, 68, 0.12);
        color: #fca5a5;
        transition: background .15s;
    }
    .og-btn-icon.og-btn-sm { padding: 0.35rem 0.5rem; font-size: 0.75rem; }
    .og-btn-icon:hover { background: rgba(239, 68, 68, 0.25); }
    .og-btn-danger { color: #fca5a5; }
    .og-btn-link {
        background: transparent;
        border: 0;
        color: #a5b4fc;
        cursor: pointer;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
    }
    .og-btn-link:hover { background: rgba(99, 102, 241, 0.1); }

    .og-options {
        margin-top: 0.35rem;
        padding-top: 0.75rem;
        border-top: 1px dashed #334155;
    }
    .og-options-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .og-options-list {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }
    .og-empty, .og-empty-sm {
        text-align: center;
        color: #94a3b8;
        padding: 1rem;
    }
    .og-empty i { font-size: 1.5rem; opacity: 0.5; }
    .og-empty p { margin-top: 0.5rem; font-size: 0.85rem; }
    .og-empty-sm { font-size: 0.8rem; padding: 0.5rem; font-style: italic; }

    .og-option {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid #2b3648;
        border-radius: 10px;
        padding: 0.45rem;
        display: flex;
        gap: 0.45rem;
        align-items: flex-start;
    }
    .og-option-fields {
        flex: 1;
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 0.4rem;
        align-items: center;
    }
    .og-option-name { grid-column: 1; }
    .og-option-price {
        position: relative;
        display: flex;
        align-items: center;
    }
    .og-option-price .og-input { padding-inline-end: 1.75rem !important; }
    .og-price-hint {
        position: absolute;
        inset-inline-end: 0.6rem;
        color: #64748b;
        font-size: 0.8rem;
        pointer-events: none;
    }
    .og-option-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }
    .og-active-toggle {
        display: inline-flex;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
        cursor: pointer;
        color: #94a3b8;
    }
    .og-active-toggle input { display: none; }
    .og-active-toggle:hover { background: rgba(255,255,255,0.05); }

    .og-note {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        margin-top: 0.35rem;
    }

    /* ==================== Mobile ==================== */
    @media (max-width: 640px) {
        .og-option-fields {
            grid-template-columns: 1fr;
            gap: 0.45rem;
        }
        .og-option-name, .og-option-price { grid-column: 1; }
        .og-option-actions {
            grid-column: 1;
            justify-content: flex-end;
            margin-top: 0.2rem;
        }
        .og-minmax { min-width: 100%; }
        .og-row-inline { flex-direction: column; align-items: stretch; }
        .og-segmented { width: 100%; }
        .og-seg { flex: 1; justify-content: center; }
    }
</style>
