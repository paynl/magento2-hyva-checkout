<?php

declare(strict_types=1);

namespace Paynl\HyvaCheckout\Magewire\Checkout\Payment\Method;

class Metjebank extends Pin
{
    public const METHOD_CODE = 'paynl_payment_metjebank';
    protected const DEFAULT_OPTION_MESSAGE = 'Choose the MetJeBank option';
    protected const ERROR_MESSAGE = 'Option is required';
}
