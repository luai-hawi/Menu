# Configurable Option Groups — Design & Reference

> Status: **shipped**
> Scope: menu items now support any number of **option groups** (`SINGLE`
> or `MULTIPLE`), each containing one or more **options** with Arabic
> labels, price deltas, optional notes, and explicit display ordering.
> The public menu computes the total price live as the customer toggles
> options; the restaurant owner dashboard provides a nested, drag-and-drop
> editor.

> ## 2026-04-16 Update — Arabic-only simplification
>
> The English-only fields were dropped from the schema and the admin UI.
> Each group & option now stores a single `*_ar` label that is rendered
> everywhere. The rest of the document still references `*_en` fields
> (original design); those fields no longer exist in the code:
> - migration `2026_04_16_210000_drop_english_columns_from_option_groups_and_options.php` removes them
> - `MenuItemOptionGroup::nameFor()` / `MenuItemOption::nameFor()` / `noteFor()` always return Arabic
> - the editor partial renders a single Arabic input per group / option
> - `MenuItemRequest` only validates `*_ar` keys
>
> ## 2026-04-16 Update — AJAX-first save flow
>
> All save endpoints (`/menu-item`, `/category`, `/restaurant/profile`,
> `/restaurant/settings`, `/restaurant/whatsapp/*`, etc.) now double as
> JSON APIs. When the client sends `Accept: application/json` or
> `X-Requested-With: XMLHttpRequest`, the controller returns
> `{success: bool, message: string, ...}` with HTTP 200/422, otherwise
> it falls back to the traditional redirect-with-flash behaviour.
>
> `resources/js/app.js` ships `window.kiloSubmit` / `window.kiloAction`
> helpers and a locale-aware toast notifier (`window.toast.success|error|info`).
> Any form with `data-ajax` or any button with `data-ajax-action` is auto-
> wired; toasts pull their default strings from `window.KiloI18n`, which
> the layout seeds from `messages.products.*` and `messages.errors.*`.

---

## 1. Data model

### Tables

```
menu_items                            (unchanged)
├── id, name, description, price, image, menu_category_id, sort_order, is_active
│
menu_item_option_groups
├── id                                PK
├── menu_item_id                      FK → menu_items.id ON DELETE CASCADE
├── group_type                        ENUM('SINGLE','MULTIPLE')
├── group_name_en                     VARCHAR(255)
├── group_name_ar                     VARCHAR(255)
├── min_choices                       TINYINT UNSIGNED  (default 0)
├── max_choices                       TINYINT UNSIGNED  (default 1)
├── is_required                       BOOLEAN           (default false)
├── position                          INT UNSIGNED      (default 0)
├── timestamps
│   INDEX (menu_item_id, position)    → fast ordered load
│
menu_item_options
├── id                                PK
├── option_group_id                   FK → menu_item_option_groups.id ON DELETE CASCADE
├── option_name_en                    VARCHAR(255)
├── option_name_ar                    VARCHAR(255)
├── price_delta                       DECIMAL(8,2)  default 0 (may be negative)
├── option_note_en                    VARCHAR(160)  nullable
├── option_note_ar                    VARCHAR(160)  nullable
├── position                          INT UNSIGNED  default 0
├── is_active                         BOOLEAN       default true
├── timestamps
    INDEX (option_group_id, position) → fast ordered load
```

### Rules embedded in the schema

| Rule | Enforced by |
|---|---|
| Deleting an item deletes its groups and options | `ON DELETE CASCADE` |
| Deleting a group deletes its options | `ON DELETE CASCADE` |
| `group_type = 'SINGLE'` ⇒ `max_choices = 1` | Form request (`MenuItemRequest::prepareForValidation`) |
| `price_delta` can be negative (e.g. "Small" pizza = -1.00) | `DECIMAL(8,2)` + `price_delta` allowed `between:-9999.99,9999.99` in validation |
| `option_note_*` capped at 160 chars | `VARCHAR(160)` + `max:160` rule |

### Eloquent relations

| Model | Relation | Target |
|---|---|---|
| `MenuItem` | `optionGroups()` hasMany | `MenuItemOptionGroup` (ordered by `position`) |
| `MenuItem` | `activeOptionGroups()` hasMany | identical today, reserved for future `is_active` on groups |
| `MenuItemOptionGroup` | `options()` hasMany | `MenuItemOption` (ordered by `position`) |
| `MenuItemOptionGroup` | `activeOptions()` hasMany | only `is_active = true` |
| `MenuItemOption` | `group()` belongsTo | `MenuItemOptionGroup` |

