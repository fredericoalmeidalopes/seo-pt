<?php
if ( ! defined('ABSPATH') ) exit;
class PTSEO_Local{
  public static function init(){
    add_action('admin_menu', array(__CLASS__,'menu'));
    add_action('admin_enqueue_scripts', array(__CLASS__,'assets'));
    add_action('wp_ajax_ptseo_local_get', array(__CLASS__,'ajax_data'));
    add_action('admin_post_ptseo_local_save', array(__CLASS__,'save'));
    add_action('admin_post_ptseo_local_download', array(__CLASS__,'download'));
    add_action('admin_post_ptseo_local_generate', array('PTSEO_Local_Tools','generate'));
    add_action('wp_head', array(__CLASS__,'schema'), 9);
  }
  public static function menu(){
    add_submenu_page('ptseo','SEO PT‑PT Local','Local (Geo)','manage_options','ptseo-local', array(__CLASS__,'page'));
    add_submenu_page('ptseo','Local — Ferramentas','Local — Ferramentas','manage_options','ptseo-local-tools', array('PTSEO_Local_Tools','page'));
  }
  public static function assets($hook){ if(strpos($hook,'ptseo-local')===false) return; wp_enqueue_style('ptseo-local', PTSEO_URL . 'modules/local/admin-local.css', array(), PTSEO_VERSION); wp_enqueue_script('select2','https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true); wp_enqueue_style('select2','https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0'); wp_enqueue_script('ptseo-local', PTSEO_URL . 'modules/local/admin-local.js', array('jquery','select2'), PTSEO_VERSION, true); wp_localize_script('ptseo-local','PTSEO_LOCAL', array('ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('ptseo_local'),'preset'=> get_option('ptseo_local_locations', array()))); }
  public static function data(){ $file=PTSEO_PATH.'modules/local/data/pt-admin.min.json'; if(file_exists($file)){ $j=@file_get_contents($file); $d=json_decode($j,true); if(is_array($d)) return $d; } return array('districts'=>array()); }
  public static function ajax_data(){ check_ajax_referer('ptseo_local','nonce'); wp_send_json( self::data() ); }
  public static function page(){ if(!current_user_can('manage_options')) return; $data_path=PTSEO_PATH.'modules/local/data/pt-admin.min.json'; $has_data=file_exists($data_path)&&filesize($data_path)>20; $dl_url=$has_data? wp_nonce_url(admin_url('admin-post.php?action=ptseo_local_download'),'ptseo_local_dl') : admin_url('admin.php?page=ptseo-local-tools'); $updated=isset($_GET['updated']); ?>
  <div class="wrap seoptl">
    <h1>SEO PT‑PT Local</h1>
    <?php if(!empty($updated)): ?><div class="notice notice-success is-dismissible"><p>Opções guardadas com sucesso.</p></div><?php endif; ?>
    <div class="seoptl-container">
      <div class="seoptl-sidebar"><ul class="seoptl-menu"><li class="active">Geographic</li></ul></div>
      <div class="seoptl-content">
        <h2>Location Settings</h2>
        <p>Listas estáticas embutidas (sem importador). Este build suporta PT completo (Continente + Ilhas).
          <a class="button button-secondary" style="margin-left:10px" href="<?php echo esc_url($dl_url); ?>"><?php echo $has_data? 'Descarregar listagens (pt-admin.min.json)' : 'Gerar e descarregar listagens'; ?></a>
        </p>
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
          <?php wp_nonce_field('ptseo_local_save'); ?><input type="hidden" name="action" value="ptseo_local_save"/>
          <div class="seoptl-row">
            <div class="seoptl-field"><label>Distrito / Ilha</label><select id="seoptl_district" class="seoptl-select" data-placeholder="Selecione o distrito/ilha"></select></div>
            <div class="seoptl-field"><label>Concelho</label><select id="seoptl_municipality" class="seoptl-select" data-placeholder="Selecione o concelho"></select></div>
            <div class="seoptl-field"><label>Freguesia</label><select id="seoptl_parish" class="seoptl-select" data-placeholder="Selecione a freguesia"></select></div>
          </div>
          <div class="seoptl-row"><button type="button" class="button button-primary" id="seoptl_add_location">Add Location</button> <a class="button" href="<?php echo esc_url( admin_url('admin.php?page=ptseo-local-tools') ); ?>">Ferramentas</a></div>
          <div class="seoptl-row"><label>Multiple Locations</label><div id="seoptl_locations_list" class="seoptl-tags"></div></div>
          <input type="hidden" name="locations_json" id="seoptl_locations_input" />
          <p><button class="button button-primary">Guardar</button></p>
        </form>
        <div class="seoptl-preview"><h3>Pré‑visualização do JSON‑LD</h3><pre id="seoptl_preview" style="white-space:pre-wrap;max-height:240px;overflow:auto;background:#0b1020;color:#e5f4ff;padding:10px;border-radius:6px"></pre></div>
      </div>
      <div class="seoptl-aside"><div class="seoptl-preview"><h3>Geographic Location</h3><ul>
        <li><strong>addressLocality</strong> <span id="prev_locality"></span></li>
        <li><strong>addressRegion</strong> <span id="prev_region"></span></li>
        <li><strong>addressCountry</strong> Portugal</li>
        <li><strong>areaServed</strong> <span id="prev_area"></span></li>
      </ul></div></div>
    </div>
  </div>
  <?php }
  public static function save(){ if(!current_user_can('manage_options')) wp_die(''); check_admin_referer('ptseo_local_save'); $json=stripslashes(isset($_POST['locations_json'])?$_POST['locations_json']:'[]'); $locs=json_decode($json,true); if(!is_array($locs)) $locs=array(); update_option('ptseo_local_locations', array_values($locs)); wp_redirect(add_query_arg('updated',1,admin_url('admin.php?page=ptseo-local'))); exit; }
  public static function download(){ if(!current_user_can('manage_options')) wp_die(''); check_admin_referer('ptseo_local_dl'); $file=PTSEO_PATH.'modules/local/data/pt-admin.min.json'; if(!file_exists($file)){ wp_redirect(admin_url('admin.php?page=ptseo-local-tools')); exit; } header('Content-Type: application/json; charset=utf-8'); header('Content-Length: '.filesize($file)); header('Content-Disposition: attachment; filename=pt-admin.min.json'); readfile($file); exit; }
  public static function schema(){ if( is_admin() ) return; $locs=get_option('ptseo_local_locations', array()); if(empty($locs)) return; $name=get_bloginfo('name'); $url=home_url('/'); $graph=array(); foreach($locs as $l){ $addr=array('@type'=>'PostalAddress','addressCountry'=>'Portugal'); if(!empty($l['parish_name'])) $addr['addressLocality']=$l['parish_name']; if(!empty($l['municipality_name'])) $addr['addressRegion']=$l['municipality_name']; $area=array(); if(!empty($l['parish_name'])) $area[]=$l['parish_name']; if(!empty($l['municipality_name'])) $area[]=$l['municipality_name']; if(!empty($l['district_name'])) $area[]=$l['district_name']; $graph[]=array('@type'=>'LocalBusiness','@id'=>trailingslashit($url).'#localbusiness-'.sanitize_title(!empty($l['parish_code'])?$l['parish_code']:uniqid()),'name'=>$name,'url'=>$url,'image'=>get_site_icon_url(),'telephone'=>'','address'=>$addr,'areaServed'=>$area); }
    $json=array('@context'=>'https://schema.org','@graph'=>$graph); echo '<script type="application/ld+json">'.wp_json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>'."
"; }
}
PTSEO_Local::init();

