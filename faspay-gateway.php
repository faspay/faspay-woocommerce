<?php
/*
Plugin Name: Faspay Gateway
Plugin URI: http://faspay.co.id/
Description: Faspay Payment Gateway.
Version: 2.0.2
Author: Gamma Satria Kurniawan @ FASPAY.

License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html   
*/
add_action('plugins_loaded', 'faspay_gateway_init', 0);

function faspay_gateway_init() {

	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

	/**
 	 * Gateway class
 	 */
	class WC_Gateway_Faspay extends WC_Payment_Gateway {
       public function __construct(){

        $this->id			= 'faspay';

        $this->has_fields 	= false;

		// Load the form fields
        $this->init_form_fields();

		// Load the settings.
        $this->init_settings();

        // Get setting values
        $this->enabled 		= $this->settings['enabled'];
        $this->title 		= "Faspay Online Payment";
        $this->description	= $this->settings['description'];
        $this->password		= $this->settings['password'];

        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
           add_action( 'woocommerce_update_options_payment_gateways_' . $this->id,
              array( &$this, 'process_admin_options' ) 
              );
           add_action('woocommerce_receipt_'.$this->id, array(&$this, 'receipt_page'));
           add_action('admin_notices_'.$this->id, array(&$this,'faspay_ssl_check'));

           add_action('woocommerce_thankyou_'.$this->id, array(&$this, 'thankyou_page'));
       } else {
           add_action( 'woocommerce_update_options_payment_gateways', 
              array( &$this, 'process_admin_options' ) 
              );
           add_action('woocommerce_receipt_faspay', array(&$this, 'receipt_page'));
           add_action('admin_notices', array(&$this,'faspay_ssl_check'));

           add_action('woocommerce_thankyou_faspay', array(&$this, 'thankyou_page'));

       }
   }

    public function thankyou_page(){}


		/**
	     * Initialize Gateway Settings Form Fields
	     */
    public function init_form_fields() {

      $this->form_fields = array(
        'enabled' => array(
            'title' => __( 'Enable/Disable', 'woothemes' ), 
            'label' => __( 'Enable Faspay', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ), 
        'title' => array(
            'title' => __( 'Title', 'woothemes' ), 
            'type' => 'text', 
            'description' => __( 'This controls the title which the user sees during checkout.', 'woothemes' ), 
            'default' => __( 'Pembayaran Faspay', 'woothemes' )
            ),
        'description' => array(
            'title' => __( 'Description', 'woothemes' ), 
            'type' => 'textarea', 
            'description' => __( 'This controls the description which the user sees during checkout.', 'woothemes' ), 
            'default' => 'Sistem pembayaran menggunakan Faspay.'
            ),  			
        'merchant_id' => array(
            'title' => __( 'Merchant ID', 'woothemes' ), 
            'type' => 'text', 
            'description' => __( 'Please field merchant ID.', 'woothemes' ), 
            ),
        'merchant_name' => array(
            'title' => __( 'Merchant Name', 'woothemes' ), 
            'type' => 'text', 
            'description' => __( 'Please field merchant name.', 'woothemes' ), 
            ),
        'user_id' => array(
            'title' => __( 'User ID', 'woothemes' ), 
            'type' => 'text', 
            'description' => __( 'Please field user ID.', 'woothemes' ), 
            ),
        'password' => array(
            'title' => __( 'Password', 'woothemes' ), 
            'type' => 'text', 
            'description' => __( 'Please field password.', 'woothemes' ), 
            ),
        'order_expire' => array(
            'title' => __( 'Order Expire In', 'woothemes' ), 
            'type' => 'text', 
            'description' => __( 'Please field order expire.', 'woothemes' ), 
            ),
        'server' => array(
            'title' => __( 'Server', 'woothemes' ), 
            'type' => 'select',
            'options'  => array(
               'http://faspaydev.mediaindonusa.com/pws/300002/183xx00010100000' => __( 'Development', 'woothemes' ),

               'https://faspay.mediaindonusa.com/pws/300002/383xx00010100000'   => __( 'Production', 'woothemes' )
               ), 
            'description' => __( 'Please field server.', 'woothemes' ), 
            ),
        'tcash' => array(
            'title' => __( 'Payment Channel', 'woothemes' ), 
            'label' => __( 'T-Cash', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ), 
        'xl_tunai' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'XL Tunai', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'mynt_artajasa' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Mynt Artajasa', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'mandiri_ecash' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Mandiri Ecash', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'dompetku' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Dompetku', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'bbm_money' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'BBM Money', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'bri_mobile_cash' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'BRI Mobile Cash', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'bri_epay' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'BRI E-Pay', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'permata_va' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Permata Virtual Account', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),	
        'bca_klik_pay' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'BCA KlikPay', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'mandiri_click_pay' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Mandiri ClickPay', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'bii_mobile_banking' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'BII Mobile Banking', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'bii_internet_banking' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'BII Virtual Account', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'cimb_clicks' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'CIMB Clicks', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'danamon' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Danamon Debit Online', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'credit_card' => array(
            'title' => __( '', 'woothemes' ), 
            'label' => __( 'Credit Card Visa / Master', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '',
            'default' => 'no'
            ),
        'parameter' => array(
            'title' => __( 'BCA KlikPay Parameter :', 'woothemes' ), 
            'type' => 'hidden',  
            ),
        'klik_pay_code' => array(
            'title' => __( 'Klik Pay Code', 'woothemes' ), 
            'type' => 'text', 
            ),
        'clear_key' => array(
            'title' => __( 'Clear Key', 'woothemes' ), 
            'type' => 'text' 
            ),
        'text_full_payment' => array(
            'title' => __( '', 'woothemes' ), 
            'type' => 'hidden', 
            'description' => __( 'Konfigurasi Untuk Full Payment.', 'woothemes' ), 
            ),
        'mid_full_payment' => array(
            'title' => __( 'MID Full Payment', 'woothemes' ), 
            'type' => 'text',  
            ),						
        'cicilan_tiga_bulan' => array(
            'title' => __( '', 'woothemes' ), 
            'type' => 'hidden', 
            'description' => __( 'Konfigurasi Untuk Cicilan 3 bulan.', 'woothemes' ), 
            ),
        'mid_tiga_bulan' => array(
            'title' => __( 'MID Cicilan 3 Bulan', 'woothemes' ), 
            'type' => 'text',  
            ),
        'pm_tiga_bulan' => array(
            'title' => __( 'Price Minimum', 'woothemes' ), 
            'type' => 'text',  
            ),	
        'status_cicilan_tiga' => array(
            'title' => __( 'Status', 'woothemes' ), 
            'label' => __( 'Enable', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),	
        'cicilan_enam_bulan' => array(
            'title' => __( '', 'woothemes' ), 
            'type' => 'hidden', 
            'description' => __( 'Konfigurasi Untuk Cicilan 6 bulan.', 'woothemes' ), 
            ),
        'mid_enam_bulan' => array(
            'title' => __( 'MID Cicilan 6 Bulan', 'woothemes' ), 
            'type' => 'text' 
            ),
        'pm_enam_bulan' => array(
            'title' => __( 'Price Minimum', 'woothemes' ), 
            'type' => 'text' 
            ),
        'status_cicilan_enam' => array(
            'title' => __( 'Status', 'woothemes' ), 
            'label' => __( 'Enable', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),			
        'cicilan_duabelas_bulan' => array(
            'title' => __( '', 'woothemes' ), 
            'type' => 'hidden', 
            'description' => __( 'Konfigurasi Untuk Cicilan 12 bulan.', 'woothemes' ), 
            ),
        'mid_duabelas_bulan' => array(
            'title' => __( 'MID Cicilan 12 Bulan', 'woothemes' ), 
            'type' => 'text' 
            ),
        'pm_duabelas_bulan' => array(
            'title' => __( 'Price Minimum', 'woothemes' ), 
            'type' => 'text' 
            ),
        'status_cicilan_duabelas' => array(
            'title' => __( 'Status', 'woothemes' ), 
            'label' => __( 'Enable', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'cicilan_duaempat_bulan' => array(
            'title' => __( '', 'woothemes' ), 
            'type' => 'hidden', 
            'description' => __( 'Konfigurasi Untuk Cicilan 24 bulan.', 'woothemes' ), 
            ),
        'mid_duaempat_bulan' => array(
            'title' => __( 'MID Cicilan 24 Bulan', 'woothemes' ), 
            'type' => 'text' 
            ),
        'pm_duaempat_bulan' => array(
            'title' => __( 'Price Minimum', 'woothemes' ), 
            'type' => 'text' 
            ),
        'status_cicilan_duaempat' => array(
            'title' => __( 'Status', 'woothemes' ), 
            'label' => __( 'Enable', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),
        'title_mix' => array(
            'title' => __( '', 'woothemes' ), 
            'type' => 'hidden', 
            'description' => __( 'Konfigurasi Untuk Kombinasi (MIX).', 'woothemes' ), 
            ),
        'status_mix' => array(
            'title' => __( 'Status MIX', 'woothemes' ), 
            'label' => __( 'Enable', 'woothemes' ), 
            'type' => 'checkbox', 
            'description' => '', 
            'default' => 'no'
            ),																																					        
        );
    }

		/**
		 * Admin Panel Options 
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 **/
    public function admin_options() {
        ?>
        <h3><?php _e('Faspay','woothemes'); ?></h3>	    	
        <p>
        <?php _e( 'Faspay Gateway Systems', 'woothemes' ); ?>
        </p>
        <table class="form-table">
        <?php $this->generate_settings_html(); ?>
        </table>    	
        <p>
        <?php
    }

	    /**
		 * Payment fields for faspay
		 **/	
    public function generate_faspay_form( $order_id ) {
        global $woocommerce;

        $order = new WC_Order( $order_id );

        $item_loop = 0;

        if (sizeof($order->get_items())>0) : 
            foreach ($order->get_items() as $item) :
                if ($item['qty']) :

                $item_loop++;

                $faspay_args['item_name_'.$item_loop] = $item['name'];
                $faspay_args['quantity_'.$item_loop] = $item['qty'];
                $faspay_args['amount_'.$item_loop] = $item['line_total'];

                endif;
            endforeach; 
        endif;

		// Shipping Cost
        $item_loop++;
        $faspay_args['item_name_'.$item_loop] = __('Shipping cost', 'woothemes');
        $faspay_args['quantity_'.$item_loop] = '1';
        $faspay_args['amount_'.$item_loop] = number_format($order->order_shipping, 2);

        $faspay_args_array = array();

        foreach ($faspay_args as $key => $value) {
           $faspay_args_array[] = '<input type="hidden" name="'.$key.'" value="'.esc_attr( $value ).'" />';
       }

		// PrepareParameters
       $params = array(
         'action'   => 'payment',
         'product'  => 'Order : #'.$order_id.'',
					'price'    => ''.$order->order_total.'', // Total Amount
					'quantity' => 1,
					'comments' => 'Transaksi Pembelian', // Optional           
					'ureturn'  => add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id')))),
					'unotify'  => 'http://domain.com/notify.php',
					'ucancel'  => 'http://domain.com/cancel.php',
					'format'   => 'json' // Format: xml / json. Default: xml 
        );

		//CreateTables
       function divebook_create_table_dive(){

		//GetTheTableNameWithTheWPdatabasePrefix
            global $wpdb;
            $table_name = $wpdb->prefix . "faspay_order";
            
            global $divebook_db_table_dive_version;
            $installed_ver = get_option( "divebook_db_table_dive_version" );

    		//Check if the table already exists and if the table is up to date, if not create it
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ||  $installed_ver != $divebook_db_table_dive_version ) {
                $sql = "CREATE TABLE " . $table_name . " (
                      trx_id bigint(10) NOT NULL,
                      order_id varchar(30) NOT NULL,
                      date_trx datetime NOT NULL,
                      total_amount varchar(30) NOT NULL,
                      approval_code varchar(30) NOT NULL,
                      status varchar(3) NOT NULL,
                      UNIQUE KEY trx_id (trx_id)
                      );";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                update_option( "divebook_db_table_dive_version", $divebook_db_table_dive_version );
            }
            //AddDatabaseTableVersionsToOptions
            add_option("divebook_db_table_dive_version", $divebook_db_table_dive_version);
        }
        		//ExecCreateTable	
        divebook_create_table_dive();

        global $wpdb;

        		//Choose Payment Gateways	
        echo "<ul class=customer_details><li>Please Choose Payment Channel :</li><p></p>";

        echo "<form action='' method=post>";
        		//SelectDataChoosePaymentGateway
        $sql = "SELECT ". $wpdb->prefix ."options.option_value FROM ". $wpdb->prefix ."options WHERE ". $wpdb->prefix ."options.option_name = 'woocommerce_faspay_settings'";
        $active_plugins_data ="";

        $paymentlist = $wpdb->get_results($sql, ARRAY_A);

        foreach ($paymentlist as $rows) {  
            $active_plugins_data .= $rows["option_value"];
        }

        $array = unserialize($active_plugins_data);
        $merchant_id				= $array["merchant_id"];
        $merchant_name				= $array["merchant_name"];
        $user_id					= $array["user_id"];
        $password					= $array["password"];
        $order_expire				= $array["order_expire"];
        $server						= $array["server"];
        $tcash						= $array["tcash"];
        $bbm_money                  = $array["bbm_money"];
        $xl_tunai                   = $array["xl_tunai"];
        $dompetku                   = $array["dompetku"];
        $mynt_artajasa				= $array["mynt_artajasa"];
        $bri_mobile_cash			= $array["bri_mobile_cash"];
        $bri_epay                   = $array["bri_epay"];
        $mandiri_ecash              = $array["mandiri_ecash"];
        $permata_va					= $array["permata_va"];
        $bca_klik_pay				= $array["bca_klik_pay"];
        $mandiri_click_pay			= $array["mandiri_click_pay"];
        $bii_mobile_banking		    = $array["bii_mobile_banking"];
        $bii_internet_banking		= $array["bii_internet_banking"];
        $danamon                    = $array["danamon"];
        $cimb_clicks                = $array["cimb_clicks"];
        $credit_card				= $array["credit_card"];

        $klik_pay_code              = $array["klik_pay_code"];
        $clear_key                  = $array["clear_key"];
        // $atm_bersama				= $array["atm_bersama"];
        $mid_full_payment			= $array["mid_full_payment"];
        $mid_tiga_bulan				= $array["mid_tiga_bulan"];
        $pm_tiga_bulan				= $array["pm_tiga_bulan"];
        $status_cicilan_tiga		= $array["status_cicilan_tiga"];
        $mid_enam_bulan				= $array["mid_enam_bulan"];
        $pm_enam_bulan				= $array["pm_enam_bulan"];
        $status_cicilan_enam		= $array["status_cicilan_enam"];	
        $mid_duabelas_bulan			= $array["mid_duabelas_bulan"];
        $pm_duabelas_bulan			= $array["pm_duabelas_bulan"];
        $status_cicilan_duabelas	= $array["status_cicilan_duabelas"];
        $mid_duaempat_bulan			= $array["mid_duaempat_bulan"];
        $pm_duaempat_bulan			= $array["pm_duaempat_bulan"];
        $status_cicilan_duaempat	= $array["status_cicilan_duaempat"];
        $status_mix					= $array["status_mix"];


        echo "<table width=100%>";

        if($tcash == 'yes'){
            echo"<tr><td><input type=radio name=payment value=302 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/tcash.png' title='Pembayaran melalui eMoney Telkomsel tCash'></td>";
        }
        if($xl_tunai == 'yes'){
            echo"<tr><td><input type=radio name=payment value=303 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/xl_tunai.png' title='Pembayaran melalui eMoney XL Tunai'></td>";
        }
        if($mynt_artajasa == 'yes'){
            echo"<tr><td><input type=radio name=payment value=304 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/mynt.png' title='Pembayaran melalui eMoney MYNT Artajasa'></td>";
        }
        if($mandiri_ecash == 'yes'){
            echo"<tr><td><input type=radio name=payment value=305 id=purDecFlag0 onclick=togglePurDec(event) />
            <img alt='Mandiri Ecash' src='".get_home_url()."/wp-content/plugins/faspay/images/mandiriecash.png' title='Pembayaran melalui Mobile Cash BRI'></td>";
        }
        if($dompetku == 'yes'){
            echo"<tr><td><input type=radio name=payment value=307 id=purDecFlag0 onclick=togglePurDec(event) />
            <img alt='Dompetku' src='".get_home_url()."/wp-content/plugins/faspay/images/dompetku.png' title='Pembayaran melalui eMoney XL Tunai'></td>";
        }
        if($bbm_money == 'yes'){
            echo"<tr><td><input type=radio name=payment value=308 id=purDecFlag0 onclick=togglePurDec(event) />
            <img alt='bbmmoney' src='".get_home_url()."/wp-content/plugins/faspay/images/bbm.png' title='Pembayaran melalui eMoney XL Tunai'></td>";
        }
        if($bri_mobile_cash == 'yes'){
            echo"<tr><td><input type=radio name=payment value=400 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/mocash.png' title='Pembayaran melalui Mobile Cash BRI'></td>";
        }
        if($bri_epay == 'yes'){
            echo"<tr><td><input type=radio name=payment value=401 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/bri-epay.png' title='Pembayaran melalui ePay BRI'></td>";
        }
        if($bca_klik_pay == 'yes'){
            echo"<tr><td><input type=radio name=payment value=405 id=purDecFlag1 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/bca-klikpay.png' title='Pembayaran melalui BCA KlikPay'></td>";
        }
        if($mandiri_click_pay == 'yes'){
            echo"<tr><td><input type=radio name=payment value=406 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/mandiri-clickpay.png' title='Pembayaran melalui Mandiri clickPay'></td>";
        }
        if($bii_mobile_banking == 'yes'){
            echo"<tr><td><input type=radio name=payment value=407 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/bii.png' title='Pembayaran melalui Mobile Banking BII'></td>";
        }
        if($credit_card == 'yes'){
            echo"<tr><td><input type=radio name=payment value=500 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/credit-card.png' title='Pembayaran melalui Kartu Kredit Visa/Master'></td>";
        }		
        if($bii_internet_banking == 'yes'){
            echo"<tr><td><input type=radio name=payment value=408 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/bii.png' title='Pembayaran melalui Internet Banking BII'></td>";
        }
        if($permata_va == 'yes'){
            echo "<tr><td><input type=radio name=payment value=402 id=purDecFlag0 onclick=togglePurDec(event) />
            <img src='".get_home_url()."/wp-content/plugins/faspay/images/permata-va.png' title='Pembayaran melalui Virtual Account Permata'></td>";
        }
        if($cimb_clicks == 'yes'){
            echo "<tr><td><input type=radio name=payment value=700 id=purDecFlag0 onclick=togglePurDec(event) />
            <img alt='cimb clicks' src='".get_home_url()."/wp-content/plugins/faspay/images/cimb.png' title='Pembayaran melalui Virtual Account Permata'></td>";
        }
        if($danamon == 'yes'){
            echo "<tr><td><input type=radio name=payment value=701 id=purDecFlag0 onclick=togglePurDec(event) />
            <img alt='danamon' src='".get_home_url()."/wp-content/plugins/faspay/images/danamon.png' title='Pembayaran melalui Virtual Account Permata'></td>";
        }
        echo "</tr>";	

        echo "</table>";
        		//FunctionBCAklikPay
        echo "<br>";
        echo "<script>
        function togglePurDec(evt) {
          evt = (evt) ? evt : event;
          var target = (evt.target) ? evt.target : evt.srcElement;
          var block = document.getElementById('purchaseDecisionData');
          if (target.id == 'purDecFlag1') {
           block.style.display = 'block';
        } else {
           block.style.display = 'none';
        }
        }
        </script>";

        echo '<div id=purchaseDecisionData style=display:none; margin-left:20px>';						
        echo "<ul class=customer_details><p></p>";

        $pattern = '/<!--:([#0-9A-Za-z]+)-->/';

        foreach($order->get_items() as $item) :
            echo '<strong>ITEM :&nbsp&nbsp'.$item['name'].'</strong> 
            <select name=payment_plan[]>
               <option value="1">Full Payment</option>';

               if($item['line_total'] >= '500000' ){

                if($status_cicilan_tiga == 'yes'){
                    echo '<option value="2" >Cicilan 3 Bulan</option>';
                }
                if($status_cicilan_enam == 'yes'){
                    echo '<option value="3" >Cicilan 6 Bulan</option>';
                }
                if($status_cicilan_duabelas == 'yes'){
                    echo '<option value="4" >Cicilan 12 Bulan</option>';
                }
                if($status_cicilan_duaempat == 'yes'){
                    echo '<option value="5" >Cicilan 24 Bulan</option>';
                }
            }

            echo '</select><br>'; 
        endforeach;
        echo '</div>';
		//SelectDataGeneralSettingInputAdminPanel->InMerchantDB		
        echo "<input type=hidden name=merchant_id value=$merchant_id>";
        echo "<input type=hidden name=merchant_name value=$merchant_name>";
        echo "<input type=hidden name=klik_pay_code value=$klik_pay_code>";
        echo "<input type=hidden name=clear_key value=$clear_key>";
        echo "<input type=hidden name=order_expired value=$order_expire>";
        echo "<input type=hidden name=user_id value=$user_id>";
        echo "<input type=hidden name=password value=$password>";
        echo "<input type=hidden name=server value=$server>";

        echo "<br>";
        echo "<input type=submit class=button name=submit id=submit value=Process>";
        echo "</form>";



		//ALLConditionStartHere
    if(isset($_POST['submit'])){

        WC()->cart->empty_cart();
			//FunctionParsingXML
        function  parse_xml2array( $input, $callback = NULL, $_recurse = FALSE ) {
        				// Get input, loading an xml string with simplexml if its the top level of recursion
            $data = ( ( !$_recurse ) && is_string( $input ) ) ? simplexml_load_string( $input ) : $input;		
        				// Convert SimpleXMLElements to array
            if ( $data instanceof SimpleXMLElement ) {
               $data = (array) $data;
           }		
        				// Recurse into arrays
           if ( is_array( $data ) ) foreach ( $data as &$item ) {
               $item = parse_xml2array( $item, $callback, TRUE );
           }		
        				// Run callback and return
           return ( !is_array( $data ) && is_callable( $callback ) ) ? call_user_func( $callback, $data ) : $data;
        }
    			//EndSendPostDataXML

    			//FunctionKeyGeneratorBCA	
        function genKeyId($clearKey){
            return strtoupper(bin2hex(str2bin($clearKey)));
        }

        function genSignature($klikPayCode, $transactionDate, $transactionNo, $amount, $currency, $keyId){

        					//Signature Step 1
           $tempKey1 = $klikPayCode . $transactionNo . $currency . $keyId;
           $hashKey1 = getHash($tempKey1);					 

        					// Signature Step 2
           $expDate = explode("/",substr($transactionDate,0,10));
           $strDate = intval32bits($expDate[0] . $expDate[1] . $expDate[2]);
           $amt = intval32bits($amount);
           $tempKey2 = $strDate + $amt;
           $hashKey2 = getHash((string)$tempKey2);

        					// Generate Key Step 3
           $signature = abs($hashKey1 + $hashKey2);

           return $signature; 
        }

        function genAuthKey($klikPayCode, $transactionNo, $currency, $transactionDate, $keyId){

                 $klikPayCode = str_pad($klikPayCode, 10, "0");
                 $transactionNo = str_pad($transactionNo, 18, "A");
                 $currency = str_pad($currency, 5, "1");
                 $value_1 = $klikPayCode . $transactionNo . $currency . $transactionDate . $keyId;
                 $hash_value_1 = strtoupper(md5($value_1));

                    if (strlen($keyId) == 32)
                    $key = $keyId . substr($keyId,0,16);
                    else if (strlen($keyId) == 48)
                    $key = $keyId;

                return strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_3DES, hex2bin($key), hex2bin($hash_value_1), MCRYPT_MODE_ECB)));
        }

        function convertHex2bin($data){
             $len = strlen($data);
             return pack("H" . $len, $data);
        }

        function str2bin($data){
             $len = strlen($data);
             return pack("a" . $len, $data);
        }

        function intval32bits($value){
             if ($value > 2147483647)
              $value = ($value - 4294967296);
            else if ($value < -2147483648)
              $value = ($value + 4294967296);

            return $value;
        }

        function getHash($value){
             $h = 0;
             for ($i = 0;$i < strlen($value);$i++){
              $h = intval32bits(add31T($h) + ord($value{$i}));
            }
            return $h;
        }

        function add31T($value){
             $result = 0;
             for($i=1;$i <= 31;$i++){
              $result = intval32bits($result + $value);
            }

            return $result;
        }
			//EndFunctionGeneratorBCA

        $payment 		= $_POST['payment'];
        $merchant_id 	= $_POST['merchant_id'];
        $merchant_name 	= $_POST['merchant_name'];
        $user_id 		= $_POST['user_id'];
        $password 		= $_POST['password'];
        $url	 		= $_POST['server'];
        $order_id 		= $order_id;

        $price 			= $params['price']*100.00;
        $disc=$order->get_total_discount()*100.00;
        			//$price           =$item['line_total']*100.00;
        foreach($order->get_items() as $item){
            $bill_gross += $item[line_subtotal]*100.00-$disc;
        }								
        $shipping		= $order->order_shipping*100.00;

        $email 			= $order->billing_email;
        $str 			= $user_id.$password.$order_id;
        $mi_signature 	= hash('sha1',hash('md5',$str));	

        $bill_date 		= $order->order_date;
        $bill_expired 	= '+'.$_POST['order_expired'].' '.'day';
        $expired_date 	= date('Y-m-d H:i:s',strtotime($bill_expired,strtotime($bill_date)));
        $productinfo 	= $item['name'];
        $qty 			= $item['qty'];
        $total 			= $item['line_subtotal']*100.00;

        $cust_no 		= $order->customer_user;			
        $phone 			= $order->billing_phone;
        $address 		= $order->billing_address_1;
        $city 			= $order->billing_city;
        $postcode 		= $order->billing_postcode;
        $country_code 	= $order->billing_country;
        $state		 	= $order->billing_state;
        $custname 		= $order->billing_first_name." ".$order->billing_last_name;

        $xml="<faspay>"."\n";
        $xml.="		<request>Post Data Transaksi</request>"."\n";
        $xml.="		<merchant_id>$merchant_id</merchant_id>"."\n";
        $xml.="		<merchant>$merchant_name</merchant>"."\n";
        $xml.="		<bill_no>$order_id</bill_no>"."\n";
        $xml.="		<bill_reff>$order_id</bill_reff>"."\n";
        $xml.="		<bill_date>$bill_date</bill_date>"."\n";
        $xml.="		<bill_expired>$expired_date</bill_expired>"."\n";
        $xml.="		<bill_desc>Pembelian di $merchant_name</bill_desc>"."\n";
        $xml.="		<bill_currency>IDR</bill_currency>"."\n";
        $xml.="		<bill_gross>$bill_gross</bill_gross>"."\n";
        $xml.="		<bill_tax>0.00</bill_tax>"."\n";
        $xml.="		<bill_miscfee>$shipping</bill_miscfee>"."\n";
        $xml.="		<bill_total>$price</bill_total>"."\n";
        $xml.="		<cust_no>$cust_no</cust_no>"."\n";
        $xml.="		<cust_name>$custname</cust_name>"."\n";
        $xml.="		<payment_channel>$payment</payment_channel>"."\n";

			//ConditionPayTypeKlikPayBCA
        if($payment == '405'){

                $countpay = '0';
            foreach($_POST['payment_plan'] as $row=>$act){
                 $payplan = $_POST['payment_plan'][$row];

                if($payplan){
                  $countpay++;					 

                    if($payplan >= '2' && $payplan != '1'){
                     $count++;
                     $pay = '2';
                    }
				} //IfPayplan
			} //EndForeach

				//Installment
				if($count == $countpay){
					if($pay == '2'){
						$paytype = '2';
						$xml.="		<pay_type>2</pay_type>"."\n";

						$counter = 0;
						foreach($order->get_items() as $item){
							//$nominal = $item[line_subtotal]*100.00;
							$name[$counter]=$item[name];
							$qty[$counter]=$item[qty];
							$nominal[$counter]=$item[line_subtotal]*100.00;
							$counter++;
						}
						
						$counter = 0;
						foreach($_POST['payment_plan'] as $row=>$act){
							$payplan = $_POST['payment_plan'][$row];
							$xml.="		<item>"."\n";
							$xml.="			<product>$name[$counter]</product>"."\n";
							$xml.="			<qty>$qty[$counter]</qty>"."\n";
							$xml.="			<amount>$nominal[$counter]</amount>"."\n";
							$xml.="			<payment_plan>02</payment_plan>"."\n";
							
                            if($payplan == '2'){
                               $xml.="			<tenor>03</tenor>"."\n";
                               $xml.="			<merchant_id>$mid_tiga_bulan</merchant_id>"."\n";	
                           }elseif($payplan == '3'){
                               $xml.="			<tenor>06</tenor>"."\n";
                               $xml.="			<merchant_id>$mid_enam_bulan</merchant_id>"."\n";
                           }elseif($payplan == '4'){
                               $xml.="			<tenor>12</tenor>"."\n";
                               $xml.="			<merchant_id>$mid_duabelas_bulan</merchant_id>"."\n";
                           }else{
                               $xml.="			<tenor>24</tenor>"."\n";
                               $xml.="			<merchant_id>$mid_duaempat_bulan</merchant_id>"."\n";
                           }

							//$xml.="			<merchant_id>$merchant_id</merchant_id>"."\n";

                           $counter++;
                           $xml.="		</item>"."\n";
                       }
                       if($counter > 5){
                         echo "<script>alert('Sorry, only 5 item for installment transaction !');</script>";
                         exit();
                        }
                    }
                }
				//MIX	
           if($count < $countpay && $count != ''){
               $paytype = '3';
               $xml.="		<pay_type>3</pay_type>"."\n";

               $counter = 0;
               foreach($order->get_items() as $item){
							//$nominal = $item[line_subtotal]*100.00;
                   $name[$counter]=$item[name];
                   $qty[$counter]=$item[qty];
                   $nominal[$counter]=$item[line_subtotal];
                   $counter++;
               }
               $counter = 0;
               foreach($_POST['payment_plan'] as $row=>$act){
                   $payplan = $_POST['payment_plan'][$row];
                   $xml.="		<item>"."\n";
                   $xml.="			<product>$name[$counter]</product>"."\n";
                   $xml.="			<qty>$qty[$counter]</qty>"."\n";
                   $xml.="			<amount>$nominal[$counter]</amount>"."\n";

                   if($payplan == '1'){
                    $xml.="			<payment_plan>01</payment_plan>"."\n";
                }else{
                    $xml.="			<payment_plan>02</payment_plan>"."\n";
                }

                if($payplan == '1'){
                 $xml.="			<tenor>00</tenor>"."\n";
                 $xml.="			<merchant_id>$mid_full_payment</merchant_id>"."\n";
									//$counterfull++;
             }elseif($payplan == '2'){
                 $xml.="			<tenor>03</tenor>"."\n";
                 $xml.="			<merchant_id>$mid_tiga_bulan</merchant_id>"."\n";
                 $countertiga++;	
             }elseif($payplan == '3'){
                 $xml.="			<tenor>06</tenor>"."\n";
                 $xml.="			<merchant_id>$mid_enam_bulan</merchant_id>"."\n";
                 $counterenam++;
             }elseif($payplan == '4'){
                 $xml.="			<tenor>12</tenor>"."\n";
                 $xml.="			<merchant_id>$mid_duabelas_bulan</merchant_id>"."\n";
                 $counterduabelas++;
             }else{
                 $xml.="			<tenor>24</tenor>"."\n";
                 $xml.="			<merchant_id>$mid_duaempat_bulan</merchant_id>"."\n";
                 $counterduaempat++;
             }

							//$xml.="			<merchant_id>$merchant_id</merchant_id>"."\n";
             $counter++;
             $xml.="		</item>"."\n";

						} //endforeach

						//Condition MIX
						if($status_mix == 'no'){
							echo "<script>alert('Sorry, this item not allow to combination transaction!');</script>";
							exit();
						}
						
						//MIX condition new
						$counter_count = $countertiga+$counterenam+$counterduabelas+$counterduaempat;
						if($counter_count > 5){
							echo "<script>alert('Sorry, only 5 item for installment transaction !');</script>";
							exit();	  
						}

					//$status = '3';
                    }	
				//NoInstallment
                    if($countpay = '2' && $count == ''){
                      $paytype = '1';
                      $xml.="		<pay_type>1</pay_type>"."\n";
                      foreach($order->get_items() as $item) :	
                          $nominal = $item[line_subtotal]*100;
                      $xml.="		<item>"."\n";		
                      $xml.="			<product>$item[name]</product>"."\n";
                      $xml.="			<qty>$item[qty]</qty>"."\n";
                      $xml.="			<amount>$nominal</amount>"."\n";
                      $xml.="			<payment_plan>01</payment_plan>"."\n";
                      $xml.="			<tenor>00</tenor>"."\n";
                      $xml.="			<merchant_id>$mid_full_payment</merchant_id>"."\n";
                      $xml.="		</item>"."\n";
                      endforeach;	
					//$status = '1';
                  }

              }

            else{
                $pattern = '/<!--:([#0-9A-Za-z]+)-->/';

                $xml.="		<pay_type>1</pay_type>"."\n";
                foreach($order->get_items() as $item) :	
                     $nominal = $item[line_subtotal]*100.00;
                     $xml.="		<item>"."\n";		
                     $xml.="			<product>".preg_replace($pattern, '', $item[name])."</product>"."\n";
                     $xml.="			<qty>$item[qty]</qty>"."\n";
                     $xml.="			<amount>$nominal</amount>"."\n";
                     $xml.="			<payment_plan>01</payment_plan>"."\n";
                     $xml.="			<tenor>00</tenor>"."\n";
                     $xml.="			<merchant_id>$mid_full_payment</merchant_id>"."\n";
                     $xml.="		</item>"."\n";
                endforeach;
            }

        $xml.="		<bank_userid>bot123456</bank_userid>"."\n";
        $xml.="		<msisdn>$phone</msisdn>"."\n";
        $xml.="		<email>$email</email>"."\n";
        $xml.="		<terminal>10</terminal>"."\n";
        $xml.="		<billing_address>$address</billing_address>"."\n";
        $xml.="		<billing_address_city>$city</billing_address_city>"."\n";
        $xml.="		<billing_address_region></billing_address_region>"."\n";  
        $xml.="		<billing_address_state>$state</billing_address_state>"."\n";  
        $xml.="		<billing_address_poscode>$postcode</billing_address_poscode>"."\n";
        $xml.="		<billing_address_country_code>$country_code</billing_address_country_code>"."\n";  
        $xml.="		<receiver_name_for_shipping>$custname</receiver_name_for_shipping>"."\n";  
        $xml.="		<shipping_address>$address</shipping_address>"."\n";
        $xml.="		<shipping_address_city>$city</shipping_address_city>"."\n";
        $xml.="		<shipping_address_region></shipping_address_region>"."\n";
        $xml.="		<shipping_address_state>$state</shipping_address_state>"."\n";
        $xml.="		<shipping_address_poscode>$postcode</shipping_address_poscode>"."\n";
        $xml.="		<reserve1></reserve1>"."\n";
        $xml.="		<reserve2></reserve2>"."\n";
        $xml.="		<signature>$mi_signature</signature>"."\n";								
        $xml.="		</faspay>"."\n";

        $body = $xml;
        $c = curl_init ($url);
        curl_setopt ($c, CURLOPT_POST, true);
        curl_setopt ($c, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt ($c, CURLOPT_POSTFIELDS, $body);
        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($c, CURLOPT_SSL_VERIFYPEER, false);
        $page = curl_exec ($c);
        curl_close ($c);
        $rsp = parse_xml2array($page);
			//echo $rsp['trx_id'];
			//echo $rsp['response_code'];
			//my_custom_checkout_field_update_order_meta($txnid, $rsp['trx_id']);
			//print_r($rsp);exit;
			//InsertTrxID->MerchantDB (SaveResponFromMediaIndonusa)			
         $insert_trx = $wpdb->query("insert into ". $wpdb->prefix ."faspay_order (trx_id, order_id, date_trx, total_amount, status) values ('$rsp[trx_id]', '$order_id', '$bill_date', '$bill_gross', '1')");


			//ConditionUrlRedirect
         if($payment == '405'){

            $clearKey			= $clear_key;
            $klikPayCode		= $klik_pay_code;
            $transactionNo		= $rsp[trx_id];
            $transactionDate	= date("d/m/Y H:i:s", strtotime($bill_date));
            $totalAmount		= $bill_gross/100.00;
            $currency			= "IDR";
            $srv				= get_bloginfo('wpurl');

            $keyId = genKeyId($clearKey);
            $signature_bca = genSignature($klikPayCode, $transactionDate, $transactionNo, $totalAmount, $currency, $keyId);
				//$authKey = genAuthKey($klikPayCode,$transactionNo,$currency,$transactionDate,$keyId);

            $post = array(

               "klikPayCode"		=> $klikPayCode,
               "transactionDate"	=> date("d/m/Y H:i:s", strtotime($bill_date)),
               "transactionNo" 	    => $rsp[trx_id],
               "currency"			=> 'IDR',
               "totalAmount" 		=> $totalAmount . '.00',
               "payType"			=> '0'.$paytype,
               "signature"			=> $signature_bca,
               "descp"				=> 'Pembelian Barang di'.' '.$merchant_name,
               "callback"			=> "$srv"."/wp-content/plugins/faspay/thanks-page.php?trx_id=$rsp[trx_id]",
               "miscFee"			=> $shipping/100.00 . '.00'
               );

            if($url == "http://faspaydev.mediaindonusa.com/pws/300002/183xx00010100000"){
				$urlBCA = "http://faspaydev.mediaindonusa.com/redirectbca";
                // $urlBCA = "https://202.6.215.230:8081/purchasing/purchase.do?action=loginRequest";
            }else{
                $urlBCA = "https://klikpay.klikbca.com/purchasing/purchase.do?action=loginRequest";
            }
					//echo $urlBCA;exit;							
          $string = '<form method="post" name="form" action="'.$urlBCA.'">';

          if ($post != null) {

             foreach ($post as $name => $value) {
              $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }

        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';

        echo $string;
        exit;

    }else{

				//ConditionServer
    if($server == 'http://faspaydev.mediaindonusa.com/pws/300002/183xx00010100000'){				
        $urlRedirect = "http://faspaydev.mediaindonusa.com/pws/100003/0830000010100000/";
    }else{
        $urlRedirect = "https://faspay.mediaindonusa.com/pws/100003/2830000010100000/";
    }	

     $url = $urlRedirect.$mi_signature.'?trx_id='.$rsp['trx_id'].'&merchant_id='.$merchant_id.'&bill_no='.$order_id;
     ob_start();
     header("Location:".$url);

			}
		}
    }

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {
        global $woocommerce;
        
        $order = new WC_Order( $order_id );
            
            /* 2.1.0 */
        $checkout_payment_url = $order->get_checkout_payment_url(true);

        return array(
            'result' => 'success', 
            'redirect' =>  
                add_query_arg(
                    'key', 
                    $order->order_key, 
                    $checkout_payment_url                       
            )
        );
    }

		/**
		 * receipt_page
		 **/
		function receipt_page( $order ) {
			global $woocommerce;
			//echo '<p>'.__('Thank you for your order.', 'woocommerce').'</p>';
			echo $this->generate_faspay_form( $order );
			
		}
		
		
	}

	/**
 	* Add the Gateway to WooCommerce
 	**/
     function add_faspay_gateway( $methods ) {
      $methods[] = 'WC_Gateway_Faspay'; return $methods;
  }

  add_filter('woocommerce_payment_gateways', 'add_faspay_gateway' );
}