<?php

namespace App\Support;

class CurrencyManager
{
    private string $symbol            = '₦';
    private string $position          = 'before';
    private string $decimalSeparator  = '.';
    private string $thousandSeparator = ',';
    private int $decimals             = 2;

    // =========================================
    // FORMAT NUMBER TO CURRENCY STRING
    // =========================================
    public function format(
        float $amount
    ): string {

        $amount = (float)$amount; // auto-cast null → 0.0
        $formatted = number_format(
            $amount,
            $this->decimals,
            $this->decimalSeparator,
            $this->thousandSeparator
        );

        if ($this->position === 'before') {
            return "{$this->symbol}{$formatted}";
        }

        return "{$formatted}{$this->symbol}";
    }

    // =========================================
    // SET NEW CURRENCY SYMBOL
    // =========================================
    public function setSymbol(
        string $symbol
    ): void {

        $this->symbol = $symbol;
    }

    // =========================================
    // SET CURRENCY SYMBOL POSITION
    // =========================================
    public function setPosition(
        string $position
    ): void {

        if (!in_array($position, ['before', 'after'])) {
            throw new \InvalidArgumentException("Position must be 'before' or 'after'.");
        }

        $this->position = $position;
    }

    // =========================================
    // SET DECIMAL & THOUSAND SEPARATORS
    // =========================================
    public function setSeparators(
        string $decimal, 
        string $thousand
    ): void {

        $this->decimalSeparator = $decimal;
        $this->thousandSeparator = $thousand;
    }

    // =========================================
    // SET NUMBER OF DECIMALS
    // =========================================
    public function setDecimals(
        int $decimals
    ): void {
        
        $this->decimals = max(0, $decimals);
    }
}
