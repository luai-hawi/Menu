<?php

test('an explicit supported language takes precedence over an old language cookie', function () {
    $this->withUnencryptedCookie('app_locale', 'ar')->get('/login?lang=en')
        ->assertOk()->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
});

test('the browser language preference survives requests without a language query', function () {
    $this->withUnencryptedCookie('app_locale', 'ar')->get('/login')
        ->assertOk()->assertSee('lang="ar"', false)->assertSee('dir="rtl"', false);
});

test('arabic pages use right to left direction', function () {
    $this->get('/login?lang=ar')
        ->assertOk()->assertSee('lang="ar"', false)->assertSee('dir="rtl"', false);
});

test('unsupported language inputs use the configured default', function () {
    $this->get('/login?lang=invalid')
        ->assertOk()->assertSee('lang="'.config('app.locale').'"', false);
});
