<?php 
/**
Plugin Name: WC Subscriptions Report Lite
Description: plugins.infosofttech.com presents WooCommerce Subscription Report Plugin, this plugin works with Wootheme Subscription Plugin ver 2.0.8 onwards.
Version: 1.9
Author: Infosoft Consultants
Author URI: http://plugins.infosofttech.com
Plugin URI: https://wordpress.org/plugins/wc-subscription-report-lite/

Tested Wordpress Version: 6.1.x
WC requires at least: 3.5.x
WC tested up to: 7.4.x
Requires at least: 5.7
Requires PHP: 5.6

Text Domain: icwoocommercesubscriptionslite
Domain Path: /languages/
**/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Woocommerce_Subscriptions_Report_Lite' ) ) { 
	class IC_Woocommerce_Subscriptions_Report_Lite{
		function __construct() {
			add_action( 'init', array( $this, 'load_plugin' ));
			add_action( 'init', array( $this, 'load_plugin_textdomain' ));
		}
		
		function load_plugin(){
			if ( is_admin() ) {	
				require_once('includes/ic_commerce_lite_subscriptions_init.php');
				
				global $ic_woocommerce_subscriptions_report_lite, $ic_woocommerce_subscriptions_report_lite_constant;

				$ic_woocommerce_subscriptions_report_lite_constant = array(
						"version" 				=> "1.9"
						,"product_id" 			=> "1240"
						,"plugin_key" 			=> "icwoocommercesubscriptionslite"
						,"plugin_name" 			=> "Woocommerce Subscriptions Report Lite"				
						,"plugin_main_class" 	=> "IC_Woocommerce_Subscriptions_Report_Lite"
						,"plugin_instance" 		=> "ic_woocommerce_subscriptions_report_lite"
						,"plugin_menu_name" 	=> "WC Subscription Report Lite"
						,"plugin_file" 			=> __FILE__
						,"plugin_role" 			=> 'manage_woocommerce'
						,"per_page_default"		=> 5
						,"plugin_parent_active" => false
						,"color_code" 			=> '#77aedb'
						,"plugin_parent" 		=> array(
							"plugin_name"		=>"WooCommerce"
							,"plugin_slug"		=>"woocommerce/woocommerce.php"
							,"plugin_file_name"	=>"woocommerce.php"
							,"plugin_folder"	=>"woocommerce"
							,"order_detail_url"	=>"post.php?&action=edit&post="
						)			
				);

				$IC_Commerce_Lite_Subscription_Init = new IC_Commerce_Lite_Subscription_Init(__FILE__, $ic_woocommerce_subscriptions_report_lite_constant);
			}
		}
		
		function plugin_action_links($plugin_links, $file = ''){
			//$plugin_links[] = '<a target="_blank" href="'.admin_url('admin.php?page=icwoocommercesubscriptionslite_page').'">' . esc_html__( 'Dashboard', 'icwoocommercesubscriptionslite' ) . '</a>';
			return $plugin_links;
		}
		
		function load_plugin_textdomain(){
			load_plugin_textdomain( 'icwoocommercesubscriptionslite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
		}		
	}
	$obj = new  IC_Woocommerce_Subscriptions_Report_Lite();
}