<?php

it('sends security headers on public pages', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("default-src 'self'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'");
});

it('sends HSTS in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/')->assertHeader('Strict-Transport-Security');
});

it('does not expose private files by URL', function () {
    $this->get('/storage/private/anything.pdf')->assertNotFound();
});
