<?php
/*
  Plugin Name: WP Sgv
  Plugin URI: http://midoriit.com/works/wp-sgv.html
  Description: Visualize SPARQL query result using Sgvizler
  Version: 0.5
  Author: Midori IT Office, LLC
  Author URI: http://midoriit.com/
  License: GPL3
  Text Domain: wp-sgv
  Domain Path: /languages/
*/

$wpsgv = new WPSgv();

class WPSgv {

  public function __construct() {
    register_activation_hook(__FILE__, array(&$this,'wpsgv_activate'));
    register_uninstall_hook(__FILE__, 'WPSgv::wpsgv_uninstall');
    add_action('admin_init', array(&$this, 'wpsgv_init'));
    add_action('admin_menu', array(&$this, 'wpsgv_menu'));
    add_shortcode('wp_sgv', array(&$this, 'wp_sgv_handler'));
    add_action('plugins_loaded', array(&$this, 'wp_sgv_loaded'));
  }

  function wp_sgv_loaded() {
    $ret = load_plugin_textdomain('wp-sgv', false,
      basename(dirname(__FILE__)).'/languages/');
  }

  public function wpsgv_activate() {
    add_option('wpsgv_width', '600');
    add_option('wpsgv_height', '600');
    add_option('wpsgv_endpoints', "http://ja.dbpedia.org/sparql\nhttp://datameti.go.jp/sparql");
  }

  public static function wpsgv_uninstall() {
    delete_option('wpsgv_width');
    delete_option('wpsgv_height');
    delete_option('wpsgv_endpoints');
  }

  public function wpsgv_init() {
    add_meta_box('sparql', __('SPARQL visualization shortcode', 'wp-sgv'), array(&$this, 'wpsgv_box'), 'post');
    add_meta_box('sparql', __('SPARQL visualization shortcode', 'wp-sgv'), array(&$this, 'wpsgv_box'), 'page');
  }

