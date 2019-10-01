<?php
namespace Payment;
use \shgysk8zer0\PHPAPI\{API, Headers, HTTPException, UUID};
use \shgysk8zer0\PHPAPI\Interfaces\{InputData};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \JSONSerializable;
use \Throwable;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

final class BillingAddress extends \shgysk8zer0\PaymentAPI\Abstracts\Address implements \shgysk8zer0\PaymentAPI\Interfaces\AddressInterface, JSONSerializable {}

final class ShippingAddress extends \shgysk8zer0\PaymentAPI\Abstracts\Address implements \shgysk8zer0\PaymentAPI\Interfaces\AddressInterface, JSONSerializable {}

interface BasicCardInterface
{
	public function getCardNumber(): string;

	public function setCardNumber(string $num): void;

	public function getCardSecurityCode(): string;

	public function setCardSecurityCode(string $code): void;

	public function getCardholderName(): string;

	public function setCardholderName(string $name): void;

	public function getExpiryMonth(): int;

	public function setExpiryMonth(int $month): void;

	public function getExpiryYear(): int;

	public function setExpiryYear(int $year): void;

	public function getBillingAddress():? AddressInterface;

	public function setBillingAddress(?AddressInterface $addr): void;
}

trait BasicCardData
{
	private $_cardNumber       = '';

	private $_cardSecurityCode = '';

	private $_cardholderName   = '';

	private $_expiryMonth      = 0;

	private $_expiryYear       = 0;

	private $_billingAddress   = null;

	final public function getCardNumber(): string
	{
		return $this->_cardNumber;
	}

	final public function setCardNumber(string $num): void
	{
		$this->_cardNumber = $num;
	}

	final public function getCardSecurityCode(): string
	{
		return $this->_cardSecurityCode;
	}

	final public function setCardSecurityCode(string $code): void
	{
		$this->_cardSecurityCode = $code;
	}

	final public function getCardholderName(): string
	{
		return $this->_cardholderName;
	}

	final public function setCardholderName(string $name): void
	{
		$this->_cardholderName = $name;
	}

	final public function getExpiryMonth(): int
	{
		return $this->_expiryMonth;
	}

	final public function setExpiryMonth(int $month): void
	{
		$this->_expiryMonth = $month;
	}

	final public function getExpiryYear(): int
	{
		return $this->_expiryYear;
	}

	final public function setExpiryYear(int $year): void
	{
		$this->_expiryYear = $year;
	}

	final public function getBillingAddress():? BillingAddress
	{
		return $this->_billingAddress;
	}

	final public function setBillingAddress(?BillingAddressInterface $addr): void
	{
		$this->_billingAddress = $addr;
	}

	final protected function _setInputData(InputData $input): bool
	{
		if ($input->has('cardNumber', 'cardSecurityCode', 'cardholderName', 'expiryMonth', 'expiryYear', 'billingAddress')) {
			$this->setCardNumber($input->get('cardNumber'));
			$this->setCardSecurityCode($input->get('cardSecurityCode'));
			$this->setCardholderName($input->get('cardholderName'));
			$this->setExpiryMonth($input->get('expiryMonth'));
			$this->setExpiryYear($input->get('expiryYear'));
			$this->setBillingAddress(new BillingAddress($input->get('billingAddress')));
			return true;
		} else {
			return false;
		}
	}
}

final class BasicCard implements JSONSerializable, BasicCardInterface
{
	use BasicCardData;

	final public function __construct(?InputData $input = null)
	{
		if (isset($input) and $input->has('cardNumber', 'cardSecurityCode', 'cardholderName', 'expiryMonth', 'expiryYear', 'billingAddress')) {
			$this->_setInputData($input);
		}
	}

	final public function jsonSerialize(): array
	{
		return [
			'cardNumber'       => $this->getCardNumber(),
			'cardSecurityCode' => $this->getCardSecurityCode(),
			'cardholderName'   => $this->getCardholderName(),
			'expiryMonth'      => $this->getExpiryMonth(),
			'expiryYear'       => $this->getExpiryYear(),
			'billingAddress'   => $this->getBillingAddress(),
		];
	}
}

