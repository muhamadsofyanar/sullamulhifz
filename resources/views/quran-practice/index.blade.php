@extends('layouts.app',['pageTitle'=>'Latihan Al-Qur’an'])
@section('content')
<div class="page-head">
    <div><span class="eyebrow">MURATTAL & PENGULANGAN</span><h1>Latihan Al-Qur’an</h1><p>Pilih ayat, rentang, surah, halaman, rubu’, atau target santri. Atur ulang sesuai kebutuhan.</p></div>
    @if(auth()->user()->hasAnyRole(['superadmin','institution_admin','head']))<a class="button secondary" href="{{ route('admin.quran-library.index') }}">Kelola pustaka</a>@endif
</div>

@if($sources->isEmpty())
<div class="alert danger">Sumber audio belum tersedia. Admin perlu membuka Pustaka Qur’an dan menjalankan sinkronisasi.</div>
@endif

<div class="stats-grid four quran-readiness">
    <div class="stat-card"><span>Kelengkapan audio</span><strong>{{ $timingCount }}/564</strong></div>
    <div class="stat-card"><span>Preset latihan</span><strong>{{ $presets->count() }}</strong></div>
    <div class="stat-card"><span>Target tersedia</span><strong>{{ $targets->count() }}</strong></div>
    <div class="stat-card"><span>Sumber murattal</span><strong>{{ $sources->count() }}</strong></div>
</div>

@if($featuredPresets->isNotEmpty())
<section class="card quran-featured"><div class="section-head"><div><h2>Latihan siap pakai</h2><p class="hint">Contoh sudah diisi untuk ayat, rentang, surah, dan halaman.</p></div></div>
<div class="quran-preset-grid">
@foreach($featuredPresets as $preset)
<button type="button" class="quran-preset-card" data-load-preset="{{ $preset->id }}">
    <span class="badge">{{ strtoupper($preset->mode) }}</span>
    <strong>{{ $preset->title }}</strong>
    <small>{{ $preset->description }}</small>
    <em>{{ $preset->repeat_count === 0 ? 'Tanpa batas' : $preset->repeat_count.'×' }} · {{ $preset->repeat_scope === 'each_item' ? 'setiap ayat' : 'seluruh pilihan' }}</em>
</button>
@endforeach
</div></section>
@endif

<div class="grid quran-builder-layout">
<section class="card quran-builder-card">
    <h2>Buat latihan sendiri</h2>
    <form id="quran-builder" class="stack compact">
        <label>Sumber bacaan<select name="source_id" required>@foreach($sources as $source)<option value="{{ $source->id }}" @selected($defaultSource?->id===$source->id)>{{ $source->reciter_name }} — {{ $source->rewaya }}</option>@endforeach</select></label>
        <div class="form-grid">
            <label>Mode latihan<select name="mode" data-quran-mode><option value="ayah">Satu ayat</option><option value="range" selected>Rentang ayat</option><option value="surah">Satu surah</option><option value="page">Satu halaman</option><option value="rubu">Satu rubu’</option></select></label>
            <label data-quran-surah>Surah<select name="surah_id">@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }} ({{ $surah->verse_count }} ayat)</option>@endforeach</select></label>
        </div>
        <div class="form-grid" data-quran-verses><label>Ayat awal<input type="number" name="start_verse" min="1" value="1"></label><label>Ayat akhir<input type="number" name="end_verse" min="1" value="5"></label></div>
        <label data-quran-page hidden>Halaman Mushaf<select name="page_number"><option value="">Pilih halaman</option>@foreach($pages as $page)<option value="{{ $page }}">Halaman {{ $page }}</option>@endforeach</select></label>
        <label data-quran-rubu hidden>Rubu’ Juz 30<select name="rubu_id"><option value="">Pilih rubu’</option>@foreach($rubus as $rubu)<option value="{{ $rubu->id }}">{{ $rubu->name }}</option>@endforeach</select></label>
        <div class="form-grid">
            <label>Jumlah pengulangan<select name="repeat_count"><option value="1">1×</option><option value="3">3×</option><option value="5">5×</option><option value="10" selected>10×</option><option value="20">20×</option><option value="0">Tanpa batas</option></select></label>
            <label>Pola ulang<select name="repeat_scope"><option value="each_item" selected>Setiap ayat</option><option value="whole_selection">Seluruh pilihan</option></select></label>
        </div>
        <div class="form-grid">
            <label>Jeda<select name="gap_seconds"><option value="0">Tanpa jeda</option><option value="1">1 detik</option><option value="2" selected>2 detik</option><option value="5">5 detik</option><option value="10">10 detik</option></select></label>
            <label>Kecepatan<select name="playback_rate"><option value="0.75">0,75×</option><option value="0.90" selected>0,90×</option><option value="1">1× normal</option><option value="1.15">1,15×</option><option value="1.25">1,25×</option></select></label>
        </div>
        <button type="submit" class="button primary wide">Siapkan latihan</button>
    </form>
