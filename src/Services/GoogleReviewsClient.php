<?php

namespace anytech\googlereviews\Services;

use SilverStripe\Core\Injector\Injector;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\SiteConfig\SiteConfig;

class GoogleReviewsClient {
    const ENDPOINT = 'https://places.googleapis.com/v1/places/';

    public function fetchReviews(): array {
        $cfg = SiteConfig::current_site_config();
        $apiKey = trim((string)$cfg->GooglePlacesAPIKey);
        $placeID = trim((string)$cfg->GooglePlaceID);
        $lang = $cfg->GoogleReviewsLanguage ?: 'en';
        if (!$apiKey || !$placeID) return [];

        $cache = Injector::inst()->get(CacheInterface::class . '.appGoogleReviews');
        $cacheKey = 'reviews-' . md5($placeID . '-' . $lang);
        $cached = $cache ? $cache->get($cacheKey) : null;
        if ($cached) {
            echo '<p>Using cached reviews - Nothing has changed</p>';
            return $cached;
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
        curl_close($ch);

        if (!$raw) {
            echo "Empty response from Google API.";
            return [];
        }

        $json = json_decode($raw, true);

        if (isset($json['error'])) {
            $err = $json['error'];
            $code = $err['code'] ?? 'unknown';
            $msg = $err['message'] ?? 'No message';
            $status = $err['status'] ?? '';
            echo "<strong>Google API error {$code} ({$status}):</strong> {$msg}<br>";

            // Optional: print the full error for debugging
            if (isset($err['details'])) {
                echo '<pre>' . print_r($err['details'], true) . '</pre>';
            }

            return [];
        }

        $reviews = (array)($json['reviews'] ?? []);
        if ($cache) $cache->set($cacheKey, $reviews, 300);
        return $reviews;
    }
}
