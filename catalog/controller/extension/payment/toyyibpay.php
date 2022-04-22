<?php
/**
 * toyyibPay OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author toyyibPay Team
 */
class ControllerExtensionPaymentToyyibpay extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/toyyibpay');
        $data = array(
            'button_confirm' => $this->language->get('button_confirm'),
            'action' => $this->url->link('extension/payment/toyyibpay/proceed', '', true)
        );

        return $this->load->view('extension/payment/toyyibpay', $data);
    }

    public function proceed()
    {
		
        $api_env = $this->config->get('toyyibpay_api_environment_value');
        $api_key = $this->config->get('toyyibpay_api_key_value');
        $category_code = $this->config->get('toyyibpay_category_code_value');
		$payment_channel = $this->config->get('toyyibpay_payment_channel_value');
		$payment_charge = $this->config->get('toyyibpay_payment_charge_value');
		$company_email = $this->config->get('toyyibpay_company_email_value');
        $company_phone = $this->config->get('toyyibpay_company_phone_value');

		if ($payment_charge == 0) {
			$payment_charge_on = '';
		} elseif ($payment_charge == 1) {
			$payment_charge_on = '0';
		} elseif ($payment_charge == 2) {
			$payment_charge_on = '1';
		} else {
			$payment_charge_on = '2';
		}

		$extra_email = $this->config->get('toyyibpay_extra_email_value');

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

        $products = $this->cart->getProducts();
        foreach ($products as $product) {
            $data['prod_desc'][] = $product['name'] . " x " . $product['quantity'];
        }

		$email = trim($order_info['email']) ?: $company_email;
		$phone = trim($order_info['telephone']) ?: $company_phone;
		
		
		$parameter = array(
			'userSecretKey'		        => trim($api_key),
			'categoryCode'		        => trim($category_code),
			'billName'			        => 'Order ' . $order_info['order_id'],
			'billDescription'	        => 'Payment for Order ' . $order_info['order_id'],
			'billPriceSetting'	        => 1,
			'billPayorInfo'		        => 1, 
			'billAmount'		        => strval($amount * 100), 
			'billReturnUrl'		        => $this->url->link('extension/payment/toyyibpay/return_ipn', '', true),
			'billCallbackUrl'	        => $this->url->link('extension/payment/toyyibpay/callback_ipn', '', true),
			'billExternalReferenceNo'   => $order_info['order_id'],
			'billTo'			        => trim($order_info['firstname'] . ' ' . $order_info['lastname']),
			'billEmail'			        => $email,
			'billPhone'			        => $phone,
			'billSplitPayment'	        => 0,
			'billSplitPaymentArgs'      => '',
            'billPaymentChannel'        => $payment_channel,
            'billContentEmail'          => $extra_email,
            'billChargeToCustomer'      => $payment_charge_on,
            'billASPCode'               => 'aminoc3'
		);
		
        $toyyibpay = new ToyyibPayAPI(trim($api_key),$api_env);
		$createBill = $toyyibpay->createBill($parameter);

		if( $createBill['respStatus']===false ) {
            $toyyibpay->throwException( $createBill['respData'] );
            //exit;
		}
		
		if ( empty($createBill['respData']['BillCode']) ) {
			//BillCode Not Exist
			$toyyibpay->throwException( 'ERROR : ' . $createBill['respData']['msg'] );
            exit;
		}
		
		$orderid = $order_info['order_id'];
		$tranID = $createBill['respData']['BillCode'];
		$order_status_id = $this->config->get('toyyibpay_order_status_id');
		$orderHistNotes = "Time (P): " . date(DATE_ATOM) . " BillCode: " . $tranID . " Status: Pending";
		$this->model_checkout_order->addOrderHistory($orderid, 1, $orderHistNotes, $notify = true, true);
		

        header('Location: ' . $createBill['respData']['BillURL'] );
		
    }

    public function return_ipn()
    {

        $api_env = $this->config->get('toyyibpay_api_environment_value');
		$api_key = $this->config->get('toyyibpay_api_key_value');

        try {
			$toyyibpay = new ToyyibPayAPI(trim($api_key),$api_env);
			$data = $toyyibpay->getTransactionData();
        } catch (Exception $e) {
            header('HTTP/1.1 403 Data matching failed', true, 403);
            exit();
        }

        if ($data['paid']) {
            $goTo = $this->url->link('checkout/success');
        } else {
			$this->session->data['error'] = 'Payment failed, please try again. Please contact admin if this message is wrong.';
            $goTo = $this->url->link('checkout/checkout');
        }

        if (!headers_sent()) {
            header('Location: ' . $goTo);
        } else {
            echo "If you are not redirected, please click <a href=" . '"' . $goTo . '"' . " target='_self'>Here</a><br />"
            . "<script>location.href = '" . $goTo . "'</script>";
        }

        exit();
    }
    /*     * ***************************************************
     * Callback with IPN(Instant Payment Notification)
     * **************************************************** */

    public function callback_ipn()
    {
        $this->load->model('checkout/order');
		
        $api_env = $this->config->get('toyyibpay_api_environment_value');
        $api_key = $this->config->get('toyyibpay_api_key_value');
		$toyyibpay_pending_status_id = '1';  // default setting for pending status
		$toyyibpay_failed_status_id = '10';  // default setting for failed status

		$toyyibpay = new ToyyibPayAPI(trim($api_key),$api_env);
		
        try {
			$data = $toyyibpay->getTransactionData();
        } catch (Exception $e) {
            header('HTTP/1.1 403 Data matching failed', true, 403);
            exit();
        }

        if (!$data['paid']) {
            exit;
        }

		$this->cart->clear();
        $orderid = $data['order_id'];
		$status = $data['paid'];
		$tranID = $data['billcode'];
		$invoice = $data['transaction_id'];
        $amount = $data['amount'];
		
		$parameter = array('code' => $invoice);
		$invoiceSecure = $toyyibpay->toChange($parameter);
		$invoiceSecure = $invoiceSecure['respData']['code'];
		$urlLink = $toyyibpay->requery($invoiceSecure); 
		
		
		if ($status == '1') {
			$orderHistNotes = "Time (C): " . date(DATE_ATOM) . " BillCode: " . $tranID . " Invoice No: " . $invoice . " Status: " . $data['status_name'];
            $order_status_id = $this->config->get('toyyibpay_completed_status_id');
            $goTo = $this->url->link('checkout/success');
			$this->cart->clear();
        } else if($status == '2') {
			$orderHistNotes = "Time (C): " . date(DATE_ATOM) . " BillCode: " . $tranID . " Invoice No: " . $invoice . " Status: <a href='$urlLink' target='_blank'> " . $data['status_name']. '</a>';
			$order_status_id = $toyyibpay_pending_status_id;
			$this->session->data['error'] = 'Payment pending. Please contact admin if this message is wrong.';
            $goTo = $this->url->link('checkout/checkout');

		} else {
			$orderHistNotes = "Time (C): " . date(DATE_ATOM) . " BillCode: " . $tranID . " Invoice No: " . $invoice . " Status: " . $data['status_name'];
            $order_status_id = $toyyibpay_failed_status_id;
            $this->session->data['error'] = 'Payment failed, please try again. Please contact admin if this message is wrong.';
            $goTo = $this->url->link('checkout/checkout');
        }
		
		
        //$orderHistNotes = "Time (C): " . date(DATE_ATOM) . " BillCode: " . $tranID . " Invoice No: " . $invoice . " Status: " . $data['status_name'];
		$order_info = $this->model_checkout_order->getOrder($orderid); // orderid

        //$order_status_id = $this->config->get('toyyibpay_completed_status_id');

        if ($order_info['order_status'] == 'Pending') {
            $this->model_checkout_order->addOrderHistory($orderid, $order_status_id, $orderHistNotes, $notify = true);
        }

        exit;
    }
	
}


