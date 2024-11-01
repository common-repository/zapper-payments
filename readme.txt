=== Zapper Payments ===
Contributors: keiranvv, izaksm, adame
Tags: zapper, payments, payment gateway
Requires at least: 4.3
Tested up to: 6.3.2
Requires PHP: 5.2
Stable tag: 2.1.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept card and QR code payments with Zapper and 14+ supported mobile payment apps.

== Description ==

Accept Card and QR Code Payments with Zapper and 14+ Supported Mobile Payment Apps.

The Zapper payment plugin for WooCommerce facilitates fast, secure and convenient card and QR code payments at checkout, allowing your customers to pay using either their Visa or Mastercard debit and credit cards, or using the Zapper and supported partner mobile apps to scan and pay for their order. Real-time payment updates are pushed to the merchant's store to complete the order.

== Key Features ==

* Quick and Easy Integration: Get up and running in minutes with our user-friendly setup.
* Accept Visa and Mastercard: Allow your customers to pay with their Visa and Mastercard debit and credit cards.
* QR Code Payments: Accept QR code payments from Zapper and multiple partner payment apps.
* Multiple Payment Methods: Let Zapper customers pay using in-app card, bank account, or RCS store card.
* Security First: Enjoy safe and reliable online transactions with 3D Secure and PCI compliance.

Need to register as a Zapper Merchant? [Sign up now](https://www.zapper.com/pricing/)

== Installation ==
You can find detailed instructions for this plugin and learn more about Zapper integrations [here](https://zapper.gitbook.io/integrations/web/ecommerce-plugins/woocommerce-wordpress).
###Installing the Plugin
To install the plugin:

1. Upload the plugin files to the `/wp-content/plugins/zapper-payments` directory, or install the plugin through the
WooCommerce 'Plugins' screen directly.
2. Activate the plugin through the 'Plugins' screen in Wordpress.

###How to Configure for Testing
If you do not have a Zapper account and want to test the Zapper payment plugin, simply enable Sandbox Mode (Do not use this in production). 

To make payments in the Sandbox Mode, add a test card to the Zapper mobile application as follows:
1. Navigate to Payment Methods.
2. Select "Add Card".
3. Add a valid test card number (search for "Test Credit Cards" online. e.g. 4000 0000 0000 0002).
4. Enter any future expiry date and CVC.
5. Enter any name for "Name on Card".
6. Select "Save".

###How to Configure for production
A Zapper merchant account is required to use this plugin. Register [here](https://www.zapper.com/pricing/). Once registered, you will receive your Zapper Merchant and Site ID's.

To configure the Zapper payment plugin for production, a Zapper Merchant ID and Site ID is required. 
1. Ensure Sandbox Mode is NOT enabled.
2. Enter the Merchant and Site IDs.
3. Click on "Request OTP" to have the OTP emailed to the Zapper Merchant.
4. Enter the OTP (a.k.a. Verification Code) and click "Submit OTP".
5. Click "Save changes".

Note: The "Status" field will indicate if the configuration is a success or if any errors have occurred.

Warning: If the Manual Token Entry override is used, it is up to the merchant to ensure that the POS Token entered is correct.

== Changelog ==

= 2.1.9 - 04/01/2024 =
- Add transactionId on payment for compatibility with other plugins that require transactionId

= 2.1.8 - 02/11/2023 =
- Added support for WooCommerce High-Performance Order Storage (HPOS) 

= 2.1.7 - 23/10/2023 =
- Updates for Wordpress 6.3.2
- Updates for WooCommerce 8.2.1

= 2.1.6 - 05/06/2023 =
- Updates for Wordpress 6.2.2
- Updates for WooCommerce 7.7.2

= 2.1.5 - 17/11/2022 =
- Updates for Wordpress 6.2

= 2.1.4 - 27/07/2021 =
- Updates for Wordpress 5.8

= 2.1.3 - 10/06/2020 =
- Added a link to Zapper's Integrations documentation in readme under Installation section

= 2.1.2 - 18/05/2020 =
- Fixes: In some cases, customers were redirected to sandbox where they were unable to complete the payment. 

= 2.1.1 - 13/05/2020 =
- Readme update

= 2.1.0 - 13/05/2020 =
- Added Sandbox Mode. 
- Added OTP configuration option. 
- Fixes: Emailed orders (created by the WP admin) have zero amount and unable to pay.

= 2.0.1 =
- Redirects to Zapper's Ecommerce Gateway which then automatically redirects back to the store on successful payment. 
- Improved UI. 
- Added Zapper Icon.

= 1.3.0 =
- Updates for WooCommerce 3.0

= 1.2.0 =
- Customers are now redirected to Zapper to make payments. 
- This will prevent abandoned carts from being created.

= 1.1.0 =
- Fixes: When a user makes a payment and changes the order form, resulting in the QR Code reappearing and order status being lost.

= 1.0.9 =
- Checkout page now automatically proceeds once a payment has been successful.

= 1.0.2 =
- Fixes: Versioning

= 1.0.1 =
- Fixes: UI
- Readme update

= 1.0.0 =
- Initial Release

== Upgrade Notice ==
= 1.0.0 =
N/A

== Screenshots ==
1. Zapper Payments for WooCommerce plugin configuration.
2. Checkout option to select Zapper as a payment method.
3. Desktop Payment Gateway.
4. Mobile Payment Gateway.

== Frequently Asked Questions ==
= Can I use this addon before registering as a Zapper Merchant? =
Yes, we have included a Sandbox mode that will allow you to test the plugin and payments. Make sure to disable Sandbox mode when once you have entered your Merchant details.

= Is Zapper secure? =
Absolutely, all the customer's sensitive information is never sent to the merchant. So even if your website gets compromised, your customer's card details will not be vulnerable.
Zapper is also PCI DSS Compliant and all transactions are authorised via 3D Secure.

= Are there any transaction fees associated with using the Zapper Payments plugin? =
Zapper Payments does not charge transaction fees for using our plugin. However, standard payment processing fees will apply. [View Zapper Pricing](https://www.zapper.com/pricing/)

= Is technical support available if I encounter any issues with the plugin? =
Yes, we provide dedicated technical support to assist you with any issues or questions you may have. 
Our support team is available to ensure a seamless experience for both you and your customers and can be contacted via email: [support@zapper.com](mailto:support@zapper.com) or +27 87 150 1001.

= Does this require an SSL certificate? =
We do recommend obtaining an SSL certificate to allow an additional layer of safety for your online shoppers.