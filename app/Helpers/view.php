<?php

declare(strict_types=1);

function product_image(array $p): string
{
    if (!empty($p['images']) && is_array($p['images'])) {
        return (string)$p['images'][0];
    }
    return (string)($p['image'] ?? '');
}

function first_unit_price(array $p): float
{
    $base = (float)$p['base_price'];
    $tiers = $p['tiers'] ?? [];
    $off = $tiers ? (float)($tiers[0]['discount'] ?? 0) : 0.0;
    return round($base * (1 - $off / 100), 2);
}

function pack_label(array $p): string
{
    if (empty($p['pack_units'])) {
        return '';
    }
    return !empty($p['pack_ratio'])
        ? ('Pack of ' . $p['pack_units'] . ': ' . $p['pack_ratio'])
        : ('Pack of ' . $p['pack_units']);
}

function stock_label(?string $s): string
{
    return match ($s) {
        'in_stock' => 'In stock',
        'limited' => 'Limited',
        'made_to_order' => 'Made to order',
        default => (string)$s,
    };
}

function catalog_url(array $filters, array $extra = []): string
{
    $q = array_merge($filters, $extra);
    $out = [];
    foreach ($q as $k => $v) {
        if ($v === null || $v === '' || $v === false) {
            continue;
        }
        if ($k === 'page' && (int)$v <= 1) {
            continue;
        }
        if ($k === 'sort' && ($v === 'featured' || $v === '')) {
            continue;
        }
        $out[$k] = $v;
    }
    $qs = http_build_query($out);
    return url('catalog') . ($qs !== '' ? ('?' . $qs) : '');
}

function nkw_icon(string $name, string $cls = 'ico'): string
{
    static $icons = [
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
        'bag' => '<path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
        'menu' => '<path d="M4 8h16M4 16h16"/>',
        'close' => '<path d="M5 5l14 14M19 5L5 19"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'arrow' => '<path d="M4 12h15M13 6l6 6-6 6"/>',
        'down' => '<path d="M6 9l6 6 6-6"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'user' => '<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.5-3.5 4-5 7-5s5.5 1.5 7 5"/>',
        'file' => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/>',
        'phone' => '<path d="M6 3.5h3l1.5 4-2 1.5a11 11 0 0 0 6.5 6.5l1.5-2 4 1.5v3a2 2 0 0 1-2 2A16 16 0 0 1 4 5.5a2 2 0 0 1 2-2z"/>',
        'mail' => '<rect x="3" y="5.5" width="18" height="13"/><path d="M3.5 6.5L12 13l8.5-6.5"/>',
        'pin' => '<path d="M12 21s-6.5-5.7-6.5-11a6.5 6.5 0 0 1 13 0c0 5.3-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/>',
        'insta' => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.7"/><circle cx="16.8" cy="7.2" r=".6" fill="currentColor"/>',
        'fb' => '<path d="M14 21v-8h2.7l.5-3.2H14V8c0-1 .5-1.8 1.8-1.8h1.5V3.4A18 18 0 0 0 15 3.2c-2.5 0-4.2 1.5-4.2 4.2v2.4H8V13h2.8v8"/>',
        'chat' => '<path d="M4 19l1.2-3.8A8 8 0 1 1 8.8 18.8z"/>',
        'filter' => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
        'warn' => '<path d="M12 4l9 16H3z"/><path d="M12 10v4M12 17h.01"/>',
    ];
    $inner = $icons[$name] ?? '';
    return '<svg class="' . e($cls) . '" viewBox="0 0 24 24" aria-hidden="true">' . $inner . '</svg>';
}

function status_pill(?string $status): string
{
    $status = (string)$status;
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $status) ?? $status);
    $label = ucfirst(str_replace(['_', '-'], ' ', $status));
    return '<span class="pill pill-' . e($slug) . '">' . e($label) . '</span>';
}

function trend_chip(array $trend): string
{
    $dir = $trend['dir'] ?? 'flat';
    $arrow = $dir === 'up' ? '↑' : ($dir === 'down' ? '↓' : '→');
    return '<span class="trend trend-' . e($dir) . '">' . $arrow . ' ' . e((string)($trend['label'] ?? '')) . '</span>';
}

function initials(?string $name): string
{
    $name = trim((string)$name);
    if ($name === '') {
        return 'NK';
    }
    $parts = preg_split('/\s+/', $name) ?: [];
    $ini = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $ini !== '' ? $ini : 'NK';
}