class ToyyibPayAPI
{
	private $api_key;
	private $process;
	public $is_production;
	public $url;
	public $header;

	const TIMEOUT = 10; //10 Seconds
	const PRODUCTION_URL = 'https://toyyibpay.com/';
	const STAGING_URL = 'https://dev.toyyibpay.com/';
			
	public function __construct($api_key,$is_production)
	{
		if( $is_production=='' ) $this->is_production = true;
		else $this->is_production = $is_production===true||$is_production===1||$is_production==='1' ? true : false;
		
		$this->api_key = $api_key;
		$this->header = $api_key . ':';
	}
	
	public function setMode()
	{
		if ($this->is_production) {
			$this->url = self::PRODUCTION_URL;
		} else {
			$this->url = self::STAGING_URL;
		}
		return $this;
	}

	public function throwException($message)
	{
		
		echo "<script> alert('" .trim(addslashes($message)). "'); </script>"; 
		echo "<h3>" .addslashes($message). "</h3>"; 
		
	}
	
	public function createBill($parameter)
	{
		/* Email or Mobile must be set */
		if (empty($parameter['billEmail']) && empty($parameter['billPhone'])) {
			$this->throwException("Email or Mobile must be set!");
		}

		if(empty($parameter['categoryCode'])) {
			$this->throwException("Category code must be set!");
		}
		
		//Last Check
		if (empty($parameter['categoryCode'])) {
			$this->throwException("Category Code Not Found! ");
		}
		
		/* Create Bills */
		$this->setActionURL('CREATEBILL');	
		$bill = $this->toArray($this->submitAction($parameter)); 
		$billdata = $this->setPaymentURL($bill); 
		
		return $billdata;
	}
	
