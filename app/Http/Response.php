<?php

declare(strict_types=1);

namespace App\Http;

use App\Core\View;

use Closure;
use JsonException;

class Response
{
    /*
    |--------------------------------------------------------------------------
    | Response Types
    |--------------------------------------------------------------------------
    */
    public const TYPE_HTML             = 'html';
    public const TYPE_JSON             = 'json';
    public const TYPE_TEXT             = 'text';
    public const TYPE_VIEW             = 'view';
    public const TYPE_EMPTY            = 'empty';
    public const TYPE_REDIRECT         = 'redirect';
    public const TYPE_DOWNLOAD         = 'download';
    public const TYPE_STREAM           = 'stream';
    public const TYPE_STREAM_DOWNLOAD  = 'stream-download';

    /**
     * Response body.
     */
    protected string $body = '';

    /**
     * HTTP status code.
     */
    protected int $status = 200;

    /**
     * Response headers.
     *
     * @var array<string,string>
     */
    protected array $headers = [];

    /**
     * Cookies queued for sending.
     *
     * @var array<int,array<string,mixed>>
     */
    protected array $cookies = [];

    /**
     * Stream response
     */
    protected ?Closure $stream = null;

    /**
     * The response type.
     */
    protected string $type = self::TYPE_HTML;

    /**
     * Constructor.
     */
    public function __construct(
        protected View $view
    ) {}

    /**
     * Reset response state.
     */
    protected function reset(): void
    {
        $this->body = '';
        $this->status = 200;
        $this->headers = [];
        $this->cookies = [];
        $this->stream = null;
        $this->type = self::TYPE_HTML;
    }

    /**
     * HTML response.
     */
    public function html(
        string $html,
        int $status = 200
    ): self {

        $this->reset();

        $this->type = self::TYPE_HTML;

        $this->body = $html;
        $this->status = $status;

        $this->headers['Content-Type'] = 'text/html; charset=UTF-8';

        return $this;
    }

    /**
     * JSON response.
     *
     * @throws JsonException
     */
    public function json(
        array $data,
        int $status = 200,
        int $flags = JSON_UNESCAPED_UNICODE
    ): self {

        $this->reset();

        $this->type = self::TYPE_JSON;

        $this->body = json_encode(
            $data,
            $flags | JSON_THROW_ON_ERROR
        );

        $this->status = $status;

        $this->headers['Content-Type'] = 'application/json; charset=UTF-8';

        return $this;
    }

    /**
     * Plain text response.
     */
    public function text(
        string $text,
        int $status = 200
    ): self {

        $this->reset();

        $this->type = self::TYPE_TEXT;

        $this->body = $text;
        $this->status = $status;

        $this->headers['Content-Type'] = 'text/plain; charset=UTF-8';

        return $this;
    }

    /**
     * Empty response.
     */
    public function empty(
        int $status = 204
    ): self {

        $this->reset();

        $this->type = self::TYPE_EMPTY;

        $this->status = $status;

        return $this;
    }

    /**
     * Redirect response.
     */
    public function redirect(
        string $url,
        int $status = 302
    ): self {

        $this->reset();

        $this->type = self::TYPE_REDIRECT;

        $this->status = $status;

        $this->headers['Location'] = $url;

        return $this;
    }

    /**
     * Render a view and set it as the response body.
     */
    public function view(
        string $view,
        array $data = [],
        int $status = 200
    ): self {

        $this->reset();

        $this->type = self::TYPE_VIEW;

        $this->status = $status;

        $this->body = $this->view->render(
            $view,
            $data
        );

        $this->headers['Content-Type'] = 'text/html; charset=UTF-8';

        return $this;
    }

