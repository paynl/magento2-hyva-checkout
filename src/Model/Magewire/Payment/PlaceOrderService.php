<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Model\Quote;

class PlaceOrderService extends AbstractPlaceOrderService
{
    /**
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        return 'paynl/checkout/redirect?nocache=' . time();
    }
}
