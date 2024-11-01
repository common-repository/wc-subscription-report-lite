<?php  
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	require_once('ic_commerce_lite_subscriptions_fuctions.php');

if(!class_exists('IC_Commerce_Lite_Subscription_Init')){
	class IC_Commerce_Lite_Subscription_Init extends IC_Commerce_Lite_Subscription_Fuctions{
			
			public $constants 				= array();
			
			public $plugin_parent			= NULL;
			
			public function __construct($file, $constants) {
				global $icpluginkey, $icperpagedefault, $iccurrent_page, $wp_version;
				if(is_admin()){
					
					add_action( 'admin_notices', array( $this, 'admin_notices'));
					
					$this->file 						= $file;					
					
					$this->constants 					= $constants;
					
					$this->constants['plugin_options'] 	= $this->get_option();
					
					$icpluginkey 						= $this->constants['plugin_key'];
					
					$icperpagedefault 					= $this->constants['per_page_default'];
					
					$ic_commercepro_pages				= apply_filters('ic_commerce_subscription_lite_pages',array($icpluginkey.'_page',$icpluginkey,$icpluginkey."_subscription_list"),$icpluginkey);
					
					$ic_current_page					= $this->get_request('page',NULL,false);
					
					$this->check_parent_plugin();					
					
					$this->define_constant();
					
					//$this->print_array($_REQUEST);exit;
					
					add_action('wp_ajax_'.$this->constants['plugin_key'].'_wp_ajax_action', array($this, 'wp_ajax_action'));
					
					if(in_array($ic_current_page, $ic_commercepro_pages)){
						
						do_action('ic_commerce_subscription_lite_init', $this->constants, $ic_current_page);
						
						add_action('admin_enqueue_scripts', array($this, 'wp_localize_script'));
						
						add_action('admin_init', array($this, 'admin_head'));
						
						
					}
					
					add_action('admin_menu', array( &$this, 'admin_menu' ) );
					
					add_action('activated_plugin',				array($this->constants['plugin_instance'],	'activated_plugin'));
					
					register_activation_hook(	$this->constants['plugin_file'],	array($this->constants['plugin_instance'],	'activate'));
					
					register_deactivation_hook(	$this->constants['plugin_file'], 	array($this->constants['plugin_instance'],	'deactivation'));
					
					register_uninstall_hook(	$this->constants['plugin_file'], 	array($this->constants['plugin_instance'],	'uninstall'));
					
					add_filter( 'plugin_action_links_'.$this->constants['plugin_slug'], array( $this, 'plugin_action_links' ), 9, 2 );
					
					if ( version_compare( $wp_version, '2.8alpha', '>' ) )
						add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
						
					
					if ( version_compare( $wp_version, '3.3', '>' ) )
						add_action('admin_bar_menu', array( $this, 'admin_bar_menu'), 1000);
					
				}
			}
			
			function define_constant(){
				global $icpluginkey, $icperpagedefault, $iccurrent_page, $wp_version;
				
				//New Change ID 20140918
				$this->constants['detault_stauts_slug'] 	= array("completed","on-hold","processing");
				$this->constants['detault_order_status'] 	= array("wc-completed","wc-on-hold","wc-processing");
				$this->constants['detault_subs_status'] 	= array("active");
				$this->constants['hide_order_status'] 		= array();
				
				$this->constants['sub_version'] 			= '20180822';
				$this->constants['last_updated'] 			= '20180822';
				$this->constants['customized'] 				= 'no';
				$this->constants['customized_date'] 		= '';
				
				$this->constants['first_order_date'] 		= $this->first_order_date($this->constants['plugin_key']);
				$this->constants['total_shop_day'] 			= $this->get_total_shop_day($this->constants['plugin_key']);
				$this->constants['today_date'] 				= date_i18n("Y-m-d");
				
				$this->constants['post_status']				= $this->get_setting2('post_status',$this->constants['plugin_options'],array());
				$this->constants['hide_order_status']		= $this->get_setting2('hide_order_status',$this->constants['plugin_options'],$this->constants['hide_order_status']);
				$this->constants['start_date']				= $this->get_setting('start_date',$this->constants['plugin_options'],$this->constants['first_order_date']);
				$this->constants['end_date']				= $this->get_setting('end_date',$this->constants['plugin_options'],$this->constants['today_date']);
				
				$this->constants['wp_version'] 				= $wp_version;
				
				$file 										= $this->constants['plugin_file'];
				$this->constants['plugin_slug'] 			= plugin_basename( $file );
				$this->constants['plugin_file_name'] 		= basename($this->constants['plugin_slug']);
				$this->constants['plugin_file_id'] 			= basename($this->constants['plugin_slug'], ".php" );
				$this->constants['plugin_folder']			= dirname($this->constants['plugin_slug']);
				$this->constants['plugin_url'] 				= plugins_url("", $file);//Added 20141106
				$this->constants['siteurl'] 				= site_url();//Added for SSL fix 20150212
				$this->constants['admin_page_url']			= $this->constants['siteurl'].'/wp-admin/admin.php';//Added for SSL fix 20150212
				
				$this->constants['plugin_dir'] 				= WP_PLUGIN_DIR ."/". $this->constants['plugin_folder'];				
				$this->constants['http_user_agent'] 		= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$this->constants['post_type_shop_subscription'] = isset($this->constants['post_type_shop_subscription']) ? $this->constants['post_type_shop_subscription'] : 0;
				
				$this->is_active();
			}
			
			function activate(){
				global $icpluginkey, $icperpagedefault;
				$icpluginkey 			= "icwoocommercesubscriptionslite";
				$blog_title 			= get_bloginfo('name');
				$email_send_to			= get_option( 'admin_email' );
				
				
				$default = array(
					'recent_order_per_page'				=> $icperpagedefault
					,'top_product_per_page'				=> $icperpagedefault
					,'top_customer_per_page'			=> $icperpagedefault
					,'top_billing_country_per_page'		=> $icperpagedefault
					,'top_payment_gateway_per_page'		=> $icperpagedefault
					,'top_coupon_per_page'				=> $icperpagedefault
					,'per_row_customer_page'			=>	$icperpagedefault
					,'per_row_details_page'				=> $icperpagedefault
					,'per_row_stock_page'				=> $icperpagedefault
					,'per_row_all_report_page'			=> $icperpagedefault
					,'per_row_cross_tab_page'			=> $icperpagedefault
					
					,'email_send_to'					=> $email_send_to
					,'email_from_name'					=> $blog_title
					,'email_from_email'					=> $email_send_to
					
					,'theme_color'						=> '#77aedb'//$this->constants['color_code']
					,'logo_image'						=> ''
					,'company_name'						=> $blog_title
					
					
				);
				add_option( $icpluginkey, $default );			
				add_option( $icpluginkey.'_per_page_default', $icperpagedefault);
				
				//echo $icpluginkey." - ".$icperpagedefault;
				//exit;
			}
			
			function deactivation(){
				global $icpluginkey;
				$icpluginkey = "icwoocommercesubscriptionslite";
				delete_option( $icpluginkey);
				delete_option( $icpluginkey.'_per_page_default');
				delete_option( $icpluginkey.'_activated_plugin_error');
				
			}
			
			function uninstall(){
				global $icpluginkey;
				$icpluginkey = "icwoocommercesubscriptionslite";
				delete_option( $icpluginkey);
				delete_option( $icpluginkey.'_per_page_default');
				delete_option( $icpluginkey.'_activated_plugin_error');
			}
			
			function activated_plugin(){
				global $icpluginkey;
				update_option($icpluginkey.'_activated_plugin_error',  ob_get_contents());
			}
			
			function plugin_action_links($plugin_links, $file){
				if ( ! current_user_can( $this->constants['plugin_role'] ) ) return;
				if ( $file == $this->constants['plugin_slug']) {
					$settings_link = array();
					$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_page').'" 			title="'.__($this->constants['plugin_name'].' Dashboard', 	$this->constants['plugin_key']).'">'.__('Dashboard', 	$this->constants['plugin_key']).'</a>';
					if($this->is_product_active == 1) {
						//$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_details_page').'" 	title="'.__($this->constants['plugin_name'].' Reports', 	$this->constants['plugin_key']).'">'.__('Detail', 		$this->constants['plugin_key']).'</a>';
						$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_options_page').'" 	title="'.__($this->constants['plugin_name'].' Settings', 	$this->constants['plugin_key']).'">'.__('Settings', 	$this->constants['plugin_key']).'</a>';
					}
					
					
					return array_merge( $plugin_links, $settings_link );
				}		
				return $plugin_links;
			}
			
			function plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status ){
				if ( $plugin_file == $this->constants['plugin_slug']) {
					$settings_link = array();
					$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_page').'" 			title="'.__($this->constants['plugin_name'].' Dashboard', 	$this->constants['plugin_key']).'">'.__('Dashboard', 	$this->constants['plugin_key']).'</a>';
					if($this->is_product_active == 1) {							
						//$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_details_page').'" 	title="'.__($this->constants['plugin_name'].' Reports', 	$this->constants['plugin_key']).'">'.__('Detail', 		$this->constants['plugin_key']).'</a>';
						$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_options_page').'" 	title="'.__($this->constants['plugin_name'].' Settings', 	$this->constants['plugin_key']).'">'.__('Settings', 	$this->constants['plugin_key']).'</a>';
					}
					
					
					return array_merge( $plugin_meta, $settings_link );
				}		
				return $plugin_meta;
			}
			
			function admin_menu(){
				add_menu_page($this->constants['plugin_name'],$this->constants['plugin_menu_name'], $this->constants['plugin_role'], 	$this->constants['plugin_key'].'_page', array($this, 'add_page'), plugins_url( '/assets/images/menu_icons.png',$this->constants['plugin_file']), '57.0' );
				
				add_submenu_page($this->constants['plugin_key'].'_page',	__( $this->constants['plugin_name'].' Dashboard', 				$this->constants['plugin_key'] ),	__( 'Dashboard',			$this->constants['plugin_key'] ),	$this->constants['plugin_role'],$this->constants['plugin_key'].'_page',							array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Subscription Detail', 	$this->constants['plugin_key'] ),	__( 'Subscription Detail', 	$this->constants['plugin_key'] ),	$this->constants['plugin_role'],$this->constants['plugin_key'].'_subscription_list',			array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Other Plug-ins', 			$this->constants['plugin_key'] ),	__( 'Other Plug-ins', 	$this->constants['plugin_key'] ),	$this->constants['plugin_role'],$this->constants['plugin_key'].'_addons',			array( $this, 'add_page' ));							
			}
			
			function admin_bar_menu(){
				global $wp_admin_bar;
				
				return '';
				
				if ( ! current_user_can( $this->constants['plugin_role'] ) ) return;
				
				if($this->is_product_active != 1)  return true;
				$wp_admin_bar->add_menu(
					array(	'id' => $this->constants['plugin_key'],
							'title' => __($this->constants['plugin_menu_name'], $this->constants['plugin_key']),
							'href' => admin_url('admin.php?page='.$this->constants['plugin_key'].'_page')
					)
				);
				
				$wp_admin_bar->add_menu(
					array(	'parent' => $this->constants['plugin_key'],
							'id' => $this->constants['plugin_key'].'_page',
							'title' => __('Dashboard', $this->constants['plugin_key']),
							'href' => admin_url('admin.php?page='.$this->constants['plugin_key'].'_page')
					)
				);
			}
			
			
			function add_page(){
				global $setting_intence, $activate_lite_subscription_intence;
				$current_page	= $this->get_request('page',NULL,false);
				$c				= $this->constants;
				$title			= NULL;
				$intence		= NULL;
				
				if ( ! current_user_can($this->constants['plugin_role']) ) return;
				
				switch($current_page){
					case $this->constants['plugin_key'].'_page':	
						$title = 'WooCommerce Subscription Report';
						include_once($this->constants['plugin_dir'].'/includes/ic_commerce_lite_subscriptions_dashboard.php');
						$intence = new IC_Commerce_Lite_Subscription_Dashboard($c);
						break;		
					case $this->constants['plugin_key'].'_subscription_list':
						//$title = 'Variation Stock List';
						include_once('ic_commerce_lite_subscriptions_list.php' );
						$intence = new IC_Commerce_Lite_Subscription_List($c);
						break;
					case $this->constants['plugin_key'].'_addons':
						include_once('ic_commerce_lite_subscriptions_add_ons.php' );
						$intence = new IC_Commerce_Lite_Subscription_List($c);
						break;
					default:
						//include_once('ic_commerce_pro_subscriptions_dashboard.php');
						//$intence = new IC_Commerce_Lite_Subscription_Dashboard($c);
						break;
					break;			
				}
				
				$output = "";
				
				add_action('admin_footer',  array( &$this, 'admin_footer'),9);
				
				$output .= '<div class="wrap '.$this->constants['plugin_key'].'_wrap iccommercepluginwrap">';
					
					$output .= '<div class="icon32" id="icon-options-general"><br /></div>';
					
					if($title):
						$output .= '<h2>'. __($title,$this->constants['plugin_key']).'</h2>';
					endif;
						
					echo $output;
					
					if($intence) 
						$intence->init(); 
					else 
						echo "Class not found.";
						
				echo '</div>';
				
				
			}
			
			public $is_product_active = NULL;
			
			public function is_active(){
				$r = false;
				if($this->is_product_active == NULL){					
					$actived_product = get_option($this->constants['plugin_key'] . '_activated');
					
					//$this->print_array($actived_product);
					$this->is_product_active = 0;
					if($actived_product)
					foreach($actived_product as $key => $value){
						if($this->constants['plugin_file_id'] == $key && $value == 1){
							$r = true;
							$this->is_product_active = 1;
						}
					}
				}
				return $r;
			}
			
			
			
			
			
			
			function export_csv(){
				//$this->print_array($_REQUEST);exit;
				
				if(isset($_REQUEST['export_file_format']) and ($_REQUEST['export_file_format'] == "csv" || $_REQUEST['export_file_format'] == "xls" )){
					$time_limit = apply_filters("ic_commerce_maximum_execution_time",300,$_REQUEST['export_file_format']);
					set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
				}else{
					return '';
				}
				
				if(
					isset($_REQUEST[$this->constants['plugin_key'].'_subscription_list_export_csv'])
					||
					isset($_REQUEST[$this->constants['plugin_key'].'_subscription_list_export_xls']) 
				){
					$c				= $this->constants;
					include_once('ic_commerce_lite_subscriptions_list.php' );
					$IC_Commerce_Lite_Subscription_List = new IC_Commerce_Lite_Subscription_List($c);
					
					if(isset($_REQUEST[$this->constants['plugin_key'].'_subscription_list_export_csv'])){
						$IC_Commerce_Lite_Subscription_List->ic_commerce_custom_report_page_export_csv('csv');
					}
					if(isset($_REQUEST[$this->constants['plugin_key'].'_subscription_list_export_xls'])){
						$IC_Commerce_Lite_Subscription_List->ic_commerce_custom_report_page_export_csv('xls');
					}
									
					die;				
				}
				
				
			}
			
			function export_pdf(){
								
				//$this->print_array($_REQUEST);exit;
								
				if(isset($_REQUEST['export_file_format']) and $_REQUEST['export_file_format'] == "pdf"){
					$time_limit = apply_filters("ic_commerce_maximum_execution_time",300,$_REQUEST['export_file_format']);
					set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
					
				}else{
					return '';
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_subscription_list_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_lite_subscriptions_list.php' );
					$IC_Commerce_Lite_Subscription_List = new IC_Commerce_Lite_Subscription_List($c);
					
					if(isset($_REQUEST[$this->constants['plugin_key'].'_subscription_list_export_pdf'])){
						$IC_Commerce_Lite_Subscription_List->ic_commerce_custom_report_page_export_pdf('pdf');
					}					
					die;				
				}
				
			}
			
			
			
			function export_print(){
				if(isset($_REQUEST[$this->constants['plugin_key'].'_details_page_export_print'])){
					$c				= $this->constants;
					include_once('ic_commerce_pro_subscriptions_datail_summary_reports.php' );
					$IC_Commerce_Pro_Subscription_Detail_report = new IC_Commerce_Pro_Subscription_Detail_report($c);
					$IC_Commerce_Pro_Subscription_Detail_report->ic_commerce_custom_admin_report_iframe_request('all_row');
					exit;
				}
			}
			
			function admin_head() {				
				wp_enqueue_style(  $this->constants['plugin_key'].'_admin_styles', 								$this->constants['plugin_url'].'/assets/css/admin.css' );
			}
			function admin_footer() {
				
				$current_page	= $this->get_request('page',NULL,false);
				
				//wp_enqueue_style(  $this->constants['plugin_key'].'_admin_styles', 								$this->constants['plugin_url'].'/assets/css/admin.css' );
				
				if($current_page == $this->constants['plugin_key'].'_page'){
					
					wp_enqueue_style(  $this->constants['plugin_key'].'_admin_jquery_jqplot_mint_css',				$this->constants['plugin_url'].'/assets/graph/css/jquery.jqplot.min.css');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jquery_jqplot', 						$this->constants['plugin_url'].'/assets/graph/scripts/jquery.jqplot.min.js',array('jquery'));
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_shCore', 								$this->constants['plugin_url'].'/assets/graph/scripts/shCore.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_shBrushJScript', 						$this->constants['plugin_url'].'/assets/graph/scripts/shBrushJScript.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_shBrushXml', 							$this->constants['plugin_url'].'/assets/graph/scripts/shBrushXml.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_categoryAxisRenderer', 				$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.categoryAxisRenderer.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_barRenderer_min', 				$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.barRenderer.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_pointLabels_min', 				$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.pointLabels.min.js');	
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_canvasAxisTickRenderer_min',	$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.canvasAxisTickRenderer.min.js');	
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_canvasTextRenderer_min', 		$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.canvasTextRenderer.min.js');	
					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_highlighter_min', 				$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.highlighter.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_cursor_min', 					$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.cursor.min.js');
					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_dateAxisRenderer_min', 		$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.dateAxisRenderer.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_pieRenderer_min', 				$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.pieRenderer.min.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_donutRenderer_min', 			$this->constants['plugin_url'].'/assets/graph/scripts/jqplot.donutRenderer.min.js');
					
					//New Change ID 20140918
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_jqplot_script', 						$this->constants['plugin_url'].'/assets/js/jqplot.scripts.js');	/*Don't touch this! */
					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_dashboard_summary_script',			$this->constants['plugin_url'].'/assets/js/dashboard_summary.js');
				
				}
				
				if($current_page == $this->constants['plugin_key'].'_addons'){
						wp_enqueue_style(  $this->constants['plugin_key'].'_admin_styles', 								$this->constants['plugin_url'].'/assets/css/admin.css' );
				}
				
				if($current_page == $this->constants['plugin_key'].'_subscription_list'){
					//echo $current_page;
					//die;
					wp_enqueue_script('jquery-ui-datepicker');				
					
					$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';				
					
					wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );				
					
					wp_enqueue_script( $this->constants['plugin_key'].'_jquery_collapsible', 			$this->constants['plugin_url'].'/assets/js/jquery.collapsible.js', true);
					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_details_page', 			$this->constants['plugin_url'].'/assets/js/details_page.js', true);
				
				}
			}
			
			public function check_parent_plugin(){
				if(!isset($this->constants['plugin_parent'])) return '';
				$message 				= "";
				$msg 					= false;
				$this->plugin_parent 	= $this->constants['plugin_parent'];
				$action = "";
				
				if(function_exists('wcs_get_subscription_statuses')){
					$this->constants['post_type_shop_subscription']	= 1;
				}else{
					$this->constants['post_type_shop_subscription']	= 0;
				}
				
				
				$this->constants['plugin_parent_active'] 		=  false;
				$this->constants['plugin_parent_installed'] 	=  false;
				
				if(in_array( $this->plugin_parent['plugin_slug'], apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
					$this->constants['plugin_parent_active'] 		=  true;
					$this->constants['plugin_parent_installed'] 	=  true;
					
					//New Change ID 20140918
					$this->constants['parent_plugin_version']	= get_option('woocommerce_version',0);
					$this->constants['parent_plugin_db_version']= get_option('woocommerce_db_version',0);
					
					if(!defined('WOO_VERSION'))
					if(defined('WC_VERSION')) define('WOO_VERSION', WC_VERSION);else define('WOO_VERSION', '');
					
					if ( version_compare( $this->constants['parent_plugin_db_version'], '2.2.0', '>=' ) || WOO_VERSION == '2.2-bleeding' ) {
						if ( version_compare( $this->constants['parent_plugin_db_version'], '2.2.0', '<' ) || WOO_VERSION == '2.2-bleeding' ) {
							$this->constants['post_order_status_found']	= 0;
						}else{
							$this->constants['post_order_status_found']	= 1;
						}
					}else{
						$this->constants['post_order_status_found']	= 0;
					}
					
					
		
					return $message;
				}else{
					$this->constants['plugin_parent_active'] =  false;
					if(is_dir(WP_PLUGIN_DIR.'/'.$this->plugin_parent['plugin_folder'] ) ) {
						$message = $this->constants['plugin_parent_installed'] =  true;
					}else{
						$message = $this->constants['plugin_parent_installed'] =  false;
					}
					return  $message;
				}
			}
			
			public function admin_notices(){
				$message 				= NULL;				
				if(!$this->constants['plugin_parent_active']){
					if($this->constants['plugin_parent_installed']){
						$action = esc_url(wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$this->plugin_parent['plugin_slug'].'&plugin_status=active&paged=1'), 'activate-plugin_'.$this->plugin_parent['plugin_slug']));						
						$msg = '<span>' . sprintf( __($this->constants['plugin_name'].' depends on <a href="%s">'.$this->plugin_parent['plugin_name'].'</a> to work! so please <a href="%s">activate</a> it.' , $this->constants['plugin_key'] ), $action, $action ) . '</span>';
					}else{
						$action = admin_url( 'plugin-install.php?tab=plugin-information&plugin='.$this->plugin_parent['plugin_folder'].'&TB_iframe=true&width=640&height=800');
						$msg = '<span>' . sprintf( __($this->constants['plugin_name'].' depends on <a href="%s" target="_blank" class="thickbox onclick" title="'.$this->plugin_parent['plugin_name'].'">'.$this->plugin_parent['plugin_name'].'</a> to work!' , $this->constants['plugin_key'] ),$action) . '</span>';					
					}					
					$message .= '<div class="error">';
					$message .= '<p>'.$msg.'</p>';
					$message .= '</div>';
				}
				echo $message;
			}			
			
			function wp_localize_script($hook) {
				
				if(function_exists('get_woocommerce_currency_symbol')){
					$currency_symbol	=	get_woocommerce_currency_symbol();
				}else{
					$currency_symbol	=	"$";
				}
				
				wp_enqueue_script( $this->constants['plugin_key'].'_ajax-script', 									$this->constants['plugin_url'].'/assets/js/scripts.js', true);
				
				wp_localize_script(
					$this->constants['plugin_key'].'_ajax-script', 
					'ic_ajax_object', 
					array( 
						'ajaxurl' 			=> admin_url( 'admin-ajax.php' )
						,'ic_ajax_action' 	=> $this->constants['plugin_key'].'_wp_ajax_action'
						,'first_order_date' => $this->constants['first_order_date']
						,'current_date' 	=> date("Y-m-d")
						,'total_shop_day' 	=> $this->constants['total_shop_day']
						,'defaultOpen' 		=> 'section1'
						,'color_code' 		=> $this->constants['color_code'],
						//,'defaultOpen' 	=> ''
						'currency_symbol' 	=> $currency_symbol,
						'num_decimals'      => get_option( 'woocommerce_price_num_decimals'	,	0		),
						'currency_pos'      => get_option( 'woocommerce_currency_pos'		,	'left'	),
						'decimal_sep'      	=> get_option( 'woocommerce_price_decimal_sep'	,	'.'		),
						'thousand_sep'      => get_option( 'woocommerce_price_thousand_sep'	,	','		)
					)
				); // setting ajaxurl
				
				//wp_enqueue_media();
				//wp_enqueue_script('custom-background');
				//wp_enqueue_style('wp-color-picker');
			}
			
			function get_column_key($name){
				$page			= $this->get_request('ic_admin_page','report');				
				return $key 	= $page.'_'.$name;
			}
			
			function wp_ajax_action() {
				
				$action	= $this->get_request('action',NULL,false);
				if($action ==  $this->constants['plugin_key'].'_wp_ajax_action'){
				
					if(isset($_REQUEST['export_file_format']) and $_REQUEST['export_file_format'] == "print"){
						$time_limit = apply_filters("ic_commerce_maximum_execution_time",300,$_REQUEST['export_file_format']);
						set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
					}
										
					$do_action_type	= $this->get_request('do_action_type',NULL,false);
					//$this->print_array($_REQUEST);
					//die;
					if($do_action_type){
						//$this->define_constant();
						$c	= $this->constants;
						
						//echo $do_action_type;
						
						if($do_action_type == "dashboard_summary_action"){
							include_once( 'ic_commerce_lite_subscriptions_dashboard_summary.php');
							$IC_Commerce_Lite_Subscription_Dashboard_Summary = new IC_Commerce_Lite_Subscription_Dashboard_Summary($c);
							die;
						}
												
						if($do_action_type == "subscription_list"){
							include_once( 'ic_commerce_lite_subscriptions_list.php');
							$intence = new IC_Commerce_Lite_Subscription_List($c);
							$intence->ic_commerce_report_ajax_request('limit_row');
							die;
						}
						
					}
				}
				die(); // this is required to return a proper result
				exit;
			}
			
	}
}