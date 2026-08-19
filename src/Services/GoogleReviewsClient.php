<?php

namespace anytech\googlereviews\Services;

use RuntimeException;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\SiteConfig\SiteConfig;

class GoogleReviewsClient {
    use Injectable;

    const ENDPOINT = 'https://places.googleapis.com/v1/places/';

    public function fetchReviews(): array {
        $cfg = SiteConfig::current_site_config();
        $apiKey = trim((string)$cfg->GooglePlacesAPIKey);
        $placeID = trim((string)$cfg->GooglePlaceID);
        $lang = $cfg->GoogleReviewsLanguage ?: 'en';

        if (!$apiKey || !$placeID) {
            throw new RuntimeException('Places API Key and Place ID must both be set in Settings > Google Reviews.');
        }

        $url = self::ENDPOINT . rawurlencode($placeID) . '?fields=reviews&languageCode=' . urlencode($lang);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-Goog-Api-Key: ' . $apiKey,
                'X-Goog-FieldMask: reviews'
            ],
            CURLOPT_TIMEOUT => 15
        ]);
        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            throw new RuntimeException('Empty response from the Google Places API. ' . $curlError);
        }

        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new RuntimeException('Unreadable response from the Google Places API: ' . $raw);
        }

        if (isset($json['error'])) {
            $err = $json['error'];
            $code = $err['code'] ?? 'unknown';
            $msg = $err['message'] ?? 'No message';
            $status = $err['status'] ?? '';
            throw new RuntimeException("Google API error {$code} ({$status}): {$msg}");
        }

        return (array)($json['reviews'] ?? []);
    }
}
