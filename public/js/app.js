/* @phase 5.3 Mobile, Offline & Global — PWA install/update hook */
document.addEventListener('DOMContentLoaded',()=>{
  const sidebar=document.getElementById('sidebar');
  const backdrop=document.querySelector('.sidebar-backdrop');
  const setSidebar=open=>{sidebar?.classList.toggle('open',open);backdrop?.classList.toggle('open',open);document.body.classList.toggle('sidebar-is-open',open)};
  document.querySelectorAll('[data-sidebar-toggle]').forEach(el=>el.addEventListener('click',()=>setSidebar(!sidebar?.classList.contains('open'))));
  sidebar?.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>{if(innerWidth<=780)setSidebar(false)}));
  document.addEventListener('keydown',event=>{if(event.key==='Escape')setSidebar(false)});
  document.querySelector('[data-mark-all-present]')?.addEventListener('click',()=>document.querySelectorAll('[data-attendance-status]').forEach(select=>select.value='present'));

  document.querySelectorAll('form').forEach(form=>{
    const switcher=form.querySelector('[data-target-switch]');
    if(switcher){
      const update=()=>{
        const type=switcher.value;
        form.querySelectorAll('[data-target-class]').forEach(el=>el.hidden=type!=='class');
        form.querySelectorAll('[data-target-group]').forEach(el=>el.hidden=type!=='group');
        form.querySelectorAll('[data-target-level]').forEach(el=>el.hidden=type!=='level');
        const final=form.querySelector('[data-target-id-final]');
        if(final){
          const active=type==='class'?form.querySelector('[data-target-id-class]'):type==='group'?form.querySelector('[data-target-id-group]'):type==='level'?form.querySelector('[data-target-id-level]'):null;
          final.value=active?.value||'';
        }
      };
      switcher.addEventListener('change',update);
      form.querySelectorAll('[data-target-id-class],[data-target-id-group],[data-target-id-level]').forEach(el=>el.addEventListener('change',update));
      update();
    }
    form.addEventListener('submit',event=>{setTimeout(()=>{if(event.defaultPrevented)return;const submit=form.querySelector('button[type="submit"],button:not([type])');if(submit&&!submit.dataset.keepEnabled){submit.disabled=true;submit.dataset.originalText=submit.textContent;submit.textContent='Menyimpan…'}},0)});
  });

  document.querySelectorAll('[data-copy-button]').forEach(button=>button.addEventListener('click',async()=>{
    const input=button.closest('.activation-link-box')?.querySelector('[data-copy-source]');
    if(!input)return;
    try{await navigator.clipboard.writeText(input.value)}catch(_){input.select();document.execCommand('copy')}
    button.textContent='Tersalin';setTimeout(()=>button.textContent='Salin tautan',1800);
  }));

  let connectionBanner=null;
  const showConnection=()=>{
    if(navigator.onLine){connectionBanner?.remove();connectionBanner=null;return}
    if(!connectionBanner){connectionBanner=document.createElement('div');connectionBanner.className='connection-banner';connectionBanner.textContent='Koneksi terputus. Data privat tidak disimpan ke cache.';document.body.appendChild(connectionBanner)}
  };
  window.addEventListener('online',showConnection);window.addEventListener('offline',showConnection);showConnection();

  let installPrompt=null;const installButtons=[...document.querySelectorAll('[data-pwa-install]')];
  window.addEventListener('beforeinstallprompt',event=>{event.preventDefault();installPrompt=event;installButtons.forEach(btn=>btn.hidden=false)});
  installButtons.forEach(btn=>btn.addEventListener('click',async()=>{if(!installPrompt)return;installPrompt.prompt();await installPrompt.userChoice;installPrompt=null;installButtons.forEach(item=>item.hidden=true)}));
  window.addEventListener('appinstalled',()=>installButtons.forEach(btn=>btn.hidden=true));
  if('serviceWorker'in navigator){navigator.serviceWorker.register('/service-worker.js?v=530').catch(()=>{})}
});
