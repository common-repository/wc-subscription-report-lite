<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once('ic_commerce_lite_subscriptions_fuctions.php');


if ( ! class_exists( 'IC_Commerce_Lite_Subscription_Dashboard' ) ) {
	class IC_Commerce_Lite_Subscription_Dashboard extends IC_Commerce_Lite_Subscription_Fuctions{
		
		public $per_page = 0;
		
		public $per_page_default = 5;
		
		public $constants 	=	array();
		
		public $today 		=	'';		
		
		public function __construct($constants) {
			global $options;
			
			$this->constants		= $constants;			
			$options				= $this->constants['plugin_options'];			
			$this->per_page			= $this->constants['per_page_default'];
			$this->per_page_default	= $this->constants['per_page_default'];
			$this->today			= $this->constants['today_date'];
			$this->constants['datetime']= date_i18n("Y-m-d H:i:s");
			//$this->test(date_i18n("Y-m-d") ,date_i18n("Y-m-d"));
			//$this->test();
			
		}
		
		
		function init(){
			
			global $options;
			
			if(!isset($_REQUEST['page'])) return false;
			
			if(!$this->constants['plugin_parent_active']) return false;
			
			$this->is_active();
			
			global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;
			
			$shop_order_status				= $this->get_set_status_ids();	
			
			$hide_order_status 				= $this->constants['hide_order_status'];
			$start_date 					= $this->constants['start_date'];
			$end_date 						= $this->constants['end_date'];
			$subscription_status 			= $this->get_subscription_status();
			$cancelled_id 					= $this->get_old_order_status(array('cancelled','processing','cancelled','on-hold'), array('wc-cancelled','wc-processing','wc-cancelled','wc-on-hold'));
			$refunded_id 					= $this->get_old_order_status(array('refunded'), array('wc-refunded'));
			
			
			if(isset($shop_order_status[0]) and $shop_order_status[0] == 'all'){
				unset($shop_order_status[0]);
			}
			if(isset($subscription_status[0]) and $subscription_status[0] == 'all'){
				unset($subscription_status[0]);
			}
			
			$constants 						= array(
												'shop_order_status' 	=> $shop_order_status
												,'hide_order_status'	=> $hide_order_status
												,'start_date'			=> $start_date
												,'end_date'				=> $end_date
												,'subscription_status'	=> $subscription_status
												,'cancelled_id'			=> $cancelled_id
												,'refunded_id'			=> $refunded_id
												,'last_days'			=> 'total'
												,'number_prefix' 		=> "#"
											);
			
			
			$this->constants['constant']	= $constants;											
			$start_date 					= $this->constants['today_date'];
			$end_date 						= $this->constants['today_date'];			
			
			$last_days						= 30;
			$start_date 					= date('Y-m-d', strtotime("- {$last_days} day", strtotime($end_date)));
			
			$total_user_count 				= "0";
			$total_subscriber_count			= "0";
			$new_free_trial_signup_count	= "0";
			$new_paid_subscriber_count		= "0";
			$subscription_status_count		= "0";
			$total_revenue					= "0";
						
			//$total_user_count 			= $this->get_total_user_count($shop_order_status,$subscription_status,$hide_order_status,$start_date,$end_date);
			$total_subscriber_count			= $this->get_total_subscriber_count('subscriber',$start_date,$end_date);
			$total_active_subscriber_count	= $this->get_total_active_subscriber_count('subscriber',$start_date,$end_date);			
			$active_subscription_count		= $this->get_subscription_count();
			
			$paid_signup_count				= $this->get_paid_signup_count($shop_order_status,$subscription_status,$hide_order_status,NULL,NULL);
			
			
						
			?>
            	<div class="ajax_error"></div>
				<div id="poststuff" class="woo_cr-reports-wrap">
					<div class="woo_cr-reports-top">
                    	<div class="row">
                        	<div class="postbox dashboard_summary_postbox">
                            	<h3>
                                	<span><?php _e( 'Summary', $this->constants['plugin_key'] ); ?></span>
                                </h3>
                               	<div class="summary_box_1">
                                	<br /><br />
                                	<div class="block block-orange">
                                        <div class="block-content">
                                            <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/customers-icon.png" alt="" />
                                            <p class="stat">#<?php echo $total_subscriber_count;?></p>
                                        </div>
                                        <h2><span><?php _e( 'Total Subscribed User Count', $this->constants['plugin_key'] ); ?></span></h2>
                                    </div>
                                    
                                    <div class="block block-green">
                                        <div class="block-content">
                                            <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/customers-icon.png" alt="" />
                                            <p class="stat">#<?php echo $total_active_subscriber_count;?></p>
                                        </div>
                                        <h2><span><?php _e( 'Active Subscriber Count', $this->constants['plugin_key'] ); ?></span></h2>
                                    </div>
                                    
                                    <div class="block block-pink">
                                        <div class="block-content">
                                            <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/subscriber-icon.png" alt="" />
                                            <p class="stat">#<?php echo $active_subscription_count;?></p>
                                        </div>
                                        <h2><span><?php _e( 'Active Subscription Count', $this->constants['plugin_key'] ); ?></span></h2>
                                    </div>
                                    
                                    <div class="block block-light-green">
                                        <div class="block-content">
                                            <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/sign-up-icon.png" alt="" />
                                            <p class="stat" id="paid_signup_count">#<?php echo $paid_signup_count;?></p>
                                        </div>
                                        <h2><span><?php _e( 'Paid Signup Count', $this->constants['plugin_key'] ); ?></span></h2>
                                    </div>
                                    
                                    <div style="clear:both"></div>
                                </div>
								
                        	</div>
						</div>
						<!--<div class="clearfix"></div>-->
					</div>
				</div>      
				        
                <div class="row">
                	 <div class="col-md-6">
                            <div class="postbox">
                                <h3>
                                    <span class="title"><?php _e( 'Top 5 Subscription Items', $this->constants['plugin_key'] ); ?></span>
                                    <span class="progress_status"></span>
                                </h3>
                                <div class="inside Overflow" id="sales_order_count_value">
                                    <div class="grid"><?php $this->top_subscription_item_list($constants);?></div>
                                </div>
                            </div>
                     </div>
					 <div class="col-md-6">
                            <div class="postbox">
                                <h3>
                                    <span class="title"><?php _e( 'WooCommerce Order Status', $this->constants['plugin_key'] ); ?></span>
                                    <span class="progress_status"></span>
                                </h3>
                                <div class="inside Overflow" id="sales_order_count_value">
                                    <div class="grid"><?php $this->subscription_order_statuses($constants);?></div>
                                </div>
                            </div>
                     </div>
                </div>  
				              
			 	<div class="row">
				
                     <div class="col-md-6">
                            <div class="postbox">
                                <h3>
                                    <span class="title"><?php _e( 'Subscription Status', $this->constants['plugin_key'] ); ?></span>
                                    <span class="progress_status"></span>
                                </h3>
                                <div class="inside Overflow" id="sales_order_count_value">
                                    <div class="grid"><?php $this->subscription_statuses($constants);?></div>
                                </div>
                            </div>
                     </div>
					 
					 <div class="col-md-6">
                            <div class="postbox">
                            
                            	<?php
									$status_string = '';
									
									if(count($shop_order_status)>0){
										$order_statuses = $this->ic_get_order_statuses();									
										
										foreach($shop_order_status as $key => $statues){
											$shop_order_status[$key] = isset($order_statuses[$statues]) ? $order_statuses[$statues] : $statues;
										}
										
										if(count($shop_order_status) == 1){
											$status_string = implode(", ",$shop_order_status);
											$status_string = " (WooComm Order Status = ".$status_string." )";
										}else{
											$last = array_pop($shop_order_status);
											$status_string = count($shop_order_status) ? implode(", ", $shop_order_status) . " & " . $last : $last;
											$status_string = " (WooComm Order Status = ".$status_string." )";
										}
									}
								?>                            
                                <h3>
                                    <span class="title"><?php _e( 'Subscription Status', $this->constants['plugin_key'] ); echo " ". $status_string;; ?></span>
                                    <span class="progress_status"></span>
                                </h3>
                                <div class="inside Overflow" id="sales_order_count_value">
                                    <div class="grid"><?php $this->subscription_statuses_selected_order_status($constants);?></div>
                                </div>
                            </div>
                     </div>
                     
                </div>
                                
               	<style type="text/css">
                	td.line_subtotal, th.line_subtotal{ width:120px;}
					td.order_count, th.order_count{ text-align:right;}
                </style>
			<?php
		}		
		
		//2
		function get_total_subscriber_count($role,$start_date,$end_date){
			global $wpdb;
			$sql = "";			
			$sql .= " SELECT COUNT(ID) ";
			$sql .= " FROM {$wpdb->prefix}users as users ";			
			$sql .= " LEFT JOIN  {$wpdb->prefix}usermeta as user_capabilities 	ON user_capabilities.user_id = users.ID";
			$sql .= " WHERE 1=1 ";
			if ($start_date != NULL &&  $end_date != NULL){
				//$sql .= " AND DATE(users.user_registered) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			$sql .= " AND user_capabilities.meta_key				= '{$wpdb->prefix}capabilities'";
			$sql .= " AND user_capabilities.meta_value				LIKE '%$role%'";
			
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			$items = $wpdb->get_var($sql);
			
			if($wpdb->last_error){
				echo "function get_total_subscriber_count();<br>";
				echo $wpdb->last_error;
			};
			
			$items = strlen($items) > 0 ? $items : 0;
			return $items;
		}
		
		function get_total_active_subscriber_count($role,$start_date,$end_date){
			global $wpdb;
			$sql = "";			
			$sql .= " SELECT COUNT(*) AS count ";
			$sql .= " FROM {$wpdb->posts} as shop_subscription ";			
			$sql .= " LEFT JOIN {$wpdb->postmeta} AS customer_user ON customer_user.post_id = shop_subscription.ID";			
			$sql .= " LEFT JOIN  {$wpdb->prefix}usermeta as user_capabilities 	ON user_capabilities.user_id = customer_user.meta_value";
			
			$sql .= " WHERE 1=1 ";
			
			$sql .= " AND user_capabilities.meta_key				= '{$wpdb->prefix}capabilities'";
			$sql .= " AND user_capabilities.meta_value				LIKE '%$role%'";
			$sql .= " AND customer_user.meta_key 					= '_customer_user'";
			$sql .= " AND shop_subscription.post_type 				= 'shop_subscription'";
			$sql .= " AND shop_subscription.post_status				IN('wc-active')";
			
			$sql .= " GROUP BY customer_user.meta_value";
			
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			
			$items = $wpdb->get_results($sql);
			
		//	$this->print_sql($sql);
			
			
			
			//$items = $wpdb->get_var($sql);
			
			if($wpdb->last_error){
				echo "function get_total_active_subscriber_count();<br>";
				echo $wpdb->last_error;
				
				return 0;
			};
			/*
			$count = 0;
			foreach($items as $key => $item){
				$count = $count + $item->count;
			}
			*/
			$items = count($items);
			
			$items = strlen($items) > 0 ? $items : 0;
			return $items;
			
			die;
		}
		
		function get_subscription_count($subscription_status = array('wc-active')){
			global $wpdb;
			
			$sql = " SELECT COUNT(*) FROM {$wpdb->posts} AS shop_subscription";
			
			$sql .= " WHERE 1*1";
			
			$sql .= " AND shop_subscription.post_type 				= 'shop_subscription'";
			
			if(count($subscription_status)>0){
				$in_subscription_status = implode(",",$subscription_status);
				$sql .= " AND shop_subscription.post_status	 IN('$in_subscription_status')";
			}
			
			$items = $wpdb->get_var($sql);
			
			//$this->print_array($items);
			
			$items = strlen($items) > 0 ? $items : 0;
			
			return $items;
		}
		
		//Top n Subscription Items
		function top_subscription_item_list($constants = array()){
			global $wpdb;
			
			$shop_order_status 		= isset($constants['shop_order_status']) 	? $constants['shop_order_status'] 	: array();
			$hide_order_status 		= isset($constants['hide_order_status']) 	? $constants['hide_order_status'] 	: array();
			$subscription_status 	= isset($constants['subscription_status']) 	? $constants['subscription_status'] : array();			
			$start_date 			= isset($constants['start_date']) 			? $constants['start_date'] 			: NULL;
			$end_date 				= isset($constants['end_date']) 			? $constants['end_date'] 			: NULL;
			
			$sql = " SELECT  SUM(line_total.meta_value)  as line_subtotal
							,count(product_id.meta_value) as subscription_count
							,SUM( qty.meta_value) as qty_toatal
						  	,woocommerce_order_items.order_item_name as order_item_name
						  	,product_id.meta_value  as product_id
						  	FROM {$wpdb->prefix}posts  as posts ";
	
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items 	as woocommerce_order_items 	ON woocommerce_order_items.order_id		=	posts.ID";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as line_total 	ON line_total.order_item_id		=	woocommerce_order_items.order_item_id";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as product_id 	ON product_id.order_item_id		=	woocommerce_order_items.order_item_id";
			
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as qty 	ON qty.order_item_id		=	woocommerce_order_items.order_item_id";
			
			
				
			$sql .= " WHERE  1 = 1 ";
	
			//$sql .= " AND posts.post_type ='shop_subscription'";
			$sql .= " AND posts.post_type IN ( 'shop_order','shop_order_refund' ) ";
			
			$sql .= " AND woocommerce_order_items.order_item_type ='line_item' ";
			
			$sql .= " AND line_total.meta_key ='_line_total' ";	
			
			$sql .= " AND product_id.meta_key ='_product_id' ";	
			
			$sql .= " AND qty.meta_key ='_qty' ";	
			
			$sql .= " AND line_total.meta_value > 0  ";	
			
			
		//	$sql .= " AND posts.post_status IN ('wc-on-hold','wc-processing','wc-completed') ";	
			
			if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					if($this->constants['post_type_shop_subscription'] == 1){
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					}else{
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					}
			}
			
			//$sql .= " AND   date_format(posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";	
			
			$sql .= " GROUP BY product_id.meta_value ";	
		 	$sql .= " ORDER BY   SUM(line_total.meta_value) DESC";	
			$sql .= " LIMIT 5 ";
			
			$order_items = $wpdb->get_results($sql);
			echo $wpdb->last_error  ;
		//	$this->print_array($order_items );
			?>
            <table style="width:100%" class="widefat">
                 <thead>
                    <tr class="first">
                        <th>Subscription Item Name</th>
                        <th style="text-align:right">Order Count</th>
                        <th style="text-align:right">Subscription Total</th>
                   </tr>
                </thead>
                 <tbody>
                  <?php		
				  	$i = 0;			
                 	foreach ( $order_items as $key => $order_item ) {
						if ($i>5) {
						break;
						}
                    	if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
                  ?>	
                  	<tr class="<?php echo $alternate."row_".$key;?>">
                      <td><?php echo $order_item->order_item_name?></td>
                        <td style="text-align:right"><?php echo $order_item->subscription_count?></td>
                        <td class="amount line_subtotal"><?php echo $this->price($order_item->line_subtotal)?></td>
                        
                        <!--  <td><?php //echo $order_item["order_item_name"]; ?></td>
                        <td style="text-align:right"><?php //echo $order_item["subscription_count"]; ?></td>
                        <td class="amount line_subtotal"><?php //echo $this->price( $order_item["line_subtotal"])?></td>-->
                    </tr>
               	  <?php 
				  	$i++;
				  } ?>	
                 </tbody>
            </table>
            <span class="ViewAll" style="display:none"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
            <?php
			
		}
			
		//F5
		function subscription_order_statuses(){
		
			global $wpdb;
			$sql = " SELECT  COUNT(*) as subscription_count, 
						 posts.ID as order_id ,date_format( posts.post_date, '%Y-%m-%d') as order_date,
						 posts.post_status as subscription_status,
						 SUM(postmeta.meta_value) as order_total 
						 
						  FROM {$wpdb->prefix}posts  as posts ";
	
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID ";
		
			$sql .= " WHERE  1 = 1 ";
	
			$sql .= " AND posts.post_type ='shop_order'";
		
			$sql .= " AND postmeta.meta_key =  '_order_total' " ;
		    $sql .= " GROUP BY  posts.post_status ";
			
			 $sql .= " ORDER BY SUM(postmeta.meta_value)  DESC";
			
			//echo $sql;		
			
			$order_items = $wpdb->get_results($sql);
			if($wpdb->last_error){
				echo $wpdb->last_error;
			}
			
			?>
            <table style="width:100%" class="widefat">
                 <thead>
                    <tr class="first">
                        <th>WooComm Order Status</th>
                        <th style="text-align:right">Order Count</th>
                        <th style="text-align:right">Order Total</th>
                   </tr>
                </thead>
                 <tbody>
                  <?php	
				  	if(count($order_items) > 0){
				  		$order_statuses = $this->ic_get_order_statuses();
						foreach ( $order_items as $key => $order_item ) {
							if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
					  		?>
                            <tr class="<?php echo $alternate."row_".$key;?>">
                                <td><?php echo (isset($order_statuses[$order_item->subscription_status]) ? $order_statuses[$order_item->subscription_status] : ucfirst ($order_item->subscription_status));?></td>
                                <td style="text-align:right"><?php echo $order_item->subscription_count?></td>
                                <td class="amount line_subtotal"><?php echo $this->price($order_item->order_total)?></td>
                            </tr>
               	  		<?php }
					}?>	
                 </tbody>
            </table>
            <?php
			//$this->print_array($order_items);
		}
		
		//F6
		function subscription_statuses(){
		
			global $wpdb;
			
			$sql = " SELECT 
			count(*) as subscription_count
			,sum(postmeta.meta_value) as order_total
			,posts.post_status   as subscription_status
			FROM {$wpdb->prefix}posts  as posts ";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta 	as postmeta 	ON postmeta.post_id		=	posts.ID";			
			
			
			$sql .= " WHERE  1 = 1 ";
			$sql .= " AND posts.post_type ='shop_subscription' ";
			$sql .= " AND postmeta.meta_key = '_order_total'";
			$sql .= " GROUP BY posts.post_status   ";
			
			//$this->print_sql($sql);
									
			$order_items = $wpdb->get_results($sql);
			if($wpdb->last_error){
							echo $wpdb->last_error;
					
			}
			
			?>
            <table style="width:100%" class="widefat">
                 <thead>
                    <tr class="first">
                        <th>Subscription Status</th>
                        <th style="text-align:right">Subscription Count</th>
                        <th style="text-align:right">Subscription Total</th>
                   </tr>
                </thead>
                 <tbody>
                  <?php	
				  	if(count($order_items) > 0){
				  		$order_statuses = $this->ic_wcs_get_subscription_statuses();
						foreach ( $order_items as $key => $order_item ) {
							if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
					  		?>
                            <tr class="<?php echo $alternate."row_".$key;?>">
                                <td><?php echo (isset($order_statuses[$order_item->subscription_status]) ? $order_statuses[$order_item->subscription_status] : ucfirst ($order_item->subscription_status));?></td>
                                <td style="text-align:right"><?php echo $order_item->subscription_count?></td>
                                <td class="amount line_subtotal"><?php echo $this->price($order_item->order_total)?></td>
                            </tr>
               	  		<?php }
					}?>	
                 </tbody>
            </table>
            <?php
			//$this->print_array($order_items);
		}
		
		//F7
		function subscription_statuses_selected_order_status($constants){
			
			$shop_order_status 		= isset($constants['shop_order_status']) ? $constants['shop_order_status'] : array();
			$hide_order_status 		= isset($constants['hide_order_status']) ? $constants['hide_order_status'] : array();
			$subscription_status 	= isset($constants['subscription_status']) ? $constants['subscription_status'] : array();
			
			global $wpdb;
			$sql = "SELECT 
			posts.ID as order_id
			,date_format( posts.post_date, '%Y-%m-%d') as order_date 
			,woocommerce_order_items.order_item_id
			,woocommerce_order_items.order_item_name
			
			,count(*) AS 'subscription_count'
			,SUM(line_subtotal.meta_value) as line_subtotal";
			
			if($this->constants['post_type_shop_subscription'] == 0){							
				$sql .= " ,subscription_status.meta_value as subscription_status";
			}else{
				$sql .= " ,posts.post_status as subscription_status";
			}
									
			$sql .= " 
			FROM {$wpdb->prefix}posts as posts  
			
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID 
			
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as line_subtotal ON line_subtotal.order_item_id=woocommerce_order_items.order_item_id  ";
			
			if($this->constants['post_type_shop_subscription'] == 0){				
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as subscription_status 	ON subscription_status.order_item_id=woocommerce_order_items.order_item_id";
			}
			
			if($this->constants['post_type_shop_subscription'] == 1){
				if(count($shop_order_status)>0 or count($hide_order_status)>0){
					$sql .= " LEFT JOIN  {$wpdb->prefix}posts 		as shop_order ON shop_order.ID	=	posts.post_parent";
				}
			}
			
			$sql .= " WHERE 1*1";
			
			if($this->constants['post_type_shop_subscription'] == 1){
				if(count($shop_order_status)>0 or count($hide_order_status)>0){
					$sql .= " AND shop_order.post_type 	= 'shop_order'";
				}
			}
			
			if($this->constants['post_type_shop_subscription'] == 0){							
				$sql .= " AND posts.post_type = 'shop_order'";
				$sql .= " AND subscription_status.meta_key	= '_subscription_status'";
			}else{							
				$sql .= " AND posts.post_type = 'shop_subscription'";
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					if($this->constants['post_type_shop_subscription'] == 1){
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}else{
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					}
				}
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				if($this->constants['post_type_shop_subscription'] == 1){
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}else{
					$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				}
			}
									
			$sql .= " AND woocommerce_order_items.order_item_type ='line_item'
			AND line_subtotal.meta_key ='_line_subtotal' 
			AND line_subtotal.meta_value >0 
			group by subscription_status
			order by line_subtotal DESC
			";
				$order_items = $wpdb->get_results($sql);
				if($wpdb->last_error){
					echo $wpdb->last_error;
				}
			
			?>
            <table style="width:100%" class="widefat">
                 <thead>
                    <tr class="first">
                        <th>Subscription Status</th>
                        <th style="text-align:right">Subscription Count</th>
                        <th style="text-align:right">Subscription Total</th>
                   </tr>
                </thead>
                 <tbody>
                  <?php	
				  	if(count($order_items) > 0){
				  		$order_statuses = $this->ic_wcs_get_subscription_statuses();
						foreach ( $order_items as $key => $order_item ) {
							if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
					  		?>
                            <tr class="<?php echo $alternate."row_".$key;?>">
                                <td><?php echo (isset($order_statuses[$order_item->subscription_status]) ? $order_statuses[$order_item->subscription_status] : ucfirst ($order_item->subscription_status));?></td>
                                <td style="text-align:right"><?php echo $order_item->subscription_count?></td>
                                <td class="amount line_subtotal"><?php echo $this->price($order_item->line_subtotal)?></td>
                            </tr>
               	  		<?php }
					}?>	
                 </tbody>
            </table>
            <?php
			//$this->print_array($order_items);
		}
		
		public $is_product_active = NULL;
		public function is_active(){
			$r = false;
			if($this->is_product_active == NULL){					
				$actived_product = get_option($this->constants['plugin_key'] . '_activated');
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
		
		function get_paid_signup_count($shop_order_status,$subscription_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
			$sql = "";			
			$sql .= " SELECT";
			
			//$sql .= "  COUNT(woocommerce_order_items.order_item_id) AS paid_signup_count, product_id.meta_value AS product_id, subscription_sign_up_fee.meta_value AS subscription_sign_up_fee";
			
			$sql .= " COUNT(woocommerce_order_items.order_item_id) AS paid_signup_count";
						
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}posts 		as posts ON posts.ID = woocommerce_order_items.order_id";
			
			if(count($subscription_status)>0){
				if($this->constants['post_type_shop_subscription'] == 0){
						$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as subscription_status 	ON subscription_status.order_item_id		=	woocommerce_order_items.order_item_id";				
				}
			}
			
			//$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as line_total 			ON line_total.order_item_id				=	woocommerce_order_items.order_item_id";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as product_id 			ON product_id.order_item_id				=	woocommerce_order_items.order_item_id";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta 		as subscription_sign_up_fee ON subscription_sign_up_fee.post_id = product_id.meta_value";
			
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			
			if($this->constants['post_type_shop_subscription'] == 1){
				if(count($shop_order_status)>0 or count($hide_order_status)>0){
					$sql .= " LEFT JOIN  {$wpdb->prefix}posts 		as shop_order ON shop_order.ID	=	posts.post_parent";
				}
			}
			
			$sql .= " WHERE 1=1 ";
			
			if($this->constants['post_type_shop_subscription'] == 1){
				if(count($shop_order_status)>0 or count($hide_order_status)>0){
					$sql .= " AND shop_order.post_type 	= 'shop_order'";
				}
			}
			
			if($this->constants['post_type_shop_subscription'] == 1){
				$sql .= " AND posts.post_type 	= 'shop_subscription'";
			}else{
				$sql .= " AND posts.post_type 	= 'shop_order'";
			}
			
			$sql .= " AND woocommerce_order_items.order_item_type	= 'line_item'";
			//$sql .= " AND line_total.meta_key 						= '_line_total'";			
			//$sql .= " AND line_total.meta_value 					> 0";
			
			$sql .= " AND product_id.meta_key						= '_product_id'";
			$sql .= " AND subscription_sign_up_fee.meta_key		= '_subscription_sign_up_fee'";
			$sql .= " AND subscription_sign_up_fee.meta_value		> 0";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					if($this->constants['post_type_shop_subscription'] == 1){
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}else{
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					}
				}
			}
			
			if ($start_date != NULL &&  $end_date != NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				if($this->constants['post_type_shop_subscription'] == 1){
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}else{
					$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				}
			}
			
			if(count($subscription_status)>0){
				if($this->constants['post_type_shop_subscription'] == 1){
					$in_subscription_status		= implode("', '",$subscription_status);
					$sql .= " AND  posts.post_status IN ('{$in_subscription_status}')";
				}else{
					$in_subscription_status		= implode("', '",$subscription_status);
					$sql .= " AND subscription_status.meta_key	= '_subscription_status'";
					$sql .= " AND subscription_status.meta_value IN ('$in_subscription_status')";
				}
			}
			
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			
			$items = $wpdb->get_var($sql);
			
			if($wpdb->last_error){
				echo "<br /> function Name:- ".__FUNCTION__."<br /> File Path:- ".__FILE__."<br /> Line Number:- ".__LINE__."\n";;
				echo "<br /> MySQL Error:- ".$wpdb->last_error."<br />\n";
			};
			
			//$this->print_array($items);
			
			$items = strlen($items) > 0 ? $items : 0;
			return $items;
		}
		
		
	}//End Class
}//End Class Exists
