<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('sullam:about', function (): void {
    $this->info('Sullamul Hifz MVP — Bukan Sekadar Hafal, Tapi KUAT.');
})->purpose('Menampilkan identitas aplikasi');
