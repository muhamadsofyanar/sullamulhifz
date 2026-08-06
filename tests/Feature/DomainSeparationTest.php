<?php

namespace Tests\Feature;

use Tests\TestCase;

class DomainSeparationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sullam.domain_separation_enabled' => true,
            'sullam.public_url' => 'https://sullamulhifz.or.id',
            'sullam.public_hosts' => ['sullamulhifz.or.id', 'www.sullamulhifz.or.id'],
            'sullam.portal_base_url' => 'https://app.sullamulhifz.or.id',
            'sullam.portal_url' => 'https://app.sullamulhifz.or.id/login',
            'sullam.portal_host' => 'app.sullamulhifz.or.id',
        ]);
    }

    public function test_public_domain_keeps_public_homepage(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'sullamulhifz.or.id'])
            ->get('/')
            ->assertOk()
            ->assertSee('Menumbuhkan hubungan');
    }

    public function test_public_domain_sends_login_to_portal_domain(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'sullamulhifz.or.id'])
            ->get('/login')
            ->assertRedirect('https://app.sullamulhifz.or.id/login');
    }

    public function test_public_domain_sends_dashboard_to_portal_domain(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'sullamulhifz.or.id'])
            ->get('/dashboard')
            ->assertRedirect('https://app.sullamulhifz.or.id/dashboard');
    }

    public function test_portal_root_sends_guest_to_login(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'app.sullamulhifz.or.id'])
            ->get('/')
            ->assertRedirect('https://app.sullamulhifz.or.id/login');
    }

    public function test_portal_domain_sends_public_pages_to_public_domain(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'app.sullamulhifz.or.id'])
            ->get('/tentang')
            ->assertRedirect('https://sullamulhifz.or.id/tentang');
    }

    public function test_www_is_canonicalized_to_non_www(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'www.sullamulhifz.or.id'])
            ->get('/program?asal=www')
            ->assertRedirect('https://sullamulhifz.or.id/program?asal=www');
    }

    public function test_legacy_domain_remains_available_during_transition(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'taysriulqurani.id'])
            ->get('/')
            ->assertOk();
    }
}