---

## 2. API / Route contract

All endpoints live in `routes/web.php` under the authenticated owner
group and are **session + CSRF**-protected (no JSON API needed today;
the dashboard submits standard form arrays).

| Method | URL | Controller | Purpose |
|---|---|---|---|
| `GET`  | `/dashboard` | `RestaurantController@dashboard` | lists items + their option groups (eager-loaded) |
| `POST` | `/menu-item` | `RestaurantController@storeItem` | create item + nested option groups |
| `PUT`  | `/menu-item/{item}` | `RestaurantController@updateItem` | update item + upsert / reorder / remove groups & options |
| `DELETE` | `/menu-item/{item}` | `RestaurantController@deleteItem` | cascade deletes groups + options |
| `GET`  | `/{restaurant:slug}` | `MenuController@show` | public menu page; eager-loads groups & active options |

### Payload — `POST /menu-item`

Form-encoded (multipart because of image). The `option_groups[]` nesting
is flat array notation that PHP's form parser converts into the structure
shown below.

```json
{
  "name": "Margherita Pizza",
  "description": "Classic cheese & tomato",
  "price": 10.00,
  "category_id": 3,
  "option_groups": [
    {
      "group_type": "SINGLE",
      "group_name_en": "Size",
      "group_name_ar": "الحجم",
      "is_required": true,
      "min_choices": 1,
      "max_choices": 1,
      "position": 0,
      "options": [
        { "option_name_en": "Small",  "option_name_ar": "صغير", "price_delta": -1.00, "position": 0, "is_active": 1 },
        { "option_name_en": "Medium", "option_name_ar": "وسط",  "price_delta":  0.00, "position": 1, "is_active": 1 },
        { "option_name_en": "Large",  "option_name_ar": "كبير", "price_delta":  2.50, "position": 2, "is_active": 1 }
      ]
    },
    {
      "group_type": "MULTIPLE",
      "group_name_en": "Toppings",
      "group_name_ar": "الإضافات",
      "is_required": false,
      "min_choices": 0,
      "max_choices": 2,
      "position": 1,
      "options": [
        { "option_name_en": "Extra cheese", "option_name_ar": "جبنة إضافية", "price_delta": 1.50, "option_note_en": "Contains dairy", "option_note_ar": "يحتوي على ألبان", "position": 0, "is_active": 1 },
        { "option_name_en": "Olives",       "option_name_ar": "زيتون",       "price_delta": 0.75, "position": 1, "is_active": 1 }
      ]
    }
  ]
}
```

### Payload — `PUT /menu-item/{item}`

Same shape as create, but each group and option may include an `id`:

- **with `id`** ⇒ update existing row in place
- **without `id`** ⇒ insert new row
- rows that existed before but are **absent** from the payload are **deleted**

```json
{
  "name": "Margherita Pizza",
  "description": "Classic cheese & tomato",
  "price": 11.00,
  "option_groups": [
    {
      "id": 42,
      "group_type": "SINGLE",
      "group_name_en": "Size",
      "group_name_ar": "الحجم",
      "is_required": true, "min_choices": 1, "max_choices": 1, "position": 0,
      "options": [
        { "id": 101, "option_name_en": "Small",  "option_name_ar": "صغير", "price_delta": -1.00, "position": 0 },
        { "id": 102, "option_name_en": "Medium", "option_name_ar": "وسط",  "price_delta":  0.00, "position": 1 },
        {            "option_name_en": "Large",  "option_name_ar": "كبير", "price_delta":  3.00, "position": 2 }
      ]
    }
  ]
}
```

The controller wraps persistence in a DB transaction; partial failures
do not leave the item in an inconsistent state.

### Validation rules

Centralised in `app/Http/Requests/MenuItemRequest.php`:

| Field | Rule |
|---|---|
| `name` | required, string, max:255 |
| `description` | nullable, string |
| `price` | required, numeric, min:0 |
| `category_id` (create only) | required, exists:menu_categories,id |
| `image` | nullable image mimes:jpeg,png,jpg,gif max:2048 |
| `option_groups.*.group_type` | required\_with:option\_groups, in:`SINGLE`,`MULTIPLE` |
| `option_groups.*.group_name_en` | required\_with, string, max:255 |
| `option_groups.*.group_name_ar` | required\_with, string, max:255 |
| `option_groups.*.min_choices` | nullable, integer, min:0, max:50 |
| `option_groups.*.max_choices` | nullable, integer, min:0, max:50 |
| `option_groups.*.options` | required\_with, array, min:1 |
| `option_groups.*.options.*.option_name_en` | required, string, max:255 |
| `option_groups.*.options.*.option_name_ar` | required, string, max:255 |
| `option_groups.*.options.*.price_delta` | nullable, numeric, between:-9999.99,9999.99 |
| `option_groups.*.options.*.option_note_en` | nullable, string, max:160 |
| `option_groups.*.options.*.option_note_ar` | nullable, string, max:160 |

Additional business rules enforced by `MenuItemRequest::withValidator`:

- **SINGLE** groups → `max_choices` must equal `1`, `min_choices` ≤ 1.
- **MULTIPLE** groups → `min_choices` ≤ `max_choices`; `max_choices` ≤ number of options.

All validation error messages are localized via `messages.errors.*`
(see `resources/lang/{en,ar}/messages.php`).

---

## 3. Price calculation

### Service

`App\Services\PriceCalculator` exposes two entry points:

| Method | Use when |
|---|---|
| `calculate(base, selectedIds, [options])` | you need a fast, silent total (public menu live price) |
| `calculateWithValidation(item, selectedIds)` | you need to enforce group constraints (server-side cart / pre-order) |

```php
$calc = app(App\Services\PriceCalculator::class);

// Live price (no validation, inactive options are silently dropped)
$total = $calc->calculate($item->price, [101, 203]);

// Fully validated (throws PriceCalculationException on constraint breach)
$result = $calc->calculateWithValidation($item, [101, 203]);
// $result = ['subtotal' => 12.50, 'per_group' => [42 => -1.00, 43 => 1.50]]
```

### Interaction with discounts / taxes / promotions

Apply modifiers to the *subtotal* returned above:

```php
$final = $calc->applyModifiers($subtotal, [
    'discount_percent' => 10,   // -10%
    'discount_amount'  => 2.00, // then -$2
    'tax_rate'         => 16,   // then +16% VAT
]);
```

**Order of operations** (explicit and documented in the code):

1. `subtotal = base + Σ selected_delta`
2. `discounted = subtotal − discount_percent% − discount_amount`   (clamped ≥ 0)
3. `final = discounted × (1 + tax_rate/100)`

**Promotions** (BOGO, combo meals, tiered discounts) are expected to be
computed at the cart level (across multiple lines) and fed in as a
`discount_amount` per line. This keeps the per-item calculation simple
and deterministic.

### Why this ordering

- Percent-off before tax matches what most jurisdictions mandate for VAT:
  the discount lowers the taxable base.
- Keeping tax last means a "promo-free" display price remains the printed
  price when taxes are shown separately.

---

## 4. i18n strategy

### File layout (existing Laravel convention — kept)

```
resources/lang/
├── en/messages.php   # monolithic array file, now with nested sections
└── ar/messages.php   # mirror
```

> The spec mentioned `locales/en.json` / `locales/ar.json`. We kept
> Laravel's idiomatic `lang/{locale}/messages.php` because it's already
> wired everywhere in the codebase, avoids a custom loader, and is
> fully covered by the translation-coverage tests in
> `tests/Unit/OptionGroupsI18nTest.php`.

### Key hierarchy

All new keys live under predictable top-level sections; access via
`__('messages.<section>.<key>')`.

```
products.*        → public menu strings (base_price, total_price, free, customize, flash_*)
optionGroups.*    → admin UI (add, remove, type, required, min/max, helpers)
options.*         → per-option strings (name_en/ar, price_delta, note_*, is_active, drag_to_reorder)
form.*            → form buttons (save, cancel, delete)
common.*          → shared words (add, remove, yes, no, english_abbr, arabic_abbr, characters_left)
errors.*          → validation + runtime messages (placeholders :min, :max, :count, :group)
```

### RTL