</section>

<section class="card quran-player-card" id="quran-player-card">
    <div class="quran-player-empty" data-player-empty><x-icon name="audio" size="48"/><h2>Pilih latihan</h2><p>Gunakan contoh siap pakai, target santri, atau buat pilihan sendiri.</p></div>
    <div data-player-ready hidden>
        <div class="section-head"><div><span class="eyebrow" data-player-source>SUMBER AUDIO</span><h2 data-player-title>Latihan Al-Qur’an</h2><p class="hint" data-player-summary></p></div><span class="badge" data-player-repeat></span></div>
        <div class="quran-now-playing"><div><small>Sedang diputar</small><strong data-player-current>—</strong><span data-player-progress>0/0</span></div><div class="quran-page-chip" data-player-page hidden>Halaman —</div></div>
        <audio id="quran-audio" preload="metadata"></audio>
        <div class="quran-transport">
            <button type="button" class="button secondary" data-player-prev>← Sebelumnya</button>
            <button type="button" class="button primary quran-play-button" data-player-toggle>▶ Mulai</button>
            <button type="button" class="button secondary" data-player-next>Berikutnya →</button>
        </div>
        <div class="quran-progress-track"><span data-player-bar></span></div>
        <div class="quran-counter-grid"><div><small>Ayat</small><strong data-counter-ayah>0/0</strong></div><div><small>Ulangan ayat</small><strong data-counter-item>0/0</strong></div><div><small>Putaran</small><strong data-counter-cycle>0/0</strong></div><div><small>Durasi</small><strong data-counter-time>00:00</strong></div></div>
        <figure class="quran-page-preview" data-page-preview hidden><img alt="Halaman Mushaf yang sedang diputar" data-page-image><figcaption>Gambar halaman bersumber dari layanan murattal dan digunakan sebagai bantuan orientasi halaman.</figcaption></figure>
        <div class="quran-player-actions"><button type="button" class="button ghost" data-player-stop>Hentikan & simpan</button><span class="hint">Bacaan murattal membantu latihan; talaqqi dan koreksi guru tetap utama.</span></div>
    </div>
</section>
</div>

