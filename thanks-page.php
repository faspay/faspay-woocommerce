<?php
require_once '../../../wp-config.php';
require_once '../../../wp-settings.php';
?>

<?php get_header(); ?>

<div id="primary">
	<div id="content" role="main">
    	<div class="section"></div>
                    
		<?php		
        //GetParameterURL --> Faspay
        $trx_id = $_GET['trx_id'];
		//$order =  new WC_Order( 230 );
				
		global $wpdb;		 
		       	 
        //Inquiry --> Faspay
		$cekDB = $wpdb->get_results("select * from ". $wpdb->prefix ."faspay_order where trx_id = '$trx_id'", ARRAY_A);
		foreach($cekDB as $db) {
			$bill_no = $db['order_id'];
		}
		// print_r($cekDB);exit;
		$detail = $wpdb->get_results("SELECT ". $wpdb->prefix ."options.option_value FROM ". $wpdb->prefix ."options WHERE ". $wpdb->prefix ."options.option_name = 'woocommerce_faspay_settings' ", ARRAY_A);		 
		$active_plugins_data ="";

		foreach($detail as $rows) {
			$active_plugins_data .= $rows["option_value"];
		}
		$array = unserialize($active_plugins_data);
			$merchant_id	= $array["merchant_id"];
			$user_id		= $array["user_id"];
			$password		= $array["password"];
			$server			= $array["server"];
		
		//Signature		
        $str 			= $user_id.$password.$bill_no; //$user_id.$password.$order_id;
        $mi_signature 	= hash('sha1',hash('md5',$str));
        $xml = "";
        $xml .= 	"<faspay>";
        $xml .= 	"<request>Inquiry Status Payment</request>";
        $xml .= 	"<trx_id>$trx_id</trx_id>" . "\n";
        $xml .= 	"<merchant_id>$merchant_id</merchant_id>";
        $xml .= 	"<bill_no>$bill_no</bill_no>";
        $xml .= 	"<signature>$mi_signature</signature>";
        $xml .= 	"</faspay>";
        
        $body = $xml;
						        
        //ConditionServer 		      
        $urldev		= "http://faspaydev.mediaindonusa.com/pws/100004/183xx00010100000";
        $urlprod	= "https://faspay.mediaindonusa.com/pws/100004/383xx00010100000";
        
        if($server == 'http://faspaydev.mediaindonusa.com/pws/300002/183xx00010100000'){
            $c = curl_init ($urldev);
        }else{
            $c = curl_init ($urlprod);
        }
		
		curl_setopt ($c, CURLOPT_POST, true);
		curl_setopt ($c, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt ($c, CURLOPT_POSTFIELDS, $body);
		curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($c, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec ($c);
		curl_close ($c);
		
		$xml = simplexml_load_string($response);					
		 		                     
        
        //ConditionIfSuccess
        if($xml->payment_status_code == '2'){
        
            echo "<div style='text-align: center;'>
					<br>
                  	<h1>Thank you for shopping, your <strong>Order ID : #$trx_id</strong> successfull.</h1>
					<br>
                  </div>";
            
        }else{
                
			echo "<div style='text-align:center;'>
					<br>
					<h1>Sorry your <strong>Order ID : #$trx_id</strong> failed.</h1>
					<br>
				  </div>";
        }
        ?>
                             
    	<div class="section"></div>	
        
        <!-- Include Content Themes Wordpress in Merchant HERE-->
<?php
    global $woocommerce;

	$order = new WC_Order( $bill_no );
	?>
<div class="row">
	<div class="large-12 columns">
	
	<h2 align="left"><?php _e( 'Order Details', 'yiw' ); ?></h2>
<div id="order_review" style="border: double; border-color:#71235a; padding-left:20px; padding-right:20px; padding-top:15px; margin-bottom:20px">
<table class="shop_table order_details">
	<thead>
		<tr>
			<th class="product-name"><?php _e( 'Product', 'yiw' ); ?></th>
			<th class="product-total"><?php _e( 'Total', 'yiw' ); ?></th>
		</tr>
	</thead>
	<tfoot>
	<?php
		if ( $totals = $order->get_order_item_totals() ) foreach ( $totals as $total ) :
			?>
			<tr>
				<th scope="row"><?php echo $total['label']; ?></th>
				<td><?php echo $total['value']; ?></td>
			</tr>
			<?php
		endforeach;
	?>
	</tfoot>
	<tbody>
		<?php
		if (sizeof($order->get_items())>0) :

			foreach($order->get_items() as $item) :

				$_product = get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );

				echo '
					<tr class = "' . esc_attr( apply_filters('woocommerce_order_table_item_class', 'order_table_item', $item, $order ) ) . '">
						<td class="product-name">';

				echo '<a href="'.get_permalink( $item['product_id'] ).'">' . $item['name'] . '</a> <strong class="product-quantity">&times; ' . $item['qty'] . '</strong>';

				$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
				$item_meta->display();

				if ( $_product->exists() && $_product->is_downloadable() && $order->is_download_permitted() ) :

					$download_file_urls = $order->get_downloadable_file_urls( $item['product_id'], $item['variation_id'], $item );
					foreach ( $download_file_urls as $i => $download_file_url ) :
						echo '<br/><small><a href="' . $download_file_url . '">' . sprintf( __( 'Download file %s &rarr;', 'yiw' ), ( count( $download_file_urls ) > 1 ? $i + 1 : '' ) ) . '</a></small>';
					endforeach;

				endif;

				echo '</td><td class="product-total">' . $order->get_formatted_line_subtotal( $item ) . '</td></tr>';

				// Show any purchase notes
				if ($order->status=='completed' || $order->status=='processing') :
					if ($purchase_note = get_post_meta( $_product->id, '_purchase_note', true)) :
						echo '<tr class="product-purchase-note"><td colspan="3">' . apply_filters('the_content', $purchase_note) . '</td></tr>';
					endif;
				endif;

			endforeach;
		endif;

		do_action( 'woocommerce_order_items_table', $order );
		?>
	</tbody>
</table>        
        
        
        <!-- End Order Details-->
        </div>
        </div>  
        </div> 
                
    </div>
</div>

<?php get_footer(); ?> 