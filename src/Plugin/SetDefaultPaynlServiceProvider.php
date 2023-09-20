<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Plugin;

use Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceInterface;
use Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceProvider;
use Paynl\HyvaCheckout\Model\Magewire\Payment\PlaceOrderService;
use Paynl\HyvaCheckout\Model\PaymentMethodCheck;

class SetDefaultPaynlServiceProvider
{
    private PaymentMethodCheck $paymentMethodCheck;
    private PlaceOrderService $paynlPlaceOrderService;

    /**
     * @param PaymentMethodCheck $paymentMethodCheck
     * @param PlaceOrderService $paynlPlaceOrderService
     */
    public function __construct(
        PaymentMethodCheck $paymentMethodCheck,
        PlaceOrderService $paynlPlaceOrderService
    ) {
        $this->paymentMethodCheck = $paymentMethodCheck;
        $this->paynlPlaceOrderService = $paynlPlaceOrderService;
    }

    /**
     * @param PlaceOrderServiceProvider $subject
     * @param PlaceOrderServiceInterface $result
     * @param string $code
     * @return PlaceOrderServiceInterface
     */
    public function afterGetByCode(
        PlaceOrderServiceProvider $subject,
        PlaceOrderServiceInterface $result,
        string $code
    ): PlaceOrderServiceInterface {
        if ($this->paymentMethodCheck->isPaynlMethod($code) && get_class($result) === get_class($subject->getDefaultPlaceOrderService())) {
            return $this->paynlPlaceOrderService;
        }

        return $result;
    }
}