@if($targets->isNotEmpty())
<section class="card"><div class="section-head"><div><h2>Target hafalan yang bisa langsung dilatih</h2><p class="hint">Target guru dibuka dengan pola awal 10× per ayat.</p></div></div>
<div class="cards-list">@foreach($targets as $target)<button type="button" class="item-card quran-target-button" data-load-target="{{ $target->id }}"><div><strong>{{ $target->student->full_name }} · {{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</strong><small>{{ $target->rubu?->name ?? 'Rubu belum ditentukan' }} · {{ $target->marhalah?->name ?? 'Beban mengikuti arahan guru' }}</small></div><span>Putar →</span></button>@endforeach</div>
</section>
@endif

<section class="card"><div class="section-head"><div><h2>Semua preset Juz 30</h2><p class="hint">Termasuk 37 surah, delapan rubu’, dan halaman Mushaf yang berhasil disinkronkan.</p></div></div>
<div class="quran-preset-list">@foreach($presets as $preset)<button type="button" data-load-preset="{{ $preset->id }}"><span class="badge">{{ $preset->mode }}</span><strong>{{ $preset->title }}</strong><small>{{ $preset->repeat_count === 0 ? '∞' : $preset->repeat_count.'×' }}</small></button>@endforeach</div></section>

<section class="card"><div class="section-head"><div><h2>Video bacaan terpilih</h2><p class="hint">Video hanya muncul setelah dikurasi dan diterbitkan admin.</p></div></div>
@if($videos->isEmpty())<p class="empty">Belum ada video yang diterbitkan. Audio murattal tetap dapat digunakan.</p>@else<div class="quran-video-grid">@foreach($videos as $video)<article><h3>{{ $video->title }}</h3><p>{{ $video->surah?->name_latin }} {{ $video->start_verse ? $video->start_verse.'–'.$video->end_verse : '' }}</p>@if($video->source_type==='youtube' && $video->youtubeId())<div class="video-frame"><iframe src="https://www.youtube.com/embed/{{ $video->youtubeId() }}?playsinline=1&rel=0" title="{{ $video->title }}" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>@else<video controls preload="metadata" src="{{ $video->video_url }}"></video>@endif<small>Saran: ulangi {{ $video->default_repeat }}×. {{ $video->notes }}</small></article>@endforeach</div>@endif
</section>

<script>
window.addEventListener('DOMContentLoaded',()=>{
const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
const form=document.getElementById('quran-builder');
const audio=document.getElementById('quran-audio');
const mode=form?.querySelector('[data-quran-mode]');
const player={payload:null,index:0,itemRepeat:0,cycle:0,playing:false,startedAt:null,sessionId:null,timer:null,segmentEnding:false};
const el=s=>document.querySelector(s);
const showMode=()=>{const m=mode.value;el('[data-quran-surah]').hidden=['page','rubu'].includes(m);el('[data-quran-verses]').hidden=['surah','page','rubu'].includes(m);el('[data-quran-page]').hidden=m!=='page';el('[data-quran-rubu]').hidden=m!=='rubu';if(m==='ayah'){form.start_verse.value=form.start_verse.value||1;form.end_verse.value=form.start_verse.value;}};
mode?.addEventListener('change',showMode);form?.start_verse.addEventListener('input',()=>{if(mode.value==='ayah')form.end_verse.value=form.start_verse.value});showMode();
const requestPlaylist=async params=>{const query=new URLSearchParams(params);const response=await fetch(`{{ route('quran-practice.playlist') }}?${query}`,{headers:{Accept:'application/json'}});const json=await response.json();if(!response.ok)throw new Error(Object.values(json.errors||{}).flat().join(' ')||json.message||'Playlist belum dapat dimuat.');return json};
const load=async params=>{try{stop(false);const payload=await requestPlaylist(params);player.payload=payload;player.index=0;player.itemRepeat=0;player.cycle=0;player.segmentEnding=false;el('[data-player-empty]').hidden=true;el('[data-player-ready]').hidden=false;el('[data-player-title]').textContent=payload.title;el('[data-player-source]').textContent=`${payload.source.reciter} · ${payload.source.attribution}`;el('[data-player-summary]').textContent=`${payload.summary.ayah_count} ayat · ${payload.summary.surah_count} surah · ${payload.summary.page_count} halaman`;el('[data-player-repeat]').textContent=`${payload.settings.repeat_count===0?'∞':payload.settings.repeat_count+'×'} ${payload.settings.repeat_scope==='each_item'?'per ayat':'seluruh pilihan'}`;audio.playbackRate=payload.settings.playback_rate;renderCurrent();await startSession();el('[data-player-toggle]').textContent='▶ Mulai';el('#quran-player-card')?.scrollIntoView({behavior:'smooth',block:'center'});}catch(error){alert(error.message)}};
form?.addEventListener('submit',e=>{e.preventDefault();load(Object.fromEntries(new FormData(form).entries()))});
document.querySelectorAll('[data-load-preset]').forEach(btn=>btn.addEventListener('click',()=>load({preset_id:btn.dataset.loadPreset})));
document.querySelectorAll('[data-load-target]').forEach(btn=>btn.addEventListener('click',()=>load({target_id:btn.dataset.loadTarget,source_id:form.source_id.value})));
const item=()=>player.payload?.items[player.index];
const renderCurrent=()=>{const current=item();if(!current)return;el('[data-player-current]').textContent=current.label;el('[data-player-progress]').textContent=`${player.index+1}/${player.payload.items.length}`;el('[data-counter-ayah]').textContent=`${player.index+1}/${player.payload.items.length}`;const repeat=player.payload.settings.repeat_count;el('[data-counter-item]').textContent=`${player.itemRepeat+1}/${repeat===0?'∞':repeat}`;el('[data-counter-cycle]').textContent=`${player.cycle+1}/${repeat===0?'∞':repeat}`;el('[data-player-bar]').style.width=`${((player.index+1)/player.payload.items.length)*100}%`;const page=current.page_number;el('[data-player-page]').hidden=!page;if(page)el('[data-player-page]').textContent=`Halaman ${page}`;const fig=el('[data-page-preview]');if(current.page_image_url){fig.hidden=false;el('[data-page-image]').src=current.page_image_url}else fig.hidden=true;};
const playCurrent=()=>{const current=item();if(!current)return;player.segmentEnding=false;const start=Number(current.start_seconds||0);const begin=()=>{audio.playbackRate=player.payload.settings.playback_rate;audio.currentTime=start;audio.play().then(()=>{player.playing=true;el('[data-player-toggle]').textContent='❚❚ Jeda'}).catch(()=>{el('[data-player-toggle]').textContent='▶ Mulai'})};if(audio.src!==current.audio_url){audio.src=current.audio_url;audio.addEventListener('loadedmetadata',begin,{once:true});audio.load()}else begin();renderCurrent()};
const afterGap=fn=>setTimeout(fn,Math.max(0,player.payload.settings.gap_seconds)*1000);
const segmentFinished=()=>{if(player.segmentEnding)return;player.segmentEnding=true;audio.pause();const repeat=player.payload.settings.repeat_count;const each=player.payload.settings.repeat_scope==='each_item';if(each&&(repeat===0||player.itemRepeat+1<repeat)){player.itemRepeat++;renderCurrent();return afterGap(playCurrent)}player.itemRepeat=0;if(player.index+1<player.payload.items.length){player.index++;renderCurrent();return afterGap(playCurrent)}if(!each&&(repeat===0||player.cycle+1<repeat)){player.cycle++;player.index=0;renderCurrent();return afterGap(playCurrent)}complete('completed');player.playing=false;el('[data-player-toggle]').textContent='▶ Ulangi';};
audio?.addEventListener('timeupdate',()=>{const current=item();if(current&&audio.currentTime>=Number(current.end_seconds)-.05)segmentFinished()});audio?.addEventListener('ended',segmentFinished);
el('[data-player-toggle]')?.addEventListener('click',()=>{if(!player.payload)return;if(player.playing){audio.pause();player.playing=false;el('[data-player-toggle]').textContent='▶ Lanjut'}else playCurrent()});
el('[data-player-prev]')?.addEventListener('click',()=>{if(!player.payload)return;audio.pause();player.index=Math.max(0,player.index-1);player.itemRepeat=0;renderCurrent();playCurrent()});
el('[data-player-next]')?.addEventListener('click',()=>{if(!player.payload)return;audio.pause();player.index=Math.min(player.payload.items.length-1,player.index+1);player.itemRepeat=0;renderCurrent();playCurrent()});
el('[data-player-stop]')?.addEventListener('click',()=>stop(true));
const startSession=async()=>{player.startedAt=Date.now();const context=player.payload.context||{};const response=await fetch(`{{ route('quran-practice.sessions.start') }}`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},body:JSON.stringify({student_id:context.student_id||null,preset_id:context.preset_id||null,mode:player.payload.mode,selection:{title:player.payload.title,summary:player.payload.summary,context},repeat_target:player.payload.settings.repeat_count})});if(response.ok)player.sessionId=(await response.json()).id;clearInterval(player.timer);player.timer=setInterval(()=>{if(player.startedAt)el('[data-counter-time]').textContent=formatTime(Math.floor((Date.now()-player.startedAt)/1000))},1000)};
const complete=async status=>{if(!player.sessionId)return;const duration=player.startedAt?Math.floor((Date.now()-player.startedAt)/1000):0;fetch(`{{ url('/latihan-quran/sesi') }}/${player.sessionId}/selesai`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},body:JSON.stringify({repeat_completed:player.payload.settings.repeat_scope==='each_item'?player.itemRepeat:player.cycle,duration_seconds:duration,status})});player.sessionId=null;clearInterval(player.timer)};
function stop(save=true){audio?.pause();if(audio)audio.removeAttribute('src');audio?.load();if(save&&player.payload)complete('stopped');player.playing=false;player.segmentEnding=false;el('[data-player-toggle]')&&(el('[data-player-toggle]').textContent='▶ Mulai')}
const formatTime=s=>`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
const initialTarget=new URLSearchParams(location.search).get('target');if(initialTarget)load({target_id:initialTarget,source_id:form.source_id.value});
});
</script>
@endsection
