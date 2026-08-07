@extends('layouts.app',['pageTitle'=>'Academy'])
@section('content')
<?php
$allLessons = $program->modules->flatMap->lessons;
$done = $progress->where('status', 'completed')->count();
$percent = $allLessons->count() ? (int) round(($done / $allLessons->count()) * 100) : 0;
$audienceLabel = $program->audience === 'guardian' ? 'Parent Academy' : ($program->audience === 'teacher' ? 'Academy Guru' : 'Academy');
?>
<div class="academy-course-toolbar">
    <a class="academy-course-back" href="{{ route('academy.index') }}">← Kembali ke Academy</a>
    <span class="academy-course-chip"><?= e($audienceLabel) ?></span>
</div>
<header class="academy-course-head">
    <h1>{{ $program->title }}</h1>
    <p>{{ $program->description ?: $program->summary }}</p>
</header>
<div class="academy-course-progress card">
    <div class="academy-course-progress-top"><strong>{{ $percent }}%</strong><div><span>Progress Anda</span><small>{{ $done }} dari {{ $allLessons->count() }} materi selesai</small></div></div>
    <div class="academy-progress"><span style="width:{{ $percent }}%"></span></div>
    <p>Tidak perlu terburu-buru. Ambil satu materi yang paling relevan untuk keluarga hari ini.</p>
</div>

<div class="academy-course-modules">
<?php foreach ($program->modules as $module): ?>
<section class="academy-course-module card">
    <header><div><span>MODUL</span><h2><?= e($module->title) ?></h2><p><?= e($module->summary) ?></p></div><b><?= e($module->lessons->count()) ?> materi</b></header>
    <div class="academy-course-lessons">
    <?php foreach ($module->lessons as $index => $lesson): ?>
        <?php $item = $progress->get($lesson->id); ?>
        <a class="academy-course-lesson <?= $item?->status === 'completed' ? 'completed' : '' ?>" href="<?= e(route('academy.lesson', $lesson)) ?>">
            <span class="academy-course-number"><?= $item?->status === 'completed' ? '✓' : e($index + 1) ?></span>
            <div><strong><?= e($lesson->title) ?></strong><small><?= e(['article'=>'Bacaan','activity'=>'Aktivitas','checklist'=>'Checklist','video'=>'Video','audio'=>'Audio','pdf'=>'PDF','link'=>'Tautan'][$lesson->lesson_type] ?? ucfirst($lesson->lesson_type)) ?> · ± <?= e($lesson->duration_minutes ?? 5) ?> menit</small></div>
            <span class="academy-course-arrow">→</span>
        </a>
    <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
</div>
@endsection