interface PaymentResponseInterface
{
	public function getRequestId():? string;

	public function setRequestId(?string $id): void;

	public function getMethodName():? string;

	public function setMethodName(?string $name): void;

	public function getShippingAddress():? ShippingAddress;

	public function setShippingAddress(?ShippingAddress $addr): void;

	public function getShippingOption():? string;

	public function setShippingOption(?string $opt): void;

	public function getPayerName():? string;

	public function setPayerName(?string $name): void;

	public function getPayerEmail():? string;

	public function setPayerEmail(?string $email): void;

	public function getPayerPhone():? string;

	public function setPayerPhone(?string $phone): void;

	public function getDetails():? BasicCardInterface;

	public function setDetails(?BasicCardInterface $details): void;
}

trait PaymentResponseData
{
	private $_requestId       = null;

	private $_methodName      = null;

	private $_shippingAddress = null;

	private $_shippingOption  = null;

	private $_payerName       = null;

	private $_payerEmail      = null;

	private $_payerPhone      = null;

	private $_details         = null;

	final public function getRequestId(): ?string
	{
		return $this->_requestId;
	}

	final public function setRequestId(?string $id): void
	{
		$this->_requestId = $id;
	}

	final public function getMethodName():? string
	{
		return $this->_methodName;
	}

	final public function setMethodName(?string $name): void
	{
		$this->_methodName = $name;
	}

	final public function getShippingAddress():? ShippingAddress
	{
		return $this->_shippingAddress;
	}

	final public function setShippingAddress(?ShippingAddress $addr): void
	{
		$this->_shippingAddress = $addr;
	}

	final public function getShippingOption():? string
	{
		return $this->_shippingOption;
	}

	final public function setShippingOption(?string $opt): void
	{
		$this->_shippingOption = $opt;
	}

	final public function getPayerName():? string
	{
		return $this->_payerName;
	}

	final public function setPayerName(?string $name): void
	{
		$this->_payerName = $name;
	}

	final public function getPayerEmail():? string
	{
		return $this->_payerEmail;
	}

	final public function setPayerEmail(?string $email): void
	{
		$this->_payerEmail = $email;
	}

	final public function getPayerPhone():? string
	{
		return $this->_payerPhone;
	}

	final public function setPayerPhone(?string $phone): void
	{
		$this->_payerPhone = $phone;
	}

	final public function getDetails():?BasicCardInterface
	{
		return $this->_details;
	}

	final public function setDetails(BasicCard $details): void
	{
		$this->_details = $details;
	}

	final protected function _setInputData(InputData $input): bool
	{
		if ($input->has('details', 'methodName')) {
			$this->setDetails(new BasicCard($input->get('details')));
			$this->setMethodName($input->get('methodName'));
			$this->setRequestId($input->get('requestId'));
			$this->setShippingOption($input->get('shippingOption'));
			$this->setPayerName($input->get('payerName'));
			$this->setPayerEmail($input->get('payerEmail'));
			$this->setPayerPhone($input->get('PayerPhone'));
			$shippingAddr = $input->get('shippingAddress');

			if ($shippingAddr instanceof InputData) {
				$this->setShippingAddress(new ShippingAddress($shippingAddr));
			}

			unset($shippingAddr);

			return true;
		} else {
			return false;
		}
	}
}

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

try {
	$api = new API();

	$api->on('POST', function(API $request): void
	{
		Headers::contentType('application/json');
		echo json_encode(new PaymentResponse($request->post->get('paymentResponse')));
	});

	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::contentType('application/json');
	echo json_encode($e);
} catch(Throwable $e) {
	Headers::status(HTTP::INTERNAL_SERVER_ERROR);
	Headers::contentType('application/json');
	echo json_encode(['error' => [
		'message' => $e->getMessage(),
		'code'    => $e->getCode(),
		'file'    => $e->getFile(),
		'line'    => $e->getLine(),
		'trace'   => $e->getTrace(),
	]]);
}
