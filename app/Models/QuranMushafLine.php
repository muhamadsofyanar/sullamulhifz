<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranMushafLine extends Model
{
    protected $fillable = [
        'layout_code','page_number','line_number','line_type','is_centered','text','qpc_v2','surah_number','verse_range',
        'first_word_location','last_word_location','first_surah_id','first_verse','first_word_position',
        'last_surah_id','last_verse','last_word_position','source_name','source_ref',
    ];

    protected function casts(): array
    {
        return [
            'is_centered' => 'boolean',
            'page_number' => 'integer',
            'line_number' => 'integer',
            'surah_number' => 'integer',
            'first_surah_id' => 'integer',
            'first_verse' => 'integer',
            'first_word_position' => 'integer',
            'last_surah_id' => 'integer',
            'last_verse' => 'integer',
            'last_word_position' => 'integer',
        ];
    }
}
