<?php
namespace shgysk8zer0\PaymentAPI;
use \shgysk8zer0\PaymentAPI\Interfaces\{PaymentResponseInterface};
use \JSONSerializable;

final class PaymentResponse implements JSONSerializable, PaymentResponseInterface
{
	use PaymentResponseData;

	final public function __construct(?InputData $input = null) {
		if (isset($input)) {
			$this->_setInputData($input);
		}
	}

	final public function jsonSerialize()
	{
		return [
			'requestId'       => $this->getRequestId(),
			'methodName'      => $this->getMethodName(),
			'details'         => $this->getDetails(),
			'shippingAddress' => $this->getShippingAddress(),
			'shippingOption'  => $this->getShippingOption(),
			'payerName'       => $this->getPayerName(),
			'payerEmail'      => $this->getPayerEmail(),
			'payerPhone'      => $this->getPayerPhone(),
		];
	}
}
