<?php

namespace ArcaSolutions\CoreBundle\Services;


use Symfony\Component\Translation\TranslatorInterface;

class CurrencyHandler
{
    const RETURN_CURRENCY_STRING = 1;
    const RETURN_CURRENCY_ARRAY = 2;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(TranslatorInterface $translator, Settings $settings)
    {
        $this->translator = $translator;
        $this->settings = $settings;
    }

    /**
     * Format the value to the defined currency
     *
     * @param $number
     * @param bool $withHTML Default: true
     * @param int $format (CurrencyHandler::RETURN_CURRENCY_STRING|CurrencyHandler::RETURN_CURRENCY_ARRAY) Default: RETURN_CURRENCY_STRING
     *
     * @return array|string
     */
    public function formatCurrency($number, $withHTML = true, $format = self::RETURN_CURRENCY_STRING)
    {
        $thousandSeparator = $this->translator->trans("thousands.separator", [], "units");
        $decimalSeparator = $this->translator->trans("decimal.separator", [], "units");
        $symbol = $this->settings->getDomainSetting(Settings::PAYMENT_CURRENCY_SYMBOL);

        $parts = explode('.', (string)$number);

        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? str_pad($parts[1], 2, 0, STR_PAD_RIGHT) : 0;

        $symbolRight = false;
        $langsRight = ['es','es_es', 'it', 'it_it', 'fr', 'fr_fr', 'de', 'de_de'];

        if ($symbol === '€' && in_array($this->translator->getLocale(), $langsRight, false)) {
            $symbolRight = true;
        }

        if ($withHTML) {
            $integerPart = number_format($integerPart, 0, $decimalSeparator, $thousandSeparator);

            if ($symbolRight) {
                $return = $integerPart . $decimalSeparator . '<small>' . $decimalPart . '</small>' .' ' . $symbol;
            } else {
                $return = $symbol . ' ' . $integerPart . $decimalSeparator . '<small>' . $decimalPart . '</small>';
            }

        } else {
            switch ($format) {
                case self::RETURN_CURRENCY_ARRAY:
                    $return = [
                        'symbol'  => $symbol,
                        'value'   => "{$integerPart}".($decimalPart ? "{$decimalSeparator}{$decimalPart}" : ""),
                        'decimal' => $decimalPart,
                    ];
                    break;
                case self::RETURN_CURRENCY_STRING:
                default:
                    if ($decimalPart) {
                        $number = number_format($number, 2, $decimalSeparator, $thousandSeparator);
                    }

                    if ($symbolRight) {
                        $return = $number.' '.$symbol;
                    } else {
                        $return = $symbol.' '.$number;
                    }

                    break;
            }
        }

        return $return;
    }
}
