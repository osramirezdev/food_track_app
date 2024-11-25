<?php

namespace Store\Proxy;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class that apply Proxy pattern
 *
 * @author oramirez
 * @since 24/11/24
 */
class MarketProxy {
    private string $baseUrl;

    public function __construct() {
        $this->baseUrl = config('services.market.url');
    }

    public function purchaseIngredient(string $ingredient): int {
        $url = "{$this->baseUrl}/api/farmers-market/buy";

        $response = Http::get($url, [
            'ingredient' => $ingredient,
        ]);

        if ($response->failed()) {
            Log::error("Error buying ingredient: {$ingredient}", ['response' => $response->body()]);
            throw new \Exception("Purchase can");
        }

        $data = $response->json();
        return $data['quantitySold'] ?? 0;
    }
}
