(function($){
  const DEBOUNCE_MS = 600;
  const MIN_INTERVAL_MS = 1000;
  let running = false, timer = null, lastRunTs = 0, lastHash = '';

  function colorForScore(s){
    if (s >= 90) return '#2e7d32';
    if (s >= 75) return '#f9a825';
    if (s >= 60) return '#ef6c00';
    return '#c62828';
  }

  function renderIssues(issues){
    const $wrap = $('#ptseo_issues').empty(); // ID adaptado
    if (!issues || issues.length === 0) {
      $wrap.append('<li class="ptseo-empty">Sem problemas detectados \ud83c\udf89</li>'); // ID adaptado
      return;
    }
    issues.forEach(function(it){
      const sevIcon  = it.severity === 'error' ? '⛔' : '⚠️';
      const sevClass = it.severity === 'error' ? 'ptseo-err' : 'ptseo-warn'; // Classe adaptada
      $wrap.append(
        '<li class="ptseo-issue '+sevClass+'">' + // Tag e classe adaptada
          '<div class="ptseo-issue-top">' + // Classe adaptada
            '<div class="ptseo-issue-icon">'+sevIcon+'</div>' + // Classe adaptada
            '<div class="ptseo-issue-title" title="'+(it.category||'')+'">'+it.title+'</div>' + // Classe adaptada
          '</div>' +
          '<div class="ptseo-issue-sub">'+(it.subtitle||'')+'</div>' + // Classe adaptada
        '</li>'
      );
    });
  }

  function getCurrentContent(){
    try {
      if (window.wp && wp.data && wp.data.select) {
        const sel = wp.data.select('core/editor');
        if (sel && sel.getEditedPostAttribute) {
          const c = sel.getEditedPostAttribute('content');
          if (typeof c === 'string') return c;
        }
        if (sel && sel.getEditedPostContent) {
          const c2 = sel.getEditedPostContent();
          if (typeof c2 === 'string') return c2;
        }
      }
    } catch(e) {}
    const $txt = $('#content');
    if ($txt.length) return $txt.val() || '';
    return '';
  }

  function simpleHash(str){ let h=0,i=0; for(;i<str.length;i++){ h=(h<<5)-h+str.charCodeAt(i)|0; } return h.toString(36); }

  function doAnalyze(trigger){
    if (running) return;
    const now = Date.now();
    if (now - lastRunTs < MIN_INTERVAL_MS) { scheduleAnalyze(); return; }

    const content = getCurrentContent();
    const h = simpleHash(content);
    if (h === lastHash && trigger !== 'manual') return;

    running = true; lastRunTs = now;

    const $btn  = $('#ptseo_manual_analyze'); // ID adaptado
    const $load = $('#ptseo_loading'); // ID adaptado
    $btn.prop('disabled', true); 
    $load.show();

    // Estado de carregamento no painel (evita resíduos visuais)
    $('#ptseo_issues').html('<li class="ptseo-placeholder">A analisar…</li>'); // ID adaptado

    $.post(PTSEO_LINGUA.ajaxUrl, { // Variável adaptada
      action:  'ptseo_lingua_analyze', // Action adaptada
      nonce:   PTSEO_LINGUA.nonce, // Variável adaptada
      postId:  PTSEO_LINGUA.postId || 0, // Variável adaptada
      content: content,
      _t:      now
    })
    .done(function(resp){
      if (!resp || !resp.success) throw new Error('Falha na análise.');
      const r = resp.data;
      const $score = $('#ptseo_score'); // ID adaptado
      $score.text(r.score);
      $score.css('background', colorForScore(r.score));
      
      // IDs adaptados
      const $counts = $('#ptseo_counts');
      $counts.html('Erros: ' + r.errors + ' • Avisos: ' + r.warnings + ' • Palavras: ' + r.words);

      renderIssues(r.issues);
      lastHash = h;
    })
    .fail(function(){ console.warn('[PT SEO Lingua] Não foi possível analisar agora.'); })
    .always(function(){ running = false; $btn.prop('disabled', false); $load.hide(); });
  }

  function scheduleAnalyze(){ if (timer) clearTimeout(timer); timer = setTimeout(function(){ doAnalyze('auto'); }, DEBOUNCE_MS); }

  // Gutenberg: reage a alterações do estado do editor
  if (window.wp && wp.data && wp.data.subscribe) {
    let lastSnapshot = getCurrentContent();
    wp.data.subscribe(function(){
      const current = getCurrentContent();
      if (current !== lastSnapshot) { lastSnapshot = current; scheduleAnalyze(); }
    });
  }

  // Fallback: observar alterações no DOM do editor
  try {
    const observer = new MutationObserver(function(){ scheduleAnalyze(); });
    const startObserve = () => {
      const root = document.querySelector('.edit-post-visual-editor, .block-editor-writing-flow');
      if (root) observer.observe(root, { subtree:true, characterData:true, childList:true });
    };
    if (document.readyState === 'complete') startObserve();
    else window.addEventListener('load', startObserve);
  } catch(e) {}

  // Editor clássico
  $(document).on('input keyup paste change', '#content', scheduleAnalyze);

  // Botão manual (opcional)
  $(document).on('click', '#ptseo_manual_analyze', function(){ doAnalyze('manual'); }); // ID adaptado

  // Primeira análise automática
  (function initialKickoff(){
    const run = () => doAnalyze('initial');
    if (document.readyState === 'complete') setTimeout(run, 600);
    else window.addEventListener('load', function(){ setTimeout(run, 600); });
  })();

  // Ativar toggles/arrastar da metabox (já está no PHP, mas manter aqui para garantir)
  // jQuery(function(){ if (window.postboxes && window.pagenow) postboxes.add_postbox_toggles(pagenow); });
})(jQuery);