	public function setPaymentURL($bill)
	{		
		$return = $bill;
		if( $bill['respStatus'] ) {
			if( isset($bill['respData'][0]['BillCode']) ) {
				$this->setActionURL('PAYMENT', $bill['respData'][0]['BillCode'] ); 
				$bill['respData'][0]['BillURL'] = $this->url;
			}
			$return = array('respStatus'=>$bill['respStatus'], 'respData'=>$bill['respData'][0]);
			
		}
		
		return $return;
	}
	
	public function checkBill($parameter)
	{
		$this->setActionURL('CHECKBILL');
		$checkData = $this->toArray($this->submitAction($parameter)); 
		$checkData['respData'] = $checkData['respData'][0];
		
		return $checkData;	
	}
	
	public function deleteBill($parameter)
	{
		$this->setActionURL('DELETEBILL');
		$checkData = $this->toArray($this->submitAction($parameter)); 
		$checkData['respData'] = $checkData['respData'][0];
		
		return $checkData;
	}

	public function setUrlQuery($url,$data)
	{
		if (!empty($url)) {
			if( count( explode("?",$url) ) > 1 )  
				$url = $url .'&'. http_build_query($data);
			else  
				$url = $url .'?'. http_build_query($data);
		}
		return $url;
	}

	public function getTransactionData()
	{
		
		if (isset($_GET['billcode']) && isset($_GET['transaction_id']) && isset($_GET['order_id']) && isset($_GET['status_id'])) {

			$data = array(
				'status_id' => $_GET['status_id'],
				'billcode' => $_GET['billcode'],
				'order_id' => $_GET['order_id'],
				'msg' => $_GET['msg'],
				'transaction_id' => $_GET['transaction_id']
			);
			$type = 'redirect';
			
		} elseif ( isset($_POST['refno']) && isset($_POST['status']) && isset($_POST['amount']) ) {

			$data = array(
				'status_id' => $_POST['status'],
				'billcode' => $_POST['billcode'],
				'order_id' => $_POST['order_id'],
				'amount' => $_POST['amount'],
				'reason' => $_POST['reason'],
				'transaction_id' => $_POST['refno']
			);
			$type = 'callback';
			
		} else {
			return false;
		}
		
		$checkAction = ($type=='redirect'?'RETURNREDIRECT':($type=='callback'?'RETURNCALLBACK':''));
		
		if( $type === 'redirect' ) {
			//check bill
			$parameter = array(
				'billCode' => $data['billcode'],
				'billExternalReferenceNo' => $data['order_id']
			);
			$checkbill = $this->checkBill($parameter);
			if( $checkbill['respStatus'] ) {
				if($checkbill['respData']['billpaymentStatus'] != $data['status_id']) {
					$data['status_id'] = $checkbill['respData']['billpaymentStatus'];
				}
				$data['amount'] = $checkbill['respData']['billpaymentAmount'];
			}
			else {

			}
		}
		
		//$data['status_id'] = 2;
		
		//$data['paid'] = $data['status_id'] === '1' ? true : false; /* Convert paid status to boolean */
		$data['paid'] = $data['status_id'];
		
		if( $data['status_id']=='1' ) $data['status_name'] = 'Success';
		else if( $data['status_id']=='2' ) $data['status_name'] = 'Pending';
		else $data['status_name'] = 'Unsuccessful';
		
		$data['vcode'] = $_GET['vc'];
		$amount = preg_replace("/[^0-9.]/", "", $data['amount']) * 100;
		$vcode = md5( $this->api_key . $data['order_id'] . $amount );
		
		if($data['vcode'] !== $vcode) {
			$this->throwException('Verification Code Mismatch!');
		}
		
		$data['type'] = $type;
		return $data;

	}

