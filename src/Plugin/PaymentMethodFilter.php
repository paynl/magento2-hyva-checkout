<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Plugin;

use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\MethodInterface;
use Paynl\Payment\Model\Paymentmethod\PaymentMethod;

class PaymentMethodFilter
{
    private CheckoutSession $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param MethodList $subject
     * @param array|null $result
     * @return array
     */
    public function afterGetList(MethodList $subject, ?array $result): ?array
    {
        if ($result === null) {
            return null;
        }

        return array_filter($result, [$this, 'validateMethod']);
    }

    /**
     * @param MethodInterface $method
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateMethod(MethodInterface $method): bool
    {
        if (!$method instanceof PaymentMethod) {
            return true;
        }

        $quote = $this->checkoutSession->getQuote();
        $disAllowedShippingMethods = $method->getDisallowedShippingMethods();

        if ($disAllowedShippingMethods) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if (in_array($shippingMethod, explode(',', $disAllowedShippingMethods ?? ''))) {
                return false;
            }
        }

        $companyName = $quote->getBillingAddress()->getCompany();

        //private only
        if ($method->getCompany() === "1" && $companyName) {
            return false;
        }

        //companies only
        if ($method->getCompany() === "2" && !$companyName) {
            return false;
        }

        if (!$method->isCurrentIpValid()) {
            return false;
        }

        if (!$method->isCurrentAgentValid()) {
            return false;
        }

        // as of v3.16.0 of main pay extension, checkoutActive is only implemented for paylink
        if (!$method->getCheckoutActive() && $method->getCode() === 'paynl_payment_paylink') {
            return false;
        }

        return true;
    }
}
