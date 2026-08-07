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
      const update=()=>{const isClass=switcher.value==='class';form.querySelectorAll('[data-target-class]').forEach(el=>el.hidden=!isClass);form.querySelectorAll('[data-target-group]').forEach(el=>el.hidden=isClass);const final=form.querySelector('[data-target-id-final]');if(final){const active=isClass?form.querySelector('[data-target-id-class]'):form.querySelector('[data-target-id-group]');final.value=active?.value||''}};
      switcher.addEventListener('change',update);form.querySelectorAll('[data-target-id-class],[data-target-id-group]').forEach(el=>el.addEventListener('change',update));update();
    }
    form.addEventListener('submit',event=>{setTimeout(()=>{if(event.defaultPrevented)return;const submit=form.querySelector('button[type="submit"],button:not([type])');if(submit&&!submit.dataset.keepEnabled){submit.disabled=true;submit.dataset.originalText=submit.textContent;submit.textContent='Menyimpan…'}},0)});
  });
  let installPrompt=null;const installButtons=[...document.querySelectorAll('[data-pwa-install]')];
  window.addEventListener('beforeinstallprompt',event=>{event.preventDefault();installPrompt=event;installButtons.forEach(btn=>btn.hidden=false)});
  installButtons.forEach(btn=>btn.addEventListener('click',async()=>{if(!installPrompt)return;installPrompt.prompt();await installPrompt.userChoice;installPrompt=null;installButtons.forEach(item=>item.hidden=true)}));
  window.addEventListener('appinstalled',()=>installButtons.forEach(btn=>btn.hidden=true));
  if('serviceWorker'in navigator){navigator.serviceWorker.register('/service-worker.js?v=201').catch(()=>{})}
});
