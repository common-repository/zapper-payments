function _zapper_payment_complete(result) {
  if (typeof result !== 'undefined' && typeof result.payment !== 'undefined')
    console.log(jQuery("#zapper_payment_wrapper #-zapper-deeplink"))

  document.querySelector("#zapper_payment_id").value = result.payment.paymentId;
  var submitButton = document.querySelector("input[name=woocommerce_checkout_place_order]");
  if (submitButton != null && result.status == 1) {
    window._zapper_lastPayment = result;
    jQuery(submitButton).click();
  }
}

function _zapper_showSuccess(paymentResult) {
  jQuery("#zapper_payment_wrapper .status").text('Payment Successful!');
  jQuery("#zapper_payment_wrapper #imQrCode").fadeOut();
  jQuery("#zapper_payment_wrapper #-zapper-deeplink").fadeOut();
  jQuery("#zapper_payment_wrapper #zapperPaymentDetails").fadeOut();
  jQuery("#zapper_payment_wrapper #zapperPayNowHeader").css("opacity", 0);

  jQuery("#zapper_payment_wrapper #zapperPaidBillAmount").text(paymentResult.payment.amountPaid);
  jQuery("#zapper_payment_wrapper #zapperId").text(paymentResult.payment.zapperId);
  jQuery("#zapper_payment_wrapper #zapperSuccessImage").delay(400).fadeIn();
  jQuery("#zapper_payment_wrapper #zapperPaymentSuccess").delay(800).fadeIn();
}

function zapperMethod(merchantId, siteId, billAmount, orderId) {
  window._zapper_currentOrder = {
    merchantId: merchantId,
    siteId: siteId,
    billAmount: billAmount,
    orderId: orderId
  }

  if (window._zapper_lastPayment && window._zapper_lastPayment.status == 1) {
    document.querySelector("#zapper_payment_id").value = window._zapper_lastPayment.payment.paymentId
    window.setTimeout(function () { _zapper_showSuccess(window._zapper_lastPayment) }, 2000);
  }

  zapper("#zapper_payment_wrapper", merchantId, siteId, billAmount, orderId, _zapper_payment_complete);

  var checkForDeepLink = function () {
    var deeplink = jQuery("#-zapper-deeplink");

    if (deeplink.length > 0) {
      // var existingUrl = deeplink.attr('href')
      // // var successUrl = "intent://scan/#Intent;scheme=http;package=com.android.chrome;end"
      // var successUrl = "http://www.google.com"

      // existingUrl += "&appName=browser&successCallbackURL=" + encodeURIComponent(successUrl)

      // deeplink.attr('href', existingUrl)
      deeplink.on("click", function () { jQuery('#place_order').click(); })
    } else {
      window.setTimeout(checkForDeepLink, 250);
    }
  }

  checkForDeepLink();
}

(function ($) {
  if (!$) return;
  if (window._zapper_qrcode_loaded) return;

  $(document).on('change', "#payment_method_zapper_payments", function (ev) {
    var zapper_container = $("#zapper_payment_wrapper");
    if (zapper_container.length > 0) {
      if (window.zapperMethod) {
        window.zapperMethod(window._zapper_currentOrder.merchantId, window._zapper_currentOrder.siteId, window._zapper_currentOrder.billAmount, window._zapper_currentOrder.orderId);
      }
    }
  })
})(jQuery)
