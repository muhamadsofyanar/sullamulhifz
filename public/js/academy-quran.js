window.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-academy-quran]');
  if (!root) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const form = document.querySelector('#academy-quran-builder form');
  const audio = root.querySelector('[data-quran-audio]');
  const playlistUrl = root.dataset.playlistUrl;
  const sessionUrl = root.dataset.sessionUrl;
  const completeTemplate = root.dataset.sessionCompleteTemplate;
  const ayahBookmarkTemplate = root.dataset.ayahBookmarkTemplate || '';
  const bookmarkedAyahs = new Set(JSON.parse(root.dataset.bookmarkedAyahs || '[]').map(Number));
  const player = { payload:null, index:0, itemRepeat:0, cycle:0, playing:false, startedAt:null, sessionId:null, timer:null, segmentEnding:false };
  const $ = (s) => root.querySelector(s);

  const showMode = () => {
    if (!form) return;
    const mode = form.mode?.value || 'range';
    $('[data-quran-surah]').hidden = ['page','juz','hizb_quarter','rubu'].includes(mode);
    $('[data-quran-verses]').hidden = ['surah','page','juz','hizb_quarter','rubu'].includes(mode);
    $('[data-quran-page]').hidden = mode !== 'page';
    $('[data-quran-juz]').hidden = mode !== 'juz';
    $('[data-quran-hizb-quarter]').hidden = mode !== 'hizb_quarter';
    $('[data-quran-rubu]').hidden = mode !== 'rubu';
    if (mode === 'ayah' && form.end_verse) form.end_verse.value = form.start_verse?.value || 1;
  };
  form?.mode?.addEventListener('change', showMode);
  form?.start_verse?.addEventListener('input', () => {
    if (form.mode.value === 'ayah' && form.end_verse) form.end_verse.value = form.start_verse.value;
  });
  showMode();

  const requestPlaylist = async (params) => {
    const response = await fetch(`${playlistUrl}?${new URLSearchParams(params)}`, { headers:{Accept:'application/json'} });
    const json = await response.json();
    if (!response.ok) throw new Error(Object.values(json.errors || {}).flat().join(' ') || json.message || 'Latihan belum dapat dimuat.');
    return json;
  };

  const formatTime = (s) => `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
  const item = () => player.payload?.items[player.index];
  const afterGap = (fn) => setTimeout(fn, Math.max(0, player.payload?.settings?.gap_seconds || 0) * 1000);

  const renderMushaf = () => {
    const container = $('[data-mushaf-verses]');
    if (!container || !player.payload) return;
    container.textContent = '';

    player.payload.items.forEach((ayah, index) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'academy-mushaf-ayah';
      button.dataset.mushafIndex = String(index);
      button.dir = 'rtl';
      button.setAttribute('aria-label', `${ayah.surah_name || 'Surah'} ayat ${ayah.verse_number}`);

      const text = document.createElement('span');
      text.className = 'academy-mushaf-arabic';
      text.textContent = ayah.arabic_text || '—';
      const marker = document.createElement('b');
      marker.className = 'academy-mushaf-marker';
      marker.textContent = `﴿${ayah.verse_number}﴾`;
      button.append(text, marker);

      button.addEventListener('click', () => {
        audio?.pause();
        player.index = index;
        player.itemRepeat = 0;
        render();
      });
      container.appendChild(button);
    });
  };

  const render = () => {
    const current = item();
    if (!current || !player.payload) return;

    $('[data-player-current]').textContent = current.label;
    $('[data-player-progress]').textContent = `Ayat ${player.index+1} dari ${player.payload.items.length}`;
    $('[data-counter-ayah]').textContent = `${player.index+1}/${player.payload.items.length}`;
    const repeat = player.payload.settings.repeat_count;
    $('[data-counter-item]').textContent = `Ulangan ${player.itemRepeat+1}/${repeat===0?'∞':repeat}`;
    $('[data-player-bar]').style.width = `${((player.index+1)/player.payload.items.length)*100}%`;
    $('[data-mushaf-location]').textContent = `Juz ${current.juz_number || '—'} · Halaman ${current.page_number || '—'} · Rubu‘ ${current.hizb_quarter || '—'}`;
    $('[data-mushaf-surah]').textContent = `${current.surah_name || 'Surah'} · ${current.surah_id}:${current.verse_number}`;

    root.querySelectorAll('[data-mushaf-index]').forEach((node) => {
      const active = Number(node.dataset.mushafIndex) === player.index;
      node.classList.toggle('active', active);
      if (active) node.scrollIntoView({behavior:'smooth', block:'nearest', inline:'nearest'});
    });

    const note = $('[data-audio-sync-note]');
    if (note) note.hidden = Boolean(current.audio_ready);
    const bookmark = $('[data-player-ayah-bookmark]');
    if (bookmark) {
      const saved = bookmarkedAyahs.has(Number(current.global_number));
      bookmark.textContent = saved ? '★ Ayat tersimpan' : '☆ Simpan ayat';
      bookmark.setAttribute('aria-pressed', saved ? 'true' : 'false');
    }
  };

  const playCurrent = () => {
    const current = item();
    if (!current || !player.payload) return;
    if (!current.audio_ready || !current.audio_url) {
      player.playing = false;
      $('[data-player-toggle]').textContent = '▶';
      $('[data-audio-sync-note]').hidden = false;
      return;
    }

    player.segmentEnding = false;
    const start = Number(current.start_seconds || 0);
    const begin = () => {
      audio.playbackRate = player.payload.settings.playback_rate;
      audio.currentTime = start;
      audio.play().then(() => {
        player.playing = true;
        $('[data-player-toggle]').textContent='❚❚';
        const note = $('[data-audio-sync-note]');
        if (note && current.audio_ready) note.hidden = true;
      }).catch(() => {
        player.playing = false;
        $('[data-player-toggle]').textContent='▶';
        const note = $('[data-audio-sync-note]');
        if (note) {
          note.textContent = 'Audio belum dapat diputar oleh browser. Coba tekan Putar lagi atau pilih qari lain.';
          note.hidden = false;
        }
      });
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
    const current = item() || {};
    fetch(completeTemplate.replace('__SESSION__', String(player.sessionId)), {
      method:'PUT',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},
      body:JSON.stringify({
        repeat_completed:player.itemRepeat,
        duration_seconds:duration,
        status,
        last_surah_id:current.surah_id || null,
        last_verse_number:current.verse_number || null,
        last_global_number:current.global_number || null,
        last_juz_number:current.juz_number || null,
        last_page_number:current.page_number || null,
        last_hizb_quarter:current.hizb_quarter || null,
        source_id:player.payload?.source?.id || null,
      })
    });
    player.sessionId = null;
    clearInterval(player.timer);
  };

  const segmentFinished = () => {
    if (player.segmentEnding || !player.payload) return;
    player.segmentEnding = true;
    audio.pause();
    const repeat = player.payload.settings.repeat_count;
    const each = player.payload.settings.repeat_scope === 'each_item';

    if (each && (repeat===0 || player.itemRepeat+1<repeat)) {
      player.itemRepeat++;
      render();
      return afterGap(playCurrent);
    }
    player.itemRepeat = 0;

    if (player.index+1<player.payload.items.length) {
      player.index++;
      render();
      return afterGap(playCurrent);
    }

    if (!each && (repeat===0 || player.cycle+1<repeat)) {
      player.cycle++;
      player.index=0;
      render();
      return afterGap(playCurrent);
    }

    complete('completed');
    player.playing=false;
    $('[data-player-toggle]').textContent='↻';
  };

  audio?.addEventListener('timeupdate', () => {
    const current=item();
    if(current?.audio_ready && audio.currentTime >= Number(current.end_seconds)-0.05) segmentFinished();
  });
  audio?.addEventListener('ended', segmentFinished);
  audio?.addEventListener('error', () => {
    player.playing = false;
    if ($('[data-player-toggle]')) $('[data-player-toggle]').textContent = '▶';
    const note = $('[data-audio-sync-note]');
    if (note) {
      note.textContent = 'Sumber audio gagal dimuat. Pilih qari lain atau minta admin menyinkronkan ulang audio ini.';
      note.hidden = false;
    }
  });

  const startSession = async () => {
    player.startedAt = Date.now();
    const context = player.payload.context || {};
    const response = await fetch(sessionUrl, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},
      body:JSON.stringify({
        student_id:null,
        preset_id:context.preset_id||null,
        mode:player.payload.mode,
        selection:{title:player.payload.title,source:player.payload.source,summary:player.payload.summary,context},
        repeat_target:player.payload.settings.repeat_count
      })
    });
    if (response.ok) player.sessionId = (await response.json()).id;
    clearInterval(player.timer);
    player.timer = setInterval(() => {
      if(player.startedAt) $('[data-counter-time]').textContent = formatTime(Math.floor((Date.now()-player.startedAt)/1000));
    },1000);
  };

  const stop = (save=true) => {
    audio?.pause();
    if(audio){ audio.removeAttribute('src'); audio.load(); }
    if(save && player.payload) complete('stopped');
    player.playing=false;
    player.segmentEnding=false;
    if ($('[data-player-toggle]')) $('[data-player-toggle]').textContent='▶';
  };

  const load = async (params) => {
    try {
      stop(true);
      const payload = await requestPlaylist(params);
      player.payload=payload;
      player.index=0;
      player.itemRepeat=0;
      player.cycle=0;
      player.segmentEnding=false;
      $('[data-player-empty]').hidden=true;
      $('[data-player-ready]').hidden=false;
      document.querySelector('[data-default-mushaf]')?.setAttribute('hidden','hidden');
      $('[data-player-title]').textContent=payload.title;
      $('[data-player-source]').textContent=payload.source.reciter;
      const missing = payload.summary.missing_audio_count || 0;
      $('[data-player-summary]').textContent=`${payload.summary.ayah_count} ayat · ${payload.summary.surah_count} surah · ${payload.summary.juz_count || 0} juz${missing ? ` · ${missing} audio masih sinkron` : ''}`;
      $('[data-player-repeat]').textContent=`${payload.settings.repeat_count===0?'∞':payload.settings.repeat_count+'×'} ${payload.settings.repeat_scope==='each_item'?'per ayat':'seluruh bagian'}`;
      renderMushaf();
      render();
      await startSession();
      document.getElementById('academy-quran-player')?.scrollIntoView({behavior:'smooth',block:'start'});
    } catch (error) {
      window.alert(error.message);
    }
  };

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    load(Object.fromEntries(new FormData(form).entries()));
  });
  root.querySelectorAll('[data-load-preset]').forEach(btn => btn.addEventListener('click', () => load({preset_id:btn.dataset.loadPreset,source_id:form.source_id.value})));
  $('[data-player-toggle]')?.addEventListener('click', () => {
    if(!player.payload)return;
    if(player.playing){
      audio.pause();
      player.playing=false;
      $('[data-player-toggle]').textContent='▶';
    } else playCurrent();
  });
  $('[data-player-prev]')?.addEventListener('click', () => {
    if(!player.payload)return;
    audio.pause();
    player.index=Math.max(0,player.index-1);
    player.itemRepeat=0;
    render();
  });
  $('[data-player-next]')?.addEventListener('click', () => {
    if(!player.payload)return;
    audio.pause();
    player.index=Math.min(player.payload.items.length-1,player.index+1);
    player.itemRepeat=0;
    render();
  });
  $('[data-player-ayah-bookmark]')?.addEventListener('click', async () => {
    const current = item();
    if (!current?.global_number || !ayahBookmarkTemplate) return;
    const response = await fetch(ayahBookmarkTemplate.replace('__AYAH__', String(current.global_number)), {
      method:'POST', headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}
    });
    if (!response.ok) return;
    const result = await response.json();
    if (result.saved) bookmarkedAyahs.add(Number(current.global_number));
    else bookmarkedAyahs.delete(Number(current.global_number));
    render();
  });
  $('[data-player-stop]')?.addEventListener('click', () => stop(true));

  const initial = new URLSearchParams(location.search);
  if (initial.get('preset')) load({preset_id:initial.get('preset'),source_id:form.source_id.value});
  else if (initial.get('surah') && initial.get('ayah')) load({mode:'ayah',surah_id:initial.get('surah'),start_verse:initial.get('ayah'),end_verse:initial.get('ayah'),source_id:form.source_id.value,repeat_count:10,repeat_scope:'each_item',gap_seconds:1,playback_rate:1});
});
