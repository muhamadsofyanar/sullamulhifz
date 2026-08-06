<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\StudentPledge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPledgeController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.student-pledge.edit', [
            'pledge' => StudentPledge::forInstitution($request->user()->institution_id),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'eyebrow' => ['required', 'string', 'max:190'],
            'title' => ['required', 'string', 'max:190'],
            'institution_descriptor' => ['required', 'string', 'max:190'],
            'institution_motto' => ['required', 'string', 'max:300'],
            'intro' => ['required', 'string', 'max:500'],
            'closing' => ['required', 'string', 'max:500'],
            'aspiration' => ['required', 'string', 'max:1000'],
            'items' => ['required', 'array', 'size:7'],
            'items.*.number' => ['required', 'integer', 'between:1,7'],
            'items.*.short_title' => ['required', 'string', 'max:80'],
            'items.*.title' => ['required', 'string', 'max:500'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.theme' => ['nullable', 'string', 'max:50'],
            'values' => ['required', 'array', 'size:5'],
            'values.*.title' => ['required', 'string', 'max:120'],
            'values.*.description' => ['required', 'string', 'max:500'],
            'practice' => ['required', 'array', 'size:3'],
            'practice.*.place' => ['required', 'string', 'max:120'],
            'practice.*.description' => ['required', 'string', 'max:700'],
        ]);

        foreach ($data['items'] as $index => &$item) {
            $item['number'] = $index + 1;
        }
        unset($item);

        StudentPledge::save($request->user()->institution_id, $data);

        return back()->with('success', 'Ikrar Santri berhasil diperbarui. Perubahan langsung tampil di website dan portal.');
    }

    public function reset(Request $request): RedirectResponse
    {
        StudentPledge::reset($request->user()->institution_id);

        return back()->with('success', 'Ikrar Santri dikembalikan ke data bawaan TPA Al-Insyirah.');
    }
}
