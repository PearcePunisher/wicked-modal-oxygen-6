/* Wicked Modal core logic: accessible, supports multiple instances & triggers */
(function(){
  const FOCUSABLE_SELECTORS = [
    'a[href]','area[href]','input:not([disabled])','select:not([disabled])','textarea:not([disabled])',
    'button:not([disabled])','iframe','object','embed','[contenteditable]','[tabindex]:not([tabindex="-1"])'
  ].join(',');

  const state = new Map(); // id -> { triggerEls: Set, lastActive: Element|null }
  const inertTracker = new Map(); // id -> Element[] we set inert/aria-hidden on

  function qs(sel, ctx=document){ return ctx.querySelector(sel); }
  function qsa(sel, ctx=document){ return Array.from(ctx.querySelectorAll(sel)); }

  function setBackgroundInert(host, on){
    const id = host.id || host.getAttribute('data-wm-id');
    if(!id) return;
    if(on){
      const affected = [];
      const children = Array.from(document.body.children);
      for(const el of children){
        if(el === host) continue;
        if(el.hasAttribute('aria-live')) continue; // avoid interfering with live regions
        try {
          el.setAttribute('aria-hidden','true');
          el.setAttribute('inert','');
          affected.push(el);
        } catch(e){}
      }
      inertTracker.set(id, affected);
    } else {
      const affected = inertTracker.get(id) || [];
      for(const el of affected){
        el.removeAttribute('aria-hidden');
        el.removeAttribute('inert');
      }
      inertTracker.delete(id);
    }
  }

  function lockScroll(lock){
    if(lock){
      const sb = window.innerWidth - document.documentElement.clientWidth;
      document.documentElement.style.overflow = 'hidden';
      if(sb > 0){ document.documentElement.style.paddingRight = sb + 'px'; }
    } else {
      document.documentElement.style.overflow = '';
      document.documentElement.style.paddingRight = '';
    }
  }

  function trapFocus(container, e){
    const focusables = qsa(FOCUSABLE_SELECTORS, container).filter(el => el.offsetParent !== null || el === document.activeElement);
    if(!focusables.length){ return; }
    const first = focusables[0];
    const last = focusables[focusables.length-1];
    if(e.shiftKey && document.activeElement === first){ last.focus(); e.preventDefault(); }
    else if(!e.shiftKey && document.activeElement === last){ first.focus(); e.preventDefault(); }
  }

  function openModal(id){
    const host = document.getElementById(id);
    if(!host) return;
    const overlay = qs('[data-wm-overlay]', host);
    const dialog = qs('[data-wm-dialog]', host);
  const lock = host.getAttribute('data-wm-lock-scroll') !== 'false';

    if(!state.has(id)) state.set(id, { triggerEls: new Set(), lastActive: null });
    const st = state.get(id);
    st.lastActive = document.activeElement;

  host.classList.add('wm-open');
    overlay?.classList.remove('wm-hidden');
  if(lock) lockScroll(true);
  setBackgroundInert(host, true);

    // A11y
    overlay?.setAttribute('aria-hidden','false');
    dialog?.setAttribute('aria-modal','true');
    dialog?.setAttribute('role','dialog');
    dialog?.setAttribute('tabindex','-1');

    // Focus first focusable or dialog
    setTimeout(()=>{
      const focusable = qsa(FOCUSABLE_SELECTORS, dialog).find(el=>el.offsetParent!==null) || dialog;
      focusable && focusable.focus();
    }, 0);
  }

  function closeModal(id){
    const host = document.getElementById(id);
    if(!host) return;
    const overlay = qs('[data-wm-overlay]', host);
    const dialog = qs('[data-wm-dialog]', host);
  const lock = host.getAttribute('data-wm-lock-scroll') !== 'false';

    host.classList.remove('wm-open');
  overlay?.classList.add('wm-hidden');
  if(lock) lockScroll(false);
  setBackgroundInert(host, false);

    overlay?.setAttribute('aria-hidden','true');
    dialog?.removeAttribute('aria-modal');

    const st = state.get(id);
    if(st && st.lastActive){ st.lastActive.focus?.(); }
  }

  function toggleModal(id){
    const host = document.getElementById(id);
    if(!host) return;
    if(host.classList.contains('wm-open')) closeModal(id); else openModal(id);
  }

  function attachHandlers(host){
    const id = host.id || host.getAttribute('data-wm-id');
    if(!id){ console.warn('Wicked Modal needs an id or data-wm-id'); return; }
    if(!host.id) host.id = id;

  const overlay = qs('[data-wm-overlay]', host);
    const dialog = qs('[data-wm-dialog]', host);
    const closeBtn = qs('[data-wm-close]', host);
  const closeOnOverlay = host.getAttribute('data-wm-close-on-overlay') !== 'false';
  const closeOnEsc = host.getAttribute('data-wm-close-on-esc') !== 'false';

    // Close on overlay click
    if(closeOnOverlay){
      overlay?.addEventListener('click', (e)=>{
        if(e.target === overlay) closeModal(id);
      });
    }

    // Close button
    closeBtn?.addEventListener('click', ()=> closeModal(id));

    // ESC
    host.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape' && closeOnEsc) closeModal(id);
      if(e.key === 'Tab') trapFocus(dialog || host, e);
    });

    // External triggers (buttons/links) using [data-wm-open="<id>"] etc
  qsa(`[data-wm-open="${id}"]`).forEach(el=>{
      el.addEventListener('click', (e)=>{ e.preventDefault(); openModal(id); });
      if(!state.has(id)) state.set(id, { triggerEls: new Set(), lastActive: null });
      state.get(id).triggerEls.add(el);
    });
    qsa(`[data-wm-close="${id}"]`).forEach(el=> el.addEventListener('click', (e)=>{ e.preventDefault(); closeModal(id); }));
    qsa(`[data-wm-toggle="${id}"]`).forEach(el=> el.addEventListener('click', (e)=>{ e.preventDefault(); toggleModal(id); }));

    // Triggers: time delay, exit intent, scroll depth, time on page
    const sessionKey = `wm-opened-${id}`;
    const freqMode = host.getAttribute('data-wm-frequency'); // 'session' | 'days' | null
    const freqDays = parseInt(host.getAttribute('data-wm-frequency-days')||'0',10);
    const legacyOncePerSession = host.getAttribute('data-wm-once-per-session') === 'true';
    const lsKey = `wm-opened-ts-${id}`;
    const now = Date.now();

    const allowedByFrequency = () => {
      if(freqMode === 'session'){
        if(sessionStorage.getItem(sessionKey)) return false;
        sessionStorage.setItem(sessionKey, '1');
        return true;
      }
      if(freqMode === 'days'){
        const prev = parseInt(localStorage.getItem(lsKey)||'0',10);
        const ms = (freqDays>0?freqDays:1) * 24*60*60*1000;
        if(prev && (now - prev) < ms) return false;
        localStorage.setItem(lsKey, String(now));
        return true;
      }
      if(legacyOncePerSession){
        if(sessionStorage.getItem(sessionKey)) return false;
        sessionStorage.setItem(sessionKey, '1');
        return true;
      }
      return true;
    };

    const openIfAllowed = () => { if(allowedByFrequency()) openModal(id); };

    const delayMs = parseInt(host.getAttribute('data-wm-delay')||'0',10);
    if(delayMs>0) setTimeout(openIfAllowed, delayMs);

    const timeOnPageMs = parseInt(host.getAttribute('data-wm-time-on-page')||'0',10);
  if(timeOnPageMs>0) setTimeout(openIfAllowed, timeOnPageMs);

    const scrollDepth = parseInt(host.getAttribute('data-wm-scroll-depth')||'0',10); // percent
    if(scrollDepth>0){
      const onScroll = ()=>{
        const scrolled = (window.scrollY + window.innerHeight) / Math.max(1, document.documentElement.scrollHeight) * 100;
        if(scrolled >= scrollDepth){
          window.removeEventListener('scroll', onScroll);
          openIfAllowed();
        }
      };
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    const exitIntent = host.getAttribute('data-wm-exit-intent') === 'true';
    if(exitIntent){
      const onMouseOut = (e)=>{
        if(e.clientY <= 0){
          document.removeEventListener('mouseout', onMouseOut);
          openIfAllowed();
        }
      };
      document.addEventListener('mouseout', onMouseOut);
    }

    // External CSS selector triggers
    const sel = host.getAttribute('data-wm-trigger-selector');
    if(sel){
      sel.split(',').map(s=>s.trim()).filter(Boolean).forEach(s=>{
        qsa(s).forEach(el => el.addEventListener('click', (e)=>{ e.preventDefault(); openIfAllowed(); }));
      });
    }

    // URL hash trigger
    if(host.getAttribute('data-wm-open-on-hash') === 'true'){
      const checkHash = ()=>{
        const h = window.location.hash.replace('#','');
        if(h === id || h === `open-${id}`) openIfAllowed();
      };
      window.addEventListener('hashchange', checkHash);
      checkHash();
    }

    // URL param trigger (?open_modal=1)
    const paramKey = host.getAttribute('data-wm-open-on-param');
    if(paramKey){
      const usp = new URLSearchParams(window.location.search);
      if(usp.has(paramKey)) openIfAllowed();
    }

    // Inactivity trigger
    const inactivity = parseInt(host.getAttribute('data-wm-inactivity')||'0',10);
    if(inactivity>0){
      let t;
      const reset = ()=>{ clearTimeout(t); t = setTimeout(openIfAllowed, inactivity); };
      ['mousemove','keydown','scroll','touchstart'].forEach(ev => window.addEventListener(ev, reset, { passive: true }));
      reset();
    }
  }

  function init(){
    qsa('[data-wm-modal]').forEach(attachHandlers);
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else { init(); }

  // Public API for advanced integrations
  window.WickedModal = {
    open: openModal,
    close: closeModal,
    toggle: toggleModal
  };
})();
