<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    #[DataProvider('publicPages')]
    public function test_public_pages_are_available(string $path, string $text): void
    {
        $this->get($path)->assertOk()->assertSee($text);
    }

    public static function publicPages(): array
    {
        return [
            ['/', 'Menumbuhkan hubungan'],
            ['/tentang', 'Tangga pertumbuhan'],
            ['/program', 'beberapa jalur pembinaan'],
            ['/tpa', 'Administrasi yang membantu'],
            ['/ikrar-santri', 'Ikrar Santri'],
            ['/academy', 'Ruang belajar digital'],
            ['/artikel', 'Membaca ulang perjalanan'],
            ['/kontak', 'Mari membangun perjalanan'],
            ['/privasi', 'Data anak bukan bahan promosi'],
        ];
    }

    public function test_portal_host_root_redirects_to_login_for_guest(): void
    {
        config(['sullam.portal_host' => 'app.sullamulhifz.or.id']);

        $this->withServerVariables(['HTTP_HOST' => 'app.sullamulhifz.or.id'])
            ->get('/')
            ->assertRedirect('/login');
    }

    public function test_internal_dashboard_remains_protected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
