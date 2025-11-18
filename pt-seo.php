<?php
/**
 * Plugin Name: SEO PT
 * Plugin URI: https://github.com/fredericoalmeidalopes/seo-pt
 * Description: Ferramentas de revisão PT-PT/PT-BR, sugestões de SEO e base de localização PT para WordPress.
 * Version: 2.0.5
 * Author: Frederico Lopes
 * Author URI: https://fredericolopes.com
 * Text Domain: pt-seo
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! defined('PTSEO_VERSION') ) define('PTSEO_VERSION','2.0.5');
if ( ! defined('PTSEO_PATH') )    define('PTSEO_PATH', plugin_dir_path(__FILE__));
if ( ! defined('PTSEO_URL') )     define('PTSEO_URL', plugin_dir_url(__FILE__));

require_once PTSEO_PATH . 'includes/class-options.php';
require_once PTSEO_PATH . 'includes/class-admin.php';

add_action('plugins_loaded', function(){
  $options = PTSEO_Options::get();
  if ( ! isset($options['modules']) ) $options['modules'] = array('local'=>true,'lingua'=>true);
  if ( ! empty($options['modules']['local']) )  require_once PTSEO_PATH . 'modules/local/module-local.php';
  if ( ! empty($options['modules']['lingua']) ) require_once PTSEO_PATH . 'modules/lingua/module-lingua.php';
}, 1);
