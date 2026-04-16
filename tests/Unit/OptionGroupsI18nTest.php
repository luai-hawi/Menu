<?php

use function PHPUnit\Framework\assertTrue;

/*
 * Guards that every option-groups related translation key exists in both
 * locales and that Arabic values are actually Arabic (contain at least one
 * character in the Arabic Unicode block). Cheap, fast, high-value.
 */

function i18nFlatten(array $arr, string $prefix = ''): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        $key = $prefix === '' ? (string) $k : "$prefix.$k";
        if (is_array($v)) {
            $out += i18nFlatten($v, $key);
        } else {
            $out[$key] = $v;
        }
    }

    return $out;
}

function i18nLangPath(string $locale): string
{
    return __DIR__.'/../../resources/lang/'.$locale.'/messages.php';
}

$requiredSections = ['products', 'optionGroups', 'options', 'form', 'common', 'errors'];

test('both locales define every required i18n section', function () use ($requiredSections) {
    $en = require i18nLangPath('en');
    $ar = require i18nLangPath('ar');

    foreach ($requiredSections as $section) {
        assertTrue(array_key_exists($section, $en), "en/messages.php missing section [$section]");
        assertTrue(array_key_exists($section, $ar), "ar/messages.php missing section [$section]");
        expect($en[$section])->toBeArray();
        expect($ar[$section])->toBeArray();
    }
});

test('every en key inside the new sections has a matching ar key', function () use ($requiredSections) {
    $en = require i18nLangPath('en');
    $ar = require i18nLangPath('ar');

    foreach ($requiredSections as $section) {
        $enFlat = i18nFlatten($en[$section]);
        $arFlat = i18nFlatten($ar[$section]);
        foreach (array_keys($enFlat) as $key) {
            assertTrue(
                array_key_exists($key, $arFlat),
                "ar/messages.php missing translation for [$section.$key]"
            );
        }
    }
});

test('arabic values actually contain Arabic characters', function () use ($requiredSections) {
    $ar = require i18nLangPath('ar');

    // Intentionally Latin: visual language badges (EN / AR).
    $allowedLatin = ['common.english_abbr', 'common.arabic_abbr'];

    foreach ($requiredSections as $section) {
        foreach (i18nFlatten($ar[$section]) as $key => $value) {
            $full = "$section.$key";
            if (in_array($full, $allowedLatin, true)) {
                continue;
            }
            if ($value === '' || $value === null) {
                continue;
            }
            // Strings that are *only* placeholders / digits / punctuation get a pass.
            $stripped = preg_replace('/:[a-z_]+/i', '', (string) $value);
            $stripped = preg_replace('/[\p{N}\p{P}\p{S}\s]+/u', '', $stripped);
            if ($stripped === '') {
                continue;
            }
            assertTrue(
                preg_match('/\p{Arabic}/u', (string) $value) === 1,
                "Arabic translation for [$full] contains no Arabic characters: ".$value
            );
        }
    }
});

test('validation messages expose placeholders we pass at runtime', function () {
    $en = require i18nLangPath('en');
    $ar = require i18nLangPath('ar');

    expect($en['errors']['min_selections_required'])->toContain(':min');
    expect($ar['errors']['min_selections_required'])->toContain(':min');
    expect($en['errors']['max_selections_exceeded'])->toContain(':max');
    expect($ar['errors']['max_selections_exceeded'])->toContain(':max');
    expect($en['errors']['required_group_not_answered'])->toContain(':group');
    expect($ar['errors']['required_group_not_answered'])->toContain(':group');
    expect($en['errors']['max_exceeds_options'])->toContain(':count');
    expect($ar['errors']['max_exceeds_options'])->toContain(':count');
});
