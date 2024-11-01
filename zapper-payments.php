<?php
/*
Plugin Name:          Zapper Payments for WooCommerce
Plugin URI:           http://woothemes.com/products/zapper-payments/
Description:          Add Zapper as a payment method to your website!
Version:              2.1.9 
Author:               Zapper Development
Author URI:           http://www.zapper.com
Developer:            Zapper Development
Developer URI:        http://www.zapper.com
Text Domain:          zapper-payments
WC requires at least: 2.5
WC tested up to:      8.2.1
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

add_action('plugins_loaded', 'zapper_init', 0);

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );


function zapper_init()
{
  if (!class_exists('WC_Payment_Gateway')) return;

  include_once('zapper-payment-gateway.php');

  // Add Zapper as an option to the payment gateway
  add_filter('woocommerce_payment_gateways', 'add_zapper_payment_gateway');
  function add_zapper_payment_gateway($methods)
  {
    $methods[] = 'Zapper_Payments';
    return $methods;
  }

  // Add Zapper settings page under the WooCommerce Menu
  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'zapper_action_links');
  function zapper_action_links($links)
  {
    $plugin_links = array(
      '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'zapper') . '</a>',
    );

    return array_merge($plugin_links, $links);
  }
}
