@extends('layouts.app',['pageTitle'=>'Latihan Al-Qur’an'])
@section('content')
<div class="quran-premium-page">
<section class="quran-premium-hero">
    <div class="quran-premium-copy">
        <span class="eyebrow">TEMANI DENGAN TENANG</span>
        <h1>Latihan Al-Qur’an</h1>
        <p>Dengarkan bacaan, ulangi sesuai kebutuhan, lalu dampingi anak tanpa terburu-buru.</p>
    </div>
    @if(auth()->user()->hasPermission('quran.manage'))
        <a class="quran-library-link" href="{{ route('admin.quran-library.index') }}"><x-icon name="audio" size="18"/><span>Pustaka Qur’an</span></a>
    @endif
</section>

@if($sources->isEmpty())
<div class="alert danger">Audio belum siap. Admin perlu menyinkronkan Pustaka Qur’an.</div>
@endif

@if(auth()->user()->hasAnyRole(['superadmin','institution_admin','head']))
<div class="quran-admin-readiness" aria-label="Status pustaka Al-Qur'an"><span><b>{{ $timingCount }}/6236</b> ayat</span><span><b>{{ $presets->count() }}</b> latihan</span><span><b>{{ $sources->count() }}</b> qari</span></div>
@endif

@if($targets->isNotEmpty())
<section class="quran-focus-section">
    <div class="quran-section-title"><div><span class="quran-kicker">DARI GURU</span><h2>Latihan hari ini</h2><p>Pilihan paling mudah: cukup tekan <strong>Mulai</strong>.</p></div></div>
    <div class="quran-focus-list">
    @foreach($targets as $target)
        <button type="button" class="quran-focus-target" data-load-target="{{ $target->id }}">
            <span class="quran-focus-avatar">{{ strtoupper(mb_substr($target->student->full_name,0,1)) }}</span>
            <span class="quran-focus-content">
                <small>{{ $target->student->full_name }}</small>
                <strong>{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</strong>
                <span>{{ $target->rubu?->name ?? 'Target hafalan' }} · 10× per ayat</span>
            </span>
            <span class="quran-focus-action"><b>Mulai</b><span>▶</span></span>
        </button>
    @endforeach
    </div>
</section>
@endif

@if($featuredPresets->isNotEmpty())
<section class="quran-preset-section">
    <div class="quran-section-title"><div><span class="quran-kicker">PILIHAN CEPAT</span><h2>Latihan siap pakai</h2><p>Untuk latihan yang sering digunakan di rumah.</p></div></div>
    <div class="quran-premium-presets">
    @foreach($featuredPresets->take(4) as $preset)
        <button type="button" class="quran-premium-preset" data-load-preset="{{ $preset->id }}">
            <span class="quran-preset-icon"><x-icon name="audio" size="20"/></span>
            <span class="quran-preset-copy"><strong>{{ $preset->title }}</strong><small>{{ $preset->repeat_count===0?'Tanpa batas':$preset->repeat_count.'×' }} · {{ $preset->repeat_scope==='each_item'?'setiap ayat':'seluruh bagian' }}</small></span>
            <span class="quran-preset-arrow">›</span>
        </button>
    @endforeach
    </div>
</section>
@endif

<section class="quran-player-premium" id="quran-player-card" aria-live="polite">
    <div class="quran-player-empty" data-player-empty>
        <div class="quran-player-orb"><span>▶</span></div>
        <h2>Siap menemani latihan</h2>
        <p>Pilih target dari guru, latihan siap pakai, atau atur latihan sendiri.</p>
    </div>
    <div class="quran-player-ready" data-player-ready hidden>
        <div class="quran-player-headline">
            <div><small data-player-source>SUMBER AUDIO</small><h2 data-player-title>Latihan Al-Qur’an</h2><p data-player-summary></p></div>
            <span class="quran-repeat-badge" data-player-repeat></span>
        </div>
        <div class="quran-now-playing-v2">
            <small>Sedang dibaca</small>
            <strong data-player-current>—</strong>
            <span data-player-progress>0/0</span>
            <div class="quran-page-chip" data-player-page hidden>Halaman —</div>
        </div>
        <audio id="quran-audio" preload="metadata"></audio>
        <div class="quran-main-controls">
            <button type="button" data-player-prev aria-label="Ayat sebelumnya">⏮</button>
            <button type="button" class="quran-main-play" data-player-toggle aria-label="Putar atau jeda">▶</button>
            <button type="button" data-player-next aria-label="Ayat berikutnya">⏭</button>
        </div>
        <div class="quran-status-line"><span data-counter-ayah>0/0</span><strong data-counter-item>0/0</strong><span data-counter-time>00:00</span></div>
        <div class="quran-progress-track"><span data-player-bar></span></div>
        <figure class="quran-page-preview" data-page-preview hidden><img alt="Halaman Mushaf yang sedang diputar" data-page-image><figcaption>Orientasi halaman Mushaf.</figcaption></figure>
        <button type="button" class="quran-stop-premium" data-player-stop>Hentikan latihan</button>
        <p class="quran-talaqqi-note">Murattal membantu latihan. Talaqqi dan koreksi guru tetap menjadi rujukan utama.</p>
    </div>
</section>

<details class="quran-custom-disclosure" @if($targets->isEmpty() && $featuredPresets->isEmpty()) open @endif>
    <summary><span class="quran-custom-summary-icon">＋</span><span><strong>Atur latihan sendiri</strong><small>Pilih qari, surat atau ayat, dan jumlah pengulangan.</small></span><span class="quran-custom-chevron">⌄</span></summary>
    <div class="quran-custom-body">
        <form id="quran-builder" class="stack">
            <label class="senior-field">Qari
                <select name="source_id" required data-qari-source>@foreach($sources as $source)<option value="{{ $source->id }}" data-role="{{ data_get($source->metadata,'learning_role') }}" data-description="{{ data_get($source->metadata,'description') }}" @selected($defaultSource?->id===$source->id)>{{ $source->is_default?'⭐ ':'' }}{{ $source->reciter_name }}</option>@endforeach</select>
                <small class="hint" data-qari-description>{{ data_get($defaultSource?->metadata,'description') }}</small>
            </label>

            <div class="form-grid quran-primary-fields">
                <label class="senior-field">Jenis latihan<select name="mode" data-quran-mode><option value="ayah">Satu ayat</option><option value="range" selected>Beberapa ayat</option><option value="surah">Satu surah</option><option value="juz">Satu juz</option><option value="page">Satu halaman</option><option value="hizb_quarter">Satu Rubu‘ al-Hizb</option><option value="rubu">Segment Juz 30 lama</option></select></label>
                <label class="senior-field" data-quran-surah>Surah<select name="surah_id">@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
            </div>

            <div class="quran-verse-pair" data-quran-verses><label>Ayat mulai<input type="number" name="start_verse" min="1" value="1"></label><span>sampai</span><label>Ayat akhir<input type="number" name="end_verse" min="1" value="5"></label></div>
            <label class="senior-field" data-quran-page hidden>Halaman Mushaf<select name="page_number"><option value="">Pilih halaman</option>@foreach($pages as $page)<option value="{{ $page }}">Halaman {{ $page }}</option>@endforeach</select></label>
            <label class="senior-field" data-quran-juz hidden>Juz<select name="juz_number"><option value="">Pilih juz</option>@foreach($juzs as $juz)<option value="{{ $juz }}">Juz {{ $juz }}</option>@endforeach</select></label>
            <label class="senior-field" data-quran-hizb-quarter hidden>Rubu‘ al-Hizb<select name="hizb_quarter"><option value="">Pilih rubu‘</option>@foreach($hizbQuarters as $quarter)<option value="{{ $quarter }}">Rubu‘ {{ $quarter }}/240</option>@endforeach</select></label>
            <label class="senior-field" data-quran-rubu hidden>Segment Juz 30 lama<select name="rubu_id"><option value="">Pilih segment</option>@foreach($rubus as $rubu)<option value="{{ $rubu->id }}">{{ $rubu->name }}</option>@endforeach</select></label>

            <div class="quran-repeat-box"><span>Jumlah pengulangan</span><div class="quran-repeat-stepper"><button type="button" data-repeat-minus aria-label="Kurangi pengulangan">−</button><strong data-repeat-label>10×</strong><button type="button" data-repeat-plus aria-label="Tambah pengulangan">+</button></div><select name="repeat_count" data-repeat-select aria-label="Jumlah pengulangan"><option value="1">1×</option><option value="3">3×</option><option value="5">5×</option><option value="10" selected>10×</option><option value="20">20×</option><option value="0">Tanpa batas</option></select></div>

            <details class="quran-advanced"><summary>Pengaturan lanjutan</summary><div class="form-grid">
                <label>Pola pengulangan<select name="repeat_scope"><option value="each_item" selected>Ulang setiap ayat</option><option value="whole_selection">Ulang seluruh bagian</option></select></label>
                <label>Jeda<select name="gap_seconds"><option value="0">Tanpa jeda</option><option value="1">1 detik</option><option value="2" selected>2 detik</option><option value="5">5 detik</option><option value="10">10 detik</option></select></label>
                <label>Kecepatan<select name="playback_rate"><option value="0.75">0,75×</option><option value="0.90" selected>0,90×</option><option value="1">1× normal</option><option value="1.15">1,15×</option><option value="1.25">1,25×</option></select></label>
            </div></details>
            <button type="submit" class="button primary wide quran-start-button">▶ Mulai latihan</button>
        </form>
    </div>
</details>

<details class="card quran-more-library"><summary>Temukan latihan lain</summary><div class="quran-preset-list">@foreach($presets as $preset)<button type="button" data-load-preset="{{ $preset->id }}"><span class="badge">{{ $preset->mode }}</span><strong>{{ $preset->title }}</strong><small>{{ $preset->repeat_count===0?'∞':$preset->repeat_count.'×' }}</small></button>@endforeach</div></details>

@if($videos->isNotEmpty())<details class="card quran-more-library"><summary>Video bacaan terpilih</summary><div class="quran-video-grid">@foreach($videos as $video)<article><h3>{{ $video->title }}</h3><p>{{ $video->surah?->name_latin }}</p>@if($video->source_type==='youtube' && $video->youtubeId())<div class="video-frame"><iframe src="https://www.youtube.com/embed/{{ $video->youtubeId() }}?playsinline=1&rel=0" title="{{ $video->title }}" allowfullscreen></iframe></div>@else<video controls preload="metadata" src="{{ $video->video_url }}"></video>@endif</article>@endforeach</div></details>@endif
</div>

<script>
window.addEventListener('DOMContentLoaded',()=>{
const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';const form=document.getElementById('quran-builder');const audio=document.getElementById('quran-audio');const mode=form?.querySelector('[data-quran-mode]');const player={payload:null,index:0,itemRepeat:0,cycle:0,playing:false,startedAt:null,sessionId:null,timer:null,segmentEnding:false};const el=s=>document.querySelector(s);
const showMode=()=>{const m=mode.value;el('[data-quran-surah]').hidden=['page','juz','hizb_quarter','rubu'].includes(m);el('[data-quran-verses]').hidden=['surah','page','juz','hizb_quarter','rubu'].includes(m);el('[data-quran-page]').hidden=m!=='page';el('[data-quran-juz]').hidden=m!=='juz';el('[data-quran-hizb-quarter]').hidden=m!=='hizb_quarter';el('[data-quran-rubu]').hidden=m!=='rubu';if(m==='ayah'){form.end_verse.value=form.start_verse.value||1}};
const qariSource=form?.querySelector('[data-qari-source]');const showQari=()=>{const option=qariSource?.selectedOptions?.[0];if(el('[data-qari-description]'))el('[data-qari-description]').textContent=option?.dataset.description||option?.dataset.role||''};qariSource?.addEventListener('change',showQari);showQari();mode?.addEventListener('change',showMode);form?.start_verse.addEventListener('input',()=>{if(mode.value==='ayah')form.end_verse.value=form.start_verse.value});showMode();
const repeatSelect=form?.querySelector('[data-repeat-select]');const repeatValues=['1','3','5','10','20','0'];const updateRepeat=()=>{const value=repeatSelect?.value||'10';if(el('[data-repeat-label]'))el('[data-repeat-label]').textContent=value==='0'?'∞':value+'×'};document.querySelector('[data-repeat-minus]')?.addEventListener('click',()=>{let i=Math.max(0,repeatValues.indexOf(repeatSelect.value)-1);repeatSelect.value=repeatValues[i];updateRepeat()});document.querySelector('[data-repeat-plus]')?.addEventListener('click',()=>{let current=repeatValues.indexOf(repeatSelect.value);let i=Math.min(repeatValues.length-1,current+1);repeatSelect.value=repeatValues[i];updateRepeat()});repeatSelect?.addEventListener('change',updateRepeat);updateRepeat();
const requestPlaylist=async params=>{const response=await fetch(`{{ route('quran-practice.playlist') }}?${new URLSearchParams(params)}`,{headers:{Accept:'application/json'}});const json=await response.json();if(!response.ok)throw new Error(Object.values(json.errors||{}).flat().join(' ')||json.message||'Latihan belum dapat dimuat.');return json};
const load=async params=>{try{stop(false);const payload=await requestPlaylist(params);player.payload=payload;player.index=0;player.itemRepeat=0;player.cycle=0;player.segmentEnding=false;el('[data-player-empty]').hidden=true;el('[data-player-ready]').hidden=false;el('[data-player-title]').textContent=payload.title;el('[data-player-source]').textContent=payload.source.reciter;el('[data-player-summary]').textContent=`${payload.summary.ayah_count} ayat · ${payload.summary.surah_count} surah`;el('[data-player-repeat]').textContent=`${payload.settings.repeat_count===0?'∞':payload.settings.repeat_count+'×'} ${payload.settings.repeat_scope==='each_item'?'per ayat':'seluruh bagian'}`;audio.playbackRate=payload.settings.playback_rate;renderCurrent();await startSession();el('[data-player-toggle]').textContent='▶';el('#quran-player-card')?.scrollIntoView({behavior:'smooth',block:'center'});}catch(error){alert(error.message)}};
form?.addEventListener('submit',e=>{e.preventDefault();load(Object.fromEntries(new FormData(form).entries()))});document.querySelectorAll('[data-load-preset]').forEach(btn=>btn.addEventListener('click',()=>load({preset_id:btn.dataset.loadPreset,source_id:form.source_id.value})));document.querySelectorAll('[data-load-target]').forEach(btn=>btn.addEventListener('click',()=>load({target_id:btn.dataset.loadTarget,source_id:form.source_id.value})));
const item=()=>player.payload?.items[player.index];const renderCurrent=()=>{const current=item();if(!current)return;el('[data-player-current]').textContent=current.label;el('[data-player-progress]').textContent=`Ayat ${player.index+1} dari ${player.payload.items.length}`;el('[data-counter-ayah]').textContent=`${player.index+1}/${player.payload.items.length}`;const repeat=player.payload.settings.repeat_count;el('[data-counter-item]').textContent=`Ulangan ${player.itemRepeat+1}/${repeat===0?'∞':repeat}`;el('[data-player-bar]').style.width=`${((player.index+1)/player.payload.items.length)*100}%`;const page=current.page_number;el('[data-player-page]').hidden=!page;if(page)el('[data-player-page]').textContent=`Halaman ${page}`;const fig=el('[data-page-preview]');if(current.page_image_url){fig.hidden=false;el('[data-page-image]').src=current.page_image_url}else fig.hidden=true};
const playCurrent=()=>{const current=item();if(!current)return;player.segmentEnding=false;const start=Number(current.start_seconds||0);const begin=()=>{audio.playbackRate=player.payload.settings.playback_rate;audio.currentTime=start;audio.play().then(()=>{player.playing=true;el('[data-player-toggle]').textContent='❚❚'}).catch(()=>{el('[data-player-toggle]').textContent='▶'})};if(audio.src!==current.audio_url){audio.src=current.audio_url;audio.addEventListener('loadedmetadata',begin,{once:true});audio.load()}else begin();renderCurrent()};const afterGap=fn=>setTimeout(fn,Math.max(0,player.payload.settings.gap_seconds)*1000);
const segmentFinished=()=>{if(player.segmentEnding)return;player.segmentEnding=true;audio.pause();const repeat=player.payload.settings.repeat_count;const each=player.payload.settings.repeat_scope==='each_item';if(each&&(repeat===0||player.itemRepeat+1<repeat)){player.itemRepeat++;renderCurrent();return afterGap(playCurrent)}player.itemRepeat=0;if(player.index+1<player.payload.items.length){player.index++;renderCurrent();return afterGap(playCurrent)}if(!each&&(repeat===0||player.cycle+1<repeat)){player.cycle++;player.index=0;renderCurrent();return afterGap(playCurrent)}complete('completed');player.playing=false;el('[data-player-toggle]').textContent='↻'};audio?.addEventListener('timeupdate',()=>{const current=item();if(current&&audio.currentTime>=Number(current.end_seconds)-.05)segmentFinished()});audio?.addEventListener('ended',segmentFinished);
el('[data-player-toggle]')?.addEventListener('click',()=>{if(!player.payload)return;if(player.playing){audio.pause();player.playing=false;el('[data-player-toggle]').textContent='▶'}else playCurrent()});el('[data-player-prev]')?.addEventListener('click',()=>{if(!player.payload)return;audio.pause();player.index=Math.max(0,player.index-1);player.itemRepeat=0;renderCurrent();playCurrent()});el('[data-player-next]')?.addEventListener('click',()=>{if(!player.payload)return;audio.pause();player.index=Math.min(player.payload.items.length-1,player.index+1);player.itemRepeat=0;renderCurrent();playCurrent()});el('[data-player-stop]')?.addEventListener('click',()=>stop(true));
const startSession=async()=>{player.startedAt=Date.now();const context=player.payload.context||{};const response=await fetch(`{{ route('quran-practice.sessions.start') }}`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},body:JSON.stringify({student_id:context.student_id||null,preset_id:context.preset_id||null,mode:player.payload.mode,selection:{title:player.payload.title,source:player.payload.source,summary:player.payload.summary,context},repeat_target:player.payload.settings.repeat_count})});if(response.ok)player.sessionId=(await response.json()).id;clearInterval(player.timer);player.timer=setInterval(()=>{if(player.startedAt)el('[data-counter-time]').textContent=formatTime(Math.floor((Date.now()-player.startedAt)/1000))},1000)};const complete=async status=>{if(!player.sessionId)return;const duration=player.startedAt?Math.floor((Date.now()-player.startedAt)/1000):0;fetch(`{{ url('/latihan-quran/sesi') }}/${player.sessionId}/selesai`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},body:JSON.stringify({repeat_completed:player.itemRepeat,duration_seconds:duration,status})});player.sessionId=null;clearInterval(player.timer)};function stop(save=true){audio?.pause();if(audio)audio.removeAttribute('src');audio?.load();if(save&&player.payload)complete('stopped');player.playing=false;player.segmentEnding=false;el('[data-player-toggle]')&&(el('[data-player-toggle]').textContent='▶')}const formatTime=s=>`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
const initialParams=new URLSearchParams(location.search);const initialTarget=initialParams.get('target');if(initialTarget){load({target_id:initialTarget,source_id:form.source_id.value})}else if(initialParams.get('surah_id')){const sourceId=initialParams.get('audio_source_id')||form.source_id.value;form.source_id.value=sourceId;form.mode.value='range';form.surah_id.value=initialParams.get('surah_id');form.start_verse.value=initialParams.get('start_verse')||1;form.end_verse.value=initialParams.get('end_verse')||form.start_verse.value;form.repeat_count.value=initialParams.get('repeat_count')||10;form.repeat_scope.value=(initialParams.get('repeat_mode')==='whole_selection'?'whole_selection':'each_item');showMode();updateRepeat();load({source_id:sourceId,mode:'range',surah_id:form.surah_id.value,start_verse:form.start_verse.value,end_verse:form.end_verse.value,repeat_count:form.repeat_count.value,repeat_scope:form.repeat_scope.value})}
});
</script>
@endsection
