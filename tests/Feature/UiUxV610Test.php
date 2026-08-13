<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiUxV610Test extends TestCase
{
    public function test_v610_ui_assets_and_role_workspaces_are_wired(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $css = file_get_contents(public_path('css/app-v610.css'));
        $infaq = file_get_contents(resource_path('views/infaq/index.blade.php'));
        $realisations = file_get_contents(resource_path('views/admin/infaq/realisations/index.blade.php'));
        $tahfizh = file_get_contents(resource_path('views/teacher/tahfizh/index.blade.php'));
        $guardian = file_get_contents(resource_path('views/dashboard/guardian.blade.php'));

        $this->assertStringContainsString('/css/app-v610.css', $layout);
        $this->assertStringContainsString('v610-nav-label', $layout);
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('min-height:44px', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('Bukti transfer', $infaq);
        $this->assertStringContainsString('Versi publik tersamarkan', $realisations);
        $this->assertStringContainsString('Antrean prioritas', $tahfizh);
        $this->assertStringContainsString('family-child-signals', $guardian);
    }

    public function test_no_http_route_executes_a_production_restore(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $this->assertStringNotContainsString('Artisan::call(\'db:restore', $routes);
        $this->assertStringNotContainsString('Process::run(\'mysql', $routes);
        $this->assertStringContainsString('RecoveryController::class, \'simulation\'', $routes);
    }
}
