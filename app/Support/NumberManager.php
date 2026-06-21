<?php

namespace App\Support;

class NumberManager
{
    // =========================================
    // GENERATED FORMATTED VALUES IN THOUSANDS
    // =========================================
    public function format(
        float $num
    ): string {

        $num = is_null($num) ? 0 : $num;
        
        if (floor($num) == $num) {
            return number_format($num, 0, '.', ',');
        } else {
            return number_format($num, 2, '.', ',');
        }
    }

    // =========================================
    // GENERATE VALUES IN (RAW, K, M, B, T)
    // =========================================
    function compute(
        int $number, 
        int $precision = 1
    ): string {
        
        if ($number < 1000) {
            $number_count = number_format($number, $precision);
            $suffix = '';
        } else if($number < 1000000) {
            $number_count = number_format($number / 1000, $precision);
            $suffix = 'K';
        }
        else if($number < 1000000000) {
            $number_count = number_format($number / 1000000, $precision);
            $suffix = 'M';
        }
        else if($number < 1000000000000) {
            $number_count = number_format($number / 1000000000, $precision);
            $suffix = 'B';
        }
        else {
            $number_count = number_format($number / 1000000000000, $precision);
            $suffix = 'T';
        }

        //Remove unnecessary zeros after decimal
        if($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $number_count = str_replace($dotzero, '', $number_count);
        }

        return $number_count . $suffix;
    }
}
