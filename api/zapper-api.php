<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class ZapperApi {
  function verify_zapper_payment($merchant_id, $site_id, $pos_key, $pos_secret, $payment_id) {
    $url = 'https://zapapi.zapzap.mobi/zapperpointofsale/api/v2/merchants/' . $merchant_id . '/sites/' .
      $site_id . '/payments/' . $payment_id;

    $headers = array (
      'siteid'      => $site_id,
      'poskey'      => $pos_key,
      'posid'       => $pos_secret,
      'postype'     => 'WooCommerce Addon',
      'posversion'  => '2.0.1',
      'signature'   => $this->create_signature($pos_secret, $pos_key)
    );

    $response = wp_remote_get($url, array(
      'method'    => 'GET',
      'timeout'   => 90,
      'sslverify' => false,
      'headers'   => $headers
    ));

    if ( is_wp_error( $response ) )
  		throw new Exception( __( 'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'zapper-payments' ) );

    $response_body = wp_remote_retrieve_body( $response );

    return new ZapperPaymentResponse( $response_body );
  }

  private function create_signature($pos_secret, $pos_key) {
    return strtoupper ( hash ( 'sha256', strtoupper( $pos_secret . '&' . $pos_key ) ) );
  }
}

class ZapperPaymentResponse {
  function __construct($json_body) {
    $payment = json_decode($json_body);

    $this->success = ( $payment->statusId ) == 1;

    if ( $this->success == false ) {
      $this->error = $payment->message;
    } elseif ($payment->data != null && count( $payment->data ) > 0 && in_array("", $payment->data) == false) {

      $this->payment = $payment->data[0];

      if ($this->payment->ReceiptStatus == 5) {
        $this->success = false;
        $this->error = "Your payment was declined by Zapper, please try again, or use an alternative payment method.";
      }
    } else {
      $this->success = false;
      $this->error = "No payment found. Please make sure that you have scanned the Zapper QR Code and made the payment.";
    }
  }
}
?>