  public function wpsgv_box() {

    $width = get_option('wpsgv_width');
    $height = get_option('wpsgv_height');
    $endpoints = get_option('wpsgv_endpoints');
    $charts = array(
      'google.visualization.AnnotatedTimeLine',
      'google.visualization.AreaChart',
      'google.visualization.BarChart',
      'google.visualization.BubbleChart',
      'google.visualization.CandlestickChart',
      'google.visualization.ColumnChart',
      'google.visualization.Gauge',
      'google.visualization.GeoChart',
      'google.visualization.GeoMap',
      'google.visualization.ImageSparkLine',
      'google.visualization.LineChart',
      'google.visualization.Map',
      'google.visualization.MotionChart',
      'google.visualization.OrgChart',
      'google.visualization.PieChart',
      'google.visualization.ScatterChart',
      'google.visualization.SteppedAreaChart',
      'google.visualization.Table',
      'google.visualization.TreeMap',
      'sgvizler.visualization.DefList',
      'sgvizler.visualization.D3ForceGraph',
      'sgvizler.visualization.DraculaGraph',
      'sgvizler.visualization.List',
      'sgvizler.visualization.Map',
      'sgvizler.visualization.MapWKT',
      'sgvizler.visualization.Table',
      'sgvizler.visualization.Text'
    );

    echo '<script type="text/javascript">';
    echo 'function sgv_genshortcode() {
      sgv_shortcode.value = "[wp_sgv endpoint =\"" + sgv_endpoint.value + "\"" + 
        " chart=\"" + sgv_chart.value + "\"" +
        " options=\"" + sgv_options.value + "\"" +
        " width=\"" + sgv_width.value + "\"" +
        " height=\"" + sgv_height.value + "\"]\n" + 
        sgv_query.value +
        "\n[/wp_sgv]";
      sgv_shortcode.select();
    }';
    echo '</script>';
    echo __('Chart', 'wp-sgv').' : <select id="sgv_chart">';
    foreach($charts as $chart) {
      echo '<option value="'.$chart.'">'.$chart.'</option>';
    }
    echo '</select><br />';
    echo __('Width', 'wp-sgv').' : <input type="text" id="sgv_width" value="'.$width.'" size="5" /> ';
    echo __('Height', 'wp-sgv').' : <input type="text" id="sgv_height" value="'.$height.'" size="5" /><br />';
    echo __('Endpoint', 'wp-sgv').' : <select id="sgv_endpoint">';
    $array = explode("\n", $endpoints);
    $array = array_map('trim', $array);
    foreach($array as $endpoint) {
      echo '<option value="'.$endpoint.'">'.$endpoint.'</option>';
    }
    echo '</select><br />';
    echo __('Options', 'wp-sgv').' : <textarea id="sgv_options" rows="2" style="max-width:100%;min-width:100%"></textarea><br />';
    echo __('Query', 'wp-sgv').' : <textarea id="sgv_query" rows="3" style="max-width:100%;min-width:100%"></textarea><br />';
    echo '<a class="button" onClick="sgv_genshortcode();">'.__('Generate shortcode', 'wp-sgv').'</a><br />';
    echo '<br /><textarea id="sgv_shortcode" rows="4" style="max-width:100%;min-width:100%" onClick="this.select();" readonly></textarea><br />';
  }

  public function wp_sgv_handler($atts, $content) {

    extract( shortcode_atts(array(
      'endpoint' => '',
      'chart' => 'sgvizler.visualization.Text',
      'options' => '',
      'height' => '100',
      'width' => '100'),
        $atts ) );

    $query = str_replace(array("\r\n","\r","\n","<br />","<br>","</br>","<p>","</p>"), ' ', htmlspecialchars_decode($content));

    if( empty($endpoint) ) {
      return 'no endpoint';
    } else if( !trim($query) ) {
      return 'no query';
    }

    $uniq = uniqid('',1);

    return 
    '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="http://beta.data2000.no/sgvizler/release/0.6/sgvizler.js"></script>
    <div id="sgvdiv'.$uniq.'"></div>
    <script type="text/javascript">
      var sparqlQueryString = "'.$query.'";
      q = new sgvizler.Query(null, {'.$options.'});
      q.query(sparqlQueryString)
        .endpointURL("'.$endpoint.'")
        .endpointOutputFormat("json")
        .chartFunction("'.$chart.'")
        .chartHeight("'.$height.'")
        .chartWidth("'.$width.'")
      .draw("sgvdiv'.$uniq.'");
    </script>';
  }

  function wpsgv_menu() {
    add_options_page(__('WP Sgv Options', 'wp-sgv'), 'WP Sgv', 'manage_options',
      'wp_sgv', array(&$this, 'wpsgv_options'));
  }

  function wpsgv_options() {
    if ( !current_user_can('manage_options')) {
      wp_die( __('insufficient permissions.') );
    }

    if (isset($_POST['update_option'])) {
      check_admin_referer('wpsgv_options');
      $width = $_POST['wpsgv_width'];
      if(is_numeric($width)){
        update_option('wpsgv_width', $width);
      }
      $height = $_POST['wpsgv_height'];
      if(is_numeric($height)){
        update_option('wpsgv_height', $height);
      }
      $endpoints = $_POST['wpsgv_endpoints'];
      update_option('wpsgv_endpoints', $endpoints);
    }

    $width = get_option('wpsgv_width');
    $height = get_option('wpsgv_height');
    $endpoints = get_option('wpsgv_endpoints');

    echo '<div><h2>'.__('WP Sgv Options', 'wp-sgv').'</h2>';
    echo '<form name="wpsgv_form" method="post" action="">';
    wp_nonce_field('wpsgv_options');
    echo '<table class="form-table"><tbody>';
    echo '<tr><td>'.__('Default Width', 'wp-sgv').'</td>';
    echo '<td><input type="text" name="wpsgv_width" value="'.$width.'" size="20"></td></tr>';
    echo '<tr><td>'.__('Default Height', 'wp-sgv').'</td>';
    echo '<td><input type="text" name="wpsgv_height" value="'.$height.'" size="20"></td></tr>';
    echo '<tr><td>'.__('Endpoints', 'wp-sgv').'</td>';
    echo '<td><textarea name="wpsgv_endpoints" rows="3" style="max-width:100%;min-width:100%">'.$endpoints.'</textarea></td></tr>';
    echo '</tbody></table>';
    echo '<input type="submit" name="update_option" class="button button-primary" value="'.esc_attr__('Save Changes').'" />';
    echo '</form>';
    echo '</div>';
  }

}
?>
