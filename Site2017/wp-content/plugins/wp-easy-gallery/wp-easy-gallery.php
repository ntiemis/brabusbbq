<?php
	/*
	Plugin Name: WP Easy Gallery
	Plugin URI: http://plugingarden.com/wordpress-gallery-plugin/
	Description: Wordpress Plugin for creating dynamic photo galleries	
	Author: HahnCreativeGroup
	Version: 4.3.6
	Author URI: http://plugingarden.com/
	*/
	
if (!class_exists("WP_Easy_Gallery")) {
	class WP_Easy_Gallery {	
	
		//Constructor
		public function __construct() {
			$this->plugin_name = plugin_basename(__FILE__);
			$this->plugin_version = "4.3.6";
			$this->db_version = "1.3";
			
			$this->define_tables();
			$this->define_constants();
			$this->define_options();
			
			register_activation_hook( $this->plugin_name,  array(&$this, 'create_database') );
			
			add_action('wp_enqueue_scripts', array($this, 'add_scripts'));
			add_action('wp_head', array($this, 'wp_custom_style'));
			add_action( 'admin_menu', array($this, 'add_wpeg_menu'));
			
			add_shortcode('EasyGallery', array($this, 'EasyGallery_Handler'));
			add_action( 'wp_ajax_wpeg_shortcode', array($this, 'wpeg_shortcode_callback'));
			
			add_action( 'plugins_loaded', array($this, 'update_db_check') );
		}
		
		public function define_tables() {
			global $wpdb;
			
			$wpdb->easyGalleries = $wpdb->prefix . 'easy_gallery';
			$wpdb->easyImages = $wpdb->prefix . 'easy_gallery_images';
		}
		
		public function define_constants()
		{
			if ( ! defined( 'HCGGALLERY_PLUGIN_BASENAME' ) )
				define( 'HCGGALLERY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	
			if ( ! defined( 'HCGGALLERY_PLUGIN_NAME' ) )
				define( 'HCGGALLERY_PLUGIN_NAME', trim( dirname( HCGGALLERY_PLUGIN_BASENAME ), '/' ) );
		
			if ( ! defined( 'HCGGALLERY_PLUGIN_DIR' ) )
				define( 'HCGGALLERY_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . HCGGALLERY_PLUGIN_NAME );
		}
		
		public function define_options() {
			if(!get_option('wp_easy_gallery_defaults')) {
				$gallery_options = array(
					'version'		   		=> 'free',
					'thumbnail_width'  		=> 'auto',
					'thumbnail_height' 		=> 'auto',
					'hide_overlay'	   		=> 'false',
					'hide_social'	   		=> 'false',
					'custom_style'	   		=> '',
					'use_default_style'		=> 'true',
					'drop_shadow'			=> 'true',
					'display_mode'	   => 'wp_easy_gallery',
					'num_columns'	   => 3,
					'show_gallery_name'=> 'true',
					'gallery_name_alignment' => 'left'
				);
				
				add_option('wp_easy_gallery_defaults', $gallery_options);
			}
			else {
				$wpEasyGalleryOptions	= get_option('wp_easy_gallery_defaults');
				$keys = array_keys($wpEasyGalleryOptions);
				
				if (!in_array('version', $keys)) {
					$wpEasyGalleryOptions['version'] = $this->plugin_version;	
				}				
				if (!in_array('hide_overlay', $keys)) {
					$wpEasyGalleryOptions['hide_overlay'] = "false";	
				}
				if (!in_array('hide_social', $keys)) {
					$wpEasyGalleryOptions['hide_social'] = "false";	
				}
				if (!in_array('custom_style', $keys)) {
					$wpEasyGalleryOptions['custom_style'] = "";	
				}
				if (!in_array('use_default_style', $keys)) {
					$wpEasyGalleryOptions['use_default_style'] = "true";	
				}
				if (!in_array('drop_shadow', $keys)) {
					$wpEasyGalleryOptions['drop_shadow'] = "true";	
				}
				if (!in_array('display_mode', $keys)) {
					$wpEasyGalleryOptions['display_mode'] = "wp_easy_gallery";	
				}
				if (!in_array('num_columns', $keys)) {
					$wpEasyGalleryOptions['num_columns'] = 3;	
				}
				if (!in_array('thumbnail_height', $keys)) {
					$wpEasyGalleryOptions['thumbnail_height'] = $wpEasyGalleryOptions['thunbnail_height'];
					unset($wpEasyGalleryOptions['thunbnail_height']);
				}
				if (!in_array('show_gallery_name', $keys)) {
					$wpEasyGalleryOptions['show_gallery_name'] = "true";	
				}
				if (!in_array('gallery_name_alignment', $keys)) {
					$wpEasyGalleryOptions['gallery_name_alignment'] = "left";	
				}
				
				update_option('wp_easy_gallery_defaults', $wpEasyGalleryOptions);	
			}
		}
		
		public function create_database() {
			include_once (dirname (__FILE__) . '/lib/install.php');
			
			install_db();
		}
		
		public function update_database() {
			global $wpdb;			
			$installed_ver = get_option('easy_gallery_db_version');

			$easyGallery = $wpdb->prefix . 'easy_gallery';
			$easyImages = $wpdb->prefix . 'easy_gallery_images';
			
			//Upgrade version 1.2 -> 1.3
			if ( $wpdb->get_var( "show tables like '$easyGallery'" ) == $easyGallery && version_compare($installed_ver, '1.3', '<')) {
				$wpdb->query("ALTER TABLE $easyGallery MODIFY name VARCHAR( 60 ) NOT NULL");
				$wpdb->query("ALTER TABLE $easyGallery MODIFY slug VARCHAR( 60 ) NOT NULL");
			}
			
			update_option('easy_gallery_db_version', $this->db_version);
		}
		
		public function update_db_check() {			
			if (get_option('easy_gallery_db_version') != $this->db_version) {				
				$this->update_database();
			}
		}
		
		public function add_scripts() {
			$wpEasyGalleryOptions = get_option('wp_easy_gallery_defaults');
			wp_enqueue_script('jquery');
			wp_register_script('prettyPhoto', plugins_url( '/js/jquery.prettyPhoto.js', __FILE__ ), array('jquery'));
			wp_register_script('easyGalleryTheme', plugins_url( '/js/EasyGallery_Theme.js', __FILE__ ), array('prettyPhoto', 'jquery'));
			wp_register_script('easyGalleryLoader', plugins_url( '/js/EasyGalleryLoader.js', __FILE__ ), array('prettyPhoto', 'jquery'));
		
			wp_enqueue_script('prettyPhoto');
			wp_enqueue_script('easyGalleryTheme');
			wp_enqueue_script('easyGalleryLoader');
			wp_register_style( 'prettyPhoto_stylesheet', plugins_url( '/css/prettyPhoto.css', __FILE__ ));
			wp_enqueue_style('prettyPhoto_stylesheet');
			if ($wpEasyGalleryOptions['use_default_style'] == 'true') {
				wp_register_style('easy-gallery-style', plugins_url( '/css/default.css', __FILE__ ));
				wp_enqueue_style('easy-gallery-style');
			}
		}
		
		public function wp_custom_style() {
			$styles = get_option('wp_easy_gallery_defaults');		
			$show_overlay = ($styles['hide_overlay'] == 'true') ? 'false' : 'true';
			$show_social = ($styles['hide_social'] == 'true') ? ', show_social: false' : '';
			echo "<!-- WP Easy Gallery -->\n<style>.wp-easy-gallery img {".$styles['custom_style']."}</style>";
			echo "<script>var wpegSettings = {gallery_theme: '".$styles['gallery_theme']."', show_overlay: ".$show_overlay.$show_social."};</script>";		
		}
		
		public function easy_gallery_admin_scripts() {
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_register_script('easy-gallery-uploader', plugins_url( '/js/image-uploader.js', __FILE__ ), array('jquery','media-upload','thickbox'));
			wp_enqueue_script('easy-gallery-uploader');	  
		}
		
		// Create Admin Panel
		public function add_wpeg_menu()
		{
			add_menu_page(__('WP Easy Gallery','menu-hcg'), __('WP Easy Gallery','menu-hcg'), 'manage_options', 'hcg-admin', array($this, 'show_overview') );

			// Add a submenu to the custom top-level menu:
			add_submenu_page('hcg-admin', __('WP Easy Gallery >> Add Gallery','menu-hcg'), __('Add Gallery','menu-hcg'), 'manage_options', 'add-gallery', array($this, 'add_gallery'));
		
			// Add a submenu to the custom top-level menu:
			add_submenu_page('hcg-admin', __('WP Easy Gallery >> Edit Gallery','menu-hcg'), __('Edit Gallery','menu-hcg'), 'manage_options', 'edit-gallery', array($this, 'edit_gallery'));

			// Add a second submenu to the custom top-level menu:
			add_submenu_page('hcg-admin', __('WP Easy Gallery >> Add Images','menu-hcg'), __('Add Images','menu-hcg'), 'manage_options', 'add-images', array($this, 'add_images'));
		
			// Add a second submenu to the custom top-level menu:
			add_submenu_page('hcg-admin', __('WP Easy Gallery >> Settings','menu-hcg'), __('Settings','menu-hcg'), 'manage_options', 'wpeg-settings', array($this, 'wpeg_settings'));
		
			// Add a second submenu to the custom top-level menu:
			add_submenu_page('hcg-admin', __('WP Easy Gallery >> Help (FAQ)','menu-hcg'), __('Help (FAQ)','menu-hcg'), 'manage_options', 'help', array($this, 'show_help'));
		
			wp_register_style('easy-gallery-admin-style', plugins_url( '/css/wp-easy-gallery-admin.css', __FILE__ ));
			wp_enqueue_style('easy-gallery-admin-style');
		}
	
		public function show_overview()
		{
			include("admin/overview.php");
		}
	
		public function add_gallery()
		{
			include("admin/add-gallery.php");
			$this->easy_gallery_admin_scripts();
		}
	
		public function edit_gallery()
		{
			include("admin/edit-gallery.php");
			$this->easy_gallery_admin_scripts();
		}
	
		public function add_images()
		{
			include("admin/add-images.php");
			$this->easy_gallery_admin_scripts();
			$this->add_scripts();
		}
	
		public function wpeg_settings()
		{
			include("admin/wpeg-settings.php");
		}
	
		public function show_help()
		{
			include("admin/help.php");
		}
		
		public function EasyGallery_Handler($atts) {
			$atts = shortcode_atts( array( 'id' => '-1', 'key' => '-1'), $atts );
			return $this->createEasyGallery($atts['id'], $atts['key']);
		}
	
		// function creates the gallery
		public function createEasyGallery($galleryName, $id)	
		{			
			global $wpdb;
			global $easy_gallery_table;
			global $easy_gallery_image_table;
		
			if ($id != "-1") {
				$gallery = $wpdb->get_row( "SELECT Id, name, thumbnail, thumbwidth, thumbheight FROM $wpdb->easyGalleries WHERE Id = '$id'" );
			}
			else {
				$gallery = $wpdb->get_row( "SELECT Id, name, thumbnail, thumbwidth, thumbheight FROM $wpdb->easyGalleries WHERE slug = '$galleryName'" );
			}
			$imageResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyImages WHERE gid = $gallery->Id ORDER BY sortOrder ASC" );
			$options = get_option('wp_easy_gallery_defaults');
			$galleryLink = "";
		
			switch($options['display_mode']) {
				case 'wp_easy_gallery':
					$galleryLink = $this->render_wpeg($gallery, $imageResults, $options);
					break;
				case 'wp_default':
					$galleryLink = $this->render_wp_gallery($gallery, $imageResults, $options);
					break;
				default:
					$galleryLink = $this->render_wpeg($gallery, $imageResults, $options);
					break;
			}
		
			return $galleryLink;
		}
		
		public function render_wpeg($gallery, $imageResults, $options) {
			$images = array();
			$descriptions = array();
			$titles = array();
			$i = 0;
			$thumbImage = $gallery->thumbnail;		
		
			foreach($imageResults as $image)
			{
				if($i == 0)
					$thumbImage = (strlen($gallery->thumbnail) > 0) ? $gallery->thumbnail : $image->imagePath;
				$images[$i] = "'".$image->imagePath."'";
				$descriptions[$i] = "'".$image->description."'";
				$titles[$i] = "'".$image->title."'";
				$i++;
			}
		
			$img = implode(", ", $images);
			$desc = implode(", ", $descriptions);
			$ttl = implode(", ", $titles);
		
			$thumbwidth = ($gallery->thumbwidth < 1 || $gallery->thumbwidth == "auto") ? "" : "width='".$gallery->thumbwidth."'";
			$thumbheight = ($gallery->thumbheight < 1 || $gallery->thumbheight == "auto") ? "" : "height='".$gallery->thumbheight."'";		
		
			$dShadow = ($options['drop_shadow'] == "true") ? "class=\"dShadow trans\"" : "";
			$showName = ($options['show_gallery_name'] == "true") ? "<span class=\"wpeg-gallery-name ".$options['gallery_name_alignment']."\">".$gallery->name."</span>" : "";
		
			$galleryMarkup = "<span class=\"wp-easy-gallery\"><a onclick=\"var images=[".$img."]; var titles=[".$ttl."]; var descriptions=[".$desc."]; jQuery.prettyPhoto.open(images,titles,descriptions);\" title=\"".$gallery->name."\" style=\"cursor: pointer;\"><img ".$dShadow." src=\"".$thumbImage."\" ".$thumbwidth." ".$thumbheight." border=\"0\" alt=\"".$gallery->name."\" />".$showName."</a></span>";
		
			return $galleryMarkup;
		}
		
		public function render_wp_gallery($gallery, $imageResults, $options) {
			$numColumns = $options['num_columns'];
			$showName = $options['show_gallery_name'];
			$galleryMarkup = "<style type='text/css'>#gallery-".$gallery->Id." {margin: auto;}	#gallery-".$gallery->Id." .gallery-item {float: left;margin-top: 10px;text-align: center;width: ".floor(100 / $numColumns)."%;} #gallery-".$gallery->Id." img {border: 2px solid #cfcfcf;}	#gallery-".$gallery->Id." .gallery-caption {margin-left: 0;}</style>";
			$galleryMarkup .= "<div id='gallery-".$gallery->Id."' class='gallery gallery-columns-".$numColumns." gallery-size-thumbnail'>";
			if ($showName == 'true') {
				$galleryMarkup .= "<h4 class=\"wpeg-gallery-name ".$options['gallery_name_alignment']."\">".$gallery->name."</h4>";
			}
		
			foreach($imageResults as $image) {
				$galleryMarkup .= "<dl class=gallery-item>";
				$galleryMarkup .= "<dt class='gallery-icon landscape'>";
				$galleryMarkup .= "<a href='".$image->imagePath."' rel='prettyPhoto' title='".$image->title."'>";
				$galleryMarkup .= "<img width='150' height='150' src='".$image->imagePath."' class='attachment-thumbnail' alt='".$image->title."'>";
				$galleryMarkup .= "</a>";
				$galleryMarkup .= "</dt>";
				$galleryMarkup .= "<dd class='wp-caption-text gallery-caption'>";
				$galleryMarkup .= $image->title;
				$galleryMarkup .= "</dd>";
				$galleryMarkup .= "</dl>";
			}
		
			$galleryMarkup .= "<br style='clear: both'></div>";
		
			return $galleryMarkup;
		}
		
		public function wpeg_shortcode_callback() {
			global $wpdb; // this is how you get access to the database
			global $easy_gallery_table;

			$galleryResults = $wpdb->get_results( "SELECT Id, name FROM $wpdb->easyGalleries" );
			$count = 0;
		
			$result = '{ "wpEasyGallery": [';
			foreach($galleryResults as $gallery) {
				$count++;
				$result .= '{ "id": "'.$gallery->Id.'", "name": "'.$gallery->name.'"}';
				if ($count < count($galleryResults)) { $result .= ","; }
			} 
			$result .= ']}';

			echo $result;

			wp_die(); // this is required to terminate immediately and return a proper response
		}
	
	}
}

if (class_exists("WP_Easy_Gallery")) {
    global $ob_WP_Easy_Gallery;
	$ob_WP_Easy_Gallery = new WP_Easy_Gallery();
}
	
	/* ==================================================================================
	 * Create custom database table
	 * ================================================================================== 
	 */
	 
	 
	/*
	global $wpdb;
	global $easy_gallery_table;
	global $easy_gallery_image_table;
	global $easy_gallery_db_version;	
	$easy_gallery_table = $wpdb->prefix . 'easy_gallery';
	$easy_gallery_image_table = $wpdb->prefix . 'easy_gallery_images';
	$easy_gallery_db_version = '1.3';
		
	
	
	function easy_gallery_install() {
	  global $wpdb;
	  global $easy_gallery_table;
	  global $easy_gallery_image_table;
	  global $easy_gallery_db_version;
	
	  if ( $wpdb->get_var( "show tables like '$easy_gallery_table'" ) != $easy_gallery_table ) {
				
		$sql = "CREATE TABLE $easy_gallery_table (".
			"Id INT NOT NULL AUTO_INCREMENT, ".
			"name VARCHAR( 60 ) NOT NULL, ".
			"slug VARCHAR( 60 ) NOT NULL, ".
			"description TEXT NOT NULL, ".
			"thumbnail LONGTEXT NOT NULL, ".
			"thumbwidth INT, ".
			"thumbheight INT, ".
			"PRIMARY KEY Id (Id) ".
			")";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		$sql = "CREATE TABLE $easy_gallery_image_table (".
				"Id INT NOT NULL AUTO_INCREMENT, ".
				"gid INT NOT NULL, ".
				"imagePath LONGTEXT NOT NULL, ".
				"title VARCHAR( 50 ) NOT NULL, ".
				"description LONGTEXT NOT NULL, ".
				"sortOrder INT NOT NULL, ".
				"PRIMARY KEY Id (Id) ".
				")";

	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		add_option( "easy_gallery_db_version", $easy_gallery_db_version );
	  }
	}
	*/
	/*
	function update_database() {
		global $wpdb;
		global $easy_gallery_db_version;
		
		$installed_ver = get_option('easy_gallery_db_version');

		$easyGallery = $wpdb->prefix . 'easy_gallery';
		$easyImages = $wpdb->prefix . 'easy_gallery_images';
			
		//Upgrade version 1.2 -> 1.3
		if ( $wpdb->get_var( "show tables like '$easyGallery'" ) == $easyGallery && version_compare($installed_ver, '1.3', '<')) {
			$wpdb->query("ALTER TABLE $easyGallery MODIFY name VARCHAR( 60 ) NOT NULL");
			$wpdb->query("ALTER TABLE $easyGallery MODIFY slug VARCHAR( 60 ) NOT NULL");
		}
			
		update_option('easy_gallery_db_version', $easy_gallery_db_version);
	}
		
	function update_db_check() {			
		global $easy_gallery_db_version;
			
		if (get_option('easy_gallery_db_version') != $easy_gallery_db_version) {				
			update_database();
		}
	}
	add_action( 'plugins_loaded', 'update_db_check' );
	*/
	
	/* ==================================================================================
	 * Include JS File in Header
	 * ================================================================================== 
	 */
	 
	 /*
	 function define_options() {
		 if(!get_option('wp_easy_gallery_defaults')) {
				$gallery_options = array(
					'version'		   		=> 'free',
					'thumbnail_width'  		=> 'auto',
					'thumbnail_height' 		=> 'auto',
					'hide_overlay'	   		=> 'false',
					'hide_social'	   		=> 'false',
					'custom_style'	   		=> '',
					'use_default_style'		=> 'true',
					'drop_shadow'			=> 'true',
					'display_mode'	   => 'wp_easy_gallery',
					'num_columns'	   => 3,
					'show_gallery_name'=> 'true',
					'gallery_name_alignment' => 'left'
				);
				
				add_option('wp_easy_gallery_defaults', $gallery_options);
			}
			else {
				$wpEasyGalleryOptions	= get_option('wp_easy_gallery_defaults');
				$keys = array_keys($wpEasyGalleryOptions);
				
				if (!in_array('version', $keys)) {
					$wpEasyGalleryOptions['version'] = $this->plugin_version;	
				}				
				if (!in_array('hide_overlay', $keys)) {
					$wpEasyGalleryOptions['hide_overlay'] = "false";	
				}
				if (!in_array('hide_social', $keys)) {
					$wpEasyGalleryOptions['hide_social'] = "false";	
				}
				if (!in_array('custom_style', $keys)) {
					$wpEasyGalleryOptions['custom_style'] = "";	
				}
				if (!in_array('use_default_style', $keys)) {
					$wpEasyGalleryOptions['use_default_style'] = "true";	
				}
				if (!in_array('drop_shadow', $keys)) {
					$wpEasyGalleryOptions['drop_shadow'] = "true";	
				}
				if (!in_array('display_mode', $keys)) {
					$wpEasyGalleryOptions['display_mode'] = "wp_easy_gallery";	
				}
				if (!in_array('num_columns', $keys)) {
					$wpEasyGalleryOptions['num_columns'] = 3;	
				}
				if (!in_array('thumbnail_height', $keys)) {
					$wpEasyGalleryOptions['thumbnail_height'] = $wpEasyGalleryOptions['thunbnail_height'];
					unset($wpEasyGalleryOptions['thunbnail_height']);
				}
				if (!in_array('show_gallery_name', $keys)) {
					$wpEasyGalleryOptions['show_gallery_name'] = "true";	
				}
				if (!in_array('gallery_name_alignment', $keys)) {
					$wpEasyGalleryOptions['gallery_name_alignment'] = "left";	
				}
				
				update_option('wp_easy_gallery_defaults', $wpEasyGalleryOptions);	
			}
	 }
	 add_action('init', 'define_options');
	 */
	 /*
	 function wp_custom_style() {
		$styles = get_option('wp_easy_gallery_defaults');		
		$show_overlay = ($styles['hide_overlay'] == 'true') ? 'false' : 'true';
		$show_social = ($styles['hide_social'] == 'true') ? ', show_social: false' : '';
		echo "<!-- WP Easy Gallery -->\n<style>.wp-easy-gallery img {".$styles['custom_style']."}</style>";
		echo "<script>var wpegSettings = {gallery_theme: '".$styles['gallery_theme']."', show_overlay: ".$show_overlay.$show_social."};</script>";		
	}
	add_action('wp_head', 'wp_custom_style');
	*/
	
	/*
	function attach_EasyGallery_scripts() {
		$wpEasyGalleryOptions = get_option('wp_easy_gallery_defaults');
		wp_enqueue_script('jquery');
		wp_register_script('prettyPhoto', plugins_url( '/js/jquery.prettyPhoto.js', __FILE__ ), array('jquery'));
		wp_register_script('easyGalleryTheme', plugins_url( '/js/EasyGallery_Theme.js', __FILE__ ), array('prettyPhoto', 'jquery'));
		wp_register_script('easyGalleryLoader', plugins_url( '/js/EasyGalleryLoader.js', __FILE__ ), array('prettyPhoto', 'jquery'));
		
		wp_enqueue_script('prettyPhoto');
		wp_enqueue_script('easyGalleryTheme');
		wp_enqueue_script('easyGalleryLoader');
		wp_register_style( 'prettyPhoto_stylesheet', plugins_url( '/css/prettyPhoto.css', __FILE__ ));
		wp_enqueue_style('prettyPhoto_stylesheet');
		if ($wpEasyGalleryOptions['use_default_style'] == 'true') {
			wp_register_style('easy-gallery-style', plugins_url( '/css/default.css', __FILE__ ));
	  		wp_enqueue_style('easy-gallery-style');
		}
	}
	add_action('wp_enqueue_scripts', 'attach_EasyGallery_scripts');
	*/
	/*
	function attach_Easy_Gallery_JS()
	{
		if ( ! defined( 'HCGGALLERY_PLUGIN_BASENAME' ) )
		define( 'HCGGALLERY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	
		if ( ! defined( 'HCGGALLERY_PLUGIN_NAME' ) )
			define( 'HCGGALLERY_PLUGIN_NAME', trim( dirname( HCGGALLERY_PLUGIN_BASENAME ), '/' ) );
		
		if ( ! defined( 'HCGGALLERY_PLUGIN_DIR' ) )
			define( 'HCGGALLERY_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . HCGGALLERY_PLUGIN_NAME );
	}
	
	add_action ('wp_head', 'attach_Easy_Gallery_JS');
	*/
	/*
	function easy_gallery_admin_scripts() {
	  wp_enqueue_style('thickbox');
	  wp_enqueue_script('media-upload');
	  wp_enqueue_script('thickbox');
	  wp_register_script('easy-gallery-uploader', plugins_url( '/js/image-uploader.js', __FILE__ ), array('jquery','media-upload','thickbox'));
	  wp_enqueue_script('easy-gallery-uploader');	  
	}
	*/
	/*
	// Create Admin Panel
	function add_hcg_menu()
	{
		add_menu_page(__('WP Easy Gallery','menu-hcg'), __('WP Easy Gallery','menu-hcg'), 'manage_options', 'hcg-admin', 'showHcgMenu' );

		// Add a submenu to the custom top-level menu:
		add_submenu_page('hcg-admin', __('WP Easy Gallery >> Add Gallery','menu-hcg'), __('Add Gallery','menu-hcg'), 'manage_options', 'add-gallery', 'add_gallery');
		
		// Add a submenu to the custom top-level menu:
		add_submenu_page('hcg-admin', __('WP Easy Gallery >> Edit Gallery','menu-hcg'), __('Edit Gallery','menu-hcg'), 'manage_options', 'edit-gallery', 'edit_gallery');

		// Add a second submenu to the custom top-level menu:
		add_submenu_page('hcg-admin', __('WP Easy Gallery >> Add Images','menu-hcg'), __('Add Images','menu-hcg'), 'manage_options', 'add-images', 'add_images');
		
		// Add a second submenu to the custom top-level menu:
		add_submenu_page('hcg-admin', __('WP Easy Gallery >> Settings','menu-hcg'), __('Settings','menu-hcg'), 'manage_options', 'wpeg-settings', 'wpeg_settings');
		
		// Add a second submenu to the custom top-level menu:
		add_submenu_page('hcg-admin', __('WP Easy Gallery >> Help (FAQ)','menu-hcg'), __('Help (FAQ)','menu-hcg'), 'manage_options', 'help', 'help');
		
		wp_register_style('easy-gallery-admin-style', plugins_url( '/css/wp-easy-gallery-admin.css', __FILE__ ));
	  	wp_enqueue_style('easy-gallery-admin-style');
	}
	
	add_action( 'admin_menu', 'add_hcg_menu' );
	*/
	/*
	function showHcgMenu()
	{
		include("admin/overview.php");
	}
	
	function add_gallery()
	{
		include("admin/add-gallery.php");
		easy_gallery_admin_scripts();
	}
	
	function edit_gallery()
	{
		include("admin/edit-gallery.php");
		easy_gallery_admin_scripts();
	}
	
	function add_images()
	{
		include("admin/add-images.php");
		easy_gallery_admin_scripts();
		attach_EasyGallery_scripts();
	}
	
	function wpeg_settings()
	{
		include("admin/wpeg-settings.php");
	}
	
	function help()
	{
		include("admin/help.php");
	}
	*/
	
	/* ==================================================================================
	 * Gallery Creation Filter
	 * ================================================================================== 
	 */
	 /*
	// function creates the gallery
	function createEasyGallery($galleryName, $id)	
	{			
		global $wpdb;
		global $easy_gallery_table;
		global $easy_gallery_image_table;
		
		if ($id != "-1") {
			$gallery = $wpdb->get_row( "SELECT Id, name, thumbnail, thumbwidth, thumbheight FROM $easy_gallery_table WHERE Id = '$id'" );
		}
		else {
			$gallery = $wpdb->get_row( "SELECT Id, name, thumbnail, thumbwidth, thumbheight FROM $easy_gallery_table WHERE slug = '$galleryName'" );
		}
		$imageResults = $wpdb->get_results( "SELECT * FROM $easy_gallery_image_table WHERE gid = $gallery->Id ORDER BY sortOrder ASC" );
		$options = get_option('wp_easy_gallery_defaults');
		$galleryLink = "";
		
		switch($options['display_mode']) {
			case 'wp_easy_gallery':
				$galleryLink = render_wpeg($gallery, $imageResults, $options);
				break;
			case 'wp_default':
				$galleryLink = render_wp_gallery($gallery, $imageResults, $options);
				break;
			default:
				$galleryLink = render_wpeg($gallery, $imageResults, $options);
				break;
		}
		
		return $galleryLink;
	}
	*/
	/*
	function render_wpeg($gallery, $imageResults, $options) {
		$images = array();
		$descriptions = array();
		$titles = array();
		$i = 0;
		$thumbImage = $gallery->thumbnail;		
		
		foreach($imageResults as $image)
		{
			if($i == 0)
				$thumbImage = (strlen($gallery->thumbnail) > 0) ? $gallery->thumbnail : $image->imagePath;
			$images[$i] = "'".$image->imagePath."'";
			$descriptions[$i] = "'".$image->description."'";
			$titles[$i] = "'".$image->title."'";
			$i++;
		}
		
		$img = implode(", ", $images);
		$desc = implode(", ", $descriptions);
		$ttl = implode(", ", $titles);
		
		$thumbwidth = ($gallery->thumbwidth < 1 || $gallery->thumbwidth == "auto") ? "" : "width='".$gallery->thumbwidth."'";
		$thumbheight = ($gallery->thumbheight < 1 || $gallery->thumbheight == "auto") ? "" : "height='".$gallery->thumbheight."'";		
		
		$dShadow = ($options['drop_shadow'] == "true") ? "class=\"dShadow trans\"" : "";
		$showName = ($options['show_gallery_name'] == "true") ? "<p class=\"wpeg-gallery-name ".$options['gallery_name_alignment']."\">".$gallery->name."</p>" : "";
		
		$galleryMarkup = "<span class=\"wp-easy-gallery\"><a onclick=\"var images=[".$img."]; var titles=[".$ttl."]; var descriptions=[".$desc."]; jQuery.prettyPhoto.open(images,titles,descriptions);\" title=\"".$gallery->name."\" style=\"cursor: pointer;\"><img ".$dShadow." src=\"".$thumbImage."\" ".$thumbwidth." ".$thumbheight." border=\"0\" alt=\"".$gallery->name."\" /></a>".$showName."</span>";
		
		return $galleryMarkup;
	}
	*/
	/*
	function render_wp_gallery($gallery, $imageResults, $options) {
		$numColumns = $options['num_columns'];
		$showName = $options['show_gallery_name'];
		$galleryMarkup = "<style type='text/css'>#gallery-".$gallery->Id." {margin: auto;}	#gallery-".$gallery->Id." .gallery-item {float: left;margin-top: 10px;text-align: center;width: ".floor(100 / $numColumns)."%;} #gallery-".$gallery->Id." img {border: 2px solid #cfcfcf;}	#gallery-".$gallery->Id." .gallery-caption {margin-left: 0;}</style>";
		$galleryMarkup .= "<div id='gallery-".$gallery->Id."' class='gallery gallery-columns-".$numColumns." gallery-size-thumbnail'>";
		if ($showName == 'true') {
			$galleryMarkup .= "<h4 class=\"wpeg-gallery-name ".$options['gallery_name_alignment']."\">".$gallery->name."</h4>";
		}
		
		foreach($imageResults as $image) {
			$galleryMarkup .= "<dl class=gallery-item>";
			$galleryMarkup .= "<dt class='gallery-icon landscape'>";
			$galleryMarkup .= "<a href='".$image->imagePath."' rel='prettyPhoto' title='".$image->title."'>";
			$galleryMarkup .= "<img width='150' height='150' src='".$image->imagePath."' class='attachment-thumbnail' alt='".$image->title."'>";
			$galleryMarkup .= "</a>";
			$galleryMarkup .= "</dt>";
			$galleryMarkup .= "<dd class='wp-caption-text gallery-caption'>";
			$galleryMarkup .= $image->title;
			$galleryMarkup .= "</dd>";
			$galleryMarkup .= "</dl>";
		}
		
		$galleryMarkup .= "<br style='clear: both'></div>";
		
		return $galleryMarkup;
	}
	*/
	/*
	function EasyGallery_Handler($atts) {
	  $atts = shortcode_atts( array( 'id' => '-1', 'key' => '-1'), $atts );
	  return createEasyGallery($atts['id'], $atts['key']);
	}
	add_shortcode('EasyGallery', 'EasyGallery_Handler');
	*/
	/*
	function wpeg_shortcode_callback() {
		global $wpdb; // this is how you get access to the database
		global $easy_gallery_table;

		$galleryResults = $wpdb->get_results( "SELECT Id, name FROM $easy_gallery_table" );
		$count = 0;
		
		$result = '{ "wpEasyGallery": [';
		foreach($galleryResults as $gallery) {
			$count++;
			$result .= '{ "id": "'.$gallery->Id.'", "name": "'.$gallery->name.'"}';
			if ($count < count($galleryResults)) { $result .= ","; }
		} 
		$result .= ']}';

        echo $result;

		wp_die(); // this is required to terminate immediately and return a proper response
	}
	add_action( 'wp_ajax_wpeg_shortcode', 'wpeg_shortcode_callback' );
	*/
	
	add_action( 'init', 'wpeg_code_button' );	
	function wpeg_code_button() {
		add_filter( "mce_external_plugins", "wpeg_code_add_button" );
		add_filter( 'mce_buttons', 'wpeg_code_register_button' );
	}
	function wpeg_code_add_button( $plugin_array ) {
		$plugin_array['wpegbutton'] = $dir = plugins_url( 'js/shortcode.js', __FILE__ );
		return $plugin_array;
	}
	function wpeg_code_register_button( $buttons ) {
		array_push( $buttons, 'wpegselector' );
		return $buttons;
	}
?>