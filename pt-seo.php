<?php
/**
 * Plugin Name: PT SEO Suite
 * Description: Suite de SEO para PT com módulos: (1) Local (JSON-LD + listas PT), (2) Análise Linguística PT‑PT. Painel principal tipo Yoast.
 * Version: 2.0.5
 * Author: Frederico Lopes
 * Text Domain: pt-seo
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! defined('PTSEO_VERSION') ) define('PTSEO_VERSION','2.0.5');
if ( ! defined('PTSEO_PATH') )    define('PTSEO_PATH', plugin_dir_path(__FILE__));
if ( ! defined('PTSEO_URL') )     define('PTSEO_URL', plugin_dir_url(__FILE__));

// Carregar o domínio de texto
add_action('plugins_loaded', function() {
    load_plugin_textdomain('pt-seo', false, basename(PTSEO_PATH) . '/languages');
}, 0);

require_once PTSEO_PATH . 'includes/class-options.php';
require_once PTSEO_PATH . 'includes/class-admin.php';

add_action('plugins_loaded', function(){
  $options = PTSEO_Options::get();
  if ( ! isset($options['modules']) ) $options['modules'] = array('local'=>true,'lingua'=>true);
  if ( ! empty($options['modules']['local']) )  require_once PTSEO_PATH . 'modules/local/module-local.php';
  if ( ! empty($options['modules']['lingua']) ) require_once PTSEO_PATH . 'modules/lingua/module-lingua.php';
}, 1);
