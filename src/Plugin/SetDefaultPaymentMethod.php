<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Plugin;

use Hyva\Checkout\Magewire\Checkout\Payment\MethodList;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Paynl\Payment\Helper\PayHelper;
use Paynl\Payment\Model\Config;

class SetDefaultPaymentMethod
{
    private Config $config;
    private SessionCheckout $sesionCheckout;
    private CartRepositoryInterface $cartRepository;
    private PayHelper $payHelper;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config,
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $cartRepository,
        PayHelper $payHelper
    ) {
        $this->config = $config;
        $this->sessionCheckout = $sessionCheckout;
        $this->cartRepository = $cartRepository;
        $this->payHelper = $payHelper;
    }

    /**
     * @param MethodList $subject
     * @return void
     */
    public function afterBoot(MethodList $subject): void
    {
        if (!$subject->method && ($defaultOption = $this->config->getDefaultPaymentOption())) {
            try {
                $quote = $this->sessionCheckout->getQuote();
                $method = $quote->getPayment()->setMethod($defaultOption);
                $this->cartRepository->save($quote);
                $subject->method = $defaultOption;
            } catch (LocalizedException $e) {
                $this->payHelper->logNotice('PAY. Hyva Checkout: Failed to set default payment method on quote ' . $quote->getId() .'. Reason: ' . $e->getMessage());
            }
        }
    }
}
