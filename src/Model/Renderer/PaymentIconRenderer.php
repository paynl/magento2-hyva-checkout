<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Model\Renderer;

use Magento\Framework\Escaper;

class PaymentIconRenderer
{
    private Escaper $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * @param string $urlPath
     * @param string $altText
     * @return string
     */
    public function render(string $urlPath, string $altText): string
    {
        $escapedSrc = $this->escaper->escapeHtmlAttr($urlPath);
        $escapedAlt = $this->escaper->escapeHtmlAttr($altText);

        return '<img src="'.$escapedSrc.'" alt="'. $escapedAlt .'" class="payment-icon" width="35px" />';
    }
}
