<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    #[DataProvider('publicPages')]
    public function test_public_pages_are_available(string $path, string $text): void
    {
        $this->get('https://sullamulhifz.or.id'.$path)->assertOk()->assertSee($text);
    }

    public static function publicPages(): array
    {
        return [
            ['/', 'Jaga perjalanan Al-Qur’an'],
            ['/tentang', 'Tangga pertumbuhan'],
            ['/program', 'Satu filosofi'],
            ['/tpa', 'Administrasi yang membantu'],
            ['/ikrar-santri', 'Ikrar Santri'],
            ['/academy', 'Belajar bersama agar pendampingan tidak terputus'],
            ['/artikel', 'Membaca ulang perjalanan'],
            ['/kontak', 'Mari membangun perjalanan'],
            ['/privasi', 'Perjalanan Al-Qur’an adalah data yang perlu dijaga'],
        ];
    }

    public function test_portal_host_root_redirects_to_login_for_guest(): void
    {
        config(['sullam.portal_host' => 'app.sullamulhifz.or.id']);

        $this->get('https://app.sullamulhifz.or.id/')
            ->assertRedirect('https://app.sullamulhifz.or.id/login');
    }

    public function test_internal_dashboard_remains_protected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
