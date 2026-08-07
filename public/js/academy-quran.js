window.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-academy-quran]');
  if (!root) return;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const form = document.getElementById('academy-quran-builder');
  const audio = root.querySelector('[data-quran-audio]');
  const playlistUrl = root.dataset.playlistUrl;
  const sessionUrl = root.dataset.sessionUrl;
  const completeTemplate = root.dataset.sessionCompleteTemplate;
  const player = { payload:null, index:0, itemRepeat:0, cycle:0, playing:false, startedAt:null, sessionId:null, timer:null, segmentEnding:false };
  const $ = (s) => root.querySelector(s);

  const showMode = () => {
    const mode = form?.mode?.value || 'range';
    $('[data-quran-surah]').hidden = ['page','rubu'].includes(mode);
    $('[data-quran-verses]').hidden = ['surah','page','rubu'].includes(mode);
    $('[data-quran-page]').hidden = mode !== 'page';
    $('[data-quran-rubu]').hidden = mode !== 'rubu';
    if (mode === 'ayah') form.end_verse.value = form.start_verse.value || 1;
  };
  form?.mode?.addEventListener('change', showMode);
  form?.start_verse?.addEventListener('input', () => { if (form.mode.value === 'ayah') form.end_verse.value = form.start_verse.value; });
  showMode();

  const requestPlaylist = async (params) => {
    const response = await fetch(`${playlistUrl}?${new URLSearchParams(params)}`, { headers:{Accept:'application/json'} });
    const json = await response.json();
    if (!response.ok) throw new Error(Object.values(json.errors || {}).flat().join(' ') || json.message || 'Latihan belum dapat dimuat.');
    return json;
  };

  const formatTime = (s) => `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
  const item = () => player.payload?.items[player.index];
  const render = () => {
    const current = item(); if (!current) return;
    $('[data-player-current]').textContent = current.label;
    $('[data-player-progress]').textContent = `Ayat ${player.index+1} dari ${player.payload.items.length}`;
    $('[data-counter-ayah]').textContent = `${player.index+1}/${player.payload.items.length}`;
    const repeat = player.payload.settings.repeat_count;
    $('[data-counter-item]').textContent = `Ulangan ${player.itemRepeat+1}/${repeat===0?'∞':repeat}`;
    $('[data-player-bar]').style.width = `${((player.index+1)/player.payload.items.length)*100}%`;
  };
  const afterGap = (fn) => setTimeout(fn, Math.max(0, player.payload.settings.gap_seconds) * 1000);

  const playCurrent = () => {
    const current = item(); if (!current) return;
    player.segmentEnding = false;
    const start = Number(current.start_seconds || 0);
    const begin = () => {
      audio.playbackRate = player.payload.settings.playback_rate;
      audio.currentTime = start;
      audio.play().then(() => { player.playing = true; $('[data-player-toggle]').textContent='❚❚'; }).catch(() => { $('[data-player-toggle]').textContent='▶'; });
    };
    if (audio.src !== current.audio_url) {
      audio.src = current.audio_url;
      audio.addEventListener('loadedmetadata', begin, {once:true});
      audio.load();
    } else begin();
    render();
  };

  const complete = async (status) => {
    if (!player.sessionId) return;
    const duration = player.startedAt ? Math.floor((Date.now()-player.startedAt)/1000) : 0;
    fetch(completeTemplate.replace('__SESSION__', String(player.sessionId)), { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'}, body:JSON.stringify({repeat_completed:player.itemRepeat,duration_seconds:duration,status}) });
    player.sessionId = null; clearInterval(player.timer);
  };

  const segmentFinished = () => {
    if (player.segmentEnding) return;
    player.segmentEnding = true; audio.pause();
    const repeat = player.payload.settings.repeat_count;
    const each = player.payload.settings.repeat_scope === 'each_item';
    if (each && (repeat===0 || player.itemRepeat+1<repeat)) { player.itemRepeat++; render(); return afterGap(playCurrent); }
    player.itemRepeat = 0;
    if (player.index+1<player.payload.items.length) { player.index++; render(); return afterGap(playCurrent); }
    if (!each && (repeat===0 || player.cycle+1<repeat)) { player.cycle++; player.index=0; render(); return afterGap(playCurrent); }
    complete('completed'); player.playing=false; $('[data-player-toggle]').textContent='↻';
  };

  audio?.addEventListener('timeupdate', () => { const current=item(); if(current && audio.currentTime >= Number(current.end_seconds)-0.05) segmentFinished(); });
  audio?.addEventListener('ended', segmentFinished);

  const startSession = async () => {
    player.startedAt = Date.now();
    const context = player.payload.context || {};
    const response = await fetch(sessionUrl, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},body:JSON.stringify({student_id:null,preset_id:context.preset_id||null,mode:player.payload.mode,selection:{title:player.payload.title,source:player.payload.source,summary:player.payload.summary,context},repeat_target:player.payload.settings.repeat_count})});
    if (response.ok) player.sessionId = (await response.json()).id;
    clearInterval(player.timer);
    player.timer = setInterval(() => { if(player.startedAt) $('[data-counter-time]').textContent = formatTime(Math.floor((Date.now()-player.startedAt)/1000)); },1000);
  };

  const stop = (save=true) => {
    audio?.pause(); if(audio){ audio.removeAttribute('src'); audio.load(); }
    if(save && player.payload) complete('stopped');
    player.playing=false; player.segmentEnding=false; $('[data-player-toggle]') && ($('[data-player-toggle]').textContent='▶');
  };

  const load = async (params) => {
    try {
      stop(true);
      const payload = await requestPlaylist(params);
      player.payload=payload; player.index=0; player.itemRepeat=0; player.cycle=0; player.segmentEnding=false;
      $('[data-player-empty]').hidden=true; $('[data-player-ready]').hidden=false;
      $('[data-player-title]').textContent=payload.title;
      $('[data-player-source]').textContent=payload.source.reciter;
      $('[data-player-summary]').textContent=`${payload.summary.ayah_count} ayat · ${payload.summary.surah_count} surah`;
      $('[data-player-repeat]').textContent=`${payload.settings.repeat_count===0?'∞':payload.settings.repeat_count+'×'} ${payload.settings.repeat_scope==='each_item'?'per ayat':'seluruh bagian'}`;
      render(); await startSession();
      document.getElementById('academy-quran-player')?.scrollIntoView({behavior:'smooth',block:'center'});
    } catch (error) { window.alert(error.message); }
  };

  form?.addEventListener('submit', (e) => { e.preventDefault(); load(Object.fromEntries(new FormData(form).entries())); });
  root.querySelectorAll('[data-load-preset]').forEach(btn => btn.addEventListener('click', () => load({preset_id:btn.dataset.loadPreset,source_id:form.source_id.value})));
  $('[data-player-toggle]')?.addEventListener('click', () => { if(!player.payload)return; if(player.playing){audio.pause();player.playing=false;$('[data-player-toggle]').textContent='▶';}else playCurrent(); });
  $('[data-player-prev]')?.addEventListener('click', () => { if(!player.payload)return; audio.pause(); player.index=Math.max(0,player.index-1);player.itemRepeat=0;render();playCurrent(); });
  $('[data-player-next]')?.addEventListener('click', () => { if(!player.payload)return; audio.pause(); player.index=Math.min(player.payload.items.length-1,player.index+1);player.itemRepeat=0;render();playCurrent(); });
  $('[data-player-stop]')?.addEventListener('click', () => stop(true));

  const initial = new URLSearchParams(location.search);
  if (initial.get('preset')) load({preset_id:initial.get('preset'),source_id:form.source_id.value});
});