    /**
     * Download response.
     */
    public function download(
        string $file,
        ?string $filename = null
    ): self {

        if (!is_file($file)) {
            throw new \RuntimeException(
                "File not found."
            );
        }

        $this->reset();

        $this->type = self::TYPE_DOWNLOAD;

        $filename ??= basename($file);

        $type = mime_content_type($file);

        $this->status = 200;

        $this->headers = [

            'Content-Type'
                => $type ?: 'application/octet-stream',

            'Content-Length'
                => (string) filesize($file),

            'Content-Disposition'
                => 'attachment; filename="' . $filename . '"',

            'Cache-Control'
                => 'no-cache',

            'Pragma'
                => 'public',
        ];

        $content = file_get_contents($file);

        if ($content === false) {
            throw new \RuntimeException(
                "Unable to read file."
            );
        }

        $this->body = $content;

        return $this;
    }

    /**
     * Stream response
     */
    public function stream(
        Closure $callback,
        int $status = 200,
        array $headers = [],
        string $type = self::TYPE_STREAM
    ): self {

        $this->reset();

        $this->type = $type;

        $this->status = $status;

        $this->stream = $callback;

        $this->headers = array_merge(
            [
                'Content-Type'
                    => 'application/octet-stream',
            ],
            $headers
        );

        return $this;
    }

    /**
     * Build stream downloads
     */
    public function streamDownload(
        string $file,
        ?string $filename = null
    ): self {

        if (!is_file($file)) {
            throw new \RuntimeException('File not found.');
        }

        $filename ??= basename($file);

        return $this->stream(
            function () use ($file) {

                $handle = fopen($file, 'rb');

                while (!feof($handle)) {

                    echo fread($handle, 8192);

                    flush();
                }

                fclose($handle);

            },
            200,
            [
                'Content-Type' => mime_content_type($file),
                'Content-Length' => (string) filesize($file),
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache',
            ],
            self::TYPE_STREAM_DOWNLOAD
        );
    }

    /**
     * Replace response body.
     */
    public function body(
        string $body
    ): self {

        $this->body = $body;

        return $this;
    }

    /**
     * Set status code.
     */
    public function status(
        int $status
    ): self {

        $this->status = $status;

        return $this;
    }

    /**
     * Add or replace a header.
     */
    public function header(
        string $name,
        string $value
    ): self {

        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Queue a cookie.
     */
    public function cookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {

        $this->cookies[] = [
            'name'      => $name,
            'value'     => $value,
            'expires'   => $expires,
            'path'      => $path,
            'domain'    => $domain,
            'secure'    => $secure,
            'httponly'  => $httpOnly,
            'samesite'  => $sameSite,
        ];

        return $this;
    }

    /**
     * Get body.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get status.
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get headers.
     *
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get queued cookies.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Get stream callback.
     */
    public function getStream(): ?Closure
    {
        return $this->stream;
    }

    /**
     * Get response type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Determine if the response is HTML.
     */
    public function isHtml(): bool
    {
        return $this->type === self::TYPE_HTML;
    }

    /**
     * Determine if the response is a view.
     */
    public function isView(): bool
    {
        return $this->type === self::TYPE_VIEW;
    }

    /**
     * Determine if the response is JSON.
     */
    public function isJson(): bool
    {
        return $this->type === self::TYPE_JSON;
    }

    /**
     * Determine if the response is plain text.
     */
    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Determine if the response is empty.
     */
    public function isEmpty(): bool
    {
        return $this->type === self::TYPE_EMPTY;
    }

    /**
     * Determine if the response is a redirect.
     */
    public function isRedirect(): bool
    {
        return $this->type === self::TYPE_REDIRECT;
    }

    /**
     * Determine if the response is a download.
     */
    public function isDownload(): bool
    {
        return $this->type === self::TYPE_DOWNLOAD;
    }

    /**
     * Determine if the response is streamed.
     */
    public function isStream(): bool
    {
        return in_array(
            $this->type,
            [
                self::TYPE_STREAM,
                self::TYPE_STREAM_DOWNLOAD
            ],
            true
        );
    }
}