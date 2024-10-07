<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Magewire\Checkout\Payment\Method;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Paynl\Payment\Model\Paymentmethod\Instore;
use Rakit\Validation\Validator;

class Pin extends Ideal
{
    public const METHOD_CODE = 'paynl_payment_instore';
    protected const DEFAULT_OPTION_MESSAGE = 'Choose the pin terminal';

    protected const ERROR_MESSAGE = 'PIN terminal is required';

    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $quoteRepository;
    private PaymentHelper $paymentHelper;

    public function __construct(
        Validator $validator,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        parent::__construct($validator, $checkoutSession, $quoteRepository, $paymentHelper, $escaper);

        $this->checkoutSession = $checkoutSession;
        $this->paymentHelper = $paymentHelper;
        $this->quoteRepository = $quoteRepository;
    }

    public function mount(): void
    {
        /**
         * @var Instore $method
         */
        $method = $this->paymentHelper->getMethodInstance(static::METHOD_CODE);

        $this->displayMode = (int) $method->showPaymentOptions();
        if ($this->displayMode !== static::DISPLAY_MODE_NONE) {
            return;
        }

        $quote = $this->checkoutSession->getQuote();
        $quote->getPayment()->setAdditionalInformation(
            'payment_option',
            $method->getDefaultPaymentOption()
        );
        $this->quoteRepository->save($quote);

        parent::mount();
    }
}
