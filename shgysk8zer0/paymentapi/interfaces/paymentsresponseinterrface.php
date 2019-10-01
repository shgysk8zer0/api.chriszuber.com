<?php
namespace shgysk8zer0\PaymentAPI\Interfaces;

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

	public function setDetails(BasicCard $details): void;
}
