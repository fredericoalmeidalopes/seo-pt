<?php
if ( ! defined('ABSPATH') ) exit;
class PTSEO_Lingua{
  public static function init(){ add_action('admin_menu', array(__CLASS__,'menu')); add_action('admin_enqueue_scripts', array(__CLASS__,'enqueue')); add_action('add_meta_boxes', array(__CLASS__,'metabox')); add_action('wp_ajax_ptseo_lingua_analyze', array(__CLASS__,'ajax_analyze')); }
  public static function menu(){ add_submenu_page('ptseo','Análise Linguística','Linguística (PT‑PT)','manage_options','ptseo-lingua', array(__CLASS__,'page')); }
  // O método dict() não é mais necessário, pois load_dictionary() será usado para o backend.
  // Mantemos o método aqui, mas ele não será mais chamado pelo enqueue.
  private static function dict(){ $t=PTSEO_PATH.'modules/lingua/dictionary/ptbr-ptpt.json'; $p=PTSEO_PATH.'modules/lingua/dictionary/patterns.json'; $terms=array(); $pats=array(); if(file_exists($t)){$terms=json_decode(@file_get_contents($t),true); if(!is_array($terms))$terms=array();} if(file_exists($p)){$pats=json_decode(@file_get_contents($p),true); if(!is_array($pats))$pats=array();} return array($terms,$pats); }
  public static function enqueue($hook){ if($hook!=='post.php' && $hook!=='post-new.php') return; 
    // Removido o carregamento do dicionário e padrões para o JS, pois a análise será via AJAX
    wp_enqueue_style('ptseo-lingua', PTSEO_URL . 'modules/lingua/lingua.css', array(), PTSEO_VERSION); 
    wp_enqueue_script('ptseo-lingua', PTSEO_URL . 'modules/lingua/lingua-unified.js', array('jquery', 'wp-data'), PTSEO_VERSION, true); 
    wp_localize_script('ptseo-lingua','PTSEO_LINGUA', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('ptseo_lingua_nonce'), // Adicionar nonce para segurança
      'postId'  => isset($_GET['post']) ? intval($_GET['post']) : (isset($GLOBALS['post']->ID) ? intval($GLOBALS['post']->ID) : 0),
    )); 
    // Adicionar postbox para compatibilidade com o ptpt-curator
    wp_enqueue_script('postbox');
  }
  public static function metabox(){ add_meta_box('ptseo_lingua','Análise Linguística PT‑PT', array(__CLASS__,'box'), null, 'normal','high'); }
  public static function box($post){ echo '<div id="ptseo_linguabox" class="ptseo-linguabox">'; echo '<div class="score"><span class="label">Pontuação de Análise PT‑PT</span><span class="badge" id="ptseo_score">100</span></div>'; echo '<div class="meta"><span id="ptseo_counts">Erros: 0 • Avisos: 0 • Palavras: 0</span></div>'; echo '<ul id="ptseo_issues" class="issues"></ul>'; echo '<p><button type="button" class="button" id="ptseo_manual_analyze">Analisar agora (opcional)</button><span class="ptseo-loading" id="ptseo_loading" style="display:none;">A analisar…</span></p>'; echo '</div>'; }
  
  // Métodos de análise do ptpt-curator
  public static function ajax_analyze() {
    if (!current_user_can('edit_posts')) wp_send_json_error(['message' => 'no_permission'], 403);
    // O plugin seo-pt não usa nonce para esta ação, mas vamos adicionar um check básico
    // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ptseo_lingua_nonce')) wp_send_json_error(['message' => 'nonce_fail'], 403);

    // ===== Normalização do conteúdo =====
    $content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
    $post_id = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
    if (!$content && $post_id) {
        $post = get_post($post_id);
        if ($post) $content = $post->post_content;
    }

    // Remove shortcodes [shortcode] e comentários <!-- wp:... -->
    $content = preg_replace('/\[\/?[a-zA-Z0-9_\-]+(?:\s+[^\]]*)?\]/u', ' ', $content);
    $content = preg_replace('/<!--.*?-->/us', ' ', $content);

    // Tira tags e entidades
    $plain = wp_strip_all_tags($content);
    $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Remove zero-width / normaliza espaços
    $plain = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $plain);
    $plain = preg_replace('/\s+/u', ' ', $plain);
    $plain = trim($plain);

    // Contagem de palavras (com extensão de caracteres)
    $words = str_word_count($plain, 0, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜàáâãäåçèéêëìíîïñòóôõöùúûü');

    // ===== Dicionário =====
    $dict = self::load_dictionary();

    $issues = [];
    $error_count = 0;
    $warning_count = 0;

    // Vocabulário pt-BR -> pt-PT
    foreach ($dict as $entry) {
        $pattern = isset($entry['pattern']) ? $entry['pattern'] : null;
        $labelBR = isset($entry['label_br']) ? $entry['label_br'] : null;
        $suggest = isset($entry['suggest_pt']) ? $entry['suggest_pt'] : null;
        if (!$pattern || !$suggest) continue;

        $c = self::count_regex($pattern, $plain);
        if ($c > 0) {
            $error_count += $c;
            $issues[] = [
                'severity' => 'error',
                'title'    => sprintf(
                    '%s: “%s” (%d %s) %s: “%s”.',
                    __('Termo Brasileiro encontrado', 'pt-seo'),
                    $labelBR ?: $suggest,
                    $c, __('vez(es)', 'pt-seo'),
                    __('Sugestão PT-PT', 'pt-seo'),
                    $suggest
                ),
                'category' => 'vocab',
                'subtitle' => __('Vocabulário – Alerta e Sugestão', 'pt-seo'),
            ];
        }
    }

    // ===== Estilo (avisos) =====
    // 1) Gerúndio pt-BR (estou/está/...) + -ando/-endo/-indo; evita "estar a + infinitivo"
    $gerund_pattern = '/\b(estou|est[aá]|est[aá]s|estamos|est[ãa]o|tava|estava|estavam)\s+(?!a\s+)(\p{L}+?)(ando|endo|indo)\b/iu';
    $ger_count = self::count_regex($gerund_pattern, $plain);
    if ($ger_count > 0) {
        $warning_count += $ger_count;
        $issues[] = [
            'severity' => 'warning',
            'title'    => sprintf(
                __('Estilo Linguístico Brasileiro (gerúndio) encontrado (%d vez(es)) Sugestão PT-PT: usar “estar a + infinitivo” (ex.: “estou a fazer”).', 'pt-seo'),
                $ger_count
            ),
            'category' => 'style',
            'subtitle' => __('Sintaxe/Gramática – Estilo', 'pt-seo'),
        ];
    }

    // 2) "você"
    $voce_pattern = '/\bvoc[eê]\b/iu';
    $voce_count = self::count_regex($voce_pattern, $plain);
    if ($voce_count > 0) {
        $warning_count += $voce_count;
        $issues[] = [
            'severity' => 'warning',
            'title'    => sprintf('“você” (%d vez(es)). Sugestão PT-PT: “tu” ou forma impessoal, conforme o tom.', $voce_count),
            'category' => 'style',
            'subtitle' => __('Sintaxe/Gramática – Estilo', 'pt-seo'),
        ];
    }

    // ===== Pontuação e resposta =====
    $score = max(0, 100 - ($error_count * 5 + $warning_count * 2));
    $result = [
        'score'    => $score,
        'errors'   => $error_count,
        'warnings' => $warning_count,
        'words'    => $words,
        'issues'   => $issues,
    ];

    wp_send_json_success($result);
  }

  private static function count_regex($pattern, $text) {
    if (!$pattern || !$text) return 0;
    if (@preg_match_all($pattern, $text, $m)) return count($m[0]);
    return 0;
  }

  private static function load_dictionary() {
    // Usar o dicionário existente do seo-pt
    $file = PTSEO_PATH . 'modules/lingua/dictionary/ptbr-ptpt.json';
    $formatted_dict = [];
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        
        // O JSON está no formato { "termo_br": { "suggest": "termo_pt", ... } }
        // A função ajax_analyze espera um array de arrays, como:
        // [ { "pattern": "/\\btermo_br\\b/iu", "label_br": "termo_br", "suggest_pt": "termo_pt" }, ... ]
        if (is_array($data)) {
            foreach ($data as $term_br => $details) {
                $formatted_dict[] = [
                    'pattern'    => '/\b' . preg_quote($term_br, '/') . '\b/iu',
                    'label_br'   => $term_br,
                    'suggest_pt' => isset($details['suggest']) ? $details['suggest'] : '',
                    // Outros campos como severity e category não são estritamente necessários aqui, mas podem ser úteis
                ];
            }
        }
    }
    return $formatted_dict;
  }

  public static function page(){ echo '<div class="wrap"><h1>Análise Linguística PT‑PT</h1><p>Analisa o conteúdo em tempo real para conversão PT‑BR → PT‑PT (Gutenberg e Editor Clássico).</p></div>'; }
}
PTSEO_Lingua::init();
