<?php


/* 
 * @link https://developer.payeezy.com/payeezy-api/apis/post/transactions-3
*/
class PayeezyCreditCardPayment extends Payment
{
	private static $db = array(
		'RawResponse' => 'Text',
		'TransactionID' => 'Varchar(20)',
		'CardholderName' => 'Varchar(255)',
		'BillingAddress' => 'Varchar(255)',
		'BillingCity' => 'Varchar(50)',
		'BillingState' => 'Varchar(10)',
		'BillingZip' => 'Varchar(20)',
		'BillingCountry' => 'Varchar(20)'
	);
	
	private static $PaymentMethod = 'Credit Card Payment';
	
	private static $default_billing_country = 'US';
	private static $default_currency_code = 'USD';
	
	private static $live_url = 'https://api.payeezy.com/v1/transactions';
	private static $cert_url = 'https://api-cert.payeezy.com/v1/transactions';
	private static $dev_url = 'https://api-qa.payeezy.com/v1/transactions';
	
	public function PaymentFields($defaults=array())
	{
		$fields = parent::PaymentFields($defaults);
		$fields[] = TextField::create('BillingAddress','Address');
		$fields[] = TextField::create('BillingCity','City');
		$fields[] = DropdownField::create('BillingState','State',FormUtilities::GetStates())->setEmptyString('-- Select --');
		$fields[] = TextField::create('BillingZip','Zip');
		$fields[] = DropdownField::create('BillingCountry','Country',FormUtilities::GetCountries())->setEmptyString('-- Select --')->setValue($this->config()->default_billing_country);
		$fields[] = DropdownField::create('CardType','Card Type',array(
			'visa' => 'Visa',
			'mastercard' => 'Mastercard',
			'discover' => 'Discover',
			'american express' => 'American Express'
		))->setEmptyString('-- Select --');
		$fields[] = TextField::create('CardNumber','Card Number');
		$fields[] = TextField::create('CardholderName','Name on Card');
		$stack1 = FieldGroup::create('Expiration')->addExtraClass('stacked col2');
		$expMonths = array();
		for($m=1;$m<=12;$m++) { $expMonths[sprintf("%02s",$m)] = date('F',strtotime('2000-'.sprintf("%02s",$m).'-01')); }
		$expYears = array();
		for($y=date('Y');$y<=(date('Y')+10);$y++) { $expYears[$y] = $y; }
		$stack1->push( DropdownField::create('CardExpMonth','',$expMonths)->setEmptyString('-- Select --')->setRightTitle('Month') );
		$stack1->push( DropdownField::create('CardExpYear','',$expYears)->setEmptyString('-- Select --')->setRightTitle('Year') );
		$fields[] = $stack1;
		$fields[] = TextField::create('CardCVV','CVV Code');
		return $fields;
	}
	
	public function PaymentRequiredFields()
	{
		return array_merge(
			parent::PaymentRequiredFields(),
			array(
				'BillingAddress',
				'BillingCity',
				'BillingState',
				'BillingZip',
				'BillingCountry',
				'CardType',
				'CardNumber',
				'CardholderName',
				'CardExpMonth',
				'CardExpYear',
				'CardCVV'
			)
		);
	}
	
	public function ValidateSubmission($data = array())
	{
		$invalid = parent::ValidateSubmission($data);
		// make sure credit card number is minimum length
		if ( (strlen(preg_replace('/[^0-9]/','',$data['CardNumber'])) < 15) || (strlen(preg_replace('/[^0-9]/','',$data['CardNumber'])) > 16) ) { $invalid['CardNumber'] = 'Invalid Card Number Length'; }
		
		// make sure cvv code is correct length
		if ( (strlen($data['CardCVV']) > 4) || (strlen($data['CardCVV']) < 3) ) { $invalid['CardNumber'] = 'Invalid CVV code length'; }
		
		// make sure exp is valid
		if ( ($data['CardExpYear'] == date('Y')) && ($data['CardExpMonth'] < date('m')) ) { $invalid['CardExpMonth'] = 'Invalid Expiration Date'; }
		return $invalid;
	}
			
