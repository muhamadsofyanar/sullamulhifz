<?php

namespace Tests\Feature;

/** @phase 4.2 Brand & Universal Home */

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPositioningV420Test extends TestCase
{
    use RefreshDatabase;

    public function test_home_positions_product_for_all_primary_audiences(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SATU EKOSISTEM')
            ->assertSee('Personal')
            ->assertSee('Bimbingan Ustadz')
            ->assertSee('Keluarga')
            ->assertSee('Lembaga')
            ->assertSee('TPA Al-Insyirah adalah salah satu penerapan');
    }

    public function test_all_solution_pages_and_product_pages_are_available(): void
    {
        foreach (['personal', 'ustadz', 'keluarga', 'lembaga'] as $audience) {
            $this->get(route('public.solution', $audience))->assertOk()->assertSee('POLA HUBUNGAN');
        }

        $this->get(route('public.features'))->assertOk()->assertSee('Satu mesin perjalanan');
        $this->get(route('public.pricing'))->assertOk()->assertSee('Harga resmi belum dipublikasikan');
    }
}
