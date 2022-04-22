<?php

/**
 * toyyibPay OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author toyyibPay Team
 */
 
// Versioning
$_['toyyibpay_ptype'] = "OpenCart";
$_['toyyibpay_pversion'] = "2.3";

// Heading
$_['heading_title'] = 'toyyibPay Payment Gateway';

// Text
$_['text_payment'] = 'Payment';
$_['text_success'] = 'Success: You have modified toyyibPay Payment Gateway account details!';
$_['text_edit'] = 'Edit toyyibPay';
$_['text_toyyibpay'] = '<a onclick="window.open(\'https://toyyibpay.com/\');" style="text-decoration:none;"><img src="view/image/payment/toyyibpay.png" alt="toyyibPay" title="toyyibPay. Fair Payment Software" style="border: 0px solid #EEEEEE;" height=25 width=94/></a>';

// Entry
$_['toyyibpay_api_key'] = 'User Secret Key';
$_['toyyibpay_category_code'] = 'Category Code';
$_['toyyibpay_api_environment'] = 'API Environment';
$_['toyyibpay_payment_channel'] = 'Payment Channel';
$_['toyyibpay_payment_charge'] = 'Payment Charge';
$_['toyyibpay_company_email'] = 'Company Email';
$_['toyyibpay_company_phone'] = 'Company Phone';
$_['toyyibpay_extra_email'] = 'Extra e-mail to customers';
$_['entry_minlimit'] = 'Minimum Limit';
$_['entry_completed_status'] = 'Completed Status';
$_['entry_geo_zone'] = 'Geo Zone';
$_['entry_status'] = 'Status';
$_['entry_sort_order'] = 'Sort Order';

// Help
$_['help_api_key'] = 'Get your User Secret Key from your toyyibPay account';
$_['help_category_code'] = 'Get your Category Codefrom your toyyibPay account';
$_['help_minlimit'] = 'Set total minimum limit to enable toyyibPay conditionally';
$_['help_api_environment'] = 'Select the payment Environment (Production or Sandbox)';
$_['help_payment_channel'] = 'Online Banking (FPX) or Debit / Credit Card';
$_['help_payment_charge'] = 'Choose how to implement transaction charge';
$_['help_company_email'] = 'Setting your default email';
$_['help_company_phone'] = 'Setting your default phone';
$_['help_extra_email'] = 'If you have any extra e-mail to send to your customers';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify toyyibPay Extensions!';
$_['error_api_key'] = '<b>toyyibPay API Key</b> Required!';
$_['error_api_environment'] = '<b>toyyibPay API Environment</b> Required!';
$_['error_payment_channel'] = '<b>toyyibPay Payment Channel</b> Required!';
