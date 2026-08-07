document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('[data-academy-video-stage]').forEach(stage=>{
    const button=stage.querySelector('[data-academy-video-fullscreen]');
    const label=stage.querySelector('[data-fullscreen-label]');
    if(!button)return;
    const sync=()=>{
      const active=document.fullscreenElement===stage;
      button.setAttribute('aria-pressed',active?'true':'false');
      if(label)label.textContent=active?'Keluar layar penuh':'Layar penuh';
    };
    button.addEventListener('click',async()=>{
      try{
        if(document.fullscreenElement===stage){await document.exitFullscreen();}
        else if(stage.requestFullscreen){await stage.requestFullscreen();}
      }catch(_){/* Native YouTube fullscreen remains available as fallback. */}
      sync();
    });
    document.addEventListener('fullscreenchange',sync);
  });
});
