<?php

namespace App\Support;

class TextManager
{
    // =========================================
    // FORMAT TITLES
    // =========================================
    public function formatTitle(
        string $title
    ): string {

        $clean     = preg_replace("/[^A-Za-z\s]/", '', $title);
        $clean     = preg_replace('/\s+/', ' ', trim($clean));
        $formatted = ucwords(strtolower($clean));

        return $formatted;
    }

    // =========================================
    // FORMAT PUSH MESSAGE
    // =========================================
    public function formatPushMessage(
        string $htmlMessage
    ): string {

        $textWithLineBreaks = preg_replace('/<br\s*\/?>/i', "\n", $htmlMessage);
        $plainText          = strip_tags($textWithLineBreaks);

        return trim($plainText);
    }

    // =========================================
    // FORMAT MAIL MESSAGE
    // =========================================
    public function formatMailMessage(
        string $text
    ): string {

        $text = str_replace(["\r\n", "\\r\\n", "\\r", "\\n"], "\n", $text);
        $text = stripslashes(rtrim($text, '\\'));

        return nl2br($text);
    }

    // =========================================
    // FORMAT NAMES
    // =========================================
    public function formatUserName(
        string $name
    ): string {

        $clean     = preg_replace("/[^A-Za-z\s'-]/", '', $name);
        $clean     = preg_replace('/\s+/', ' ', trim($clean));
        $formatted = ucwords(strtolower($clean));

        return $formatted;
    }
}
