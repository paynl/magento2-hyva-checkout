<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Model\Magewire\Payment;

use Exception;
use Hyva\Checkout\Model\Magewire\Payment\AbstractOrderData;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Paynl\Payment\Helper\PayHelper;

class PlaceOrderService extends AbstractPlaceOrderService
{
    protected QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;
    protected PayHelper $payHelper;

    /**
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param PayHelper $payHelper
     * @param AbstractOrderData|null $orderData
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        PayHelper $payHelper,
        AbstractOrderData $orderData = null
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->payHelper = $payHelper;
        parent::__construct($cartManagement, $orderData);
    }

    /**
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     * @throws Exception
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        try {
            return sprintf(
                'paynl/checkout/redirect?mqid=%s&nocache=%s',
                $this->quoteIdToMaskedQuoteId->execute((int) $quote->getId()),
                time()
            );
        } catch (Exception $e) {
            $this->payHelper->logCritical(__METHOD__ . ': Failed to retrieve masked quote id for quote ' . $quote->getId());
            throw new Exception('Could not fetch masked quote id to build the redirect url. Exception: ' . $e->getMessage());
        }
    }
}
