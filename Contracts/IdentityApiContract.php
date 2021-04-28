<?php

namespace App\Contracts;

interface IdentityApiContract {
    public function get($path, $params, $httpCodesMap);
    public function post($path, $params, $httpCodesMap);
    public function put($path, $params, $httpCodesMap);
    public function patch($path, $params, $httpCodesMap);
    public function delete($path, $params, $httpCodesMap);
}
