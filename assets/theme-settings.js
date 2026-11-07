(function(){
    'use strict';
    const KEY='sdplink.preferences';
    const LEGACY_KEY='ghostlink.preferences';
    const defaults={theme:'glass',motion:true,density:'comfortable',fontSize:'normal'};
    const allowedThemes=['glass','aurora','midnight','rose'];
    function read(){
        try{
            const current=localStorage.getItem(KEY);
            const legacy=localStorage.getItem(LEGACY_KEY);
            const value=current||legacy||'{}';
            const parsed=JSON.parse(value);
            if(!current&&legacy)localStorage.setItem(KEY,legacy);
            return {...defaults,...parsed};
        }catch(e){return {...defaults}}
    }
    let prefs=read();
    function apply(){
        document.body.dataset.theme=allowedThemes.includes(prefs.theme)?prefs.theme:'glass';
        document.body.dataset.density=prefs.density==='compact'?'compact':'comfortable';
        document.body.dataset.fontSize=prefs.fontSize==='large'?'large':'normal';
        document.body.classList.toggle('reduce-motion',!prefs.motion);
        let style=document.getElementById('ghostlink-theme');
        if(!style){style=document.createElement('link');style.id='ghostlink-theme';style.rel='stylesheet';style.href='assets/themes/ghostlink.css';document.head.append(style)}
    }
    function save(){localStorage.setItem(KEY,JSON.stringify(prefs));apply()}
    function openSettings(){
        if(document.querySelector('.theme-settings-backdrop'))return;
        const overlay=document.createElement('div');
        overlay.className='theme-settings-backdrop';
        overlay.innerHTML='<section class="theme-settings-card" role="dialog" aria-modal="true" aria-labelledby="themeSettingsTitle"><h2 id="themeSettingsTitle">Appearance & preferences</h2><p>Personalize SDP Link on this device. Your choices are saved in this browser.</p><div class="theme-setting-row"><label for="glTheme">Color theme</label><select id="glTheme"><option value="glass">Glass</option><option value="aurora">Aurora</option><option value="midnight">Midnight</option><option value="rose">Rose</option></select></div><div class="theme-setting-row"><label for="glDensity">Layout density</label><select id="glDensity"><option value="comfortable">Comfortable</option><option value="compact">Compact</option></select></div><div class="theme-setting-row"><label for="glFont">Text size</label><select id="glFont"><option value="normal">Standard</option><option value="large">Large</option></select></div><div class="theme-setting-row"><label for="glMotion">Animations</label><input id="glMotion" type="checkbox"></div><button class="theme-settings-close" type="button">Done</button></section>';
        document.body.append(overlay);
        overlay.querySelector('#glTheme').value=prefs.theme;
        overlay.querySelector('#glDensity').value=prefs.density;
        overlay.querySelector('#glFont').value=prefs.fontSize;
        overlay.querySelector('#glMotion').checked=prefs.motion;
        overlay.addEventListener('change',event=>{
            const el=event.target;
            if(el.id==='glTheme')prefs.theme=el.value;
            if(el.id==='glDensity')prefs.density=el.value;
            if(el.id==='glFont')prefs.fontSize=el.value;
            if(el.id==='glMotion')prefs.motion=el.checked;
            save();
        });
        overlay.addEventListener('click',event=>{if(event.target===overlay||event.target.closest('.theme-settings-close'))overlay.remove()});
        overlay.addEventListener('keydown',event=>{if(event.key==='Escape')overlay.remove()});
        overlay.querySelector('#glTheme').focus();
    }
    function install(){
        apply();
        const trigger=document.createElement('button');
        trigger.type='button';trigger.className='theme-settings-trigger';
        trigger.innerHTML='<span aria-hidden="true">◐</span> Appearance';
        trigger.addEventListener('click',openSettings);
        const nav=document.querySelector('.nav');
        const side=document.querySelector('.side-foot');
        if(nav)nav.append(trigger);
        else if(side){
            const link=document.createElement('a');link.href='#';link.className='side-link';link.textContent='Appearance';
            link.addEventListener('click',event=>{event.preventDefault();openSettings()});side.prepend(link);
        }else{
            trigger.style.position='fixed';trigger.style.right='18px';trigger.style.bottom='18px';trigger.style.zIndex='100';document.body.append(trigger);
        }
        document.addEventListener('keydown',event=>{if(event.altKey&&event.key.toLowerCase()==='t')openSettings()});
    }
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',install);else install();
})();
