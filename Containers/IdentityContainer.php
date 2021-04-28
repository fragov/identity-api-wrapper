<?php

namespace App\Containers;

use App\Contracts\IdentityApiContract;
use App\Contracts\IdentityContract;
use App\Exceptions\WrongParametersException;
use Illuminate\Support\Facades\Validator;

class IdentityContainer implements IdentityContract
{
    public $api;

    public function __construct(IdentityApiContract $api)
    {
        $this->api = $api;
        Validator::extend('product', static function ($attribute, $value, $parameters) {
            return in_array($value, [0, 12, 15, 16, 17], true);
        });
        Validator::extend('add', static function ($attribute, $value, $parameters) {
            return in_array($value, [0, 4, 8, 16, 32, 128, 256, 4096, 8192, 131072, 4194304, 8388608], true);
        });
        Validator::extend('phone', static function ($attribute, $value, $parameters) {
            return preg_match('/\+\d{1,14}/', $value);
        });
        Validator::extend('signature', static function ($attribute, $value, $parameters) {
            return in_array($value, ['QES', 'ADV', 'BAS'], true);
        });
    }

    /**
     * @param $params
     * @return mixed
     * @throws WrongParametersException
     */
    public function putOrder($params)
    {
        $orderRules = include(__DIR__ . '/../Rules/OrderRules.php');
        $validate = Validator::make($params, $orderRules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            202 => 'Order accepted, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
        ];

        $response = $this->api->put('putOrder', $params, $httpCodesMap);

        return $response->json();
    }

    /**
     * @param string $orderId
     * @param string $variant
     * @return mixed
     * @throws WrongParametersException
     */
    public function getStatus($orderId, $variant = 'Default')
    {
        $defaultPath = "getStatus/{$orderId}";
        $variantToPathMap = [
            'Default' => $defaultPath,
            'ExtendedList' => $defaultPath . '/ExtendedList',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getStatus method");
        }

        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get($variantToPathMap[$variant], [], $httpCodesMap);

        return $response->json();
    }

    /**
     * @param string $orderId
     * @param string $variant
     * @return mixed
     * @throws WrongParametersException
     */
    public function getIdentData($orderId, $variant = 'Default')
    {
        $defaultPath = "getIdentData/{$orderId}";
        $variantToPathMap = [
            'Default' => $defaultPath,
            'IncludeInitialData' => $defaultPath . '/IncludeInitialData',
            'IncludeIdentifyMethod' => $defaultPath . '/IncludeIdentifyMethod',
            'Signed' => $defaultPath . '/Signed',
            'crypt' => $defaultPath . '/crypt',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getIdentData method");
        }

        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get($variantToPathMap[$variant], [], $httpCodesMap);

        return $response->json();
    }

