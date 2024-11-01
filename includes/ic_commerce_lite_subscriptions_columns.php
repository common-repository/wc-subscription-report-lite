<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Commerce_Lite_Subscription_Columns' ) ) {
	class IC_Commerce_Lite_Subscription_Columns{
		
		public $constants 	=	array();
		
		public function __construct($constants) {
			global $options;			
			$this->constants		= $constants;			
			
		}
		
		
		function grid_columns_subscription_list($report_name = ''){
			$columns = array(					
				"subscription_id"					=> "Subscr. ID"
				,"order_id"							=> "Order ID"
				,"order_date"						=> "Order Date"
				,"order_item_name"					=> "Order Item Name"
				,"billing_email"					=> "Email ID"
				,"billing_name"						=> "Billing Name"
				//,"subscription_sign_up_fee"			=> "Sign Up Fee"
				//,"line_subtotal"					=> "Sub Total"
				,"schedule_trial_end"				=> "Trial Expiry Date"
				,"subscription_date"				=> "Subscr. Start Date"
				,"schedule_end"						=> "Subscr. Expiry Date"
				,"subscription_status"				=> "Subscr. Status"	
				,"billing_period"					=> "Subscr. Period"
				//,"subscription_length"				=> "Subscription Length"
				,"billing_interval"					=> "Subscr. Interval"
				,"qty"								=> "Quantity"
				,"order_total"					    => "Recurring Amount"
				//,"line_total"						=> "Recurring Amount"					
			);
			
			$columns 	= apply_filters("ic_commerce_subscriptions_list_columns", $columns, $report_name);
				
			return $columns;
		}
		
		
		function grid_columns_subscription_list_extra_columns($report_name = ''){
			$columns = array(
				 "billing_first_name"				=> "Billing First Name"
				,"billing_last_name"				=> "Billing Last Name"
				,"billing_company"					=> "Billing Company"
				,"billing_address_1"				=> "Billing Address 1"
				,"billing_address_2"				=> "Billing Address 2"
				,"billing_city"						=> "Billing City"
				,"billing_postcode"					=> "Billing Postcode"
				,"billing_country"					=> "Billing Country"
				,"billing_state"					=> "Billing State"
				,"billing_phone"					=> "Billing Phone"
				
				,"shipping_first_name"				=> "Shipping Fist Name"
				,"shipping_last_name"				=> "Shipping Last Name"
				,"shipping_company"					=> "Shipping Company"
				,"shipping_address_1"				=> "Shipping Address 1"
				,"shipping_address_2"				=> "Shipping Address 2"
				,"shipping_city"					=> "Shipping City"
				,"shipping_postcode"				=> "Shipping Postcode"
				,"shipping_country"					=> "Shipping Country"
				,"shipping_state"					=> "Shipping State"
			);
			
			$columns 	= apply_filters("ic_commerce_subscriptions_list_extra_columns", $columns, $report_name);
				
			return $columns;
		}	
		
		function result_columns_subscription_list($report_name = ''){
			$columns = array();
			switch($report_name){
				case "subscription_product":
					$columns = array(	
						"total_row_count" 				=> "Total Row Count"
						,"total_count"					=> "Total Count"
						,"total_amount"					=> "Total Amount"
					);					
					break;
				default:
					$columns = array(	
						"total_row_count" 				=> "Total Row Count"
						,"order_total"					=> "Recurring Amount"
					);
					break;
			}
			
			
			$columns 	= apply_filters("ic_commerce_subscriptions_list_results_columns", $columns, $report_name);
			
			return $columns;
		}	
		
	}//END class IC_Commerce_Pro_Subscription_Columns{
}//END if ( ! class_exists( 'IC_Commerce_Pro_Subscription_Columns' ) ) {