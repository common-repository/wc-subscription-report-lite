<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('IC_Commerce_Lite_Subscription_List')) {
	require_once('ic_commerce_lite_subscriptions_fuctions.php');	
    class IC_Commerce_Lite_Subscription_List extends IC_Commerce_Lite_Subscription_Fuctions{
		
		public $per_page = 0;	
		
		public $per_page_default = 100;
		
		public $request_data =	array();
		
		public $constants 	=	array();
		
		public $request		=	array();
		
		public $order_meta	= array();
		
		public function __construct($constants){
			global $options, $last_days_orders;
			
			$this->constants		= $constants;			
			$options				= $this->constants['plugin_options'];
			$this->per_page_default	= $this->constants['per_page_default'];			
			$per_page 				= (isset($options['per_apge']) and strlen($options['per_apge']) > 0)? $options['per_apge'] : $this->per_page_default;
			$this->per_page 		= is_numeric($per_page) ? $per_page : $this->per_page_default;
			//$this->per_page = 600;
		}
		
		function test(){
			global $wpdb;
			$sql = "SELECT 
					posts.ID as order_id
					,date_format( posts.post_date, '%Y-%m-%d') as order_date 
					,woocommerce_order_items.order_item_id
					,woocommerce_order_items.order_item_name
					FROM {$wpdb->prefix}posts as posts  
					
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID 
					
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id 
					
					WHERE 
					
					posts.post_type ='shop_order'  
					AND woocommerce_order_items.order_item_type ='line_item'
					AND woocommerce_order_itemmeta.meta_key ='_subscription_status'
					AND date_format( posts.post_date, '%Y-%m-%d') between '2014-01-01' and '2015-01-01'
					";
			$order_items = $wpdb->get_results($sql);
			//$this->print_array( $order_items);		
		} 
		
		function init(){
			
			if(!isset($_REQUEST['page'])){return false;}
			
			if ( !current_user_can( $this->constants['plugin_role'] ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			
			//New Change ID 20140918
				$shop_order_status		= $this->get_set_status_ids();	
				$hide_order_status		= $this->constants['hide_order_status'];
				$hide_order_status		= implode(",",$hide_order_status);
				
				$order_status_id 		= "";
				$order_status 			= "";
				
				if($this->constants['post_order_status_found'] == 0 ){					
					$order_status_id 	= implode(",",$shop_order_status);
				}else{
					$order_status_id 	= "";
					$order_status 		= implode(",",$shop_order_status);
				}
				
				$order_status			= strlen($order_status) > 0 		?  $order_status 		: '-1';
				$order_status_id		= strlen($order_status_id) > 0 		?  $order_status_id 	: '-1';
				$hide_order_status		= strlen($hide_order_status) > 0 	?  $hide_order_status 	: '-1';
				
				$publish_order			= "no";				
				
				$subscription_status 	= $this->get_subscription_status();
				$subscription_status 	= implode(",",$subscription_status);
				
				$cancelled_id 			= $this->get_old_order_status(array('cancelled','processing'), array('wc-cancelled','wc-processing'));
				$cancelled_id 			= implode(",",$cancelled_id);
				
				$optionsid				= "per_row_details_page";
				$per_page 				= $this->get_number_only($optionsid,$this->per_page_default);
				//$per_page				= 600;
				$last_days_orders		= $this->get_number_only('last_days_subscription',15);
				
				$page_title 		= apply_filters( $this->constants['plugin_key'].'_page_default_title', "Daily Summary Report");
				$detail_view		= apply_filters( $this->constants['plugin_key'].'_default_detail_view', "yes");
				
				$sales_order		= $this->get_request('sales_order',false);	
				$end_date				= $this->get_request('end_date',false);
				$start_date			= $this->get_request('start_date',false);
				$order_status_id	= $this->get_request('order_status_id',$order_status_id,true);//New Change ID 20140918
				$order_status		= $this->get_request('order_status',$order_status,true);//New Change ID 20140918
				$publish_order		= $this->get_request('publish_order',$publish_order,true);//New Change ID 20140918
				$hide_order_status	= $this->get_request('hide_order_status',$hide_order_status,true);//New Change ID 20140918
				$adjacents			= $this->get_request('adjacents',3,true);
				$page				= $this->get_request('page',NULL);
				$sort_by 			= $this->get_request('sort_by','order_id',true);
				$order_by 			= $this->get_request('order_by','DESC',true);
				$count_generated	= 0;
				
				$billing_email 		= $this->get_request('billing_email',NULL,true);
			    $country_code		= '-1';
				
				if(!$end_date){$end_date = date_i18n('Y-m-d');}
				if(!$start_date){
					$last_days_orders 		= apply_filters($page.'_back_day', $last_days_orders);//-1,-2,-3,-4,-5
					$start_date = date('Y-m-d', strtotime(" - " .$last_days_orders.' day', strtotime(date_i18n("Y-m-d"))));
					//echo $page.'_back_day';
				}
				
				$_REQUEST['end_date'] = $end_date;
				$_REQUEST['start_date'] = $start_date;
			
			//$this->test();
			?>
			<h2 class="hide_for_print"><?php _e($page_title,$this->constants['plugin_key']);?></h2>
            <div id="navigation" class="hide_for_print">
				<div class="collapsible" id="section1">Custom Search<span></span></div>
				<div class="container">
					<div class="content">
						<div class="search_report_form">
							<div class="form_process"></div>
							<form action="" name="Report" id="search_order_report" method="post">
								<div class="form-table">
									<div class="form-group">
										<div class="FormRow FirstRow">
											<div class="label-text"><label for="start_date">From Date:</label></div>
											<div class="input-text"><input type="text" value="<?php echo $start_date;?>" id="start_date" name="start_date" readonly maxlength="10" /></div>
										</div>
										<div class="FormRow">
											<div class="label-text"><label for="end_date">To Date:</label></div>
											<div class="input-text"><input type="text" value="<?php echo $end_date;?>" id="end_date" name="end_date" readonly maxlength="10" /></div>
										</div>
									</div>
									
									<div class="form-group">
										<div class="FormRow" style="width:100%">											
											<input type="hidden" name="cancelled_id" 			id="cancelled_id" 		value="<?php echo $cancelled_id;?>" />
											<input type="hidden" name="hide_order_status" 		id="hide_order_status" 	value="<?php echo $hide_order_status;?>" />
											<input type="hidden" name="publish_order" 			id="publish_order" 		value="<?php echo $publish_order;?>" />
											<input type="hidden" name="action" 					id="action" 			value="<?php echo $this->get_request('action',$this->constants['plugin_key'].'_wp_ajax_action',true);?>" />
											<input type="hidden" name="limit"  					id="limit" 				value="<?php echo $this->get_request('limit',$per_page,true);?>" />
											<input type="hidden" name="p"  						id="p" 					value="<?php echo $this->get_request('p',1,true);?>" />
											<input type="hidden" name="admin_page"  			id="admin_page" 		value="<?php echo $this->get_request('admin_page',$page,true);?>" />
											<input type="hidden" name="adjacents"  				id="adjacents" 			value="<?php echo $adjacents;?>" />
											<input type="hidden" name="do_action_type" 			id="do_action_type" 	value="<?php echo $this->get_request('do_action_type','subscription_list',true);?>" />
											<input type="hidden" name="ic_admin_page" 			id="ic_admin_page" 		value="<?php echo $this->get_request('ic_admin_page',$page,true);?>" />
											<input type="hidden" name="count_generated"  		id="count_generated" 	value="<?php echo $count_generated;?>" />
											
											<span class="submit_buttons">
												<input name="ResetForm" id="ResetForm" class="onformprocess" value="Reset" type="reset"> 
												<input name="SearchOrder" id="SearchOrder" class="onformprocess searchbtn btn_margin" value="Search" type="submit"> &nbsp; &nbsp; &nbsp; <span class="ajax_progress"></span>
											</span>
										</div>
									</div>
								</div>
							</form>							
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="table table_shop_content search_report_content hide_for_print"></div>
            <div id="search_for_print_block" class="search_for_print_block"></div>			
            <?php
		}
		
		function ic_commerce_report_ajax_request($type = 'limit_row'){
			$this->get_subscription_list($type);
		}
		
		function get_subscription_list($type = 'limit_row'){
			$request 			= $this->get_all_request();extract($request);
			$columns 			= $this->get_columns($report_name);			
			$subscription_lists = $this->subscription_list_query($type, $columns);						
			if(count($subscription_lists) > 0):
			
					$report_name 		= $this->get_request('report_name','subscription_list_page',true);
					
					$amount 			= array("order_total","billing_interval","qty");
					$summary 			= array();
					$TotalOrderCount	= 0;
					$TotalAmount		= 0;
					$total_pages		= 0;
					$summary 			= $this->subscription_list_query('total_row');
					$total_pages		= isset($summary['total_row_count']) ? $summary['total_row_count'] : 0;					
					$date_format		= get_option('date_format');
				
					$this->print_header($type);	
					?>                    
					<table style="width:100%" class="widefat widefat_normal_table" cellpadding="0" cellspacing="0">
							<thead>
								<tr class="first">
                                	<?php 
										$cells_status = array();
										foreach($columns as $key => $value):
											$class = $key;
											if(in_array($key,$amount)){
												$class .= " amount";
											}
									?>
                                    	<th class="<?php echo $class;?>"><?php echo $value;?></th>
                                    <?php endforeach;?>
								</tr>
							</thead>
							
							<tbody>
								<?php					
								foreach ( $subscription_lists as $key => $item ) {
									if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
									$TotalOrderCount = $TotalOrderCount + 1;
								//	$TotalAmount = $TotalAmount + (isset($item->order_total) ? $item->order_total : 0);
										$TotalAmount = $TotalAmount + (isset($item->line_total) ? $item->line_total : 0);
									
									?>
									<tr class="<?php echo $alternate."row_".$key;?>">
                                    <?php
									foreach ( $columns as $key => $label ) {
										$td_value 	= "";
										$td_class 	= $key;
										$cell_value = isset($item->$key) ? $item->$key : '';
										$td_value 	= $cell_value;
										
										switch($key){
											case "billing_interval":
											case "qty":
												$td_class .= " amount";
												break;
											case "order_total":
												$td_class .= " amount";
												$td_value = $td_value == 0 ? $zero : $this->price($td_value);
												break;
											case "order_date":
											case "schedule_trial_end":
											case "subscription_date":
											case "schedule_end":
												$td_value 	= empty($cell_value) ? '' : date($date_format, strtotime($cell_value));
												break;
											default:
												
											break;
										}
										
										echo "<td class=\"{$td_class}\">{$td_value}</td>";										
									}
									?>
									</tr>									
									<?php 
								} ?>
							</tbody>							           
						</table>
                        <?php 
						 if($type != 'all_row') $this->total_count($TotalOrderCount, $TotalAmount, $total_pages,$summary); else $this->back_print_botton('bottom');
						 //$detail_view 		= $this->get_request('detail_view','no');
						 //$zero				= $this->price(0);
						 //echo $this->result_grid($detail_view,$summary,$zero);
						 //$this->print_array($summary);
						 ?>
				<?php else:?>        
						<div class="order_not_found">No orders found</div>
				<?php endif;?> 
			
        <?php    
		}
		
		
		
		var $all_row_result = null;
		
		function subscription_list_query($type = 'total_row', $columns = array(), $total_columns = array()){
			global $wpdb;
			
			$request = $this->get_all_request();extract($request);
			
			$order_status			= $this->get_string_multi_request('order_status',$order_status, "-1");
			$hide_order_status		= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");//New Change ID 20140918			
			$page 					= $this->get_request('page',NULL,true);
			
			//echo $country_code;
			
			$sql = "SELECT 
			shop_subscription.ID 									as subscription_id
			,shop_subscription.post_parent 							as order_id
			,date_format( shop_subscription.post_date, '%Y-%m-%d') 	as order_date 
			,date_format( shop_subscription.post_date, '%Y-%m-%d') 	as subscription_date 
			,woocommerce_order_items.order_item_id 					as order_item_id
			,woocommerce_order_items.order_item_name                as order_item_name
			,shop_subscription.post_status 							as subscription_status ";
		
			$sql .= "	,quantity.meta_value 						as qty  ";
			$sql .= "	,line_total.meta_value 						as line_total  ";
//			$sql .= "	,line_total.meta_value 						as line_total  ";
		
			$sql .= "		FROM {$wpdb->prefix}posts 				as shop_subscription
			
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id	=	shop_subscription.ID ";
			
			$sql .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as quantity ON quantity.order_item_id	=	woocommerce_order_items.order_item_id ";
			
			
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as line_total 			ON line_total.order_item_id				=	woocommerce_order_items.order_item_id";
			
			$sql .=	" WHERE 1*1";
			
			$sql .= "	AND shop_subscription.post_type ='shop_subscription'  ";			
			
			$sql .= "	AND woocommerce_order_items.order_item_type ='line_item'";
			
			$sql .= " AND line_total.meta_key 						= '_line_total'";
			
			$sql .= "	AND quantity.meta_key ='_qty'";
			
			
			
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(shop_subscription.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
			}
			
			
			
			$wpdb->flush();
			
			//$this->print_sql($sql);
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			
			if($type == 'total_row'){
				if($this->all_row_result){
					if($count_generated == 1){
						$order_items 		= $this->create_summary($request);						
					}else{
						$order_items 		= $this->all_row_result;
						$summary 			= $this->get_count_total($order_items,'order_total');				
						$order_items 		= $summary;
					}
					
				}else{					
					if($count_generated == 1 || ($p > 1)){
						$order_items 		= $this->create_summary($request);						
					}else{
						
						$order_items 					= $wpdb->get_results($sql);
						$subscription_ids   			= $this->get_items_id_list($order_items,'subscription_id');
						$required_postmeta_list			= array();
						$required_postmeta_list 		= apply_filters("ic_commerce_subscriptions_required_postmeta_list", $required_postmeta_list, $request, $type, $page, $columns, $total_columns);
						$required_postmeta				= $this->get_required_postmeta($subscription_ids, $columns, $required_postmeta_list);
						//$this->print_array($required_postmeta	);
						if(count($order_items)> 0){
							foreach($order_items as $key => $item){
						
								$subscription_id 						= isset($item->subscription_id) 					? $item->subscription_id 					: 0;
								$required_postmeta[$subscription_id] 	= isset($required_postmeta[$subscription_id]) 		? $required_postmeta[$subscription_id] 		: array();
								
								foreach($required_postmeta[$subscription_id] as $k => $v){
									$order_items[$key]->$k =  $v;
								}
								
								$order_items[$key]->billing_first_name	= isset($order_items[$key]->billing_first_name)		? $order_items[$key]->billing_first_name 	: '';
								$order_items[$key]->billing_last_name	= isset($order_items[$key]->billing_last_name)		? $order_items[$key]->billing_last_name 	: '';
								$order_items[$key]->billing_name		= $order_items[$key]->billing_first_name.' '.$order_items[$key]->billing_last_name;
							}
						}
												
						if($wpdb->last_error) echo $wpdb->last_error;
						
						$order_items 	= apply_filters("ic_commerce_subscriptions_data_items", $order_items, $request, $type, $page, $columns, $total_columns);
						
						$summary 		= $this->get_count_total($order_items,'order_total');				
						$order_items 	= $summary;
					}					
				}								
				return $order_items;
			}
			
			if($type == 'limit_row'){
				$sql .= " ORDER BY shop_subscription.post_date desc";
				$sql .= " LIMIT $start, $limit";
				$order_items = $wpdb->get_results($sql);
				$wpdb->flush();
			}
			
			if($type == 'all_row' or $type == 'all_row_total'){
				$sql .= " ORDER BY shop_subscription.post_date desc";
				$order_items = $wpdb->get_results($sql);
				$this->all_row_result = $order_items;
				$wpdb->flush();
			}
			
			if($type == 'limit_row' || $type == 'all_row' or $type == 'all_row_total'){
				
				$subscription_ids   			= $this->get_items_id_list($order_items,'subscription_id');
				$required_postmeta_list			= array("billing_last_name","billing_first_name","_schedule_trial_end","_schedule_next_payment","_schedule_end","_order_currency","_billing_period");
				$required_postmeta_list 		= apply_filters("ic_commerce_subscriptions_required_postmeta_list", $required_postmeta_list, $request, $type, $page, $columns, $total_columns);
				$required_postmeta				= $this->get_required_postmeta($subscription_ids, $columns, $required_postmeta_list);
				//$this->print_array($wpdb);				
				if(count($order_items)> 0){
					foreach($order_items as $key => $item){
						
						$subscription_id 						= isset($item->subscription_id) 				? $item->subscription_id 				: 0;						 
						$required_postmeta[$subscription_id] 	= isset($required_postmeta[$subscription_id]) 	? $required_postmeta[$subscription_id] 	: array();
						
						foreach($required_postmeta[$subscription_id] as $k => $v){
							$order_items[$key]->$k =  $v;
						}
						
						$order_items[$key]->billing_first_name	= isset($order_items[$key]->billing_first_name)		? $order_items[$key]->billing_first_name 	: '';
						$order_items[$key]->billing_last_name	= isset($order_items[$key]->billing_last_name)		? $order_items[$key]->billing_last_name 	: '';
						$order_items[$key]->billing_name		= $order_items[$key]->billing_first_name.' '.$order_items[$key]->billing_last_name;
					}
				}
				
				$order_items 	= apply_filters("ic_commerce_subscriptions_data_items", $order_items, $request, $type, $page, $columns, $total_columns);
			}
			
			if($wpdb->last_error) echo $wpdb->last_error;
			return $order_items;
		}
		
		/*Get Order Item Meta Data */
		function get_order_itemmeta($order_item_id)
		{
			global $wpdb;
		
			$sql = "SELECT
				* FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta			
				WHERE order_item_id = {$order_item_id}
				";
				
			$order_items = $wpdb->get_results($sql);
			//$this->print_array( $order_items);
			return $order_items;			
		}
		
		function get_postmeta($post_id){
			global $wpdb;
			$sql = "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id='{$post_id}'" ;
			$order_items = $wpdb->get_results($sql);
			
			return $order_items;
		}
		
		
		function get_all_request(){
			global $request, $back_day;
			if(!$this->request){
				$request 			= array();
				$start				= 0;
				
				$limit 						= $this->get_request('limit',3,true);
				$p 							= $this->get_request('p',1,true);				
				$page						= $this->get_request('page',NULL);				
				$report_name				= $this->get_request('report_name',"product_page",true);		
				$country_code				= $this->get_request('country_code',"-1",true);
				$subscription_status		= $this->get_request('subscription_status',"-1",true);		
				
				if($p > 1){	$start = ($p - 1) * $limit;}				
				$_REQUEST['start']= $start;				
				if(isset($_REQUEST)){
					foreach($_REQUEST as $key => $value ):					
						$v =  $this->get_request($key,NULL);
						$request[$key]		= $v;
					endforeach;
				}
				$this->request = $request;				
			}else{				
				$request = $this->request;
			}
			
			return $request;
		}
		
		function total_count($TotalOrderCount = 0, $TotalAmount = 0, $total_pages = 0, $summary = array()){
			global $request;
			
			$admin_page 		= $this->get_request('page');
			$limit	 			= $this->get_request('limit',15, true);
			$adjacents			= $this->get_request('adjacents',3);
			$detail_view		= $this->get_request('detail_view',"no");
			$targetpage 		= "admin.php?page=".$admin_page;
			$create_pagination 	= $this->pagination($total_pages,$limit,$adjacents,$targetpage,$request);
			
			$woocommerce_currency = get_option('woocommerce_currency','USD');
			$woocommerce_currency = strlen($woocommerce_currency) >0 ? $woocommerce_currency : "USD";
			?>
				<table style="width:100%">
					<tr>
						<td valign="middle" class="grid_bottom_total">
						<?php if($detail_view == "no"):?>
							Order: <strong><?php echo $TotalOrderCount ?>/<?php echo $total_pages?></strong>, Amount: <strong><?php echo $this->price($TotalAmount, array('currency' => $woocommerce_currency)); ?></strong>
						<?php endif;?>
						</td>
						<td>					
							<?php echo $create_pagination;?>
                        	<div class="clearfix"></div>
                            <div>
                        	<?php
								$this->export_to_csv_button('bottom',$total_pages, $summary);
								//$this->back_button();
							?>
                            </div>
                            <div class="clearfix"></div>
                        </td>
					</tr>
				</table>
                <script type="text/javascript">
                	jQuery(document).ready(function($) {$('.pagination a').removeAttr('href');});
                </script>
			<?php
		}
		
		
		function export_to_csv_button($position = 'bottom', $total_pages = 0, $summary = array()){
			global $request;
			
			$admin_page 		= 	$this->get_request('admin_page');
			$admin_page_url 	= 	get_option('siteurl').'/wp-admin/admin.php';
			$mngpg 				= 	$admin_page_url.'?page='.$admin_page ;
			$request			=	$this->get_all_request();
			
			$request['total_pages'] = $total_pages;	
			
			$request['count_generated']		=	1;
			
			foreach($summary as $key => $value):
				$request[$key]		=	$value;
			endforeach;
					
			$request_			=	$request;
			
			unset($request['action']);
			unset($request['page']);
			unset($request['p']);
			
			
			?>
			<div id="<?php echo $admin_page ;?>Export" class="RegisterDetailExport">
				<?php if($position == "bottom"):?>
					<form id="search_order_pagination" class="search_order_pagination" action="<?php echo $mngpg;?>" method="post">
						<?php foreach($request_ as $key => $value):?>
							<input type="hidden" name="<?php echo $key;?>" value="<?php echo $value;?>" />
						<?php endforeach;?>
					</form>
				<?php endif;?>
			</div>
            <?php
		}
		
		function pagination($total_pages = 50,$limit = 10,$adjacents = 3,$targetpage = "admin.php?page=RegisterDetail",$request = array()){		
				if(count($request)>0){
					unset($request['p']);
					$new_request = array_map(create_function('$key, $value', 'return $key."=".$value;'), array_keys($request), array_values($request));
					$new_request = implode("&",$new_request);
					$targetpage = $targetpage."&".$new_request;
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
						$pagination.= "<a href=\"$targetpage&p=$prev\" data-p=\"$prev\">previous</a>\n";
					else
						$pagination.= "<span class=\"disabled\">previous</span>\n";	
					
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
						$pagination.= "<a href=\"$targetpage&p=$next\" data-p=\"$next\">next</a>\n";
					else
						$pagination.= "<span class=\"disabled\">next</span>\n";
					$pagination.= "</div>\n";		
				}
				return $pagination;
			
		}
		
		function print_header($type = NULL){
			$out = "";
			//echo $type;
			if($type == 'all_row'){
				
				$company_name	= $this->get_request('company_name','');
				$report_title	= $this->get_request('report_title','');
				$display_logo	= $this->get_request('display_logo','');
				$display_date	= $this->get_request('display_date','');
				$display_center	= $this->get_request('display_center','');
				
				$print_header_logo = "print_header_logo";				
				if($display_center) $print_header_logo .= " center_header";
				
				$out .= "<div class=\"print_header\">";
				if($company_name or $display_logo){
					$out .= "	<div class=\"".$print_header_logo."\">";
					if(strlen($company_name) > 0)	$out .= "<div class='header'><h2>".$company_name."</h2></div>";
					if(strlen($display_logo) > 0 and $display_logo == 1){
						$logo_image = $this->get_setting('logo_image',$this->constants['plugin_options'], '');
						$out 		.= "<div class='clear'><img src='".$logo_image."' alt='' /></div>";
					}				
					$out .= "	</div>";
				}
				if(strlen($report_title) > 0)	$out .= "<div class='clear'><label>Report Title: </label><label>".$report_title."</label></div>";
				if(strlen($display_date) > 0)	$out .= "<div class='Clear'><label>Date: </label> <label>".date('Y-m-d')."</label></div>";
				$out .= "</div>";
			}
			
			echo $out;		
		}
		
		function get_count_total($data,$amt = 'total_amount'){
			$total = 0;
			$return = array();
			$detail_view 		= $this->get_request('detail_view','no');
			$total_columns 		= $this->result_columns($detail_view);
			$order_status		= array();
			$orders				= array();
			if(count($total_columns) > 0){
				foreach($data as $key => $value){
					//$total = $total + $value->$amt;
					
					foreach($total_columns as $ckey => $label):
						$return[$ckey] 	= isset($value->$ckey)? (isset($return[$ckey])	? ($return[$ckey] + $value->$ckey): $value->$ckey) : 0;
					endforeach;
					
					if(!isset($orders[$value->order_id]) )$orders[$value->order_id] = $value->order_id;
				}
			}else{
				foreach($data as $key => $value){
					//$total = $total + $value->$amt;
					if(!isset($orders[$value->order_id]) )$orders[$value->order_id] = $value->order_id;
				}
			}
			
			$return['total_row_amount'] = $total;
			$return['total_row_count'] = count($data);
			$return['total_order_count'] = count($orders);
			//$this->print_array($return);
			return $return;
		}
		
		function get_columns($report_name = 'subscription_list_page'){
			$grid_column = $this->get_grid_columns();			
			return $grid_column->grid_columns_subscription_list($report_name);
		}
		
		function get_columns_csv($report_name = 'subscription_list_page'){
			$grid_column = $this->get_grid_columns();			
			return $grid_column->grid_columns_subscription_list_extra_columns($report_name);
		}
		
		function result_columns($detail_view = ''){
			$grid_column = $this->get_grid_columns();
			$detail_view 		= $this->get_request('detail_view','no');			
			return $grid_column->result_columns_subscription_list($detail_view);
		}
		
		var $request_string = array();
		function get_string_multi_request($id=1,$string, $default = NULL){
			
			if(isset($this->request_string[$id])){
				$string = $this->request_string[$id];
			}else{
				if($string == "'-1'" || $string == "\'-1\'"  || $string == "-1" ||$string == "''" || strlen($string) <= 0)$string = $default;
				if(strlen($string) > 0 and $string != $default){ $string  		= "'".str_replace(",","','",$string)."'";}
				$this->request_string[$id] = $string;			
			}
			
			return $string;
		}
		function get_paying_state($state_key = 'billing_state',$country_key = false, $deliter = "-"){
				global $wpdb;
				if($country_key){
					//$sql = "SELECT CONCAT(billing_country.meta_value,'{$deliter}', billing_by.meta_value) as id, billing_by.meta_value as label, billing_country.meta_value as billing_country ";
					$sql = "SELECT billing_by.meta_value as id, billing_by.meta_value as label, billing_country.meta_value as billing_country ";
				}else
					$sql = "SELECT billing_by.meta_value as id, billing_by.meta_value as label ";
				
				$sql .= "
					FROM `{$wpdb->prefix}posts` AS posts
					LEFT JOIN {$wpdb->prefix}postmeta as billing_by ON billing_by.post_id=posts.ID";
				if($country_key)
					$sql .= " 
					LEFT JOIN {$wpdb->prefix}postmeta as billing_country ON billing_country.post_id=posts.ID";
				$sql .= "
					WHERE billing_by.meta_key='_{$state_key}' AND posts.post_type='shop_order'
				";
				
				if($country_key)
					$sql .= "
					AND billing_country.meta_key='_{$country_key}'";
				
				$sql .= " 
				GROUP BY billing_by.meta_value
				ORDER BY billing_by.meta_value ASC";
				
				$results	= $wpdb->get_results($sql);
				$country    = new WC_Countries;
				
				if($country_key){
					foreach($results as $key => $value):
							$v = $this->get_state($value->billing_country, $value->label);
							$v = trim($v);
							if(strlen($v)>0)
								$results[$key]->label = $v ." (".$value->billing_country.")";
							else
								unset($results[$key]);
					endforeach;
				}else{
					
					foreach($results as $key => $value):
							$v = isset($country->countries[$value->label]) ? $country->countries[$value->label]: $value->label;
							$v = trim($v);
							if(strlen($v)>0)
								$results[$key]->label = $v;
							else
								unset($results[$key]);
					endforeach;
				}
				return $results; 
		}
		
		function ic_commerce_custom_report_page_export_csv($export_file_format = 'csv'){
			$request 			= $this->get_all_request();extract($request);
			$columns1 			= $this->get_columns($report_name);
			$columns2 			= $this->get_columns_csv($report_name);
			
			$columns 			= array_merge((array)$columns1, (array)$columns2);
			
			$rows				= $this->subscription_list_query('all_row', $columns);
			$export_file_name	= $this->get_request('export_file_name','subscriptions',true);
			
			$summary			= $this->subscription_list_query('total_row', $columns);
			
			global $woocommerce;
			$export_rows 	= array();			
			$i 				= 0;
			
			$num_decimals   = get_option( 'woocommerce_price_num_decimals'	,	0		);
			$decimal_sep    = get_option( 'woocommerce_price_decimal_sep'	,	'.'		);
			$thousand_sep   = get_option( 'woocommerce_price_thousand_sep'	,	','		);			
			$zero			= number_format(0, $num_decimals,$decimal_sep,$thousand_sep);
			$country      	= $this->get_wc_countries();
			
			foreach ( $rows as $rkey => $rvalue ):
					$order_item = $rvalue;
					foreach($columns as $key => $value):
						$cell_value = isset($order_item->$key) ? $order_item->$key : '';
						switch ($key) {
							case "schedule_end":
							case "schedule_next_payment":
							case "schedule_trial_end":
								$export_rows[$i][$key] = empty($cell_value) ? '' : date("Y-m-d",strtotime($cell_value));
								break;
							case 'billing_country':
							case 'shipping_country':
								$export_rows[$i][$key] 	= isset($country->countries[$order_item->$key]) ? $country->countries[$order_item->$key]: $order_item->$key;
								break;
							case 'billing_state':
								$billing_state 			= isset($order_item->billing_state) 	? $order_item->billing_state : '';
								$billing_country 		= isset($order_item->billing_country) 	? $order_item->billing_country : '';
								$export_rows[$i][$key] 	= $this->get_billling_state_name($billing_country,$billing_state);
								break;
							case 'shipping_state':
								$shipping_state 		= isset($order_item->shipping_state) 	? $order_item->shipping_state : '';
								$shipping_country 		= isset($order_item->shipping_country) 	? $order_item->shipping_country : '';
								$export_rows[$i][$key] 	= $this->get_billling_state_name($shipping_country,$shipping_state);
								break;
							default:
								$export_rows[$i][$key] = $cell_value;
								break;
	
						}
					endforeach;
					$i++;
			endforeach;
			
			
			$total_label_flag = false;
			foreach($columns as $key => $value):					
				switch ($key) {					
						case 'order_total':
							$td_value 	= isset($summary[$key]) ? $summary[$key] : '';
							$td_value 	=  strlen($td_value)>0 ? $td_value : 0;
							$td_value	=  $td_value > 0 ? number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep) : $zero;//Added 20153001
							break;						
						case 'product_quantity':
						case 'total_order_count':
							$td_value 	= isset($summary[$key]) ? $summary[$key] : '';
							$td_value 	=  strlen($td_value)>0 ? $td_value : 0;
							break;
						case "subscription_id":
							if($total_label_flag)
								$td_value = "";
							else{
								$td_value = "Total";
								$total_label_flag = true;
							}
							break;						
						case "order_date":
						case "schedule_trial_end":
						case "subscription_date":
						case "order_id":
						case "order_date":
						case "order_item_name":
						case "billing_first_name":
						case "schedule_trial_end":
						case "subscription_date":
						case "schedule_end":
						case "subscription_status":
						case "billing_period":
						case "billing_interval":
						case "qty":
							$td_value = '';
							break;
						default:
							$td_value = isset($summary[$key]) ? $summary[$key] : '';
							break;
					}
					$export_rows[$i][$key] = $td_value;
			endforeach;
			
			//$this->print_array($columns);
			//$this->print_array($export_rows);die;
			
			$today = date_i18n("Y-m-d-H-i-s");				
			$FileName = $export_file_name."-".$today.".".$export_file_format;	
			$this->ExportToCsv($FileName,$export_rows,$columns,$export_file_format);
		}
		
		function ExportToCsv($filename = 'export.csv',$rows,$columns,$format="csv"){				
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			if($format=="xls"){
				$csv_terminated = "\r\n";
				$csv_separator = "\t";
			}
				
			foreach($columns as $key => $value):
				$l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $csv_enclosed;
				$schema_insert .= $l;
				$schema_insert .= $csv_separator;
			endforeach;// end for
		 
		   $out = trim(substr($schema_insert, 0, -1));
		   $out .= $csv_terminated;
			
			//printArray($rows);
			
			for($i =0;$i<count($rows);$i++){
				
				//printArray($rows[$i]);
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						
						if(!isset($rows[$i][$key])) echo $i;
						
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $csv_enclosed;
							}
						 }else{
							$schema_insert .= '';
						 }
						
						
						
						if ($j < $fields_cnt - 1)
						{
							$schema_insert .= $csv_separator;
						}
						$j++;
				}
				$out .= $schema_insert;
				$out .= $csv_terminated;
			}
			
			if($format=="csv"){
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));	
				header("Content-type: text/x-csv");
				header("Content-type: text/csv");
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=$filename");
			}elseif($format=="xls"){
				
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				header("Pragma: no-cache");
				header("Expires: 0");
			}
			
			echo $out;
			exit;
		 
		}
	}
}