- `resources/views/layouts/app.blade.php` sets `<html dir="{rtl|ltr}">`
  from `app()->getLocale()`; Cairo web font is loaded when `ar`.
- `resources/views/menu/show.blade.php` does the same.
- Logical CSS properties (`margin-inline-start`, `border-inline-start`,
  `padding-inline-end`) are used in new styles so no flipping is needed.

### Tests

`tests/Unit/OptionGroupsI18nTest.php` guards:

1. Every section exists in both `en` and `ar`.
2. Every `en.section.key` has a matching `ar.section.key`.
3. Arabic values actually contain Arabic characters (excluding explicit
   language-badge keys).
4. Placeholder tokens (`:min`, `:max`, `:count`, `:group`) are present
   in the messages that code passes them to.

---

## 5. Performance & caching

### Eager-loading (always)

- `dashboard` view: `menuCategories.menuItems.optionGroups.options`.
- Public menu: `activeMenuCategories.activeMenuItems.optionGroups.activeOptions`.

Without eager-loading, a menu with 50 items × 3 groups × 4 options would
emit 1 + 50 + 150 = **201** queries; with it, you get **5** queries
(one per table layer) regardless of size.

### Indexes

```sql
CREATE INDEX mi_opt_groups_item_pos_idx ON menu_item_option_groups (menu_item_id, position);
CREATE INDEX mi_opts_group_pos_idx      ON menu_item_options (option_group_id, position);
```

These cover the only hot-path access pattern: "load this item's groups in
order, then each group's options in order."

### Caching opportunities (not yet implemented — recommended)

The public menu is a read-heavy, write-rare page. Two tiers of caching
fit naturally:

| Layer | Key | Invalidate on |
|---|---|---|
| **HTML fragment cache** | `menu:{restaurant_id}:{locale}` | any `menu_items` / `menu_item_option_groups` / `menu_item_options` / `menu_categories` / `restaurants.theme_colors` write |
| **Query cache** | `restaurant:{slug}:v1` (eager payload) | same triggers |

A lightweight implementation: wrap the whole `MenuController::show`
lookup in `Cache::remember($key, 3600, …)` and bust the key from an
`Observer` on each of the four models. For this scale (single-tenant),
even that is overkill; keep it in mind if load grows.

### Payload size

- Groups & options are rendered into the HTML once and hydrated into
  Alpine component state via `@js(...)` (JSON-encoded, safely escaped).
- For an item with 3 groups × 4 options, that's ~1.5 KB of inline JSON
  per item — acceptable even for menus of 100+ items.

---

## 6. Migration plan (minimal-downtime upgrade)

### Backwards compatibility

The new tables **add** data; they do not modify `menu_items`. Items
without option groups continue to render and price exactly as before.

### Deploy steps

1. **Deploy code** (models, controller, views, JS bundle). Safe because:
   - `MenuItemRequest` still accepts legacy payloads (option\_groups is `nullable`).
   - Blade partial renders nothing when `groups = []`.
2. **Run migrations** — `php artisan migrate`. Creates the two new tables and their indexes. **No data copy is needed** for existing items (they simply have zero option groups).
3. **Rebuild assets** — `npm install && npm run build` (adds SortableJS).
4. Restart PHP-FPM / queue workers as usual.

There is no destructive schema change. Rolling back is
`php artisan migrate:rollback --step=2` + redeploy the previous code;
no data loss in either direction because existing items have no rows in
the new tables.

### Data transformation for existing items

None required — the baseline is "zero option groups per item". Owners
can opt-in to option groups on a per-item basis via the dashboard.

If a future bilingual migration for item names is desired (see
§8 Open enhancements), the plan is:

1. Add `name_en`, `name_ar`, `description_en`, `description_ar` columns
   (nullable).
2. Run `UPDATE menu_items SET name_en = name, name_ar = name`.
3. Ship a locale-aware accessor on the model.
4. Drop the old `name`/`description` columns in a follow-up release
   (after the UI is updated).

---

## 7. Acceptance criteria

### Data

- [x] `menu_item_option_groups` exists with columns per spec.
- [x] `menu_item_options` exists with columns per spec.
- [x] `ON DELETE CASCADE` wired from item → groups → options.
- [x] Indexes created on `(menu_item_id, position)` and `(option_group_id, position)`.

### API / persistence

