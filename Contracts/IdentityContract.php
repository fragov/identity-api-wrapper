<?php

namespace App\Contracts;

interface IdentityContract {
    public function putOrder($params);
    public function getStatus($orderId, $variant);
    public function getIdentData($orderId, $variant);
    public function getIdentDataPDF($orderId, $variant);
    public function getESignPDF($orderId, $variant);
    public function getESignHash($orderId);
    public function getESignAuditLogPDF($orderId);
    public function getVideoFileBinaryAsync($orderId, $callbackUrl);
    public function getVideoFileBinary($orderId, $variant);
    public function getVoiceFiles($orderId, $variant);
    public function delIdentData($orderId);
    public function getAllStatus($orderId, $params, $variant);
    public function cancelOrder($orderId);
    public function requestSign($orderId);
    public function confirmSign($orderId, $tan);
    public function requestResendSignTan($orderId);
    public function requestPhoneVerification($orderId);
    public function provePhoneControl($orderId, $tan);
    public function requestResendPhoneTan($orderId);
    public function serverStatus();
    public function systemStatus();
    public function getBankList();
    public function checkSignmeUser($params);
    public function requestNewPassword($mobile);
    public function confirmNewPassword($tan);
}