	/**
	* @param $data array - Submission data
	 * data:
	 *	merchant_ref - string (default: Sale)
	 *  amount - int (default: object field Amount)
	 * 	currency_code - string (default: USD)
	 *	credit_card - array(
	 *		type - string (visa, mastercard, discover, american express)
	 *		cardholder_name - string
	 *		card_number - int (Card number with no spaces or dashes)
	 *		exp_date - int (MMDD format ex. 1216)
	 *		cvv - int (3 or 4 digit cvv code)
	 *	)
	 *	billing_address - array(
	 *		street - string
	 *		city - string
	 *		state_province - string
	 *		zip_postal_code - string
	 *		country - string
	 *	)
	 */
	public function Process($data = array())
	{
		// save some information to the dataobject
		$this->CardholderName = $data['CardholderName'];
		$this->BillingAddress = $data['BillingAddress'];
		$this->BillingCity = $data['BillingCity'];
		$this->BillingState = $data['BillingState'];
		$this->BillingZip = $data['BillingZip'];
		$this->BillingCountry = $data['BillingCountry'];
		
		$payload = array(
			"merchant_ref" => (isset($data['OrderNumber'])) ? $data['OrderNumber'] : "Sale",
			"currency_code" => (isset($data['CurrencyCode'])) ? $data['CurrencyCode'] : $this->config()->default_currency_code,
			"partial_redemption" => "false",
			"transaction_type" => "purchase",
			"method" => "credit_card",
			"credit_card" => array(
				"type" => $data['CardType'],
				"cardholder_name" => $this->CardholderName,
				"card_number" => preg_replace('/[^0-9]/','',$data['CardNumber']),
				"exp_date" => sprintf("%02s",$data['CardExpMonth']).substr($data['CardExpYear'],2,2),
				"cvv" => preg_replace('/[^0-9]/','',$data['CardCVV'])
			),
			"billing_address" => array(
				"street" => $this->BillingAddress,
				"city" => $this->BillingCity,
				"state_province" => $this->BillingState,
				"zip_postal_code" => $this->BillingZip,
				"country" => $this->BillingCountry
			)
		);
						
		// We don't really want to use the amount from the submission, so check to see of the amount has already been set
		$Amount = ($this->Amount) ? $this->Amount : $data['Amount'];
		// convert amount to the correct format
		$payload['amount'] = round((float)$Amount * 100);

		$client = new Payeezy_Client();
		$SiteConfig = SiteConfig::current_site_config();
		$client->setApiKey($SiteConfig->PayeezyApiKey);
		$client->setApiSecret($SiteConfig->PayeezyApiSecret);
		$client->setMerchantToken($SiteConfig->PayeezyMerchantToken);
		switch($SiteConfig->PayeezyMode)
		{
			default:
			case 'Certify':
			{
				$client->setUrl($this->config()->cert_url);
				break;
			}
			case 'Sandbox':
			{
				$client->setUrl($this->config()->dev_url);
				break;
			}
			case 'Live':
			{
				$client->setUrl($this->config()->live_url);
				break;
			}
		}

		$purchase_card_transaction = new Payeezy_CreditCard($client);
		
		$purchase_response = $purchase_card_transaction->purchase($payload);

		// process the response
		$this->RawResponse = json_encode($purchase_response);
		// failed by default
		$this->Status = 'Failed';
		if (!is_object($purchase_response))
		{
			Debug::log("Failure: \n".$purchase_response);
			$this->Status = 'Failed';
			$this->Message = 'No response from payment gateway';
		}
		elseif ( (isset($purchase_response->Error)) || (isset($purchase_response->error)) )
		{
			$error = (isset($purchase_response->Error)) ? $purchase_response->Error : $purchase_response->error;
			$this->Status = 'Failed';
			Debug::log("Error: \n".print_r($purchase_response,1));
			if (is_string($error))
			{
				$this->Message = $error;
			}
			elseif (isset($error->messages))
			{
				$this->Message = '(';
				foreach($error->messages as $message)
				{
					$this->Message .= ' '.$message->description.'.';
				}
				$this->Message .= ')';
			}
		}
		elseif (isset($purchase_response->bank_resp_code))
		{
			if (substr($purchase_response->bank_resp_code,0,1) == "1")
			{
				$this->Status = 'Success';
			}
			else
			{
				$this->Status = 'Declined';
				if (isset($purchase_response->bank_message))
				{
					$this->Message = $purchase_response->bank_message;
				}
			}
			$this->TransactionID = $purchase_response->transaction_id;
		}
		else
		{
			Debug::log("Failure: \n".print_r($purchase_response,1));
		}

		$this->write();
		return parent::Process($args);
	}
	
}








