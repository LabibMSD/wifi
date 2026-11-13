<?php

test('the Indonesian homepage presents the company profile and removes network and asset sections', function () {
    app()->setLocale('id');

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('<html lang="id">', false)
        ->assertSee('Eponet')
        ->assertSee('2.819')
        ->assertSee('1.035')
        ->assertSee('66')
        ->assertSee('Profil usaha dan layanan.')
        ->assertSee('Pelanggan menurut layanan')
        ->assertSee('TV digital, dan TV analog')
        ->assertDontSee('Loket layanan')
        ->assertDontSee('Aset operasional yang tercatat')
        ->assertDontSee('Pajinggahan')
        ->assertDontSee('Kabel fiber 24 core')
        ->assertDontSee('landing.')
        ->assertDontSee(':business')
        ->assertSee(__('landing.services.heading'))
        ->assertSee(__('landing.theme.toggle'))
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-label-light', false)
        ->assertSee('data-label-dark', false);

    expect(substr_count($response->getContent(), route('filament.admin.auth.login')))
        ->toBe(1);
});

test('the English homepage presents translated company profile details', function () {
    app()->setLocale('en');

    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="en">', false)
        ->assertSee('2,819')
        ->assertSee('1,035')
        ->assertSee('Business and service profile.')
        ->assertSee('Customers by service')
        ->assertSee('digital TV, and analog TV services')
        ->assertDontSee('Service counters')
        ->assertDontSee('Operational assets on record')
        ->assertDontSee('24-core fiber cable')
        ->assertDontSee('landing.')
        ->assertDontSee(':business');
});
