<?php
if ( ! defined('ABSPATH') ) exit;
class PTSEO_Options{
  public static function get($key=null,$default=array()){
    $opts = get_option('ptseo_options', array('modules'=>array('local'=>true,'lingua'=>true)) );
    if ($key===null) return $opts; return isset($opts[$key])? $opts[$key] : $default;
  }
  public static function update($data){ update_option('ptseo_options',$data); }
}
