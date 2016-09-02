<?php

class PayeezyPayment_SiteConfig extends DataExtension
{
	private static $db = array(
		'PayeezyApiKey' => 'Varchar(255)',
		'PayeezyApiSecret' => 'Varchar(255)',
		'PayeezyMerchantToken' => 'Varchar(255)',
		'PayeezyMode' => "Enum('Certify,Sandbox,Live','Certify')"
	);
	
	public function updateCMSFields(&$fields)
	{
		
		$fields->addFieldToTab('Root.Payments.Methods.Payeezy', HeaderField::create('payeezyHead','Payeezy Config',3) );
		$fields->addFieldToTab('Root.Payments.Methods.Payeezy', TextField::create('PayeezyApiKey','API Key') );
		$fields->addFieldToTab('Root.Payments.Methods.Payeezy', TextField::create('PayeezyApiSecret','API Secret') );
		$fields->addFieldToTab('Root.Payments.Methods.Payeezy', TextField::create('PayeezyMerchantToken','Merchant Token') );
		if (Permission::check('ADMIM'))
		{
			$fields->addFieldToTab('Root.Payments.Methods.Payeezy', DropdownField::create('PayeezyMode','Api Mode',$this->owner->obj('PayeezyMode')->enumValues()) );
		}
	}
}