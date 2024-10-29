<?php

/*
Plugin Name: AdLemons
Plugin URI: http://adlemons.org/wordpress-plugin
Description: Este es el plugin de AdLemons para WordPress
Author: Efectividads Social Ads, S.L.
Version: 1.0
Author URI: http://efectividads.com
 
/*
  Copyright 2011  AdLemons  (email : info at adlemons.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// plugin class adlemons
class adlemons {
	var $opt;
	var $table_name;
	var $blogurl = '';
	var $zones;

	// ---------------------------------------------------------------- constructor
	function adlemons()
	{
		global $wpdb;
		
		$this->table_name	= $wpdb->prefix.'adlemons';
		$this->blogurl		= get_bloginfo('url');
		
		$zones = $this->get_zones_from_api($this->blogurl);
		$this->zones = $zones;
		
		add_action('admin_menu', array(&$this, 'adlemons_menu'));
	}

	// ---------------------------------------------------------------------- menu
	function adlemons_menu()
	{
		$iconlink = plugins_url().'/'. substr(plugin_basename(__FILE__),0,strpos(plugin_basename(__FILE__),"/")) . '/menu_icon.png';
		add_menu_page('AdLemons Plugin', 'AdLemons', 'administrator', 8, array(&$this,'overview'), $iconlink);
	}

	function overview()
	{
		$blogurl		= $this->blogurl;
		
		$this->print_overview_info();
		$zones = $this->get_zones_from_api($blogurl);
		
		$this->zones = $zones;
	}

	// creamos la subpagina de opciones
	function print_overview_info()
	{
		echo '<div class="wrap">';
		echo 	'<h2>AdLemons</h2>';
		echo 		'<div style="background-color: #F6F6F6; border: 1px solid #CCCCCC; float: left; margin: 10px; padding: 10px; width: 760px;">';
		echo 			__('Ya has instalado el plugin de AdLemons para WordPress. Enhorabuena!', 'adlemons');
		echo			'<br/><br/>';
		echo 			__('Ahora solo te queda colocar las zonas en su lugar, para hacerlo acude a <b> Apariencia > Widgets</b> y arrastra cada widget de zona creada en AdLemons en su sitio del sidebar y ¡listo! <br/>', 'adlemons');
		echo			'<br/>';
		echo 			__('Si tienes dudas puedes acudir a la base de conocimiento de AdLemons <a href="http://adlemons.com/base">http://adlemons.com/base</a> o ponerte en contacto con nosotros en: <a href="http://adlemons.com/es/contacto">http://adlemons.com/es/contacto</a>', 'adlemons');
		
		$code = "";
		if(is_string($zones))
		{
			$zones = json_decode($this->zones);
			$code  = $zones[0]->code;
		}
		
		if($code == '00')
		{
			echo '<br>';
			echo '<div style="background-color:#FFFBCC;border:1px solid #E6DB55;margin:10px 0;padding:5px;">';
			echo __('Atenci&oacute;n: No hemos encontrado zonas para tu blog en AdLemons','adlemons');
			echo '</div>';
			echo __('Puede ser por dos motivos: ','adlemons');
			echo 	'<br><br>';
			echo 	'<ol>';
			echo 		__('<li>Todav&iacute;a no has dado de alta tu blog en AdLemons, en ese caso, puedes hacerlo registr&aacute;ndote en <a href="http://panel.adlemons.com/register">http://panel.adlemons.com/register</a> y d&aacute;ndo de alta el blog y las zonas publicitarias que quieras vender, luego vuelve aqu&iacute; y actualiza esta p&aacute;gina. </li><br>','adlemons');
			echo 		__('<li>Existe alguna diferencia entre el el dominio del blog dado de alta en AdLemons y el que aparece en la barra de navegaci&oacute;n realmente, en ese caso te recomendamos que des de alta de nuevo el blog con la url correcta, para que el plugin pueda detectarla sin problemas.</li>','adlemons');
			echo 	'</ol>';
			echo '</div>';
		}
		echo '</div>';
		
	}

	function get_zones_from_api($urlparam)
	{
		if(!empty($urlparam))
		{
			$zonesjson = $this->do_post_request('http://panel.adlemons.com/api/get_zones_by_url', 'site_url='.$urlparam);
			$zones = json_decode($zonesjson);
		}else{
			$zones = false;
		}
		
		return $zones;
	}

	function create_adlemonsZone_widget($widget_id, $widget_name, $widget_callback, $widget_loc, $widget_param)
	{
		global $wpdb;
		wp_register_sidebar_widget($widget_id, $widget_name, $widget_callback, $widget_param);
	}

	// página de configuración
	function config_page() {
		add_options_page('AdLemons', 'AdLemons', 8, 'AdLemons_options', array(&$this, 'options_plugin'));
	}

	// ----- Do a PHP Post without cURL
	function do_post_request($url, $params)
	{
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	// ejecuta resultado
	function init($atts) {
		global $wpdb;
	
		// options (config)
		$this->opt = $wpdb->get_row('SELECT * FROM '.$this->table_name.' LIMIT 1', ARRAY_A);
	
		// atributes (user)
		extract(shortcode_atts(array('frase' => $this->opt['frase']), $atts));
		include('file.php');
	}
}

// END class adlemons ------------------------------------------------------------------------

// -------------------------------------------------------------------------- widget wordpress
	function widget_adlemons_init()
	{
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( 'adlemons', false, $plugin_dir );
		
		$adlemonsPlugin	= new adlemons();
		$zones = $adlemonsPlugin->zones;
		
		$code = "";
		
		if(is_string($zones))
		{
			$nozonecodearray = json_decode($zones);
			$code  = $nozonecodearray[0]->code;
		}
		
		$params = "uh";
		
		$i = 0;
		if($code != '00')
		{
			foreach($zones as $zone)
			{
				//Obtemeos el script code <script... >
				$params = widget_adlemons_get_scriptcode($zone->zone_url);
			
				//Rellenamos su contenedor widget_adlemons.$i
				wp_register_sidebar_widget('widget_adlemons'.$i, "AdLemons :: ".$zone->zone_name, 'widget_adlemons'.$i, $options="", $params);
			
				//incrementamos contador $i
				$i = $i + 1;
			}
		}
		widget_adlemons_check_widgets($zones);
	}
	
	// Check if a widget is allocated or not
	function widget_adlemons_check_widgets($zones)
	{
		$i=0;
		
		$url	= "http://panel.adlemons.com/api/update_zonelocation";
		$hash	= "Z8JuY74zRr";
		
		$code = "";
		
		if(is_string($zones))
		{
			$nozonecodearray = json_decode($zones);
			$code  = $nozonecodearray[0]->code;
		}
		
		if($code != '00')
		{
			foreach($zones as $zone)
			{
				//Si la zona está colocada y su estado inicial no lo estaba, lo seteamos a colocada
				if(is_active_widget('widget_adlemons'.$i) && $zone->zone_loc != 1)
					widget_adlemons_do_post_request($url, 'hash='.$hash.'&zone_id='.$zone->zone_id.'&zone_loc=1');
			
				//Si la zona inicial no está colocada, y su estado inicial si lo estaba, lo seteamos a no colocada
				if(!is_active_widget('widget_adlemons'.$i) && $zone->zone_loc == 1)
					widget_adlemons_do_post_request($url, 'hash='.$hash.'&zone_id='.$zone->zone_id.'&zone_loc=0');
			
				$i += 1;
			}
		}
	}
	
	// ----- Do a PHP Post without cURL
	function widget_adlemons_do_post_request($url, $params)
	{
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	function widget_adlemons_get_scriptcode($jsurl)
	{
		$scriptcode = "<script src='".$jsurl."' type='text/javascript' charset='UTF-8' ></script>";
		return $scriptcode;
	}
	
	
	function widget_adlemons_create_widgets()
	{
		$zones			= $adlemonsPlugin->get_zones_from_api(get_bloginfo('url'));
		print_r($zones);
	}


// -------------------------------------------- Widget Containers

	// Contenido widget
	function widget_adlemons0($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons1($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons2($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons3($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons4($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons5($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons6($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons7($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons8($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

	// Contenido widget
	function widget_adlemons9($args, $params)
	{
		extract($args); // extrae before_widget, before_title, after_title, after_widget
		echo $params;
	}

// End Widget Containers -------------------------------------------- 

	// cargamos widgets al cargar WordPress
	add_action("plugins_loaded", "widget_adlemons_init");
// -----------------------------------------------------------------


?>
