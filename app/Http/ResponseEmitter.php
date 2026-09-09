<?php

declare(strict_types=1);

namespace App\Http;

class ResponseEmitter
{
    /**
     * Emit the response to the client.
     */
    public function emit(
        Response $response
    ): never {

        if (!headers_sent()) {

            http_response_code(
                $response->getStatus()
            );

            foreach (
                $response->getHeaders() as $name => $value
            ) {

                header("$name: $value", true);
            }

            foreach (
                $response->getCookies() as $cookie
            ) {

                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    [
                        'expires'  => $cookie['expires'],
                        'path'     => $cookie['path'],
                        'domain'   => $cookie['domain'],
                        'secure'   => $cookie['secure'],
                        'httponly' => $cookie['httponly'],
                        'samesite' => $cookie['samesite'],
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect Responses
        |--------------------------------------------------------------------------
        */
        if ($response->isRedirect()) {
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Stream Responses
        |--------------------------------------------------------------------------
        */
        if ($response->isStream()) {

            ($response->getStream())();

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty Responses
        |--------------------------------------------------------------------------
        */
        if ($response->isEmpty()) {
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Standard Responses
        |--------------------------------------------------------------------------
        */

        echo $response->getBody();

        exit;
    }
}