class PTSEO_Local_Tools{
  const DSRC='https://raw.githubusercontent.com/mapaslivres/divisoes-administrativas-pt/master/data/districts.csv';
  const MSRC='https://raw.githubusercontent.com/mapaslivres/divisoes-administrativas-pt/master/data/municipalities.csv';
  const PSRC='https://raw.githubusercontent.com/mapaslivres/divisoes-administrativas-pt/master/data/freguesias.csv';
  public static function page(){ if(!current_user_can('manage_options')) return; $file=PTSEO_PATH.'modules/local/data/pt-admin.min.json'; ?>
    <div class="wrap"><h1>Ferramentas – SEO PT‑PT Local</h1>
      <p>Gerar <code>pt-admin.min.json</code> (Portugal completo – Continente + Açores + Madeira) a partir de fontes públicas com códigos INE.</p>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
        <?php wp_nonce_field('ptseo_local_gen'); ?><input type="hidden" name="action" value="ptseo_local_generate"/>
        <p><button class="button button-primary">Gerar Portugal completo (minificado)</button></p>
      </form>
      <?php if(file_exists($file)): ?><p><strong>Ficheiro atual:</strong> <?php echo esc_html($file);?> (<?php echo size_format(filesize($file));?>) <a class="button" href="<?php echo esc_url( wp_nonce_url(admin_url('admin-post.php?action=ptseo_local_download'),'ptseo_local_dl') );?>">Descarregar JSON</a></p><?php endif; ?>
    </div>
  <?php }
  private static function fetch($url){ $res=wp_remote_get($url, array('timeout'=>30)); if(is_wp_error($res)) return false; $csv=wp_remote_retrieve_body($res); if(!$csv) return false; $lines=preg_split("/
||
/",$csv); $rows=array(); $header=array(); foreach($lines as $i=>$line){ if($line==='') continue; $f=str_getcsv($line); if($i===0){ $header=$f; continue; } if(count($f)==count($header)){ $assoc=array(); for($k=0;$k<count($header);$k++){ $assoc[$header[$k]]=$f[$k]; } $rows[]=$assoc; } } return $rows; }
  public static function generate(){ if(!current_user_can('manage_options')) wp_die(''); check_admin_referer('ptseo_local_gen'); $ds=self::fetch(self::DSRC); $ms=self::fetch(self::MSRC); $ps=self::fetch(self::PSRC); if(!$ds||!$ms||!$ps) wp_die('Falha ao obter fontes. Verifique conectividade.'); $districts=array(); foreach($ds as $r){ $code=trim($r['ine_id']); $name=trim($r['name']); if($code&&$name&&$name!=='name') $districts[$code]=$name; } $municip=array(); foreach($ms as $r){ $code=trim($r['ine_id']); $name=trim($r['name']); if($code&&$name&&$name!=='name') $municip[$code]=$name; } $out=array('districts'=>array()); foreach($districts as $dc=>$dn){ $out['districts'][$dn]=array('code'=>$dc,'municipalities'=>array()); } foreach($municip as $mc=>$mn){ $dc=substr($mc,0,2); $dn=isset($districts[$dc])?$districts[$dc]:null; if(!$dn) continue; $out['districts'][$dn]['municipalities'][$mn]=array('code'=>$mc,'parishes'=>array()); } foreach($ps as $r){ $pc=trim($r['ine_id']); $pn=trim($r['name']); if(!$pc||!$pn||$pn==='name') continue; $mc=substr($pc,0,4); $dc=substr($pc,0,2); $dn=isset($districts[$dc])?$districts[$dc]:null; $mn=isset($municip[$mc])?$municip[$mc]:null; if(!$dn||!$mn) continue; $out['districts'][$dn]['municipalities'][$mn]['parishes'][$pc]=$pn; } ksort($out['districts'],SORT_NATURAL|SORT_FLAG_CASE); foreach($out['districts'] as &$d){ ksort($d['municipalities'],SORT_NATURAL|SORT_FLAG_CASE); foreach($d['municipalities'] as &$m){ ksort($m['parishes'],SORT_NATURAL|SORT_FLAG_CASE);} } $json=wp_json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); $file=PTSEO_PATH.'modules/local/data/pt-admin.min.json'; if(!file_put_contents($file,$json)) wp_die('Não foi possível escrever o ficheiro de dados.'); wp_safe_redirect( admin_url('admin.php?page=ptseo-local&done=1') ); exit; }
}
