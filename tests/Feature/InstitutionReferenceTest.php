<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstitutionReferenceTest extends TestCase
{
    public function test_public_institution_showcase_is_available(): void
    {
        $this->get('/lembaga/tpa-al-insyirah')
            ->assertOk()
            ->assertSee('TPA Al-Insyirah')
            ->assertSee('88')
            ->assertSee('Tahfizh A')
            ->assertSee('Human Before Data');
    }

    public function test_public_institution_reference_guide_is_available(): void
    {
        $this->get('/referensi-lembaga')
            ->assertOk()
            ->assertSee('WAJIB DISESUAIKAN')
            ->assertSee('Tidak ada ranking')
            ->assertSee('Lihat TPA Al-Insyirah');
    }
}