	public function setActionURL($action, $id = '')
	{
		$this->setMode();
		$this->action = $action;
		
		if ($this->action == 'PAYMENT') {
			$this->url .= $id;
		}

		else if ($this->action == 'CREATEBILL') {
			$this->url .= 'index.php/api/createBill';
		}
		else if ($this->action == 'CHECKBILL') {
			$this->url .= 'index.php/api/getBillTransactions';
		}
		else if ($this->action == 'DELETEBILL') {
			$this->url .= 'index.php/api/getBillTransactions';
		}
		else if ($this->action == 'CHANGECODE') {
				$this->url .= 'index.php/api/changeCode';
		}
		else if ($this->action == 'REQUERY') {
			$this->url .= 'index.php/api/callStatus?code=';
		} 
		else 
		{
			$this->throwException('URL Action not exist');
		}
		
		return $this;
	}
	
	public function submitAction($data='')
	{		
		$this->process = curl_init();
		curl_setopt($this->process, CURLOPT_HEADER, 0);
		curl_setopt($this->process, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->process, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->process, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->process, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($this->process, CURLOPT_USERPWD, $this->header);
		
		curl_setopt($this->process, CURLOPT_URL, $this->url);
		curl_setopt($this->process, CURLOPT_POSTFIELDS, http_build_query($data));
		if ($this->action == 'DELETE') {
			curl_setopt($this->process, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		$response = curl_exec($this->process);
		$httpStatusCode  = curl_getinfo($this->process, CURLINFO_HTTP_CODE);
		curl_close($this->process);
		
		if( $httpStatusCode==200 ) {
			$respStatus = true;
			if( trim($response)=='[FALSE]' ) {
				$respStatus = false;
				$response = 'API_ERROR '. trim($response) .' : Please check your request data with Toyyibpay Admin';
			}
			else if( trim($response)=='[KEY-DID-NOT-EXIST]' ) {
				$respStatus = false;
				$response = 'API_ERROR '. trim($response) .' : Please check your api key.';
			}
			
			if( trim($response)=='' ) {
				$respStatus = false;
				$response = 'API_ERROR : No Response Data From Toyyibpay.';
			}
		}
		else {
			$respStatus = false;
			$response = 'API_ERROR '. $httpStatusCode .' : Cannot Connect To ToyyibPay Server.';
		}			
		
		//$return = json_decode($response, true);
		$return = array('respStatus'=>$respStatus, 'respData'=>$response);
		
		
		return $return;
	}
	
	public function toArray($json)
	{
		if( is_string($json['respData']) && is_array(json_decode($json['respData'],true)) ) { //check json ke x
			return array('respStatus'=>$json['respStatus'], 'respData'=>json_decode($json['respData'],true));
		} else {
			return array('respStatus'=>$json['respStatus'], 'respData'=>$json['respData']);
		}
	}
	
	public function toChange($parameter)
	{
		$this->setActionURL('CHANGECODE');
		$changeData = $this->toArray($this->submitAction($parameter));
		$changeData['respData'] = $changeData['respData'][0];

		return $changeData;
		
	}
	
	public function requery($invoiceNo)
	{
		$this->setMode();
		$url = $this->url.'index.php/api/callStatus?code='.$invoiceNo;
		
		return $url;
		
	}


}//close class ToyyibPayAPI