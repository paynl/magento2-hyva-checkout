<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Model;

class PaymentMethodCheck
{

    /**
     * @param string $methodCode
     * @return bool
     */
    public function isPaynlMethod(string $methodCode): bool
    {
        return strpos($methodCode, 'paynl_payment_') === 0;
    }
}
