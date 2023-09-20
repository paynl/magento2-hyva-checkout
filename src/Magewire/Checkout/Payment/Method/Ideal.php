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
use Magewirephp\Magewire\Component\Form;
use Rakit\Validation\Validator;

class Ideal extends Form implements EvaluationInterface
{
    public const METHOD_CODE = 'paynl_payment_ideal';
    public const DISPLAY_MODE_DROPDOWN = 1;
    public const DISPLAY_MODE_LIST = 2;

    protected const DEFAULT_OPTION_MESSAGE = 'Choose your bank';
    protected const ERROR_MESSAGE = 'Issuer is required.';

    public ?string $instructions = null;
    public ?int $displayMode = null;
    public array $issuers = [];
    public ?string $selectedIssuer = null;

    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $quoteRepository;
    private PaymentHelper $paymentHelper;
    private Escaper $escaper;

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
        $method = $this->paymentHelper->getMethodInstance(static::METHOD_CODE);
        $this->displayMode = (int) $method->showPaymentOptions();
        $this->instructions = nl2br($this->escaper->escapeHtml($method->getInstructions()));

        $options = array_map(
            fn ($method) => $method instanceof \stdClass ? get_object_vars($method) : $method,
            $method->getPaymentOptions()
        );

        $issuers = array_map(fn ($issuer) => [
            'id' => $issuer['id'] ?? '',
            'name' => $issuer['name'] ?? '',
            'visibleName' => $issuer['visibleName'] ?? '',
            'image' => $issuer['image'] ?? '',
            'logo' => $issuer['logo'] ?? '',
            'radioName' => 'paymentOptionsList_' . static::METHOD_CODE,
            'uniqueId' => 'paymentOptionsList_' . static::METHOD_CODE . '_' . ($issuer['id'] ?? ''),
            'showLogo' => !empty($issuer['logo'])
        ] , $options);

        if ($this->displayMode === static::DISPLAY_MODE_DROPDOWN) {
            array_unshift($issuers, $this->getDefaultOption());
        }

        $this->issuers = $issuers;

        $quote = $this->checkoutSession->getQuote();
        if ($selectedIssuer = $quote->getPayment()->getAdditionalInformation('payment_option')) {
            $this->selectedIssuer = $selectedIssuer;
        }
    }

    /**
     * @return array
     */
    private function getDefaultOption(): array
    {
        $message = __(static::DEFAULT_OPTION_MESSAGE);
        $name = 'paymentOptionsList_' . static::METHOD_CODE . '_';

        return [
            'id' => '',
            'name' => $message,
            'visibleName' => $message,
            'radioName' => $name,
            'uniqueId' => $name,
            'showLogo' => false
        ];
    }

    /**
     * @param string $value
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updatedSelectedIssuer(string $value): ?string
    {
        $value = empty($value) ? null : $value;

        $quote = $this->checkoutSession->getQuote();
        $quote->getPayment()->setAdditionalInformation('payment_option', $value);

        $this->quoteRepository->save($quote);

        return $value;
    }

    /**
     * @param EvaluationResultFactory $resultFactory
     * @return EvaluationResultInterface
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        if (!$this->selectedIssuer) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage((string) __(static::ERROR_MESSAGE));
        }

        return $resultFactory->createSuccess();
    }
}
