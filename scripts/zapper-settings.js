
function set_status(message, type = undefined) {
    const [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText] = getControls()

    if (type === 'error') {
        $statusText.css('color', 'red');
    } else if (type == "success") {
        $statusText.css('color', 'green');
    }
    else {
        $statusText.css('color', 'black');
    }
    $statusText.empty()
    $statusText.append(message)
}

function request_otp_ajax() {
    const [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText] = getControls()
    $reqOtpButton.attr('disabled', true)
    set_status('Requesting OTP. Please wait...')
    $otpText.val('')
    var merchantId = jQuery("#woocommerce_zapper_payments_merchant_id").val()
    var siteId = jQuery("#woocommerce_zapper_payments_site_id").val()
    jQuery.ajax({
        type: "GET",
        url: `${scriptVars.posApiUrl}/api/v2/merchants/${merchantId}/sites/${siteId}/onetimepins`,
        dataType: "json",
        success: function (response) {
            $reqOtpButton.removeAttr('disabled')
            if (response && response.statusId == 1) {
                set_status('Success. The OTP has been emailed to the Zapper Merchant. Submit the OTP to complete the setup.')
                $submitOtpButton.removeAttr('disabled')
                $otpText.removeAttr('disabled')

            } else {
                set_status('OTP request failed. Please ensure that the Merchant details are correct.', 'error')
            }
        },
        error: function (response) {
            $reqOtpButton.removeAttr('disabled')
            set_status('OTP request failed. Please ensure that the Merchant details are correct.', 'error')

        }

    });
}

function submit_otp_ajax() {
    const [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText] = getControls()
    $submitOtpButton.attr('disabled')
    set_status('Submitting OTP. Please wait...')
    var merchantId = jQuery("#woocommerce_zapper_payments_merchant_id").val()
    var siteId = jQuery("#woocommerce_zapper_payments_site_id").val()
    jQuery.ajax({
        type: "GET",
        url: `${scriptVars.posApiUrl}/api/v2/merchants/${merchantId}/sites/${siteId}/?OneTimePin=${$otpText.val()}`,
        dataType: "json",
        success: function (response) {
            $otpText.val('')
            if (response && response.statusId == 1 && response.data) {
                try {
                    var data = JSON.parse(atob(response.data[0]))
                    $secret.val(data.Secret.toUpperCase())
                    $submitOtpButton.attr('disabled', true)
                    $otpText.attr('disabled', true)
                    set_status('Save changes to complete configuration.')
                } catch (err) {
                    set_status(`Failed to parse response from Zapper API. ${err.message}`, 'error')
                }

            } else {
                set_status('OTP verification failed. Please ensure that the OTP is correct, then retry.')
            }
        },
        error: function (response) {
            $submitOtpButton.removeAttr('disabled')
            $otpText.val('')
            set_status('OTP verification failed. Please ensure that the OTP is correct and retry.')
        }

    });
}

jQuery(document).ready(function () {
    const [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText] = getControls()
    $submitOtpButton.removeAttr('disabled');

    updateControls()
    jQuery(`input[id="woocommerce_zapper_payments_manual_override"]`).click(function () {
        if (jQuery(this).prop("checked") == false) {
            $secret.val("")
        }
        updateControls()
    });
    $secret.keyup(function () {
        jQuery(this).val(jQuery(this).val().replace(/ +?/g, ''));
        updateControls()
    });

    jQuery(`input[id="woocommerce_zapper_payments_sandbox"]`).click(updateControls);
    jQuery(`input[id="woocommerce_zapper_payments_enabled"]`).click(updateControls);
    $merchantId.keyup(updateControls);
    $siteId.keyup(updateControls);
})

function updateControls() {
    const [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText] = getControls()
    var isPosTokenSet = false
    var isMerchantDetailsSet = false
    var zapperEnabled = isCheckedById("woocommerce_zapper_payments_enabled")
    var overrideEnabled = isCheckedById("woocommerce_zapper_payments_manual_override")
    var sandboxEnabled = isCheckedById("woocommerce_zapper_payments_sandbox")
    var mid = $merchantId.val()
    var sid = $siteId.val()
    var secret = $secret.val()


    if (secret !== undefined && secret !== null && secret !== '') {
        isPosTokenSet = true
    }

    if (mid !== undefined && mid !== null && mid !== 0 && mid !== ''
        && sid !== undefined && sid !== null && sid !== 0 && sid !== '') {
        isMerchantDetailsSet = true
    }

    handleOverrideChange(overrideEnabled)

    if (!zapperEnabled) {
        set_status('Zapper is disabled.')
        return
    }

    if (sandboxEnabled) {
        set_status('Using sandbox test Merchant.')
        return
    }

    if (secret.includes(' ')) {
        set_status('Invalid Token. Whitespaces are not allowed.', 'error')
        return
    }

    if (overrideEnabled && isPosTokenSet && isMerchantDetailsSet) {
        set_status('Configured using Manual Token Entry override.')
        return
    }

    if (!overrideEnabled && isPosTokenSet && isMerchantDetailsSet) {
        set_status('Configuration complete.', 'success')
        return
    }

    if (!isPosTokenSet || !isMerchantDetailsSet) {
        set_status('Requires configuration. Please ensure that the Merchant ID, Site ID and POS Token is set.')
        return
    }
}

function handleOverrideChange(overrideEnabled) {
    const [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText] = getControls()
    if (overrideEnabled) {
        $reqOtpButton.attr('disabled', true);
        $submitOtpButton.attr('disabled', true);
        $otpText.attr('disabled', true);
        $secret.removeAttr('readonly');
    } else {
        $secret.attr('readonly', true)
        $reqOtpButton.removeAttr('disabled')
    }
    $submitOtpButton.attr('disabled', true)
    $otpText.attr('disabled', true)
}

function isCheckedById(id) {
    return (jQuery(`#${id}`).is(":checked"));
}

function getControls() {
    var $merchantId = jQuery("#woocommerce_zapper_payments_merchant_id")
    var $siteId = jQuery("#woocommerce_zapper_payments_site_id")
    var $secret = jQuery('#woocommerce_zapper_payments_pos_secret')
    var $reqOtpButton = jQuery('#woocommerce_zapper_payments_request_otp_button')
    var $submitOtpButton = jQuery('#woocommerce_zapper_payments_submit_otp_button')
    var $otpText = jQuery('#woocommerce_zapper_payments_otp')
    var $statusText = jQuery('#woocommerce_zapper_payments_otp_status')

    return [$merchantId, $siteId, $secret, $reqOtpButton, $submitOtpButton, $otpText, $statusText]
}