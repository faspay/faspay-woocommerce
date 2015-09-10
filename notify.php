<?php
include "../../../wp-config.php";

//GetParseXMLfromFaspay
if(isset($HTTP_RAW_POST_DATA)){
    $xml = $HTTP_RAW_POST_DATA;
    $notification = simplexml_load_string($xml);


    $trx_id         = $notification->trx_id;
    $merchant_id    = $notification->merchant_id;
    $merchant_name  = $notification->merchant;
    $order_id       = (int) $notification->bill_no;
    $payment_reff   = $notification->payment_reff;
    $payment_date   = $notification->payment_date;
    $payment_status = $notification->payment_status_code;
    $signature      = $notification->signature;
//ConditionIfPaymentStatus
    switch ($payment_status) {
       case 2:

       global $woocommerce;

       $order = new WC_Order($order_id);

       $order->update_status('completed', __( 'Payment to be made upon delivery.', 'woocommerce' ));

		// Reduce stock levels
       $order->reduce_order_stock();

		// Remove cart
       $woocommerce->cart->empty_cart();

		//Update -> Faspay table						
       $update_faspay = $wpdb->query("UPDATE ". $wpdb->prefix ."faspay_order SET status = '2', approval_code = '$payment_reff' WHERE trx_id = '$trx_id'");
		// echo $update_faspay;exit;

		//SendResponBerhasil->Faspay
       $xml ="<faspay>";
       $xml.="<response>Payment Notification</response>";
       $xml.="<trx_id>$trx_id</trx_id>";
       $xml.="<merchant_id>$merchant_id</merchant_id>";
       $xml.="<bill_no>$order_id</bill_no>";
       $xml.="<response_code>00</response_code>";
       $xml.="<response_desc>Sukses</response_desc>";
       $xml.="<response_date>$payment_date</response_date>";
       $xml.="</faspay>";

       echo "$xml";

       break;

       case 3:
       echo "Payment Failed";

   }
}
?>