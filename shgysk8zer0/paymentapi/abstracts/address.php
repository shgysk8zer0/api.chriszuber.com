<?php
namespace shgysk8zer0\PaymentAPI\Abstracts;
use \shgysk8zer0\PHPAPI\Interfaces\{InputData};
use \JSONSerializable;

abstract class Address implements \shgysk8zer0\PaymentAPI\Interfaces\AddressInterface, JSONSerializable
{
	use \shgysk8zer0\PaymentAPI\Traits\Address;

	final public function __construct(?InputData $input = null)
	{
		if (isset($input)) {
			$this->_setData($input);
		}
	}

	final public function jsonSerialize(): array
	{
		return [
			'recipient'    => $this->getRecipient(),
			'addressLine'  => $this->getAddressLine(),
			'city'         => $this->getCity(),
			'country'      => $this->getCountry(),
			'organization' => $this->getOrganization(),
			'region'       => $this->getRegion(),
			'postalCode'   => $this->getPostalCode(),
		];
	}
}
