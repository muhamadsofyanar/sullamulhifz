document.addEventListener('DOMContentLoaded',()=>{
  const sidebar=document.getElementById('sidebar');
  document.querySelectorAll('[data-sidebar-toggle]').forEach(el=>el.addEventListener('click',()=>{sidebar?.classList.toggle('open');document.querySelector('.sidebar-backdrop')?.classList.toggle('open')}));
  document.querySelector('[data-mark-all-present]')?.addEventListener('click',()=>document.querySelectorAll('[data-attendance-status]').forEach(select=>select.value='present'));
  document.querySelectorAll('form').forEach(form=>{
    const switcher=form.querySelector('[data-target-switch]');
    if(switcher){
      const update=()=>{const isClass=switcher.value==='class';form.querySelectorAll('[data-target-class]').forEach(el=>el.hidden=!isClass);form.querySelectorAll('[data-target-group]').forEach(el=>el.hidden=isClass);const final=form.querySelector('[data-target-id-final]');if(final){const active=isClass?form.querySelector('[data-target-id-class]'):form.querySelector('[data-target-id-group]');final.value=active?.value||''}};
      switcher.addEventListener('change',update);form.querySelectorAll('[data-target-id-class],[data-target-id-group]').forEach(el=>el.addEventListener('change',update));update();
    }
    form.addEventListener('submit',event=>{setTimeout(()=>{if(event.defaultPrevented)return;const submit=form.querySelector('button[type="submit"],button:not([type])');if(submit&&!submit.dataset.keepEnabled){submit.disabled=true;submit.dataset.originalText=submit.textContent;submit.textContent='Menyimpan…'}},0)});
  });
  if('serviceWorker'in navigator){navigator.serviceWorker.register('/service-worker.js').catch(()=>{})}
});
