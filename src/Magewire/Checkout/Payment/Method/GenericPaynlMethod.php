<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Magewire\Checkout\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magewirephp\Magewire\Component\Form;
use Paynl\Payment\Model\Paymentmethod\PaymentMethod;
use Rakit\Validation\Validator;

class GenericPaynlMethod extends Form implements EvaluationInterface
{
    public ?string $instructions = null;
    public ?string $kvknummer = null;
    public ?string $vatnummer = null;
    public ?string $dateofbirth = null;
    public ?bool $billinkAgree = null;

    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $quoteRepository;
    private PaymentHelper $paymentHelper;
    private Escaper $escaper;

    protected ?CartInterface $quote = null;
    protected ?String $methodCode;
    protected ?PaymentMethod $paymentMethodInstance = null;

    /**
     * @param Validator $validator
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        Validator $validator,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        parent::__construct($validator);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->paymentHelper = $paymentHelper;
        $this->escaper = $escaper;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $quote = $this->checkoutSession->getQuote();

        if ($dob = $quote->getPayment()->getAdditionalInformation('dob')) {
            $this->dateofbirth = $dob;
        }

        if ($kvk = $quote->getPayment()->getAdditionalInformation('kvknummer')) {
            $this->kvknummer = $kvk;
        }

        if ($vat = $quote->getPayment()->getAdditionalInformation('vatnummer')) {
            $this->vatnummer = $vat;
        }

        if ($billink = $quote->getPayment()->getAdditionalInformation('billink_agree')) {
            $this->billinkAgree = $billink;
        }
    }

    /**
     * @param string $code
     * @return void
     * @throws LocalizedException
     */
    public function registerMethodCode(string $code): void
    {
        $this->methodCode = $code;
        $this->paymentMethodInstance = $this->paymentHelper->getMethodInstance($code);
    }

    /**
     * @param string|null $value
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updatedDateofbirth(?string $value): ?string
    {
        $value = empty($value) ? null : $value;

        $quote = $this->getQuote();
        $quote->getPayment()->setAdditionalInformation('dob', $value);
        $this->quoteRepository->save($quote);

        return $value;
    }

    /**
     * @param string|null $value
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updatedKvknummer(?string $value): ?string
    {
        $value = empty($value) ? null : $value;

        $quote = $this->getQuote();
        $quote->getPayment()->setAdditionalInformation('kvknummer', $value);
        $this->quoteRepository->save($quote);

        return $value;
    }

    /**
     * @param string|null $value
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updatedVatnummer(?string $value): ?string
    {
        $value = empty($value) ? null : $value;

        $quote = $this->getQuote();
        $quote->getPayment()->setAdditionalInformation('vatnummer', $value);
        $this->quoteRepository->save($quote);

        return $value;
    }

    /**
     * @param bool|null $value
     * @return bool|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updatedBillinkAgree(?bool $value): ?bool
    {
        $value = empty($value) ? null : $value;

        $quote = $this->getQuote();
        $quote->getPayment()->setAdditionalInformation('billink_agree', $value);
        $this->quoteRepository->save($quote);

        return $value;
    }

    /**
     * @param EvaluationResultFactory $resultFactory
     * @return EvaluationResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        /** @var PaymentMethod $methodInstance */
        $payment = $this->getQuote()->getPayment();
        $methodInstance = $payment->getMethodInstance();
        $cocRequired = $methodInstance->getKVK() === "2";
        $vatRequired = $methodInstance->getVAT() === "2";
        $dobRequired = $methodInstance->getDOB() === "2";

        if ($cocRequired) {
            if (empty($this->billinkAgree) && $payment->getMethod() === 'paynl_payment_billink') {
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('payment:method:error')
                    ->withMessage((string) __('You must first agree to the payment terms.'));
            }

            if (strlen((string) $this->kvknummer) < 8) {
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('payment:method:error')
                    ->withMessage((string) __('Enter a valid COC number'));
            }
        }

        if ($vatRequired && strlen((string) $this->vatnummer) < 8) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage((string) __('Enter a valid VAT-id'));
        }

        if ($dobRequired && empty($this->dateofbirth)) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage((string) __('Enter a valid date of birth'));
        }

        return $resultFactory->createSuccess();
    }

    /**
     * @return string|null
     */
    public function getInstructions(): ?string
    {
        return $this->paymentMethodInstance->getInstructions();
    }

    /**
     * @return bool
     */
    public function showDOB(): bool
    {
        return ((int) $this->paymentMethodInstance->getDOB()) > 0;
    }

    /**
     * @return bool
     */
    public function showKVK(): bool
    {
        return ((int) $this->paymentMethodInstance->getKVK()) > 0;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function showVAT(): bool
    {
        if (!$this->getCompany()) {
            return false;
        }

        return ((int) $this->paymentMethodInstance->getVAT()) > 0;
    }

    /**
     * @return bool
     */
    public function showKVKAgree(): bool
    {
        $method = $this->paymentMethodInstance;

        return $method->getCode() === 'paynl_payment_billink' && $method->getKVK();
    }

    /**
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getCompany(): ?string
    {
        $billingAddress = $this->getQuote()->getBillingAddress();

        if ($billingAddress) {
            return $billingAddress->getCompany();
        }

        return null;
    }

    /**
     * @return CartInterface|\Magento\Quote\Model\Quote|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
}
