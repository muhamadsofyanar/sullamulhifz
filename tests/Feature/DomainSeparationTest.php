<?php

namespace Tests\Feature;

/** @phase 6.0 API domain isolation regression */

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
            'sullam.api_host' => 'api.sullamulhifz.or.id',
        ]);
    }

    public function test_public_domain_keeps_public_homepage(): void
    {
        $this->get('https://sullamulhifz.or.id/')
            ->assertOk()
            ->assertSee('Jaga perjalanan Al-Qur’an');
    }

    public function test_public_domain_sends_login_to_portal_domain(): void
    {
        $this->get('https://sullamulhifz.or.id/login')
            ->assertRedirect('https://app.sullamulhifz.or.id/login');
    }

    public function test_public_domain_sends_dashboard_to_portal_domain(): void
    {
        $this->get('https://sullamulhifz.or.id/dashboard')
            ->assertRedirect('https://app.sullamulhifz.or.id/dashboard');
    }

    public function test_portal_root_sends_guest_to_login(): void
    {
        $this->get('https://app.sullamulhifz.or.id/')
            ->assertRedirect('https://app.sullamulhifz.or.id/login');
    }

    public function test_portal_domain_sends_public_pages_to_public_domain(): void
    {
        $this->get('https://app.sullamulhifz.or.id/tentang')
            ->assertRedirect('https://sullamulhifz.or.id/tentang');
    }

    public function test_www_is_canonicalized_to_non_www(): void
    {
        $this->get('https://www.sullamulhifz.or.id/program?asal=www')
            ->assertRedirect('https://sullamulhifz.or.id/program?asal=www');
    }

    public function test_legacy_domain_remains_available_during_transition(): void
    {
        $this->get('https://taysriulqurani.id/')
            ->assertOk();
    }

    public function test_api_path_is_not_exposed_on_public_or_portal_domains(): void
    {
        $this->get('https://sullamulhifz.or.id/api/v1/meta')->assertNotFound();
        $this->get('https://app.sullamulhifz.or.id/api/v1/meta')->assertNotFound();
    }

    public function test_api_metadata_is_available_on_the_api_domain(): void
    {
        $this->get('https://api.sullamulhifz.or.id/api/v1/meta')
            ->assertOk()
            ->assertJsonPath('product', 'Sullamul Hifz');
    }
}