    /**
     * @param string $orderId
     * @param string $variant
     * @return string
     * @throws WrongParametersException
     */
    public function getIdentDataPDF($orderId, $variant = 'Default'): string
    {
        $defaultPath = "getIdentDataPDF/{$orderId}";
        $variantToPathMap = [
            'Default' => $defaultPath,
            'crypt' => $defaultPath . '/crypt',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getIdentDataPDF method");
        }

        $httpCodesMap = [
            200 => 'OK, PDF in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get($variantToPathMap[$variant], [], $httpCodesMap);

        return $response->body();
    }

    /**
     * @param string $orderId
     * @param string $variant
     * @return mixed|string
     * @throws WrongParametersException
     */
    public function getESignPDF($orderId, $variant = 'Default')
    {
        $defaultPath = "getESignPDF/{$orderId}";
        $variantToPathMap = [
            'Default' => $defaultPath,
            'crypt' => $defaultPath . '/crypt',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getESignPDF method");
        }

        $httpCodesMap = [
            200 => 'OK, PDF stream in body if only one document, otherwise a JSON document',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get($variantToPathMap[$variant], [], $httpCodesMap);

        if ($response->header('Content-Type') === 'application/json') {
            return $response->json();
        }

        return $response->body();
    }

    /**
     * @param string $orderId
     * @return mixed
     */
    public function getESignHash($orderId)
    {
        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get("getESignHash/{$orderId}", [], $httpCodesMap);

        return $response->json();
    }

    /**
     * @param string $orderId
     * @return string
     */
    public function getESignAuditLogPDF($orderId): string
    {
        $httpCodesMap = [
            200 => 'OK, PDF document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get("getESignAuditLogPDF/{$orderId}", [], $httpCodesMap);

        return $response->body();
    }

    /**
     * @param string $orderId
     * @param string $callbackUrl
     * @throws WrongParametersException
     * @return bool
     */
    public function getVideoFileBinaryAsync($orderId, $callbackUrl): bool
    {
        $params = ['callbackUrl' => $callbackUrl];
        $rules = ['callbackUrl' => 'required|url'];
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            200 => 'OK',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->post("getVideoFileBinaryAsync/{$orderId}", $params, $httpCodesMap);

        return $response->status() === 200;
    }

    /**
     * @param string $orderId
     * @param string $variant
     * @return string
     * @throws WrongParametersException
     */
    public function getVideoFileBinary($orderId, $variant = 'Default'): string
    {
        $defaultPath = "getVideoFileBinary/{$orderId}";
        $variantToPathMap = [
            'Default' => $defaultPath,
            'crypt' => $defaultPath . '/crypt',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getVideoFileBinary method");
        }

        $httpCodesMap = [
            200 => 'OK, binary video file (video/mp4 or video/webm) in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get($variantToPathMap[$variant], [], $httpCodesMap);

        return $response->body();
    }

    /**
     * @param string $orderId
     * @param string $variant
     * @return mixed
     * @throws WrongParametersException
     */
    public function getVoiceFiles($orderId, $variant = 'Default')
    {
        $defaultPath = "getVoiceFiles/{$orderId}";
        $variantToPathMap = [
            'Default' => $defaultPath,
            'crypt' => $defaultPath . '/crypt',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getVoiceFiles method");
        }

        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found',
        ];

        $response = $this->api->get($variantToPathMap[$variant], [], $httpCodesMap);

        return $response->json();
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function delIdentData($orderId): bool
    {
        $httpCodesMap = [
            202 => 'Accepted, data deleted',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            406 => 'Invalid order state',
        ];

        $response = $this->api->delete("delIdentData/{$orderId}", [], $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @param string $orderId
     * @param array $params
     * @param string $variant
     * @return mixed
     * @throws WrongParametersException
     */
    public function getAllStatus($orderId, $params, $variant = 'Default')
    {
        $rules = include(__DIR__ . '/../Rules/AllStatusRules.php');
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $defaultPath = "getAllStatus/{$orderId}";

        $variantToPathMap = [
            'Default' => $defaultPath,
            'ExtendedList' => $defaultPath . '/ExtendedList',
        ];

        if (!$variantToPathMap[$variant]) {
            throw new WrongParametersException("Variant {$variant} does not exist for getAllStatus method");
        }

        $httpCodesMap = [
            200 => 'OK, JSON data in body',
            304 => 'No new status information available since last call',
            400 => 'Bad request, error information in body',
            401 => 'Authentication failed',
        ];

        $response = $this->api->post($variantToPathMap[$variant], $params, $httpCodesMap);

        return $response->json();
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function cancelOrder($orderId): bool
    {
        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error information in body',
            401 => 'Authentication failed',
            404 => 'Order not found',
            406 => 'Order not in a cancellable state',
        ];

        $response = $this->api->post("cancelOrder/{$orderId}", [], $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function requestSign($orderId): bool
    {
        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error information in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            406 => 'Invalid order state',
            410 => 'Order has already been processed',
        ];

        $response = $this->api->post("requestSign/{$orderId}", [], $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @param string $orderId
     * @param string $tan
     * @return bool
     * @throws WrongParametersException
     */
    public function confirmSign($orderId, $tan)
    {
        $params = ['tan' => $tan];
        $rules = ['tan' => 'required|string|min:6|max:6'];
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            409 => 'Wrong TAN given',
            410 => 'Order has already been processed',
            412 => 'Signing process has not been started, call requestSign first',
            429 => 'Too many tries with a wrong TAN, order cancelled',
        ];

        $response = $this->api->post("confirmSign/{$orderId}", $params, $httpCodesMap);

        return $response->statue() === 202;
    }

    /**
     * @param string $orderId
     * @return bool
     */
    public function requestResendSignTan($orderId): bool
    {
        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            410 => 'Order has already been processed',
            412 => 'Signing process has not been started, call requestSign first',
        ];

        $response = $this->api->post("requestResendSignTan/{$orderId}", [], $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @param string $orderId
     * @return bool
     */
    public function requestPhoneVerification($orderId): bool
    {
        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            406 => 'Invalid order state',
            410 => 'Order has already been processed',
        ];

        $response = $this->api->post("requestPhoneVerification/{$orderId}", [], $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @param string $orderId
     * @param string $tan
     * @return bool
     * @throws WrongParametersException
     */
    public function provePhoneControl($orderId, $tan): bool
    {
        $params = ['tan' => $tan];
        $rules = ['tan' => 'required|string|min:5|max:5'];
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            410 => 'Order has already been processed',
            412 => 'Process has not been started, call requestPhoneVerification first',
            429 => 'Too many tries with a wrong TAN, order cancelled',
        ];

        $response = $this->api->post("provePhoneControl/{$orderId}", $params, $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @param string $orderId
     * @return bool
     */
    public function requestResendPhoneTan($orderId): bool
    {
        $httpCodesMap = [
            202 => 'Accepted',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
            404 => 'Order not found or already deleted',
            410 => 'Order has already been processed',
            412 => 'Process has not been started, call requestPhoneVerification first',
        ];

        $response = $this->api->post("requestResendPhoneTan/{$orderId}", [], $httpCodesMap);

        return $response->status() === 202;
    }

    /**
     * @return bool
     */
    public function serverStatus()
    {
        $httpCodesMap = [
            200 => 'Service available',
            500 => 'Service not available',
        ];

        $response = $this->api->get('serverStatus', [], $httpCodesMap);

        return $response->status() === 200;
    }

    /**
     * @return mixed
     */
    public function systemStatus()
    {
        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            401 => 'Authentication failed, please check username and password',
            500 => 'Service not available at all',
        ];

        $response = $this->api->get('systemStatus', [], $httpCodesMap);

        return $response->json();
    }

    /**
     * @return mixed
     */
    public function getBankList()
    {
        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
        ];

        $response = $this->api->get('getBankList', [], $httpCodesMap);

        return $response->json();
    }

    /**
     * @param array $params
     * @return mixed
     * @throws WrongParametersException
     */
    public function checkSignmeUser($params)
    {
        $rules = [
            'Email' => 'required|email',
            'signatureType' => 'signature',
        ];
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            200 => 'OK, JSON document in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
        ];

        $response = $this->api->post("checkSignmeUser", $params, $httpCodesMap);

        return $response->json();
    }

    /**
     * @param strin $mobile
     * @return bool
     * @throws WrongParametersException
     */
    public function requestNewPassword($mobile): bool
    {
        $params = ['mobile' => $mobile];
        $rules = ['mobile' => 'required|phone|max:64'];
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            200 => 'OK, TAN is sent to the mobile number',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username and password',
        ];

        $response = $this->api->post("requestNewPassword", $params, $httpCodesMap);

        return $response->status() === 200;
    }

    /**
     * @param string $tan
     * @return mixed
     * @throws WrongParametersException
     */
    public function confirmNewPassword($tan)
    {
        $params = ['tan' => $tan];
        $rules = ['tan' => 'required|string|min:6|max:6'];
        $validate = Validator::make($params, $rules);

        if ($validate->fails()) {
            throw new WrongParametersException($validate->errors());
        }

        $httpCodesMap = [
            200 => 'OK, password has been changed, new password in body',
            400 => 'Bad request, error description in body',
            401 => 'Authentication failed, please check username, password and TAN',
        ];

        $response = $this->api->post("confirmNewPassword", $params, $httpCodesMap);

        return $response->json();
    }
}
