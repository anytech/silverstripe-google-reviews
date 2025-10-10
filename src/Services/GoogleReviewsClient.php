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
        if ($cached) return $cached;

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
        if (!$raw) return [];

        $json = json_decode($raw, true);
        $reviews = (array)($json['reviews'] ?? []);
        if ($cache) $cache->set($cacheKey, $reviews, 300);
        return $reviews;
    }
}
