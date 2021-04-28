<?php

namespace App\Containers;

use App\Contracts\IdentityApiContract;
use App\Exceptions\NoConfigurationException;
use HttpRequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class IdentityApiContainer implements IdentityApiContract {

    private $apiUrl;
    private $username;
    private $password;

    public function __construct()
    {
        $this->username = env('API_USERNAME');
        $this->password = env('API_PASSWORD');
        $url = env('API_URL');
        $version = env('API_VERSION');

        if (!$this->username) {
            throw new NoConfigurationException('Username for Basic authentication is required');
        }
        if (!$this->password) {
            throw new NoConfigurationException('Password for Basic authentication is required');
        }
        if (!$url) {
            throw new NoConfigurationException('URL is required');
        }
        if (!$version) {
            throw new NoConfigurationException('Version of API is required');
        }

        $this->apiUrl = "https://{$url}/{$version}/";
    }

    /**
     * @throws HttpRequestException
     */
    public function throwError($status, $httpCodesMap): void
    {
        $errorMessage = $httpCodesMap[$status] ?: 'Request failed';
        throw new HttpRequestException($errorMessage);
    }

    /**
     * @throws HttpRequestException
     */
    public function get($path, $params, $httpCodesMap): Response
    {
        $response = Http::withBasicAuth($this->username, $this->password)->get($this->apiUrl . $path, $params);
        if ($response->failed()) {
            $this->throwError($response->status(), $httpCodesMap);
        }
        return $response;
    }

    /**
     * @throws HttpRequestException
     */
    public function post($path, $params, $httpCodesMap): Response
    {
        $response = Http::withBasicAuth($this->username, $this->password)->post($this->apiUrl . $path, $params);
        if ($response->failed()) {
            $this->throwError($response->status(), $httpCodesMap);
        }
        return $response;
    }

    /**
     * @throws HttpRequestException
     */
    public function put($path, $params, $httpCodesMap): Response
    {
        $response = Http::withBasicAuth($this->username, $this->password)->put($this->apiUrl . $path, $params);
        if ($response->failed()) {
            $this->throwError($response->status(), $httpCodesMap);
        }
        return $response;
    }

    /**
     * @throws HttpRequestException
     */
    public function patch($path, $params, $httpCodesMap): Response
    {
        $response = Http::withBasicAuth($this->username, $this->password)->patch($this->apiUrl . $path, $params);
        if ($response->failed()) {
            $this->throwError($response->status(), $httpCodesMap);
        }
        return $response;
    }

    /**
     * @throws HttpRequestException
     */
    public function delete($path, $params, $httpCodesMap): Response
    {
        $response = Http::withBasicAuth($this->username, $this->password)->delete($this->apiUrl . $path, $params);
        if ($response->failed()) {
            $this->throwError($response->status(), $httpCodesMap);
        }
        return $response;
    }
}
