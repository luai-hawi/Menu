<!DOCTYPE html>
@php
    $menuLocale = app()->getLocale();
    $menuDir    = $menuLocale === 'ar' ? 'rtl' : 'ltr';
@endphp
<html lang="{{ $menuLocale }}" dir="{{ $menuDir }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $restaurant->name }} - Menu</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @if($menuLocale === 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Cairo', 'Inter', sans-serif !important; }</style>
    @endif
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        @php
            $tc = $restaurant->theme_colors ?: [];
            // Helper: read key with fallback
            $cv = fn(string $k, string $d) => $tc[$k] ?? $d;
            // Helper: hex to "r,g,b" string
            $rgb = function(string $hex) {
                $r = sscanf($hex, "#%02x%02x%02x");
                return $r ? implode(',', $r) : '0,0,0';
            };

            // ── All 41 controllable color tokens ──────────────────────────────
            $page_bg          = $cv('page_bg',          '#0a0e27');
            $page_bg_2        = $cv('page_bg_2',        '#141b3c');
            $page_bg_3        = $cv('page_bg_3',        '#1e2749');
            $header_bg_start  = $cv('header_bg_start',  '#0a0e27');
            $header_bg_end    = $cv('header_bg_end',    '#1e2749');
            $rest_name        = $cv('restaurant_name',  '#ffffff');
            $rest_tagline     = $cv('restaurant_tagline','#e2e8f0');
            $text_primary     = $cv('text_primary',     '#ffffff');
            $text_secondary   = $cv('text_secondary',   '#e2e8f0');
            $text_muted       = $cv('text_muted',       '#94a3b8');
            $text_price       = $cv('text_price',       '#22d3ee');
            $text_opt_price   = $cv('text_option_price','#86efac');
            $card_bg          = $cv('card_bg',          '#1a2254');
            $card_border      = $cv('card_border',      '#334155');
            $card_border_hov  = $cv('card_border_hover','#6366f1');
            $card_bar         = $cv('card_accent_bar',  '#6366f1');
            $card_bar_end     = $cv('card_accent_bar_end','#8b5cf6');
            $btn_p            = $cv('btn_primary',      '#6366f1');
            $btn_p_end        = $cv('btn_primary_end',  '#8b5cf6');
            $btn_qty          = $cv('btn_qty',          '#6366f1');
            $btn_qty_end      = $cv('btn_qty_end',      '#8b5cf6');
            $btn_order        = $cv('btn_order',        '#22c55e');
            $btn_order_end    = $cv('btn_order_end',    '#16a34a');
            $pill_bg          = $cv('pill_bg',          '#1a2254');
            $pill_border      = $cv('pill_border',      '#334155');
            $pill_text        = $cv('pill_text',        '#94a3b8');
            $pill_active      = $cv('pill_active',      '#6366f1');
            $pill_active_end  = $cv('pill_active_end',  '#8b5cf6');
            $pill_active_text = $cv('pill_active_text', '#ffffff');
            $opt_group_bg     = $cv('option_group_bg',  '#0f1631');
            $opt_sel_bg       = $cv('option_selected_bg','#2e3a8c');
            $opt_accent       = $cv('option_input_accent','#6366f1');
            $input_bg         = $cv('input_bg',         '#1e2749');
            $input_border     = $cv('input_border',     '#334155');
            $input_focus      = $cv('input_focus',      '#6366f1');
            $input_text       = $cv('input_text',       '#ffffff');
            $footer_bg        = $cv('footer_bg',        '#070b1e');
            $footer_text      = $cv('footer_text',      '#94a3b8');
            $footer_heading   = $cv('footer_heading',   '#ffffff');
            $border           = $cv('border',           '#334155');
            $border_sec       = $cv('border_secondary', '#475569');
        @endphp
        :root {
            /* ── Page backgrounds ── */
            --color-page-bg:         {{ $page_bg }};
            --color-page-bg-2:       {{ $page_bg_2 }};
            --color-page-bg-3:       {{ $page_bg_3 }};
            /* ── Header ── */
            --color-header-bg-start: {{ $header_bg_start }};
            --color-header-bg-end:   {{ $header_bg_end }};
            --color-restaurant-name: {{ $rest_name }};
            --color-restaurant-tagline: {{ $rest_tagline }};
            /* ── Text ── */
            --color-text-primary:    {{ $text_primary }};
            --color-text-secondary:  {{ $text_secondary }};
            --color-text-muted:      {{ $text_muted }};
            --color-text-price:      {{ $text_price }};
            --color-text-option-price: {{ $text_opt_price }};
            /* ── Cards ── */
            --color-card-bg:            {{ $card_bg }};
            --color-card-border:        {{ $card_border }};
            --color-card-border-hover:  {{ $card_border_hov }};
            --color-card-accent-bar:    {{ $card_bar }};
            --color-card-accent-bar-end:{{ $card_bar_end }};
            /* ── Buttons ── */
            --color-btn-primary:        {{ $btn_p }};
            --color-btn-primary-end:    {{ $btn_p_end }};
            --color-btn-qty:            {{ $btn_qty }};
            --color-btn-qty-end:        {{ $btn_qty_end }};
            --color-btn-order:          {{ $btn_order }};
            --color-btn-order-end:      {{ $btn_order_end }};
            /* ── Category pills ── */
            --color-pill-bg:            {{ $pill_bg }};
            --color-pill-border:        {{ $pill_border }};
            --color-pill-text:          {{ $pill_text }};
            --color-pill-active:        {{ $pill_active }};
            --color-pill-active-end:    {{ $pill_active_end }};
            --color-pill-active-text:   {{ $pill_active_text }};
            /* ── Options ── */
            --color-option-group-bg:    {{ $opt_group_bg }};
            --color-option-selected-bg: {{ $opt_sel_bg }};
            --color-option-input-accent:{{ $opt_accent }};
            /* ── Inputs / search ── */
            --color-input-bg:           {{ $input_bg }};
            --color-input-border:       {{ $input_border }};
            --color-input-focus:        {{ $input_focus }};
            --color-input-text:         {{ $input_text }};
            /* ── Footer ── */
            --color-footer-bg:          {{ $footer_bg }};
            --color-footer-text:        {{ $footer_text }};
            --color-footer-heading:     {{ $footer_heading }};
            /* ── Borders ── */
            --color-border:             {{ $border }};
            --color-border-secondary:   {{ $border_sec }};

            /* ── Derived RGB versions (for rgba() usage) ── */
            --color-btn-primary-rgb:       {{ $rgb($btn_p) }};
            --color-btn-qty-rgb:           {{ $rgb($btn_qty) }};
            --color-btn-order-rgb:         {{ $rgb($btn_order) }};
            --color-card-bg-rgb:           {{ $rgb($card_bg) }};
            --color-page-bg-rgb:           {{ $rgb($page_bg) }};
            --color-card-border-hover-rgb: {{ $rgb($card_border_hov) }};

            /* ── Compound gradients ── */
            --gradient-btn-primary: linear-gradient(135deg, var(--color-btn-primary) 0%, var(--color-btn-primary-end) 100%);
            --gradient-btn-qty:     linear-gradient(135deg, var(--color-btn-qty)     0%, var(--color-btn-qty-end)     100%);
            --gradient-btn-order:   linear-gradient(135deg, var(--color-btn-order)   0%, var(--color-btn-order-end)   100%);
            --gradient-card-bar:    linear-gradient(135deg, var(--color-card-accent-bar) 0%, var(--color-card-accent-bar-end) 100%);
            --gradient-pill-active: linear-gradient(135deg, var(--color-pill-active) 0%, var(--color-pill-active-end) 100%);

            /* ── Shadows / radii (not color-controlled, structural) ── */
            --shadow-md: 0 8px 25px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 25px 50px rgba(0, 0, 0, 0.25);
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        body {
            background: var(--color-page-bg) !important;
            color: var(--color-text-primary) !important;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            scroll-behavior: smooth;
            padding-bottom: 100px;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--color-header-bg-start) 0%, var(--color-page-bg-2) 50%, var(--color-header-bg-end) 100%) !important;
            position: relative;
            overflow: hidden;
            min-height: 70vh;
        }

        .header-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(var(--color-btn-primary-rgb), 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(var(--color-btn-primary-rgb), 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .restaurant-logo {
            width: 180px !important;
            height: 180px !important;
            object-fit: cover !important;
            border-radius: 50% !important;
            border: 4px solid var(--color-border-secondary) !important;
            box-shadow: var(--shadow-lg) !important;
            transition: all 0.4s ease !important;
        }

        .restaurant-logo-placeholder {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 4px solid var(--color-border-secondary);
            box-shadow: var(--shadow-lg);
            background: var(--gradient-btn-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }

        .restaurant-logo:hover,
        .restaurant-logo-placeholder:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-xl);
        }

        .menu-item-card {
            background: var(--color-card-bg) !important;
            border: 1px solid var(--color-card-border) !important;
            border-radius: var(--radius-xl) !important;
            overflow: hidden !important;
            box-shadow: var(--shadow-md) !important;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            backdrop-filter: blur(10px) !important;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .menu-item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-card-bar);
            z-index: 1;
        }

        .menu-item-card:hover {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: var(--shadow-xl), 0 0 40px rgba(var(--color-card-border-hover-rgb), 0.25) !important;
            border-color: var(--color-card-border-hover) !important;
        }

        .menu-image {
            width: 100% !important;
            height: 200px !important;
            object-fit: cover !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .menu-image:hover {
            transform: scale(1.05) !important;
        }

        .menu-image-placeholder {
            width: 100% !important;
            height: 200px !important;
            background: linear-gradient(135deg, var(--color-page-bg-3), var(--color-page-bg-2)) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .menu-item-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .menu-item-header {
            margin-bottom: 1rem;
        }

        .menu-item-title {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: var(--color-text-primary) !important;
            margin-bottom: 0.5rem !important;
            line-height: 1.3 !important;
        }

        .menu-item-description {
            color: var(--color-text-muted) !important;
            font-size: 0.95rem !important;
            line-height: 1.5 !important;
            margin-bottom: 1rem !important;
            flex-grow: 1;
        }

        .menu-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        /* ---- Option groups on product card ---- */
        .options-block {
            margin: 0.75rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .option-group {
            background: var(--color-option-group-bg);
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 0.75rem;
        }
        .option-group-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--color-text-secondary);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        .required-tag {
            font-size: 0.65rem;
            background: rgba(245, 87, 108, 0.15);
            border: 1px solid rgba(245, 87, 108, 0.4);
            color: #fca5a5;
            padding: 1px 6px;
            border-radius: 999px;
        }
        .optional-tag, .constraints-tag {
            font-size: 0.65rem;
            background: rgba(148, 163, 184, 0.15);
            border: 1px solid rgba(148, 163, 184, 0.35);
            color: #cbd5e1;
            padding: 1px 6px;
            border-radius: 999px;
        }
        .option-choice {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.6rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.15s ease;
            font-size: 0.85rem;
        }
        .option-choice:hover       { background: rgba(var(--color-btn-primary-rgb), 0.06); }
        .option-chosen             { background: var(--color-option-selected-bg); }
        .option-choice input       { accent-color: var(--color-option-input-accent); }
        .option-choice-name        { color: var(--color-text-primary); }
        .option-choice-note {
            grid-column: 2 / 3;
            grid-row: 2;
            font-size: 0.72rem;
            color: var(--color-text-muted);
            font-style: italic;
        }
        .option-choice-price       {
            font-weight: 600;
            color: var(--color-text-option-price);
            font-variant-numeric: tabular-nums;
        }
        .option-group-error {
            margin-top: 0.4rem;
            padding: 0.35rem 0.55rem;
            background: rgba(245, 87, 108, 0.1);
            border-inline-start: 3px solid #f5576c;
            border-radius: 6px;
            font-size: 0.75rem;
            color: #fca5a5;
        }
        .price-block { display: flex; flex-direction: column; align-items: flex-start; }
        .base-price-label {
            font-size: 0.7rem;
            color: var(--color-text-muted);
            text-decoration: line-through;
            opacity: 0.7;
        }

        /* RTL */
        html[dir="rtl"] .option-choice          { direction: rtl; }
        html[dir="rtl"] .option-choice-price    { font-variant-numeric: tabular-nums; }

        .price {
            font-size: 1.75rem !important;
            font-weight: 800 !important;
            color: var(--color-text-price) !important;
            -webkit-text-fill-color: var(--color-text-price) !important;
            background: none !important;
            -webkit-background-clip: unset !important;
            background-clip: unset !important;
            font-family: 'Inter', sans-serif !important;
            letter-spacing: -0.02em !important;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--color-input-bg);
            border: 1px solid var(--color-input-border);
            border-radius: 12px;
            padding: 0.5rem;
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }

        .quantity-btn {
            background: var(--gradient-btn-qty);
            border: none;
            color: var(--color-pill-active-text);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(var(--color-btn-qty-rgb), 0.3);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        input.quantity-input[type="number"],
        input.quantity-input[type="number"]:focus {
            min-width: 40px;
            width: 40px;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            transform: none !important;
            color: var(--color-input-text) !important;
            -webkit-text-fill-color: var(--color-input-text) !important;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            outline: none;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
            backdrop-filter: none !important;
            /* Hide native browser number spinner so the digit stays centred */
            -moz-appearance: textfield;
        }
        input.quantity-input[type="number"]::-webkit-outer-spin-button,
        input.quantity-input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .category-header {
            position: relative !important;
            padding-bottom: 1.5rem !important;
            margin-bottom: 3rem !important;
        }

        .category-header h2 {
            font-family: 'Playfair Display', serif !important;
            font-size: 3rem !important;
            font-weight: 700 !important;
            color: var(--color-btn-primary) !important;
            margin-bottom: 0.5rem !important;
        }

        .category-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 4px;
            background: var(--gradient-card-bar);
            border-radius: 2px;
        }

        .responsive-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
            gap: 2rem !important;
            margin-bottom: 2rem !important;
        }

        .order-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-card-bg);
            border-top: 2px solid var(--color-border-secondary);
            padding: 1rem;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .order-summary.visible {
            transform: translateY(0);
        }

        .order-btn {
            background: var(--gradient-btn-order);
            color: var(--color-pill-active-text);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--color-btn-order-rgb), 0.35);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.visible {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border-secondary);
            border-radius: var(--radius-xl);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.visible .modal-content {
            transform: scale(1);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--color-border);
        }

        .modal-header h3 {
            color: var(--color-text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--color-border);
            background: var(--color-page-bg-3);
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: var(--color-text-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            background: var(--color-input-bg);
            border: 2px solid var(--color-input-border);
            color: var(--color-input-text);
            padding: 1rem;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: var(--color-input-focus);
            box-shadow: 0 0 0 3px rgba(var(--color-btn-primary-rgb), 0.1);
            outline: none;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--color-page-bg-3);
            color: var(--color-text-secondary);
            border: 2px solid var(--color-border);
        }

        .btn-secondary:hover {
            background: var(--color-page-bg-2);
            border-color: var(--color-border-secondary);
        }

        .btn-whatsapp {
            background: var(--gradient-btn-order);
            color: var(--color-pill-active-text);
        }

        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--color-btn-order-rgb), 0.35);
        }

        @media (max-width: 600px) {
            .responsive-grid {
                grid-template-columns: repeat(1, 1fr) !important;
                gap: 1rem !important;
            }

            .menu-item-card {
                font-size: 0.9rem;
            }

            .menu-item-content {
                padding: 1rem;
            }

            .menu-item-title {
                font-size: 1.2rem !important;
            }

            .price {
                font-size: 1.4rem !important;
            }

            .menu-image,
            .menu-image-placeholder {
                height: 200px !important;
            }

            .restaurant-logo,
            .restaurant-logo-placeholder {
                width: 140px !important;
                height: 140px !important;
            }

            .category-header h2 {
                font-size: 2.5rem !important;
            }

            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }

        @media (max-width: 480px) {
            .responsive-grid {
                gap: 0.75rem !important;
            }

            .menu-item-content {
                padding: 0.75rem;
            }

            .restaurant-logo,
            .restaurant-logo-placeholder {
                width: 120px !important;
                height: 120px !important;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Launch Animation Styles */
        .animate-bounce-in {
            animation: bounceIn 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .animate-slide-up {
            animation: slideUp 1s ease-out 0.3s both;
        }

        .animate-slide-up-delayed {
            animation: slideUp 1s ease-out 0.6s both;
        }

        .animate-slide-up-delayed-2 {
            animation: slideUp 1s ease-out 0.9s both;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3) rotate(-180deg);
            }
            50% {
                opacity: 1;
                transform: scale(1.05) rotate(0deg);
            }
            70% {
                transform: scale(0.9) rotate(0deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes slideUp {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .glass {
            background: rgba(var(--color-card-bg-rgb), 0.25) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(var(--color-btn-primary-rgb), 0.15) !important;
        }

        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.6;
        }
        .category-pill {
    padding: 0.5rem 1rem;
    background: var(--color-pill-bg);
    border: 2px solid var(--color-pill-border);
    border-radius: 25px;
    color: var(--color-pill-text);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.category-pill:hover,
.category-pill.active {
    background: var(--gradient-pill-active);
    border-color: transparent;
    color: var(--color-pill-active-text);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--color-btn-primary-rgb), 0.3);
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    font-size: 1.25rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-link:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.social-link.facebook { background: linear-gradient(135deg, #1877f2, #42a5f5); }
.social-link.instagram { background: linear-gradient(135deg, #e4405f, #f093fb); }
.social-link.snapchat { background: linear-gradient(135deg, #fffc00, #fff700); color: #000; }
.social-link.twitter { background: linear-gradient(135deg, #1da1f2, #42a5f5); }
.social-link.tiktok { background: linear-gradient(135deg, #000000, #333333); }
.social-link.whatsapp { background: linear-gradient(135deg, #25d366, #128c7e); }

/* Theme overrides for Tailwind classes */
.text-white { color: var(--color-text-primary) !important; }
.text-gray-300 { color: var(--color-text-secondary) !important; }
.text-gray-400 { color: var(--color-text-muted) !important; }
.text-gray-500 { color: var(--color-text-muted) !important; }
.bg-gray-800 { background: var(--color-input-bg) !important; }
.bg-gray-700 { background: var(--color-page-bg-3) !important; }
.border-gray-600 { border-color: var(--color-input-border) !important; }
.text-green-400 { color: var(--color-text-price) !important; }
.placeholder-gray-400::placeholder { color: var(--color-text-muted) !important; }
.focus\:border-blue-500:focus { border-color: var(--color-input-focus) !important; }
.focus\:ring-blue-500\/20:focus { box-shadow: 0 0 0 3px rgba(var(--color-btn-primary-rgb), 0.1) !important; }

/* Specific overrides for search bar and language selector */
#menuSearch { background: var(--color-input-bg) !important; color: var(--color-input-text) !important; border-color: var(--color-input-border) !important; }
#language-select { background: var(--color-input-bg) !important; color: var(--color-input-text) !important; }
#menuSearch::placeholder { color: var(--color-text-muted) !important; }

</style>
</head>
<body>
    <!-- Language Selector -->
    <div style="position: absolute; top: 16px; left: 16px; z-index: 100;">
        <div class="rounded-lg p-2 flex items-center space-x-2" style="background: transparent; border: 1px solid rgba(var(--color-btn-primary-rgb), 0.3);">
            <i class="fas fa-globe" style="color: var(--color-text-primary);"></i>
            <select id="language-select" style="background: transparent; color: var(--color-text-primary); border: none; outline: none; font-size: 0.875rem;">
                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
            </select>
        </div>
    </div>

    <!-- Company Logo - Top Right -->
    <div style="position: absolute; top: 16px; right: 16px; z-index: 100;">
        <div>
            <img src="{{ asset('images/logo.png') }}" alt="Hawi Tech" style="width: 90px; height: 90px; object-fit: contain;filter: brightness(0) invert(1);transition: all 0.3s ease;">
        </div>
    </div>



    <!-- Header -->
    <div class="header-gradient">
        @if($restaurant->background_image)
            <div class="absolute inset-0" style="background-image: url('{{ asset('storage/' . $restaurant->background_image) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, 0.6);"></div>
            </div>
        @endif
        <div class="relative max-w-6xl mx-auto px-4 py-10">
            <div class="text-center">
                @if($restaurant->logo)
                    <img src="{{ asset('storage/' . $restaurant->logo) }}"
                         alt="{{ $restaurant->name }}"
                         class="restaurant-logo mx-auto mb-8 animate-bounce-in">
                @else
                    <div class="restaurant-logo-placeholder mx-auto mb-8 animate-bounce-in">
                        <span style="color: var(--color-pill-active-text); font-size: 3rem; font-weight: 700;">{{ substr($restaurant->name, 0, 1) }}</span>
                    </div>
                @endif
                <h1 class="text-6xl md:text-7xl font-bold mb-6 font-display animate-slide-up" style="color: var(--color-restaurant-name);">
                    {{ $restaurant->name }}
                </h1>
                @if($restaurant->description)
                    <p class="text-xl md:text-2xl max-w-3xl mx-auto mb-8 leading-relaxed animate-slide-up-delayed" style="color: var(--color-restaurant-tagline);">
                        {{ $restaurant->description }}
                    </p>
                @endif
            </div>
        </div>
        <!-- Social Media Links -->
@if($restaurant->facebook_url || $restaurant->instagram_url || $restaurant->snapchat_url || $restaurant->whatsapp_url || $restaurant->twitter_url || $restaurant->tiktok_url)
    <div class="social-links animate-slide-up-delayed-2">
        @if($restaurant->facebook_url)
            <a href="{{ $restaurant->facebook_url }}" target="_blank" class="social-link facebook animate-float">
                <i class="fab fa-facebook-f"></i>
            </a>
        @endif
        @if($restaurant->instagram_url)
            <a href="{{ $restaurant->instagram_url }}" target="_blank" class="social-link instagram animate-float">
                <i class="fab fa-instagram"></i>
            </a>
        @endif
        @if($restaurant->snapchat_url)
            <a href="{{ $restaurant->snapchat_url }}" target="_blank" class="social-link snapchat animate-float">
                <i class="fab fa-snapchat-ghost"></i>
            </a>
        @endif
        @if($restaurant->twitter_url)
            <a href="{{ $restaurant->twitter_url }}" target="_blank" class="social-link twitter animate-float">
                <i class="fab fa-twitter"></i>
            </a>
        @endif
        @if($restaurant->tiktok_url)
            <a href="{{ $restaurant->tiktok_url }}" target="_blank" class="social-link tiktok animate-float">
                <i class="fab fa-tiktok"></i>
            </a>
        @endif
        @if($restaurant->whatsapp_url)
            <a href="{{ $restaurant->whatsapp_url }}" target="_blank" class="social-link whatsapp animate-float">
                <i class="fab fa-whatsapp"></i>
            </a>
        @endif
    </div>
@endif
    </div>


<!-- Search and Category Navigation -->
<div class="max-w-7xl mx-auto px-4 py-8" style="background: var(--color-page-bg);">
    <!-- Search Bar -->
    <div class="max-w-md mx-auto mb-6">
        <div class="relative">
            <input type="text" id="menuSearch" placeholder="{{ __('messages.search_menu') }}"
                   class="w-full pl-12 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- Category Quick Links -->
    @if(!$restaurant->activeMenuCategories->isEmpty())
        <div class="flex flex-wrap justify-center gap-3 mb-4">
            <button onclick="clearSearch()" class="category-pill active" data-category="all">
                {{ __('messages.all_categories') }}
            </button>
            @foreach($restaurant->activeMenuCategories as $category)
                <button onclick="filterByCategory('{{ $category->id }}')" class="category-pill" data-category="{{ $category->id }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    @endif

</div>


    <!-- Menu Content -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        @if($restaurant->activeMenuCategories->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🍽️</div>
                <h2 class="text-4xl font-semibold text-gray-400 mb-6">{{ __('messages.menu_coming_soon') }}</h2>
                <p class="text-gray-500 text-xl">{{ __('messages.preparing_delicious') }}</p>
            </div>
        @else
            @foreach($restaurant->activeMenuCategories as $category)
                <div class="mb-20" data-category-section="{{ $category->id }}">
                    <div class="category-header animate-fade-in-up">
                        <h2>{{ $category->name }}</h2>
                    </div>

                    <div class="responsive-grid">
                        @foreach($category->activeMenuItems as $item)
                            @php
                                // Precompute locale-aware payload for Alpine so the JS doesn't duplicate logic.
                                $itemOptionsPayload = $item->optionGroups->map(function ($g) use ($menuLocale) {
                                    return [
                                        'id'          => $g->id,
                                        'type'        => $g->group_type,
                                        'name'        => $g->nameFor($menuLocale),
                                        'min'         => (int) $g->min_choices,
                                        'max'         => (int) $g->max_choices,
                                        'required'    => (bool) $g->is_required,
                                        'options'     => $g->activeOptions->map(fn ($o) => [
                                            'id'          => $o->id,
                                            'name'        => $o->nameFor($menuLocale),
                                            'note'        => $o->noteFor($menuLocale),
                                            'price_delta' => (float) $o->price_delta,
                                        ])->values()->all(),
                                    ];
                                })->filter(fn ($g) => count($g['options']) > 0)->values()->all();
                            @endphp
                            <div
                                class="menu-item-card animate-fade-in-up"
                                data-item-id="{{ $item->id }}"
                                x-data="menuItemCard({
                                    id: {{ $item->id }},
                                    name: @js($item->name),
                                    basePrice: {{ (float) $item->price }},
                                    groups: @js($itemOptionsPayload)
                                })"
                            >
                                <!-- Item Image -->
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}"
                                         alt="{{ $item->name }}"
                                         class="menu-image">
                                @else
                                    <div class="menu-image-placeholder">
                                        <i class="fas fa-utensils text-white text-4xl opacity-50"></i>
                                    </div>
                                @endif

                                <!-- Item Content -->
                                <div class="menu-item-content">
                                    <div class="menu-item-header">
                                        <h3 class="menu-item-title">{{ $item->name }}</h3>
                                        @if($item->description)
                                            <p class="menu-item-description">{{ $item->description }}</p>
                                        @endif
                                    </div>

                                    @if(count($itemOptionsPayload) > 0)
                                        <div class="options-block">
                                            <template x-for="group in groups" :key="group.id">
                                                <div class="option-group">
                                                    <div class="option-group-title">
                                                        <span x-text="group.name"></span>
                                                        <small x-show="group.required" class="required-tag">
                                                            {{ __('messages.common.required') }}
                                                        </small>
                                                        <small x-show="!group.required && group.type === 'MULTIPLE'" class="optional-tag">
                                                            {{ __('messages.common.optional') }}
                                                        </small>
                                                        <small x-show="group.type === 'MULTIPLE' && (group.min > 0 || group.max > 0)"
                                                               class="constraints-tag"
                                                               x-text="constraintHint(group)"></small>
                                                    </div>

                                                    <template x-for="opt in group.options" :key="opt.id">
                                                        <label class="option-choice"
                                                               :class="{ 'option-chosen': isSelected(group, opt) }">
                                                            <input
                                                                :type="group.type === 'SINGLE' ? 'radio' : 'checkbox'"
                                                                :name="`g-${group.id}-item-${id}`"
                                                                :value="opt.id"
                                                                :checked="isSelected(group, opt)"
                                                                @change="toggleOption(group, opt, $event)"
                                                            >
                                                            <span class="option-choice-name" x-text="opt.name"></span>
                                                            <span class="option-choice-note" x-show="opt.note" x-text="opt.note"></span>
                                                            <span class="option-choice-price" x-text="formatDelta(opt.price_delta)"></span>
                                                        </label>
                                                    </template>

                                                    <p class="option-group-error" x-show="errorFor(group)" x-text="errorFor(group)"></p>
                                                </div>
                                            </template>
                                        </div>
                                    @endif

                                    <div class="menu-item-footer">
                                        <div class="price-block">
                                            @if(count($itemOptionsPayload) > 0)
                                                <small class="base-price-label">{{ __('messages.products.base_price') }}: {{ __('messages.currency_symbol') }}{{ number_format($item->price, 2) }}</small>
                                                <div class="price" x-text="currency + computedPrice.toFixed(2)"></div>
                                            @else
                                                <div class="price">{{ __('messages.currency_symbol') }}{{ number_format($item->price, 2) }}</div>
                                            @endif
                                        </div>

                                        @if($restaurant->whatsapp_orders_enabled && $restaurant->whatsapp_number)
                                            <div class="quantity-control flex flex-col items-center">
                                                <button type="button" class="quantity-btn"
                                                        @click="changeQuantity(1)"
                                                        :disabled="!canAddToCart()">+</button>
                                                <input type="number" class="quantity-input"
                                                       :value="quantity" min="0" max="999"
                                                       @change="setQuantity($event.target.value)"
                                                       id="qty-{{ $item->id }}">
                                                <button type="button" class="quantity-btn"
                                                        @click="changeQuantity(-1)">-</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Order Summary Bar (only show if WhatsApp ordering is enabled) -->
    @if($restaurant->whatsapp_orders_enabled && $restaurant->whatsapp_number)
        <div class="order-summary" id="orderSummary">
            <div class="max-w-md mx-auto">
                <button class="order-btn" onclick="openOrderModal()">
                    <i class="fab fa-whatsapp text-xl"></i>
                    <span>{{ __('messages.order_via_whatsapp') }}</span>
                    <span id="totalItems" class="bg-white bg-opacity-20 px-2 py-1 rounded-full text-sm">0 {{ __('messages.items') }}</span>
                </button>
            </div>
        </div>

        <!-- Order Modal -->
        <div class="modal" id="orderModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ __('messages.your_order') }}</h3>
                    <p style="color: var(--color-text-muted);">{{ __('messages.review_items') }}</p>
                </div>

                <div class="modal-body">
                    <div id="orderItems" class="mb-6"></div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.additional_notes') }}</label>
                        <textarea id="orderNotes" class="form-input" rows="3"
                                  placeholder="{{ __('messages.notes_placeholder') }}"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.your_location') }}</label>
                        <textarea id="orderLocation" class="form-input" rows="2"
                                  placeholder="{{ __('messages.location_placeholder') }}" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">
                        <i class="fas fa-times"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" class="btn btn-whatsapp" onclick="sendWhatsAppOrder()">
                        <i class="fab fa-whatsapp"></i>
                        {{ __('messages.send_order') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <footer class="mt-24" style="background: var(--color-footer-bg); border-top: 1px solid var(--color-border);">
        <div class="max-w-6xl mx-auto px-4 py-16">
            <div class="text-center">
                <!-- Restaurant Info -->
                <h3 class="text-2xl font-bold mb-4" style="color: var(--color-footer-heading);">{{ $restaurant->name }}</h3>
                <p class="mb-8 text-lg" style="color: var(--color-footer-text);">{{ __('messages.thank_you_visiting') }}</p>

                <!-- Company Info -->
                <div class="pt-8 mt-8" style="border-top: 1px solid var(--color-border);">
                    <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-8">
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('images/logo.png') }}" alt="Hawi Tech" class="w-8 h-8 rounded">
                            <span class="font-semibold" style="color: var(--color-footer-heading);">Hawi Tech</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fab fa-whatsapp text-xl" style="color: var(--color-text-price);"></i>
                            <a href="https://wa.me/970599647713" class="transition-colors" style="color: var(--color-text-price);">
                                +970 599 647 713
                            </a>
                        </div>
                    </div>
                    <p class="text-sm mt-6" style="color: var(--color-footer-text);">
                     {{ __('messages.copyright', ['year' => date('Y'), 'restaurant' => 'Hawi Tech']) }}
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @if($restaurant->whatsapp_orders_enabled && $restaurant->whatsapp_number)
    <script>
        /*
         * Cart model
         * ----------
         * Each entry is keyed by `itemId|sortedOptionIds.join(",")` so that
         * the SAME dish with different option selections is treated as a
         * separate line item (customer expectation: 1x Large Cheese vs 1x Small).
         */
        const whatsappNumber = "{{ $restaurant->whatsapp_number }}";
        const restaurantName = "{{ $restaurant->name }}";
        const translations = {
            newOrderFrom: @js(__('messages.new_order_from', ['restaurant' => $restaurant->name])),
            orderDetails: @js(__('messages.order_details')),
            total: @js(__('messages.total', ['total' => ':total'])),
            totalLabel: @js(__('messages.total_label')),
            deliveryLocation: @js(__('messages.delivery_location')),
            additionalNotesLabel: @js(__('messages.additional_notes_label')),
            thankYou: @js(__('messages.thank_you')),
            currencySymbol: @js(__('messages.currency_symbol')),
            required: @js(__('messages.common.required')),
            minSelections: @js(__('messages.errors.min_selections_required', ['min' => ':n'])),
            maxSelections: @js(__('messages.errors.max_selections_exceeded', ['max' => ':n'])),
            requiredGroupMissing: @js(__('messages.errors.required_group_not_answered', ['group' => ':g'])),
            free: @js(__('messages.products.free'))
        };

        // Global cart store on window so all menuItemCard instances share state.
        window.__menuCart = window.__menuCart || {};

        function cartKeyFor(itemId, optionIds) {
            const sorted = [...optionIds].sort((a, b) => a - b).join(',');
            return `${itemId}|${sorted}`;
        }

        function refreshOrderSummaryBar() {
            const cart = window.__menuCart;
            const totalItems = Object.values(cart).reduce((sum, it) => sum + it.quantity, 0);
            const bar = document.getElementById('orderSummary');
            const totalSpan = document.getElementById('totalItems');
            if (!bar || !totalSpan) return;
            if (totalItems > 0) {
                totalSpan.textContent = `${totalItems} ` + @js(__('messages.items'));
                bar.classList.add('visible');
            } else {
                bar.classList.remove('visible');
            }
        }

        /**
         * Alpine component for each product card.
         * Handles option selection, dynamic price, min/max validation and cart sync.
         */
        window.menuItemCard = function menuItemCard({ id, name, basePrice, groups }) {
            return {
                id,
                name,
                basePrice: Number(basePrice),
                groups: groups || [],
                currency: translations.currencySymbol,
                // Selected option IDs per group id
                selected: {},
                quantity: 0,

                init() {
                    // Pre-tick the first option of single-select required groups
                    // to keep the UI usable from the start.
                    this.groups.forEach(g => {
                        if (g.type === 'SINGLE' && g.required && g.options.length) {
                            this.selected[g.id] = [g.options[0].id];
                        } else {
                            this.selected[g.id] = [];
                        }
                    });
                },

                get allSelectedIds() {
                    return Object.values(this.selected).flat();
                },

                get computedPrice() {
                    let total = this.basePrice;
                    this.groups.forEach(g => {
                        (this.selected[g.id] || []).forEach(optId => {
                            const opt = g.options.find(o => o.id === optId);
                            if (opt) total += Number(opt.price_delta);
                        });
                    });
                    return Math.max(0, total);
                },

                formatDelta(delta) {
                    const n = Number(delta);
                    if (n === 0) return translations.free;
                    const sign = n > 0 ? '+' : '−';
                    return `${sign}${this.currency}${Math.abs(n).toFixed(2)}`;
                },

                isSelected(group, opt) {
                    return (this.selected[group.id] || []).includes(opt.id);
                },

                toggleOption(group, opt, event) {
                    const current = this.selected[group.id] || [];
                    if (group.type === 'SINGLE') {
                        this.selected[group.id] = [opt.id];
                    } else {
                        if (event.target.checked) {
                            if (!current.includes(opt.id)) current.push(opt.id);
                        } else {
                            const idx = current.indexOf(opt.id);
                            if (idx > -1) current.splice(idx, 1);
                        }
                        this.selected[group.id] = [...current];
                    }
                    // If this item already has a quantity>0 we need to remap its cart key
                    // because the option set changed.
                    if (this.quantity > 0) {
                        this.syncCart(true);
                    }
                },

                constraintHint(group) {
                    if (group.type !== 'MULTIPLE') return '';
                    if (group.min > 0 && group.max > 0 && group.min === group.max) {
                        return `(${group.min})`;
                    }
                    if (group.min > 0 && group.max > 0) {
                        return `(${group.min}-${group.max})`;
                    }
                    if (group.max > 0) {
                        return `(≤ ${group.max})`;
                    }
                    if (group.min > 0) {
                        return `(≥ ${group.min})`;
                    }
                    return '';
                },

                errorFor(group) {
                    const chosen = (this.selected[group.id] || []).length;
                    if (group.required && chosen < 1) {
                        return translations.requiredGroupMissing.replace(':g', group.name);
                    }
                    if (group.type === 'MULTIPLE') {
                        if (group.min > 0 && chosen < group.min) {
                            return translations.minSelections.replace(':n', group.min);
                        }
                        if (group.max > 0 && chosen > group.max) {
                            return translations.maxSelections.replace(':n', group.max);
                        }
                    }
                    return null;
                },

                firstError() {
                    for (const g of this.groups) {
                        const e = this.errorFor(g);
                        if (e) return e;
                    }
                    return null;
                },

                canAddToCart() {
                    return this.firstError() === null;
                },

                changeQuantity(delta) {
                    const err = this.firstError();
                    if (err && this.quantity === 0 && delta > 0) {
                        alert(err);
                        return;
                    }
                    this.setQuantity(this.quantity + delta);
                },

                setQuantity(value) {
                    const n = Math.max(0, Math.min(99, parseInt(value) || 0));
                    this.quantity = n;
                    this.syncCart(false);
                },

                syncCart(optionChange) {
                    // Remove any cart entries for this item whose option set
                    // differs from the current selection (when options changed).
                    const currentIds = this.allSelectedIds;
                    const key = cartKeyFor(this.id, currentIds);

                    if (optionChange) {
                        Object.keys(window.__menuCart).forEach(k => {
                            if (k.startsWith(`${this.id}|`) && k !== key) {
                                delete window.__menuCart[k];
                            }
                        });
                    }

                    if (this.quantity > 0) {
                        const unit = this.computedPrice;
                        // Build human-readable option chips for WhatsApp / modal.
                        const selectedChips = [];
                        this.groups.forEach(g => {
                            (this.selected[g.id] || []).forEach(optId => {
                                const opt = g.options.find(o => o.id === optId);
                                if (opt) {
                                    selectedChips.push({
                                        group: g.name,
                                        name: opt.name,
                                        delta: Number(opt.price_delta),
                                    });
                                }
                            });
                        });

                        window.__menuCart[key] = {
                            itemId: this.id,
                            name: this.name,
                            basePrice: this.basePrice,
                            unitPrice: unit,
                            quantity: this.quantity,
                            total: unit * this.quantity,
                            options: selectedChips,
                        };
                    } else {
                        delete window.__menuCart[key];
                    }

                    refreshOrderSummaryBar();
                },
            };
        };

        function openOrderModal() {
            const cart = window.__menuCart;
            const orderItems = document.getElementById('orderItems');
            const modal = document.getElementById('orderModal');

            let html = '';
            let grandTotal = 0;

            Object.values(cart).forEach(item => {
                const optsText = item.options.length
                    ? '<div style="color: var(--color-text-muted); font-size: 0.75rem; margin-top: 0.25rem;">'
                        + item.options.map(o => `• ${o.group}: ${o.name}${o.delta ? ` (${o.delta > 0 ? '+' : '−'}${translations.currencySymbol}${Math.abs(o.delta).toFixed(2)})` : ''}`).join('<br>')
                        + '</div>'
                    : '';
                html += `
                    <div class="order-item">
                        <div>
                            <div style="font-weight:600; color: var(--color-text-primary);">${item.name}</div>
                            <div style="color: var(--color-text-muted); font-size:0.875rem;">${translations.currencySymbol}${item.unitPrice.toFixed(2)} x ${item.quantity}</div>
                            ${optsText}
                        </div>
                        <div style="font-weight:700; color: var(--color-text-price);">${translations.currencySymbol}${item.total.toFixed(2)}</div>
                    </div>
                `;
                grandTotal += item.total;
            });

            html += `
                <div class="order-item" style="border: 2px solid var(--color-card-border-hover); background: var(--color-page-bg-2);">
                    <div style="font-weight:700; color: var(--color-text-primary); font-size:1.125rem;">${translations.totalLabel}</div>
                    <div style="font-weight:700; color: var(--color-text-price); font-size:1.25rem;">${translations.currencySymbol}${grandTotal.toFixed(2)}</div>
                </div>
            `;

            orderItems.innerHTML = html;
            modal.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }

        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            modal.classList.remove('visible');
            document.body.style.overflow = 'auto';
            document.getElementById('orderNotes').value = '';
            document.getElementById('orderLocation').value = '';
        }

        function sendWhatsAppOrder() {
            const notes = document.getElementById('orderNotes').value;
            const location = document.getElementById('orderLocation').value;
            if (!location.trim()) {
                alert('Please provide your location for delivery.');
                return;
            }

            let message = `${translations.newOrderFrom}\n\n${translations.orderDetails}\n`;
            let grandTotal = 0;
            const cart = window.__menuCart;
            Object.values(cart).forEach(item => {
                message += `• ${item.name} x${item.quantity} - ${translations.currencySymbol}${item.total.toFixed(2)}\n`;
                item.options.forEach(o => {
                    message += `    ↳ ${o.group}: ${o.name}${o.delta ? ` (${o.delta > 0 ? '+' : '−'}${translations.currencySymbol}${Math.abs(o.delta).toFixed(2)})` : ''}\n`;
                });
                grandTotal += item.total;
            });

            message += `\n${translations.total.replace(':total', translations.currencySymbol + grandTotal.toFixed(2))}\n\n`;
            message += `${translations.deliveryLocation}\n${location}\n\n`;
            if (notes.trim()) {
                message += `${translations.additionalNotesLabel}\n${notes}\n\n`;
            }
            message += translations.thankYou;

            const whatsappUrl = `https://wa.me/${whatsappNumber.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');

            closeOrderModal();
            // Clear cart + reset quantity inputs.
            Object.keys(window.__menuCart).forEach(k => delete window.__menuCart[k]);
            document.querySelectorAll('.quantity-input').forEach(inp => { inp.value = 0; });
            document.querySelectorAll('.menu-item-card').forEach(card => {
                if (card.__x) { card.__x.$data.quantity = 0; }
            });
            refreshOrderSummaryBar();
        }

        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });

        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.menu-item-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>

    <!-- Language switching functionality -->
    <script>
        // Language switching functionality
        document.getElementById('language-select').addEventListener('change', function() {
            const selectedLang = this.value;

            // Set cookie to remember language preference
            const expires = new Date();
            expires.setFullYear(expires.getFullYear() + 1);
            document.cookie = "app_locale=" + selectedLang + "; path=/; expires=" + expires.toUTCString() + "; SameSite=Lax";

            // Build URL with language query parameter
            const url = new URL(window.location.href);
            url.searchParams.set('lang', selectedLang);

            // Reload the page with language parameter
            window.location.href = url.toString();
        });

        // Set initial language from cookie, query param, or default
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const queryLang = urlParams.get('lang');

            const cookies = document.cookie.split(';');
            let appLocale = 'en';

            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'app_locale') {
                    appLocale = value;
                    break;
                }
            }

            // Override with query param if present
            if (queryLang && ['en', 'ar'].includes(queryLang)) {
                appLocale = queryLang;
            }

            // Update the select element
            const select = document.getElementById('language-select');
            if (select) {
                select.value = appLocale;
            }
        });

    </script>
    @endif
    <!-- Search and Filter Functionality -->
    <script>
        document.getElementById('menuSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const menuItems = document.querySelectorAll('.menu-item-card');
    const categories = document.querySelectorAll('[data-category-section]');

    if (searchTerm === '') {
        // Show all items and categories when search is cleared
        menuItems.forEach(item => item.style.display = 'block');
        categories.forEach(category => category.style.display = 'block');
        return;
    }

    menuItems.forEach(item => {
        const itemName = item.querySelector('.menu-item-title').textContent.toLowerCase();
        const itemDescription = item.querySelector('.menu-item-description')?.textContent.toLowerCase() || '';

        if (itemName.includes(searchTerm) || itemDescription.includes(searchTerm)) {
            item.style.display = 'block';
            item.closest('[data-category-section]').style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });

    // Hide empty categories
    categories.forEach(category => {
        const visibleItems = category.querySelectorAll('.menu-item-card[style="display: block"], .menu-item-card:not([style*="display: none"])');
        if (visibleItems.length === 0 && searchTerm !== '') {
            category.style.display = 'none';
        }
    });
});

        function filterByCategory(categoryId) {
            const menuSections = document.querySelectorAll('[data-category-section]');
            const pills = document.querySelectorAll('.category-pill');

            // Update active pill
            pills.forEach(pill => pill.classList.remove('active'));
            document.querySelector(`[data-category="${categoryId}"]`).classList.add('active');

            // Show/hide sections
            if (categoryId === 'all') {
                menuSections.forEach(section => section.style.display = 'block');
            } else {
                menuSections.forEach(section => {
                    if (section.dataset.categorySection === categoryId) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            }

            // Clear search
            document.getElementById('menuSearch').value = '';
        }

        function clearSearch() {
            const menuSections = document.querySelectorAll('[data-category-section]');
            const menuItems = document.querySelectorAll('.menu-item-card');
            const pills = document.querySelectorAll('.category-pill');

            // Show all items and sections
            menuSections.forEach(section => section.style.display = 'block');
            menuItems.forEach(item => item.style.display = 'block');

            // Update active pill
            pills.forEach(pill => pill.classList.remove('active'));
            document.querySelector('[data-category="all"]').classList.add('active');

            // Clear search
            document.getElementById('menuSearch').value = '';
        }

    </script>
</body>
</html>