- [x] `POST /menu-item` accepts nested `option_groups[]` and persists
      item + groups + options atomically in one transaction.
- [x] `PUT /menu-item/{id}` performs a diff-based upsert:
      unchanged rows by `id`, new rows inserted, absent rows deleted.
- [x] Validation rejects: missing bilingual name, empty options array,
      `SINGLE` with `max_choices > 1`, `MULTIPLE` with `min > max`, or
      `max > option_count`.

### UI — admin dashboard

- [x] Owner can add any number of option groups per item.
- [x] Each group clearly shows its `SINGLE`/`MULTIPLE` type selector
      and its own card with bilingual name fields.
- [x] Inline `+ Add option` button inside each group.
- [x] Each option row has English name, Arabic name, price delta, note
      toggle (collapsed by default), active switch, and delete button.
- [x] Drag handles on the group card and on each option row reorder
      via SortableJS.
- [x] Live client-side validation errors show under the group
      (`min > max`, `max > options`, empty options list).

### UI — customer menu

- [x] Product card lists all option groups with their options.
- [x] `SINGLE` groups render radio inputs; `MULTIPLE` render checkboxes.
- [x] Required groups show a `Required` tag; constraint hints
      (`min-max`) are shown for `MULTIPLE`.
- [x] Card price updates live as selections change; base price shown
      struck-through underneath when options exist.
- [x] "+" button is disabled while validation errors are present.
- [x] Cart uses a composite key `itemId|sortedOptionIds` so the same
      dish with different options is treated as distinct line items.
- [x] WhatsApp order message includes the chosen options per line.

### i18n / RTL

- [x] All new UI strings use `__('messages.<section>.<key>')`.
- [x] Sections `products`, `optionGroups`, `options`, `form`, `common`,
      `errors` exist in both `en` and `ar` with matching keys.
- [x] `<html dir="rtl">` is set when locale is `ar` (admin + public).
- [x] Cairo web font is loaded for Arabic.

### Tests

- [x] Unit tests cover every PriceCalculator branch (13 tests).
- [x] Translation-coverage tests (4 tests) guard i18n drift.
- [x] Feature tests cover create / update / validation (6 tests).

---

## 8. Audit checklist (use before PR-merge or review)

```text
Schema
  [x] Migrations present and reversible
  [x] Cascade deletes in place
  [x] Hot-path indexes present

Backend
  [x] FormRequest centralises all validation
  [x] Controller wraps persistence in a DB transaction
  [x] PriceCalculator returns rounded, clamped values
  [x] Eager-loading added to every consumer of menu_items

Frontend — dashboard
  [x] Dynamic option-groups editor (Alpine.js) — add, edit, remove
  [x] Drag-and-drop reordering (SortableJS) for groups and options
  [x] Single-select type is visually distinct from multi-select
  [x] Notes are collapsed by default; 160-char counter shown
  [x] All labels, placeholders, helpers, error messages wired to __()
  [x] Arabic inputs have dir="rtl"

Frontend — public menu
  [x] Option groups rendered with correct widget (radio / checkbox)
  [x] Live total price updates client-side
  [x] Base price shown struck-through when options exist
  [x] Min/max/required constraint validation runs before cart add
  [x] Cart entries distinguish items by option selection
  [x] WhatsApp message includes chosen options

i18n / Accessibility
  [x] New top-level sections exist in both locales
  [x] Every en key has an ar counterpart (enforced by test)
  [x] Placeholder tokens appear in both locales (enforced by test)
  [x] <html dir="…"> set on both admin and public layouts
  [x] Cairo font loaded for ar

Testing
  [x] Unit tests (PriceCalculator, i18n) pass
  [x] Feature tests (create, update, constraint, validation) pass
  [x] Existing test suite remains green (no regressions)
```

---

## 9. Open enhancements (not in scope)

- Item-level bilingual `name_en`/`name_ar` (plan in §6).
- Per-option images (schema/UX work; the service layer already accepts
  it via `ImageService::uploadAndCompressImage`).
- Stock / availability windows per option.
- An option-group template library owners can reuse across items
  (needs a `template_id` column on groups + a new `option_group_templates`
  table).
- JSON API endpoints (current architecture is Blade + session; the
  controller method is already decoupled enough to be dropped into a
  future API without logic changes).
