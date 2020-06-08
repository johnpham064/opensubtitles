<?php

declare(strict_types=1);

/*
 * Kick Ass Subtitles source code file
 *
 * @link      https://kickasssubtitles.com
 * @copyright Copyright (c) 2016-2019
 * @author    grzesw <contact@kickasssubtitles.com>
 */

namespace KickAssSubtitles\OpenSubtitles;
include("ApiResponse.php");
class Client
{
    /**
     * Endpoint.
     *
     * @var string
     */
    protected $endpoint = 'http://api.opensubtitles.org/xml-rpc';

    /**
     * Useragent.
     *
     * @var string
     *
     * @see https://trac.opensubtitles.org/projects/opensubtitles/wiki/DevReadFirst
     */
    protected $useragent = 'OSTestUserAgent';

    /**
     * Language (ISO639-1).
     *
     * @var string
     */
    protected $language = 'en';

    /**
     * Username.
     *
     * @var string
     */
    protected $username;

    /**
     * Password.
     *
     * @var string
     */
    protected $password;

    /**
     * Token.
     *
     * @var string
     */
    protected $token;

    /**
     * Create client instance.
     *
     * @param array $options
     *
     * @return Client
     *
     * @throws ClientException
     */
    public static function create(array $options): self
    {
        return new static($options);
    }

    /**
     * Constructor.
     *
     * @param array $options
     *
     * @throws ClientException
     */
    protected function __construct(array $options)
    {
        if (!function_exists('xmlrpc_encode_request')) {
            throw new ClientException(ClientException::ERR_MISSING_EXTENSION);
        }
        foreach ($options as $option => $value) {
            $this->{$option} = $value;
        }
        if (null === $this->username || null === $this->password) {
            throw new ClientException(ClientException::ERR_MISSING_USERNAME_PASSWORD);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (null !== $this->token) {
            $this->logOut($this->token);
        }
    }

    /**
     * Obtain token.
     *
     * @return string
     */
    public function obtainToken(): string
    {
        if (null !== $this->token) {
            return $this->token;
        }
        $response = $this->logIn(
            $this->username,
            $this->password,
            $this->language,
            $this->useragent
        )->toArray();
        $this->token = $response['token'];

        return $this->token;
    }

    /**
     * Build XML-RPC request.
     *
     * @param string $method
     * @param array  $params
     *
     * @return string
     */
    public function buildRequest(string $method, array $params = []): string
    {
        $request = xmlrpc_encode_request($method, $params, [
            'encoding' => 'UTF-8',
        ]);

        return $request;
    }

    /**
     * Send XML-RPC request.
     *
     * @param string $request
     *
     * @return array
     *
     * @throws ClientException
     */
    public function sendRequest(string $request): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: text/xml',
                'content' => $request,
            ],
        ]);
        $file = file_get_contents($this->endpoint, false, $context);
        $response = xmlrpc_decode($file, 'UTF-8');
        if (is_array($response) && xmlrpc_is_fault($response)) {
            throw new ClientException($response['faultString'], $response['faultCode']);
        }
        if (empty($response['status']) || '200 OK' !== $response['status']) {
            throw new ClientException(ClientException::ERR_INVALID_RESPONSE_STATUS);
        }

        return $response;
    }

    /**
     * Call API method.
     *
     * @param string $method
     * @param array  $params
     *
     * @return ApiResponse
     *
     * @throws ClientException
     */
    public function __call(string $method, array $params = []): ApiResponse
    {
        $method = ucfirst($method);
        if (!in_array($method, [
            'ServerInfo',
            'LogIn',
            'LogOut',
        ], true)) {
            $token = $this->obtainToken();
            array_unshift($params, $token);
        }
        $request = $this->buildRequest($method, $params);
        $response = $this->sendRequest($request);

        return new ApiResponse($method, $response);
    }
	
	
}
