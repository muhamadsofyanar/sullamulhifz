{{-- @phase 6.0 Distraction-free memorization submission --}}
@php($quickStudents = $quickStudents ?? collect())
@php($quickSelectedStudentId = (int) ($quickSelectedStudentId ?? 0))
<form class="stack compact quick-submission" method="post" action="{{ $quickAction }}" data-quick-submission>
    @csrf
    <input type="hidden" name="submission_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
    @if($quickStudents->isNotEmpty())
        <label>Santri
            <select name="student_id" required data-quick-student>
                <option value="">Pilih santri</option>
                @foreach($quickStudents as $quickStudent)<option value="{{ $quickStudent->id }}" @selected($quickSelectedStudentId === (int) $quickStudent->id)>{{ $quickStudent->full_name }}</option>@endforeach
            </select>
        </label>
    @endif
    <label>Bagian setoran
        <select name="memorization_target_id" data-quick-source>
            <option value="">Pilih manual / latihan bebas</option>
            @foreach($quickTargets as $target)
                <option value="{{ $target->id }}" @selected((int) ($quickAutoTargetId ?? 0) === (int) $target->id)
                    data-student="{{ $target->student_id }}" data-surah="{{ $target->surah_id }}"
                    data-start="{{ $target->start_verse }}" data-end="{{ $target->end_verse }}">
                    @if($quickStudents->isNotEmpty()){{ $target->student?->full_name }} — @endif{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}
                </option>
            @endforeach
        </select>
        <small>Target aktif diprioritaskan otomatis dan tetap dapat diubah.</small>
    </label>
    <details class="quick-portion-edit">
        <summary>Ubah bagian ayat</summary>
        <div class="form-grid">
            <label>Surah<select name="surah_id" required data-quick-surah>@foreach($quickSurahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
            <label>Ayat awal<input type="number" name="start_verse" min="1" required data-quick-start></label>
            <label>Ayat akhir<input type="number" name="end_verse" min="1" required data-quick-end></label>
        </div>
    </details>
    <fieldset class="quick-decision"><legend>Kesimpulan setelah menyimak</legend>
        <label class="decision-continue"><input type="radio" name="daily_decision" value="lanjut" required><span><b>Lanjut</b><small>Sudah cukup kuat</small></span></label>
        <label class="decision-strengthen"><input type="radio" name="daily_decision" value="kuatkan" required><span><b>Kuatkan</b><small>Latih bagian tertentu</small></span></label>
        <label class="decision-repeat"><input type="radio" name="daily_decision" value="ulang" required><span><b>Ulang</b><small>Belum siap lanjut</small></span></label>
    </fieldset>
    <label>Satu catatan kecil <small>(opsional)</small><input name="short_note" maxlength="500" placeholder="Contoh: ulang ayat 6–8 sebanyak 3×"></label>
    <button class="button primary">{{ $quickSubmitLabel ?? 'Simpan hasil setoran' }}</button>
</form>
<script>
(function () {
    var form = document.currentScript.previousElementSibling;
    if (!form || !form.matches('[data-quick-submission]')) return;
    var source = form.querySelector('[data-quick-source]');
    var student = form.querySelector('[data-quick-student]');
    function apply() {
        var option = source && source.options[source.selectedIndex];
        var details = form.querySelector('.quick-portion-edit');
        if (!option || !option.dataset.surah) {
            if (details) details.open = true;
            return;
        }
        if (details) details.open = false;
        if (student && option.dataset.student) student.value = option.dataset.student;
        form.querySelector('[data-quick-surah]').value = option.dataset.surah;
        form.querySelector('[data-quick-start]').value = option.dataset.start || '';
        form.querySelector('[data-quick-end]').value = option.dataset.end || '';
    }
    function chooseForStudent() {
        if (!source || !student) return apply();
        var studentId = student.value;
        var firstMatch = null;
        Array.prototype.forEach.call(source.options, function (option, index) {
            if (index === 0) return;
            var matches = !studentId || option.dataset.student === studentId;
            option.hidden = !matches;
            if (matches && !firstMatch) firstMatch = option;
        });
        source.value = firstMatch ? firstMatch.value : '';
        apply();
    }
    if (source) source.addEventListener('change', apply);
    if (student) student.addEventListener('change', chooseForStudent);
    if (student && student.value) chooseForStudent(); else apply();
})();
</script>
