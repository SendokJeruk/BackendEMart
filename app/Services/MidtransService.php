<?php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Setting;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = Setting::getValue('MIDTRANS_SERVER_KEY');
        Config::$isProduction = Setting::getValue('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(array $params)
    {
        try {
            $transaction = Snap::createTransaction($params);
            return [
                'snap_token' => $transaction->token,
                'redirect_url' => $transaction->redirect_url,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
