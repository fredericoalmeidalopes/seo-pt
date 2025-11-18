<?php
if ( ! defined('ABSPATH') ) exit;
class PTSEO_Admin{
  public static function init(){ add_action('admin_menu',array(__CLASS__,'menu')); add_action('admin_enqueue_scripts',array(__CLASS__,'assets')); }
  public static function menu(){ add_menu_page('PT SEO','PT SEO','manage_options','ptseo',array(__CLASS__,'dashboard'),'dashicons-analytics',65); add_submenu_page('ptseo','Funcionalidades do site','Funcionalidades do site','manage_options','ptseo',array(__CLASS__,'dashboard')); }
  public static function assets($hook){ if (strpos($hook,'ptseo')===false) return; wp_enqueue_style('ptseo-admin', PTSEO_URL . 'assets/admin.css', array(), PTSEO_VERSION); wp_enqueue_script('ptseo-admin', PTSEO_URL . 'assets/admin.js', array('jquery'), PTSEO_VERSION, true); }
  public static function dashboard(){ if(!current_user_can('manage_options')) return; $opts = PTSEO_Options::get(); $modules = isset($opts['modules'])? $opts['modules']:array('local'=>true,'lingua'=>true); ?>
  <div class="wrap ptseo">
    <h1 class="ptseo-title"><span class="logo">pt</span> SEO</h1>
    <div class="ptseo-panel">
      <div class="ptseo-panel-header">

        <h2>Funcionalidades do site</h2><p>Escolha que funcionalidades quer utilizar.</p>
      </div>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" class="ptseo-cards-form">
        <?php wp_nonce_field('ptseo_save_modules'); ?><input type="hidden" name="action" value="ptseo_save_modules"/>
        <div class="ptseo-cards">
          <div class="ptseo-card"><div class="ptseo-card-head"><div class="ptseo-icon ai">AI</div><div class="ptseo-card-title">SEO PT‑PT Local</div></div>
            <p>Defina a localização (Distrito/Ilha → Concelho → Freguesia) e gere JSON‑LD <em>LocalBusiness</em> optimizado para Portugal. Dados 100% offline.</p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=ptseo-local'));?>" class="button">Abrir módulo</a></p>
            <div class="ptseo-toggle"><span>Ativar funcionalidade</span><label class="switch"><input type="checkbox" name="modules[local]" value="1" <?php checked( !empty($modules['local']) ); ?>><span class="slider"></span></label></div>
          </div>
          <div class="ptseo-card"><div class="ptseo-card-head"><div class="ptseo-icon lm">PT</div><div class="ptseo-card-title">Análise Linguística PT‑PT</div></div>
            <p>Curadoria em tempo real do conteúdo: detecta termos e estruturas do Português do Brasil e sugere equivalentes e estilo PT‑PT, com pontuação.</p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=ptseo-lingua'));?>" class="button">Abrir módulo</a></p>
            <div class="ptseo-toggle"><span>Ativar funcionalidade</span><label class="switch"><input type="checkbox" name="modules[lingua]" value="1" <?php checked( !empty($modules['lingua']) ); ?>><span class="slider"></span></label></div>
          </div>
        </div>
        <p><button class="button button-primary">Guardar alterações</button></p>
      </form>
    </div>
  </div>
  <?php }
}
PTSEO_Admin::init();
add_action('admin_post_ptseo_save_modules', function(){ if(!current_user_can('manage_options')) wp_die(''); check_admin_referer('ptseo_save_modules'); $opts=PTSEO_Options::get(); $opts['modules']=array('local'=>!empty($_POST['modules']['local']), 'lingua'=>!empty($_POST['modules']['lingua'])); PTSEO_Options::update($opts); wp_redirect(admin_url('admin.php?page=ptseo&updated=1')); exit;});
