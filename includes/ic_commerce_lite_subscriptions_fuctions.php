<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'IC_Commerce_Lite_Subscription_Fuctions' ) ) {
	class IC_Commerce_Lite_Subscription_Fuctions{
		
		public $firstorderdate = NULL;
		
		public $constants 	=	array();
		
		public function __construct($constants) {
			global $options;			
			$this->constants		= $constants;
			$options				= $this->constants['plugin_options'];			
		}
		
		function get_number_only($value, $default = 0){
			global $options;
			$per_page = (isset($options[$value]) and strlen($options[$value]) > 0)? $options[$value] : $default;
			$per_page = is_numeric($per_page) ? $per_page : $default;
			return $per_page;
		}
		function ic_cr_get_country_name($country_code){			
			$country      = new WC_Countries;					
			return $country->countries[$country_code];
		}
		function first_order_date($key = NULL){
			global $wpdb;
			if($this->firstorderdate){				
				return $this->firstorderdate;
			}else{
				$sql = "SELECT DATE_FORMAT(posts.post_date, '%Y-%m-%d') AS 'OrderDate' FROM {$wpdb->prefix}posts  AS posts	WHERE posts.post_type='shop_order' Order By posts.post_date ASC LIMIT 1";
				return $this->firstorderdate = $wpdb->get_var($sql);
			}
		}
		
		
		function get_total_shop_day($key = NULL){
			 $now = time(); // or your date as well
			//$this->first_order_date();
			$first_date = strtotime(($this->first_order_date($key)));
			$datediff = $now - $first_date;
			$total_shop_day = floor($datediff/(60*60*24));
			return $total_shop_day;
		}
		
		function price($vlaue, $order_currency = array()){
			if(function_exists('wc_price')){
				$v = wc_price($vlaue, $order_currency);
			}elseif(function_exists('woocommerce_price')){
				$v = woocommerce_price($vlaue, $order_currency);
			}else{
				$v = apply_filters( 'ic_commerce_currency_symbol', '&#36;', 'USD').$vlaue;
				
			}
			return $v;
		}
		
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest = implode(",", $newRequest);
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
		}
		
		function create_dropdown($data = NULL, $name = "",$id='', $show_option_none="Select One", $class='', $default ="-1", $type = "array", $multiple = false, $size = 0, $d = "-1"){
			$count 				= count($data);
			$dropdown_multiple 	= '';
			$dropdown_size 		= '';
			
			$selected =  explode(",",$default);
			
			if($count<=0) return '';
			
			if($multiple == true and $size >= 0){
				//$this->print_array($data);
				
				if($count < $size) $size = $count + 1;
				$dropdown_multiple 	= ' multiple="multiple"';
				//echo $count;
				$dropdown_size 		= ' size="'.$size.'"  data-size="'.$size.'"';
			}
			$output = "";
			$output .= '<select name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$dropdown_multiple.$dropdown_size.'>';
			
			//if(!$dropdown_multiple)
			
			//$output .= '<option value="-1">'.$show_option_none.'</option>';
			
			if($show_option_none){
				if($default == "all"){
					$output .= '<option value="'.$d.'" selected="selected">'.$show_option_none.'</option>';
				}else{
					$output .= '<option value="'.$d.'">'.$show_option_none.'</option>';
				}
			}
			
			if($type == "object"){
				foreach($data as $key => $value):
					$s = '';
					
					if(in_array($value->id,$selected)) $s = ' selected="selected"';					
					//if($value->id == $default ) $s = ' selected="selected"';
					
					$c = (isset($value->counts) and $value->counts > 0) ? " (".$value->counts.")" : '';
					
					$output .= "\n<option value=\"".$value->id."\"{$s}>".$value->label.$c."</option>";
				endforeach;
			}else if($type == "array"){
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}else{
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}
						
			$output .= '</select>';
			
			echo $output ;
		
		}
		
		function get_product_data($product_type = 'all'){
				
				global $wpdb;
				
				$category_id			= $this->get_request('category_id','-1');
				
				$taxonomy				= $this->get_request_default('taxonomy','product_cat');
				
				$purchased_product_id	= $this->get_request_default('purchased_product_id','-1');	
					
				$publish_order			= 'no';
				
				$sql = "SELECT woocommerce_order_itemmeta.meta_value AS id, woocommerce_order_items.order_item_name AS label 
				
				FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items				
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
				
				if($category_id != "-1" && $category_id >= 0){
					$sql .= " 
							LEFT JOIN {$wpdb->prefix}term_relationships		AS term_relationships		ON term_relationships.object_id				= woocommerce_order_itemmeta.meta_value
							LEFT JOIN {$wpdb->prefix}term_taxonomy			AS term_taxonomy			ON term_taxonomy.term_taxonomy_id			= term_relationships.term_taxonomy_id
							LEFT JOIN {$wpdb->prefix}terms					AS terms					ON terms.term_id							= term_taxonomy.term_id";
				}
				if($product_type == 1)
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id_order_itemmeta ON variation_id_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
				
				if($product_type == 2 || ($product_type == 'grouped' || $product_type == 'external' || $product_type == 'simple' || $product_type == 'variable_')){
					$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships_product_type 	ON term_relationships_product_type.object_id		=	woocommerce_order_itemmeta.meta_value 
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy_product_type 		ON term_taxonomy_product_type.term_taxonomy_id		=	term_relationships_product_type.term_taxonomy_id
							LEFT JOIN  {$wpdb->prefix}terms 				as terms_product_type 				ON terms_product_type.term_id						=	term_taxonomy_product_type.term_id";
				}
				
				if($publish_order == "yes")	$sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON posts.ID = woocommerce_order_items.order_id";				
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id'";
				
				if($category_id != "-1" && $category_id >= 0){
					$sql .= " AND term_taxonomy.taxonomy = 'product_cat'";
				}
				
				if($product_type == 1)
					$sql .= " AND variation_id_order_itemmeta.meta_key = '_variation_id' AND (variation_id_order_itemmeta.meta_value IS NOT NULL AND variation_id_order_itemmeta.meta_value > 0)";
				
				if($category_id != "-1" && $category_id >= 0)
					$sql .= " AND terms .term_id IN(".$category_id.")";
				
				if($publish_order == 'yes')	$sql .= " AND posts.post_status = 'publish'";
				
				if($publish_order == 'publish' || $publish_order == 'trash')	$sql .= " AND posts.post_status = '".$publish_order."'";
				
				if($product_type == 'grouped' || $product_type == 'external' || $product_type == 'simple' || $product_type == 'variable_'){
					$sql .= " AND terms_product_type.name IN ('{$product_type}')";
				}
				
				$sql .= " GROUP BY woocommerce_order_itemmeta.meta_value ORDER BY woocommerce_order_items.order_item_name ASC";
			
				$products = $wpdb->get_results($sql);
				
				//echo mysql_error();
			
				return $products;
		}
		
		function get_product_data2($post_type = 'product', $post_status = 'no'){
				global $wpdb;
			$category_id			= $this->get_request('category_id','-1');
			
			if($post_status == "yes") $post_status == 'publish';
			if($post_status == "publish") $post_status == 'publish';
			$publish_order			= $this->get_request_default('publish_order',$post_status,true);//if publish display publish order only, no or null display all order
			
			$sql = "SELECT *, posts.ID AS id, posts.post_title AS label FROM `{$wpdb->prefix}posts` AS posts";
			
			if($category_id != "-1" && $category_id >= 0){
				$sql .= " LEFT JOIN {$wpdb->prefix}term_relationships AS term_relationships ON term_relationships.object_id = posts.ID
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}terms AS terms ON terms.term_id = term_taxonomy.term_id";
			}
			$sql .= " WHERE posts.post_type = '{$post_type}'";
			
			if($category_id != "-1" && $category_id >= 0) $sql .= " AND terms .term_id 		IN(".$category_id.")";
			
			if($publish_order == 'publish' || $publish_order == 'trash')	$sql .= " AND posts.post_status = '".$publish_order."'";
			
			$sql .= " GROUP BY posts.ID ORDER BY posts.post_title";
			
			$products = $wpdb->get_results($sql);
			
			//$this->print_array($products);
			
			return $products;
		}
		
		function get_category_data($taxonomy = 'product_cat', $post_status = 'no'){
				global $wpdb;
				
				$post_status = $this->get_request_default('post_status',$post_status,true);
				if($post_status == "yes") $post_status == 'publish';
				
				$sql = "SELECT terms.term_id AS id, terms.name AS label
				FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
				
				LEFT JOIN {$wpdb->prefix}term_relationships AS term_relationships ON term_relationships.object_id = woocommerce_order_itemmeta.meta_value
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}terms AS terms ON terms.term_id = term_taxonomy.term_id";
				
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON posts.ID = woocommerce_order_items.order_id";
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id' 
				AND term_taxonomy.taxonomy = '{$taxonomy}'";
				
				
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " AND posts.post_status = '".$post_status."'";
				
				$sql .= " GROUP BY terms.term_id
				ORDER BY terms.name ASC";
			
				
				$products_category = $wpdb->get_results($sql);
				
				return $products_category; 
		}
		
		
		function get_category_data2($taxonomy = 'product_cat',$post_status = 'no', $count = true){
				global $wpdb;
				
				$post_status = $this->get_request_default('post_status',$post_status,true);
				if($post_status == "yes") $post_status == 'publish';
				
				$sql = "SELECT 
				terms.term_id AS id, terms.name AS label";
				
				if($count)
					$sql .= ", count(posts.ID) AS counts";
				
				$sql .= " FROM `{$wpdb->prefix}posts` AS posts				
				LEFT JOIN {$wpdb->prefix}term_relationships AS term_relationships ON term_relationships.object_id = posts.ID
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}terms AS terms ON terms.term_id = term_taxonomy.term_id";
				
				$sql .= " WHERE term_taxonomy.taxonomy = '{$taxonomy}'";				
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " AND posts.post_status = '".$post_status."'";
				
				$sql .= " 
				GROUP BY terms.term_id
				ORDER BY terms.name ASC";
				
				$products_category = $wpdb->get_results($sql);
				return $products_category; 
		}
		
		
		
		function get_order_customer($post_type = 'shop_order',$post_status = 'no'){
				global $wpdb;
				
				$post_status = $this->get_request_default('post_status',$post_status,true);
				if($post_status == "yes") $post_status == 'publish';
				
				
				$sql = "SELECT billing_email.meta_value AS id, concat(billing_first_name.meta_value, ' ',billing_last_name.meta_value) AS label, COUNT(billing_email.meta_value) AS counts FROM `{$wpdb->prefix}posts` AS posts
					LEFT JOIN  {$wpdb->prefix}postmeta as customer_user ON customer_user.post_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_first_name ON billing_first_name.post_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_last_name ON billing_last_name.post_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_email ON billing_email.post_id=posts.ID
				";
				$sql .= " WHERE 
					post_type='{$post_type}' 
				AND customer_user.meta_key = '_customer_user'
				AND billing_first_name.meta_key = '_billing_first_name'
				AND billing_last_name.meta_key = '_billing_last_name'
				AND billing_email.meta_key = '_billing_email'
				";
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " AND posts.post_status = '".$post_status."'";
				
				$sql .= " 
				GROUP BY billing_email.meta_value
				ORDER BY label  ASC";
				
				$products_category = $wpdb->get_results($sql);
				return $products_category; 
		}
		
		
		
		function get_order_username_list()
		{
			global $wpdb,$sql;
			$sql="SELECT users.user_email AS label
					,customer_user.post_author AS id 
					FROM `{$wpdb->prefix}posts` AS  customer_user
					LEFT JOIN  `{$wpdb->prefix}users` AS  users ON users.ID = customer_user.post_author
					LEFT JOIN  {$wpdb->prefix}usermeta as usermeta ON usermeta.user_id=users.ID
					WHERE customer_user.post_type ='shop_order' AND post_status='publish'
						AND  usermeta.meta_value =9		
				";
				$sql .= " 
				GROUP BY id
				ORDER BY label  ASC";
			$products_category = $wpdb->get_results($sql);
			return $products_category; 		
		
		}
		
		function get_paying_country($code = "_billing_country"){
			global $wpdb;
			
			$country      	= new WC_Countries;
			
			$sql = "SELECT 
			postmeta.meta_value AS 'id'
			,postmeta.meta_value AS 'label'
			
			FROM {$wpdb->prefix}postmeta as postmeta
			WHERE postmeta.meta_key='{$code}'
			GROUP BY postmeta.meta_value
			ORDER BY postmeta.meta_value ASC";
			$results = $wpdb->get_results($sql);
			
			foreach($results as $key => $value):
					$results[$key]->label = isset($country->countries[$value->label]) ? $country->countries[$value->label]: $value->label;
			endforeach;
			
			return $results;
		}
		
		function get_payment_method_name($payment_method = NULL){
			return $payment_method;
		}
		
		function get_custom_field_data($order_item = NULL, $meta_key = NULL, $default = NULL ){
			global $ic_commerce_pro_subscription_custom_fields;
			return $ic_commerce_pro_subscription_custom_fields->get_custom_field_data($order_item,$meta_key,$default);
		}
		
		function get_all_post_meta($order_id,$is_product = false){
			$order_meta	= get_post_meta($order_id);
			
			$order_meta_new = array();
			if($is_product){
				foreach($order_meta as $omkey => $omvalue){
					$order_meta_new[$omkey] = $omvalue[0];
				}
			}else{
				foreach($order_meta as $omkey => $omvalue){
					$omkey = substr($omkey, 1);
					$order_meta_new[$omkey] = $omvalue[0];
				}
			}
			return $order_meta_new;
		}
		
		function get_item_meta_data($order_item_id = 0){
			global $last_days_orders, $wpdb;
			$sql = "";
			$sql .= " SELECT meta_key, meta_value";
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ";
			
			$sql .= " WHERE 1=1 ";
			
			$sql .= " AND ";
			
			$sql .= " woocommerce_order_itemmeta.order_item_id = {$order_item_id}";
			
			$order_meta = $wpdb->get_results($sql);
			
			$order_meta_new = array();
			foreach($order_meta as $omkey => $omvalue){
				$omkey = substr($omvalue->meta_key, 1);
				$order_meta_new[$omkey] = $omvalue->meta_value;
			}
			
			
			return $order_meta_new;
		}
		
		function emailLlink($e, $display = true){
			$return = '<a href="mailto:'.$e.'">'.$e.'</a>';
			if($display)
				echo $return;
			else
				return $return;
		}
		
		function print_array($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
		
		//New Change ID 20140918
		function print_sql($string){			
			
			$string = str_replace("\t", "",$string);
			$string = str_replace("\r\n", "",$string);
			$string = str_replace("\n", "",$string);
			
			$string = str_replace("SELECT ", "SELECT \n\t",$string);
			//$string = str_replace(",", "\n\t,",$string);
			
			$string = str_replace("FROM", "\n\nFROM",$string);
			$string = str_replace("LEFT", "\n\tLEFT",$string);
			
			$string = str_replace("AND", "\r\n\tAND",$string);			
			$string = str_replace("WHERE", "\n\nWHERE",$string);
			
			$string = str_replace("LIMIT", "\nLIMIT",$string);
			$string = str_replace("ORDER", "\nORDER",$string);
			$string = str_replace("GROUP", "\nGROUP",$string);
			
			$new_str = "<pre>";
				$new_str .= $string;
			$new_str .= "</pre>";
			
			echo $new_str;
		}
		
		function get_request_default($name, $default='', $set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = trim($_REQUEST[$name]);
				return $newRequest;
			}else{
				if($set) $_REQUEST[$name] = $default;
				return $default;
			}
		}
		
		
		function get_post_meta($post_id, $key, $key_prefix = "", $single = true ){
			return get_post_meta($post_id, $key_prefix.$key, $single);
		}
		
		function export_to_pdf($export_rows = array(),$output){
			if(count($export_rows)>0){
				
				$export_file_name 		= $this->get_request('export_file_name',"no");
				$today 					= date_i18n("Y-m-d-H-i-s");
				$export_file_format 	= 'pdf';
				
				$report_name 			= $this->get_request('report_name','');	
							
				if(strlen($report_name)> 0){
					$report_name 			= str_replace("_page","_list",$report_name);
					$report_name 			= $report_name."-";
				}
				
				$file_name 				= $export_file_name."-".$report_name.$today.".".$export_file_format;
				$file_name 				= str_replace("_","-",$file_name);
				
				$orientation_pdf 		= $this->get_request('orientation_pdf',"portrait");				
				$paper_size 			= $this->get_request('paper_size',"letter");
				
				require_once("ic_commerce_pro_subscription_dompdf_config.inc.php");
				$dompdf = new DOMPDF();	
				$dompdf->set_paper($paper_size,$orientation_pdf);
				$dompdf->load_html($output);
				$dompdf->render();
				$dompdf->stream($file_name);				
			}
		}
		
		function get_order_item_variation_sku($order_item_id = 0){
			global $wpdb;
			$sql = "
			SELECT 
			postmeta_sku.meta_value AS variation_sku				
			FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value
			WHERE woocommerce_order_items.order_item_id={$order_item_id}
			
			AND woocommerce_order_items.order_item_type = 'line_item'
			AND woocommerce_order_itemmeta.meta_key = '_variation_id'
			AND postmeta_sku.meta_key = '_sku'
			";
			return $orderitems = $wpdb->get_var($sql);
		}
		
		function get_order_product_sku($product_id = 0){
			global $wpdb;
			$sql = "SELECT postmeta_sku.meta_value AS product_sku
			FROM {$wpdb->prefix}postmeta as postmeta_sku			
			WHERE postmeta_sku.meta_key = '_sku' and postmeta_sku.post_id = {$product_id}";
			return $orderitems = $wpdb->get_var($sql);
		}
		
		function get_sku($order_item_id, $product_id){
			$td_value = $this->get_order_item_variation_sku($order_item_id);
			$td_value = strlen($td_value) > 0 ? $td_value : $this->get_order_product_sku($product_id);
			$td_value = strlen($td_value) > 0 ? $td_value : 'Not Set';
			return $td_value;
		}
		
		function get_order_item_variation_stock($order_item_id = 0){
			global $wpdb;
			$sql = "
			SELECT 
			postmeta_sku.meta_value AS variation_sku				
			FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value
			WHERE woocommerce_order_items.order_item_id={$order_item_id}
			
			AND woocommerce_order_items.order_item_type = 'line_item'
			AND woocommerce_order_itemmeta.meta_key = '_variation_id'
			AND postmeta_sku.meta_key = '_stock'
			";
			return $orderitems = $wpdb->get_var($sql);
		}
		
		function get_order_product_stock($product_id = 0){
			global $wpdb;
			$sql = "SELECT postmeta_stock.meta_value AS product_sku
			FROM {$wpdb->prefix}postmeta as postmeta_stock			
			WHERE postmeta_stock.meta_key = '_stock' and postmeta_stock.post_id = {$product_id}";
			return $orderitems = $wpdb->get_var($sql);
		}
		
		
		function get_stock_($order_item_id, $product_id){
			$td_value = $this->get_order_item_variation_stock($order_item_id);
			$td_value = strlen($td_value) > 0 ? $td_value : $this->get_order_product_stock($product_id);
			$td_value = strlen($td_value) > 0 ? $td_value : 'Not Set';
			return $td_value;
		}
		
		function get_product_category(){
				
				global $wpdb;
				$sql = "
				SELECT 
				woocommerce_order_itemmeta.meta_value 		AS id, 
				woocommerce_order_items.order_item_name 	AS label,
				term_taxonomy.term_id 						AS parent_id,
				CONCAT(term_taxonomy.term_id,'-',woocommerce_order_itemmeta.meta_value) AS category_product_id,
				terms.name 						AS name
				
				FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items				
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
				
				$sql .= " 
						LEFT JOIN {$wpdb->prefix}term_relationships		AS term_relationships		ON term_relationships.object_id				= woocommerce_order_itemmeta.meta_value
						LEFT JOIN {$wpdb->prefix}term_taxonomy			AS term_taxonomy			ON term_taxonomy.term_taxonomy_id			= term_relationships.term_taxonomy_id
						LEFT JOIN {$wpdb->prefix}terms					AS terms					ON terms.term_id							= term_taxonomy.term_id";
				
				//if($publish_order == "yes")	$sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON posts.ID = woocommerce_order_items.order_id";				
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id'";
				$sql .= " AND term_taxonomy.taxonomy = 'product_cat'";
				
				//if($publish_order == 'yes')	$sql .= " AND posts.post_status = 'publish'";				
				//if($publish_order == 'publish' || $publish_order == 'trash')	$sql .= " AND posts.post_status = '".$publish_order."'";
				
				$sql .= " GROUP BY category_product_id ORDER BY woocommerce_order_items.order_item_name ASC";
			
				$products = $wpdb->get_results($sql);
			
				return $products;
		}
		
		//New Change ID 20140918
		function get_set_status_ids(){
				if(isset($this->constants['shop_order_status'])){
					$stauts_ids = $this->constants['shop_order_status'];
				}else{
					
					if($this->constants['post_order_status_found'] == 0 ){
					
						$stauts_ids = $this->get_setting('shop_order_status',$this->constants['plugin_options'],false);
						//$this->print_array($stauts_ids);
						//echo "test";
						if(!$stauts_ids){
							
							$detault_stauts_slug	= (isset($this->constants['detault_stauts_slug']) and count($this->constants['detault_stauts_slug'])>0) ? $this->constants['detault_stauts_slug'] : array();
							
							if(count($detault_stauts_slug)>0){
								$detault_stauts_id		= array();
								//$detault_stauts_slug 	= array_merge(array('completed'), (array)$detault_stauts_slug);
								
								$new_shop_order_status 	= array();
								$shop_order_status 		= $this->shop_order_status($detault_stauts_slug);
								foreach($shop_order_status as $key => $value){
									$new_shop_order_status[$value->id] = ucfirst($value->label);				
									if(in_array($value->label,$detault_stauts_slug)){
										$detault_stauts_id[]= $value->id;
									}
								}
								
								$stauts_ids = $detault_stauts_id;
							}else{
								$stauts_ids = $detault_stauts_slug;
							}
						}else{
							$stauts_ids = $stauts_ids;
						
						}
					}else if($this->constants['post_order_status_found'] == 1 ){
						$stauts_ids = $this->get_setting('post_order_status',$this->constants['plugin_options'],false);
						if(!$stauts_ids){
							$detault_order_status	= (isset($this->constants['detault_order_status']) and count($this->constants['detault_order_status'])>0) ? $this->constants['detault_order_status'] : array();
							$stauts_ids = $detault_order_status;	
						}
					}
					
					if(isset($stauts_ids[0]) and $stauts_ids[0] == 'all') unset($stauts_ids[0]);
					if(isset($stauts_ids[0]) and $stauts_ids[0] == 'all2') unset($stauts_ids[0]);
				}
				$this->constants['shop_order_status']	=	$stauts_ids;				
				return $stauts_ids;
			}
			
			function get_subscription_status(){
				if(isset($this->constants['subscription_status'])){
					$stauts_ids = $this->constants['subscription_status'];
				}else{
					
					
					$stauts_ids = $this->get_setting('subscription_status',$this->constants['plugin_options'],false);
					if(!$stauts_ids){
						$detault_subs_status	= (isset($this->constants['detault_subs_status']) and count($this->constants['detault_subs_status'])>0) ? $this->constants['detault_subs_status'] : array();
						$stauts_ids = $detault_subs_status;
					}					
					if(isset($stauts_ids[0]) and $stauts_ids[0] == 'all') unset($stauts_ids[0]);
					if(isset($stauts_ids[0]) and $stauts_ids[0] == 'all2') unset($stauts_ids[0]);
				}
				$this->constants['subscription_status']	=	$stauts_ids;				
				return $stauts_ids;
			}
			
			//New Change ID 20140918
			function shop_order_status($shop_order_status = array()){
				global $wpdb;
				
				$sql = "SELECT terms.term_id AS id, terms.name AS label, terms.slug AS slug
				FROM {$wpdb->prefix}terms as terms				
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_id = terms.term_id
				WHERE term_taxonomy.taxonomy = 'shop_order_status'";
				
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode("', '",$shop_order_status);
					$sql .= "	AND terms.slug IN ('{$in_shop_order_status}')";
				}
				
				$sql .= "
				GROUP BY terms.term_id
				ORDER BY terms.name ASC";
				
				$shop_order_status = $wpdb->get_results($sql);
				
				return $shop_order_status;
			}//END shop_order_status
			
			//New Change ID 20140918
			function ic_get_order_statuses_slug_id(){
				return $this->shop_order_status();
			}
			
			//New Change ID 20140918
			function get_value($data = NULL, $id, $default = ''){
				if($data){
					if($data->$id)
						return $data->$id;
				}
				return $default;
			}
			
			//New Change ID 20140918
			function get_setting($id, $data, $defalut = NULL){
				if(isset($data[$id]))
					return $data[$id];
				else
					return $defalut;
			}
			
			//New Change ID 20140918
			function get_setting2($id, $data, $defalut = NULL){
				if(isset($data[$id]))
					return array($data[$id]);
				else
					return $defalut;
			}
			
			//New Change ID 20140918
			function get_post_order_status($key = NULL){
				$sql = "SELECT DATE_FORMAT(posts.post_date, '%Y-%m-%d') AS 'OrderDate' FROM {$wpdb->prefix}posts  AS posts	WHERE posts.post_type='shop_order' Order By posts.post_date ASC LIMIT 1";
				return $this->firstorderdate = $wpdb->get_var($sql);
				
				global $wpdb;
			}
			
			//New Change ID 20140918
			function ic_get_order_statuses(){
				if(!isset($this->constants['wc_order_statuses'])){
					if(function_exists('wc_get_order_statuses')){
						$order_statuses = wc_get_order_statuses();						
					}else{
						$order_statuses = array();
					}
					
					$order_statuses['trash']	=	"Trash";
										
					$this->constants['wc_order_statuses'] = $order_statuses;
				}else{
					$order_statuses = $this->constants['wc_order_statuses'];
				}
				return $order_statuses;
			}
			
			//New Change ID 20140918
			function ic_get_order_status($order_item){
				if(!isset($this->constants['wc_order_statuses'])){
					$order_statuses = $this->ic_get_order_statuses();
				}else{
					$order_statuses = $this->constants['wc_order_statuses'];
				}
				
				$order_status = isset($order_item->order_status) ? $order_item->order_status : '';
				$order_status = isset($order_statuses[$order_status]) ? $order_statuses[$order_status] : $order_status;
				return $order_status;
			}
			
			//New Change ID 20140918
			function get_post_order_status2(){
				global $wpdb;
				
				$sql = " SELECT post_status as id, post_status as label, post_status as order_status  FROM {$wpdb->prefix}posts WHERE  post_type IN ('shop_order') AND post_status NOT IN ('auto-draft','inherit','publish') GROUP BY post_status ORDER BY post_status";				
				$order_items = $wpdb->get_results($sql);
				
				$order_statuses = $this->ic_get_order_statuses();
				$trash_label = "";
				$trash_id 	= "";
				$order_statuses_found = array();
				if(count($order_statuses)>0){
					foreach ( $order_items as $key => $order_item ) {
						if($order_item->order_status == "trash"){
							$trash_label 	= isset($order_statuses[$order_item->order_status]) ? $order_statuses[$order_item->order_status] : '';						
						}else{
							$order_statuses_found[$order_item->id] 	= isset($order_statuses[$order_item->order_status]) ? $order_statuses[$order_item->order_status] : '';
						}
					}
										
					if($trash_label){
						if(!in_array('trash',$this->constants['hide_order_status'])){
							$order_statuses_found['trash'] 	= $trash_label;
						}
					}
				}
				
				
				return $order_statuses_found;
			}
			
			function humanTiming ($time, $current_time = NULL, $suffix = ''){
				if($time){
					if($current_time == NULL)
						$time = time() - $time; // to get the time since that moment
					else
						$time = $current_time - $time; // to get the time since that moment
				
					$tokens = array (
						31536000 => 'year',
						2592000 => 'month',
						604800 => 'week',
						86400 => 'day',
						3600 => 'hour',
						60 => 'minute',
						1 => 'second'
					);
				
					foreach ($tokens as $unit => $text) {
						if ($time < $unit) continue;
						$numberOfUnits = floor($time / $unit);
						return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'') .$suffix;
					}
				}else{
					return 0;
				}		
			}
			
			function get_woocommerce_currency_symbol_pdf( $currency = '' ) {				
				add_filter('woocommerce_currency_symbol', array($this, 'get_woocommerce_currency_symbol'),10,2);
			}
			
			function get_woocommerce_currency_symbol( $currency_symbol = '', $currency = '' ) {				
				$new_currency_symbol = "";				
				switch ( $currency ) {
					case 'AED' : $currency_symbol = $new_currency_symbol; break;
					case 'BDT' : $currency_symbol = $new_currency_symbol; break;
					case 'BRL' : $currency_symbol = $new_currency_symbol; break;
					case 'BGN' : $currency_symbol = $new_currency_symbol; break;						
					case 'RUB' : $currency_symbol = $new_currency_symbol; break;
					case 'KRW' : $currency_symbol = $new_currency_symbol; break;
					case 'TRY' : $currency_symbol = $new_currency_symbol; break;
					case 'NOK' : $currency_symbol = $new_currency_symbol; break;
					case 'ZAR' : $currency_symbol = $new_currency_symbol; break;
					case 'CZK' : $currency_symbol = $new_currency_symbol; break;
					case 'MYR' : $currency_symbol = $new_currency_symbol; break;
					case 'HUF' : $currency_symbol = $new_currency_symbol; break;
					case 'ILS' : $currency_symbol = $new_currency_symbol; break;
					case 'PHP' : $currency_symbol = $new_currency_symbol; break;
					case 'PLN' : $currency_symbol = $new_currency_symbol; break;
					case 'SEK' : $currency_symbol = $new_currency_symbol; break;
					case 'CHF' : $currency_symbol = $new_currency_symbol; break;
					case 'TWD' : $currency_symbol = $new_currency_symbol; break;
					case 'THB' : $currency_symbol = $new_currency_symbol; break;
					case 'VND' : $currency_symbol = $new_currency_symbol; break;
					case 'NGN' : $currency_symbol = $new_currency_symbol; break;
					default    : $currency_symbol = $currency_symbol; break;
				}
				return $currency_symbol;
			}
			
			//New Change ID 20141010
			function get_variation_values($variation_attributes = NULL, $all_attributes = NULL){
				global $wpdb;
				//
					$sql = "
					SELECT
					postmeta_variation.meta_value AS variation 
					,postmeta_variation.meta_key AS attribute
					FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
					LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_variation ON postmeta_variation.post_id = woocommerce_order_itemmeta.meta_value";
					
					$var = array();
					if($variation_attributes != NULL and $variation_attributes != '-1' and strlen($variation_attributes) > 0){
						$variations = explode(",",$variation_attributes);
						foreach($variations as $key => $value):
							$var[] .=  "attribute_pa_".$value;
							$var[] .=  "attribute_".$value;
						endforeach;
						$variation_attributes =  implode("', '",$var);
					}
					$sql .= "
					
					WHERE 
					
					woocommerce_order_items.order_item_type = 'line_item'
					AND woocommerce_order_itemmeta.meta_key = '_variation_id'
					AND postmeta_variation.meta_key like 'attribute_%'";
					
					if($variation_attributes != NULL and $variation_attributes != "-1" and strlen($variation_attributes)>1)
						$sql .= " AND postmeta_variation.meta_key IN ('{$variation_attributes}')";
					else				
						$sql .= " AND postmeta_variation.meta_key like 'attribute_%'";
					
					
					/*if($variation_attributes != NULL and $variation_attributes != "-1" and strlen($variation_attributes)>1)
						$sql .= " AND postmeta_variation.meta_key IN ('{$variation_attributes}')";
					else				
						$sql .= " AND postmeta_variation.meta_key like 'attribute_%'";*/
					
					/*	
					
					*/
					$items = $wpdb->get_results($sql);
					//echo mysql_error();
					
					//$this->print_array($items);
					
					$variations = array();
					$variations2 = array();
					foreach($items as $key => $value):
						if(!isset($variations2[$value->variation])){
							$var = $value->attribute;
							$var = str_replace("attribute_pa_","",$var);
							$var = str_replace("attribute_","",$var);
							
							
							$var2 = $value->variation;
							if(strlen($var2)>0){
								$var2 = str_replace("-"," ",$var2);
							}else{
								$var2 = $var;
							}
							//$variations[$var] = ucfirst($var2);						
							$variations2[$value->variation] = ucfirst($var2);
						}
							
						
					endforeach;	
					
					return $variations2;
			}
			
			//New Change ID 20141016
			function create_summary($request = array()){
				$report_name 		= $this->get_request('report_name');
				$total_columns 		= $this->result_columns($report_name);
				$summary 			= array();
				$summary['total_row_amount'] 		= isset($request['total_row_amount']) 		? $request['total_row_amount'] : '';
				$summary['total_row_count'] 		= isset($request['total_row_count']) 		? $request['total_row_count'] : '';
				
				//$this->print_array($total_columns);
				
				if(count($total_columns) > 0){
					foreach($total_columns as $key => $label):
						$summary[$key] 	= isset($request[$key]) 	? $request[$key] : '';
					endforeach;
				}
				return $summary;						
			}
			
			//New Change ID 20141016
			function result_grid($report_name = '', $summary = array(),$zero=''){			
				 $output		= "";
				// $output .= $this->print_array($summary,false);
				 if(count($summary) > 0){
						$total_columns = $this->result_columns($report_name);
						//$output .= $this->print_array($total_columns,false);
						$output .= '<table class="widefat summary_table sTable3" cellpadding="0" cellspacing="0">';
						$output .= '<thead>';
						$output .=	'<tr class="first">';				
						foreach($total_columns as $key => $label):
							$td_class = $key;
							$td_style = '';
							$td_value = "";
							switch($key):									
								case "total_row_amount":
								case "ic_commerce_order_item_count":
									$td_value = $label;
									$td_class .= " amount";
									break;
								default:
									$td_value = $label;
									break;
							endswitch;
							$td_content = "<th class=\"{$td_class}\"{$td_style}>{$td_value}</th>\n";
							$output .= $td_content;
						endforeach;									
						$output .=	'</tr>';
						$output .=	'</thead>';
						$output .=	'<tbody>';
						$output .= "<tr>";	
						foreach($total_columns as $key => $label):
							$td_class = $key;
							$td_style = '';
							$td_value = "";
							switch($key):									
								case "cost_of_good_amount":
								case "total_cost_good_amount":
								case "sales_rate_amount":
								case "total_amount":
								case "margin_profit_amount":
								
								case "coupon_amount":
								
								case "order_shipping":
								case "order_shipping_tax":
								case "order_tax":
								case "gross_amount":
								case "order_discount":
								case "order_total":
								case "total_amount":
								
								case "product_rate":
								case "total_price":
								
									$td_value = isset($summary[$key]) ? $summary[$key] : 0;
									$td_value = $td_value > 0 ? $this->price($td_value) : $zero;
									$td_class .= " amount";
									break;
								case "ic_commerce_order_item_count":
								case "total_row_count":
								case "quantity":
								case "product_quantity":
								default:
									$td_value = isset($summary[$key]) ? $summary[$key] : '';
									$td_class .= " amount";
									break;
							endswitch;
							$td_content = "<td class=\"{$td_class}\"{$td_style}>{$td_value}</td>\n";
							$output .= $td_content;
						endforeach;
						$output .=	'</tr>';
						$output .=	'</tbody>';
						$output .=	'</table>';
				}
				return $output;
			}
			
			function get_grid_columns(){
				include_once('ic_commerce_lite_subscriptions_columns.php');
				$grid_column = new IC_Commerce_Lite_Subscription_Columns($this->constants);
				return $grid_column;
			}
			
			function get_old_order_status($old = array('cancelled'),$new = array('cancelled')){
				if($this->constants['post_order_status_found'] == 0 ){
					$shop_order_status 		= $this->shop_order_status();			
					$detault_stauts_slug	= $old;
					$detault_stauts_id		= array();
					
					foreach($shop_order_status as $key => $value){
						$new_shop_order_status[$value->id] = ucfirst($value->label);
						if(in_array($value->label,$detault_stauts_slug)){
							$detault_stauts_id[]= $value->id;
						}
					}				
					$cancelled_id = $detault_stauts_id;
				}else{
					$cancelled_id = $new;
				}			
				return $cancelled_id;
			}
			
			function get_option(){
				
				$option_key		= $this->constants['plugin_key'];
				$option_array 	= get_option($option_key);
				
				if(is_array($option_array) and count($option_array)>0){
					//$this->print_array($option_array);
				}else{
					delete_option($option_key);
					$option_array = array();
					add_option($option_key,$option_array);
				}
				
				return $option_array;
			}
			
			function get_required_postmeta($order_ids = '0', $columns = array(), $extra_meta_keys = array()){
			
				global $wpdb;
				
				$post_meta_keys = array();
				
				foreach($columns as $key => $label){
					$post_meta_keys[] = $key;
				}
				
				foreach($extra_meta_keys as $key => $label){
					$post_meta_keys[] = $label;
				}
				
				foreach($post_meta_keys as $key => $label){
					$post_meta_keys[] = "_".$label;
				}
				
				$post_meta_key_string = implode("', '",$post_meta_keys);
				
				$sql = " SELECT * FROM {$wpdb->postmeta} AS postmeta";
				
				$sql .= " WHERE 1*1";
				
				if(strlen($order_ids) >0){
					$sql .= " AND postmeta.post_id IN ($order_ids)";
				}
				
				if(strlen($post_meta_key_string) >0){
					$sql .= " AND postmeta.meta_key IN ('{$post_meta_key_string}')";
				}
				
				$sql .= " ORDER BY postmeta.post_id ASC, postmeta.meta_key ASC";
				
				//echo $sql;return '';
				
				$order_meta_data = $wpdb->get_results($sql);			
				
				if($wpdb->last_error){
					echo $wpdb->last_error;
				}else{
					$order_meta_new = array();	
						
					foreach($order_meta_data as $key => $order_meta){
						
						$meta_value	= $order_meta->meta_value;
						
						$meta_key	= $order_meta->meta_key;
						
						$post_id	= $order_meta->post_id;
						
						$meta_key 	= ltrim($meta_key, "_");
						
						$order_meta_new[$post_id][$meta_key] = $meta_value;
						
					}
				}
				
				return $order_meta_new;
			
			}//End Method
			
			function get_items_id_list($order_items = array(),$field_key = 'order_id', $return_default = '-1' , $return_formate = 'string'){
				$list 	= array();
				$string = $return_default;
				if(count($order_items) > 0){
					foreach ($order_items as $key => $order_item) {
						$id = isset($order_item->$field_key) ? trim($order_item->$field_key) : '';
						
						if($id)	$list[] = $order_item->$field_key;
					}
					
					$list = array_unique($list);
					
					if($return_formate == "string"){
						$string = implode(",",$list);
					}else{
						$string = $list;
					}
				}
				return $string;
			}
			
			function ic_wcs_get_subscription_statuses(){
				if(!isset($this->constants['wc_subscription_statuses'])){
					if(function_exists('wcs_get_subscription_statuses')){
						$wc_subscription_statuses = wcs_get_subscription_statuses();						
					}else{
						$wc_subscription_statuses = array();
					}
					
					$wc_subscription_statuses['trash']	=	"Trash";
										
					$this->constants['wc_subscription_statuses'] = $wc_subscription_statuses;
				}else{
					$wc_subscription_statuses = $this->constants['wc_subscription_statuses'];
				}
				return $wc_subscription_statuses;
			}
			
			function get_dates_between($start_date, $end_date,$report_dates = array()){
				$start_date_strtotime 	= strtotime($start_date);
				$end_date_strtotime 	= strtotime($end_date);
				$current_strtotime		= $start_date_strtotime;
				$i						= 0;
				while($current_strtotime <= $end_date_strtotime){
					$current_date 		= date("Y-m-d",$current_strtotime);
					$report_dates[] 	= $current_date;		
					$i++;				
					$current_strtotime = strtotime("+ 1 day",$current_strtotime);
							
				}
				return $report_dates;
			}
			
			function get_pdf_paper_size(){
				$paper_sizes = array(
					"letter"	=>__("Letter",'icwoocommerce_textdomains'),
					"legal"		=>__("Legal",'icwoocommerce_textdomains'),
					"a0"		=>__("A0",'icwoocommerce_textdomains'),
					"a1"		=>__("A1",'icwoocommerce_textdomains'),
					"a2"		=>__("A2",'icwoocommerce_textdomains'),
					"a3"		=>__("A3",'icwoocommerce_textdomains'),
					"a4"		=>__("A4",'icwoocommerce_textdomains'),
					"a5"		=>__("A5",'icwoocommerce_textdomains'),
					"a6"		=>__("A6",'icwoocommerce_textdomains')
				);
				
				$paper_sizes = apply_filters('icwoocommerce_paper_sizes', $paper_sizes);
				
				return $paper_sizes;
			}
			
			function get_pdf_style_align($columns=array(),$alight='right',$output = '',$prefix = "", $report_name = NULL){
				$output_array 	= array();
				$report_name	= $report_name == NULL ? $this->get_request('report_name','') : $report_name;
				$custom_columns = apply_filters("ic_commerce_pdf_custom_column_right_alignment",array(), $columns,$report_name);
				foreach($columns as $key => $value):
					switch ($key) {
						case "sale_price":
							$output_array[] = "{$prefix} th.{$key}";
							$output_array[] = "{$prefix} td.{$key}";
							break;
						default:	
							if(isset($custom_columns[$key])){
								$output_array[] = "{$prefix} th.{$key}";
								$output_array[] = "{$prefix} td.{$key}";
							}												
							/*Default align*/
							break;
					}
				endforeach;
				
				if(count($output_array)>0){
					$output .= implode(",",$output_array);
					$output .= "{text-align:{$alight};}";
				}
				
				return $output;
			}
			
			function common_request_form(){
				$_REQUEST['date_format']			= isset($_REQUEST['date_format']) 			? trim($_REQUEST['date_format']) 			: get_option('date_format',"jS F Y");
				$_REQUEST['formatted_start_date']	= isset($_REQUEST['formatted_start_date']) 	? trim($_REQUEST['formatted_start_date']) 	: (isset($_REQUEST['start_date']) 	? date($_REQUEST['date_format'],strtotime($_REQUEST['start_date'])) : '');
				$_REQUEST['formatted_end_date']		= isset($_REQUEST['formatted_end_date']) 	? trim($_REQUEST['formatted_end_date']) 	: (isset($_REQUEST['end_date']) 	? date($_REQUEST['date_format'],strtotime($_REQUEST['end_date'])) 	: '');
			}
			
			function create_hidden_fields($request = array(), $type = "hidden"){
				$output_fields = "";
				//$this->print_array($request);
				foreach($request as $key => $value):
					if(is_array($value)){
						foreach($value as $akey => $avalue):
							if(is_array($avalue)){
								$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"".implode(",",$avalue)."\" />";
							}else{
								$output_fields .=  "<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"{$avalue}\" />";
							}
						endforeach;
					}else{
						$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" value=\"{$value}\" />";
					}
				endforeach;
				return $output_fields;
			}
			
			function create_search_form_hidden_fields($request = array(), $type = "hidden"){
				$output_fields = "";
				foreach($request as $key => $value):
					$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />";
				endforeach;
				return $output_fields;
			}
			
			function get_pagination($total_pages = 50,$limit = 10,$adjacents = 3,$targetpage = "admin.php?page=RegisterDetail",$request = array()){		
				
				if(count($request)>0){
					unset($request['p']);
					//$new_request = array_map(create_function('$key, $value', 'return $key."=".$value;'), array_keys($request), array_values($request));
					//$new_request = implode("&",$new_request);
					//$targetpage = $targetpage."&".$new_request;
				}
				
				
				/* Setup vars for query. */
				//$targetpage = "admin.php?page=RegisterDetail"; 	//your file name  (the name of this file)										
				/* Setup page vars for display. */
				if(isset($_REQUEST['p'])){
					$page = $_REQUEST['p'];
					$_GET['p'] = $page;
					$start = ($page - 1) * $limit; 			//first item to display on this page
				}else{
					$page = false;
					$start = 0;	
					$page = 1;
				}
				
				if ($page == 0) $page = 1;					//if no page var is given, default to 1.
				$prev = $page - 1;							//previous page is page - 1
				$next = $page + 1;							//next page is page + 1
				$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
				$lpm1 = $lastpage - 1;						//last page minus 1
				
				
				
				$label_previous = __('previous', 'icwoocommerce_textdomains');
				$label_next = __('next', 'icwoocommerce_textdomains');
				
				/* 
					Now we apply our rules and draw the pagination object. 
					We're actually saving the code to a variable in case we want to draw it more than once.
				*/
				$pagination = "";
				if($lastpage > 1)
				{	
					$pagination .= "<div class=\"pagination\">";
					//previous button
					if ($page > 1) 
						$pagination.= "<a href=\"$targetpage&p=$prev\" data-p=\"$prev\">{$label_previous}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_previous}</span>\n";	
					
					//pages	
					if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
					{	
						for ($counter = 1; $counter <= $lastpage; $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>\n";
							else
								$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
						}
					}
					elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
					{
						//close to beginning; only hide later pages
						if($page < 1 + ($adjacents * 2))		
						{
							for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//in middle; hide some front and some back
						elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//close to end; only hide early pages
						else
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
						}
					}
					
					//next button
					if ($page < $counter - 1) 
						$pagination.= "<a href=\"$targetpage&p=$next\" data-p=\"$next\">{$label_next}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_next}</span>\n";
					$pagination.= "</div>\n";		
				}
				return $pagination;
		}
		
		function get_products_list_in_category($categories = array(), $products = array(), $return_default = '-1' , $return_formate = 'string'){
				global $wpdb;
				
				$category_product_id_string = $return_default;
				
				if(is_array($categories)){
					$categories = implode(",",$categories);
				}
				
				if(is_array($products)){
					$products = implode(",",$products);
				}
				
				if($categories  && $categories != "-1") {
				
					$sql  = " SELECT ";					
					$sql .= " woocommerce_order_itemmeta.meta_value		AS product_id";					
					
					$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id";
					$sql .= " LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	woocommerce_order_itemmeta.meta_value ";
					$sql .= " LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";								
					$sql .= " WHERE 1*1 AND woocommerce_order_itemmeta.meta_key 	= '_product_id'";					
					$sql .= " AND term_taxonomy.term_id IN (".$categories .")";
										
					if($products  && $products != "-1") $sql .= " AND woocommerce_order_itemmeta.meta_value IN (".$products .")";
					
					$sql .= " GROUP BY  woocommerce_order_itemmeta.meta_value";
					
					$sql .= " ORDER BY product_id ASC";
					
					$order_items = $wpdb->get_results($sql);					
					$product_id_list = array();
					if(count($order_items) > 0){
						foreach($order_items as $key => $order_item) $product_id_list[] = $order_item->product_id;
						if($return_formate == 'string'){
							$category_product_id_string = implode(",", $product_id_list);
						}else{
							$category_product_id_string = $product_id_list;
						}
					}
				}
				
				return $category_product_id_string;
				
			}
		
		function get_wc_countries(){
			return class_exists('WC_Countries') ? (new WC_Countries) : (object) array();
		}
		
		function get_wc_states($country_code){
			global $woocommerce;
			return isset($woocommerce) ? $woocommerce->countries->get_states($country_code) : array();
		}
		
		var $states_name = array();
		var $country_states = array();
		function get_billling_state_name($cc = NULL,$st = NULL){
			global $woocommerce;
			$state_code = $st;
			
			if(!$cc) return $state_code;
			
			if(isset($this->states_name[$cc][$st])){
				$state_code = $this->states_name[$cc][$st];				
			}else{
				
				if(isset($this->country_states[$cc])){
					$states = $this->country_states[$cc];
				}else{
					$states = $this->get_wc_states($cc);//Added 20150225
					$this->country_states[$cc] = $states;						
				}				
				
				if(is_array($states)){					
					$state_code = isset($states[$state_code]) ? $states[$state_code] : $state_code;
				}
				
				$this->states_name[$cc][$st] = $state_code;				
			}
			return $state_code;
		}
		
	}//End Class
}//End Class Exists