<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Zapper_Payments extends WC_Payment_Gateway
{

  function __construct()
  {
    require 'zapper-vars.php';
    $this->gateway_url = $gateway_url;
    $this->pos_api_url = $pos_api_url;
    $this->sandbox_mid = $test_merchant_id;
    $this->sandbox_sid = $test_site_id;

    $this->id = "zapper_payments";
    $this->method_title = __("Zapper", 'zapper-payments');
    $this->method_description = __("Allow your customers to pay with Zapper on your WooCommerce Store", 'zapper-payments');
    $this->title = __("Zapper", 'zapper-payments');
    $this->icon = apply_filters('woocommerce_gateway_icon', plugins_url() . '/' . plugin_basename(dirname(__FILE__)) . '/assets/zapper.png');;
    $this->has_fields = true;
    $this->supports = array('products');

    if (is_admin()) {
      wp_register_script("zapper_payments_js", plugins_url() . '/zapper-payments/scripts/zapper-settings.js', array('jquery'));
      wp_localize_script('zapper_payments_js', 'scriptVars', array('posApiUrl' =>  $this->pos_api_url));
      wp_enqueue_script('jquery');
      wp_enqueue_script('zapper_payments_js');

      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    $this->init_form_fields();
    $this->init_settings();
    foreach ($this->settings as $setting_key => $value) {
      $this->$setting_key = $value;
    }

    add_action('woocommerce_api_' . strtolower(get_class($this)), array(&$this, 'handle_callback'));
  }

  function handle_callback()
  {
    // echo $_COOKIE['z_order_id'];

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');

    $entityBody = file_get_contents('php://input');
    $entityBody = trim($entityBody, '"');
    $entityBody = str_replace('\\"', '"', $entityBody);
    $jsonBody = json_decode($entityBody, TRUE);

    try {

      if ($this->sandbox !== 'yes') {
        $incomingSignature = $_SERVER['HTTP_SIGNATURE'];

        if ($incomingSignature == null || $incomingSignature == '') {
          $this->setStatusCode(401, 'Signature empty');
          $error = new WP_Error('401', 'Signature is empty.');
          wp_send_json_error($error);
          return;
        }

        $token = strtoupper($this->pos_secret);
        $signature = hash_hmac('sha256', $entityBody, $token);

        if ($incomingSignature != $signature) {
          $this->setStatusCode(401, 'Signature invalid');
          $error = new WP_Error('401', 'Signature invalid.');
          wp_send_json_error($error);
          return;
        }
      }

      $zapperId = $jsonBody["zapperId"];
      $reference = $jsonBody["merchantOrderId"];
      $payment_status = $jsonBody["paymentStatus"];
      $is_success = strtolower($jsonBody["status"]) == 'success';

      $order = wc_get_order($reference);

      if ($order != null && !($order->is_paid())) { // since WC 2.5
        if ($payment_status == 2 || $is_success) {
          $order->add_order_note(__('Zapper payment completed.', 'zapper-payments'));
          $order->payment_complete($zapperId);
        } else if ($payment_status == 5 || $is_success == false) {
          $order->add_order_note('Error: ' . 'Zapper Payment was unsuccessful');
        }
      } else {
        $this->setStatusCode(200, 'Payment for order ' . $reference . 'has already been completed.');
      }
      return;
    } catch (Exception $e) {
      $this->setStatusCode(500, 'Unexpected error: ' . $e->getMessage());
      $error = new WP_Error('500', 'An unexpected error occurred: ' . $e->getMessage());
      wp_send_json_error($error);
      return;
    }
  }

  private function setStatusCode($code, $message)
  {
    global $wp_version;
    if (version_compare($wp_version, '4.4', '>=')) {
      status_header($code, $message);
    } else {
      status_header($code);
    }
    nocache_headers();
  }

  public function init_form_fields()
  {
    $this->form_fields = array(
      'enabled' => array(
        'title'   => __('Enable / Disable', 'zapper-payments'),
        'label'   => __('Enable this payment method', 'zapper-payments'),
        'type'    => 'checkbox',
        'default' => 'no',
      ),
      'sandbox' => array(
        'title'   => __('Sandbox Mode', 'zapper-payments'),
        'label'   => __('Enable Sandbox mode (Warning: Do not use in production!)', 'zapper-payments'),
        'type'    => 'checkbox',
        'default' => 'no',
        'description'  => __('For Testing purposes. The Zapper App must be setup with a test card (find "test credit cards" online).', 'zapper-payments'),
        'desc_tip'          => true,
      ),
      'merchant_id' => array(
        'title'    => __('Merchant ID', 'zapper-payments'),
        'type'     => 'number',
        'desc_tip' => __('The Merchant ID you received after registering with Zapper.', 'zapper-payments'),
      ),
      'site_id' => array(
        'title'    => __('Site ID', 'zapper-payments'),
        'type'     => 'number',
        'desc_tip' => __('The Merchant Site ID you received after registering with Zapper.', 'zapper-payments'),
      ),
      'pos_secret' => array(
        'title'        => __('POS Token', 'zapper-payments'),
        'type'         => 'string',
        'desc_tip'     => __('The POS Token you received after registering with Zapper.', 'zapper-payments'),
        'custom_attributes' => array(
          'readonly' => 'readonly',
        ),
      ),
      'manual_override' => array(
        'title'   => __('Manual Token Entry', 'zapper-payments'),
        'label'   => __('Override to allow manual POS Token entry', 'zapper-payments'),
        'type'    => 'checkbox',
        'default' => 'no',
        'desc_tip'  => __('Allows the Merchant to enter the POS Token manually. Not recommended.', 'zapper-payments'),
      ),
      'request_otp_button' => array(
        'title'             => __('Request OTP', 'zapper-payments'),
        'type'              => 'button',
        'custom_attributes' => array(
          'onclick' => "request_otp_ajax()",
        ),
        'desc_tip'       => __('An OTP will be sent via email. Use the OTP to auto-populate POS Token', 'zapper-payments'),
      ),
      'otp' => array(
        'title'    => __('OTP', 'zapper-payments'),
        'type'     => 'number',
        'desc_tip' => __('After requesting OTP, enter the OTP that has been emailed to the Zapper Merchant\'s address', 'zapper-payments'),
      ),
      'submit_otp_button' => array(
        'title'             => __('Submit OTP', 'zapper-payments'),
        'type'              => 'button',
        'custom_attributes' => array(
          "onclick" => "submit_otp_ajax()"
        ),
        'desc_tip'       => __('Submit the OTP to auto-populate the POS Token', 'zapper-payments'),
      ),
      'otp_status' => array(
        'title'             => __('Status', 'zapper-payments'),
        'type'              => 'statusLabel',
        'desc_tip'       => __('Configuration status', 'zapper-payments'),
        'label'   => __('', 'zapper-payments'),
      ),
    );
  }
  // Payment Form Stuff

  // 1. Set up payment fields
  public function payment_fields()
  {
    if ($this->sandbox === 'yes') {
      echo '<input type="hidden" name="zapper_payment_id" id="zapper_payment_id" /><div id="zapper_payment_wrapper"></div><div style="text-align:center; margin-top:8px;">Sandbox mode. You will be redirected to <strong>Zapper</strong> to perform a <strong>test</strong> payment.</div>';
      return;
    }

    if ($this->enabled == "no") {
      echo 'Zapper has not been enabled by the store owner yet. Please select a different payment method.';
      return;
    }

    $isValid = $this->validate_fields();

    if (!$isValid) {
      echo '<input type="hidden" name="zapper_payment_id" id="zapper_payment_id" /><div id="zapper_payment_wrapper"></div><div style="text-align:center; margin-top:8px;"><strong>Zapper Error: </strong>Invalid Zapper configuration - Please contact the store owner.</div>';
      return;
    }

    echo '<input type="hidden" name="zapper_payment_id" id="zapper_payment_id" /><div id="zapper_payment_wrapper"></div><div style="text-align:center; margin-top:8px;">You will be redirected to <strong>Zapper</strong> to complete your order.</div>';
  }

  // 2. Payment Form validation
  public function validate_fields()
  {
    $isValid = $this->merchant_id != "" && $this->site_id != "" && $this->pos_secret != "" && !($this->has_spaces($this->pos_secret));
    if ($isValid) {
      return true;
    }
    return false;
  }

  // User Clicks "Place Order"
  public function process_payment($order_id)
  {

    $customer_order = wc_get_order($order_id);

    $return_url = urlencode($this->get_return_url($customer_order));
    $redirect_url = '';
    if ($this->sandbox !== 'yes') {
      if (!$this->validate_fields()) {
        wc_add_notice('Invalid Zapper configuration. Please assist us by informing the store.', 'error');
        return;
      }
      $redirect_url = $this->gateway_url . '/woocommerce/?a=' . $customer_order->get_total() . '&r=' . $customer_order->get_id() . '&m=' . $this->merchant_id . '&s=' . $this->site_id . '&u=' . $return_url;
    } else {
      $redirect_url = $this->gateway_url . '/woocommerce/?a=' . $customer_order->get_total() . '&r=' . $customer_order->get_id() . '&m=' . $this->sandbox_mid . '&s=' . $this->sandbox_sid . '&u=' . $return_url;
    }

    return array(
      'result'   => 'success',
      'redirect' => $redirect_url
    );
  }
  private function has_spaces($str)
  {
    if (preg_match('/\s/', $str)) {
      return true;
    }
    return false;
  }
  public function generate_button_html($key, $data)
  {
    $field    = $this->plugin_id . $this->id . '_' . $key;
    $defaults = array(
      'class'             => 'button-secondary',
      'css'               => '',
      'custom_attributes' => array(),
      'desc_tip'          => false,
      'description'       => '',
      'title'             => '',
    );

    $data = wp_parse_args($data, $defaults);

    ob_start();
?>
    <tr valign="top">
      <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>
        <?php echo $this->get_tooltip_html($data); ?>
      </th>
      <td class="forminp">
        <fieldset>
          <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
          <button class="<?php echo esc_attr($data['class']); ?>" type="button" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php echo $this->get_custom_attribute_html($data); ?>><?php echo wp_kses_post($data['title']); ?></button>
          <?php echo $this->get_description_html($data); ?>
        </fieldset>
      </td>
    </tr>
  <?php
    return ob_get_clean();
  }

  public function generate_statusLabel_html($key, $data)
  {
    $field    = $this->plugin_id . $this->id . '_' . $key;
    $defaults = array(
      'class'             => 'label',
      'css'               => '',
      'custom_attributes' => array(),
      'desc_tip'          => false,
      'description'       => '',
      'title'             => '',
    );

    $data = wp_parse_args($data, $defaults);

    ob_start();
  ?>
    <tr valign="top">
      <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>
        <?php echo $this->get_tooltip_html($data); ?>
      </th>
      <td class="forminp">
        <fieldset>
          <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
          <label class="<?php echo esc_attr($data['class']); ?>" type="statusLabel" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" style="<?php echo esc_attr($data['css']); ?>" <?php echo $this->get_custom_attribute_html($data); ?>><?php echo wp_kses_post($data['label']); ?></label>
          <?php echo $this->get_description_html($data); ?>
        </fieldset>
      </td>
    </tr>
<?php
    return ob_get_clean();
  }
}
