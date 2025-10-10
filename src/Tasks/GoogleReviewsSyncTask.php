<?php

namespace anytech\googlereviews\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;
use anytech\googlereviews\Services\GoogleReviewsClient;
use anytech\googlereviews\Models\GoogleReview;

class GoogleReviewsSyncTask extends BuildTask {
    private static $segment = 'google-reviews-sync';
    protected $title = 'Google Reviews Sync';
    protected $description = 'Fetches reviews from Google Places and stores them as DataObjects.';

    public function run($request) {
        $cfg = SiteConfig::current_site_config();
        $min = (int)($cfg->GoogleReviewsMinRating ?: 0);

        $client = new GoogleReviewsClient();
        $rows = $client->fetchReviews();

        $count = 0;
        foreach ($rows as $r) {
            $rating = (int)($r['rating'] ?? 0);
            if ($min > 0 && $rating < $min) continue;

            $id = (string)($r['name'] ?? sha1(json_encode($r)));
            if (GoogleReview::get()->filter('GoogleReviewID', $id)->exists()) continue;

            $gr = GoogleReview::create();
            $gr->GoogleReviewID = $id;
            $gr->AuthorName = (string)($r['authorAttribution']['displayName'] ?? '');
            $gr->AuthorURL = (string)($r['authorAttribution']['uri'] ?? '');
            $gr->AuthorPhotoURL = (string)($r['authorAttribution']['photoUri'] ?? '');
            $gr->Rating = $rating;
            $gr->Text = (string)($r['text']['text'] ?? ($r['originalText']['text'] ?? ''));
            $gr->RelativeTime = (string)($r['relativePublishTimeDescription'] ?? '');
            $gr->TimeUnix = isset($r['publishTime']) ? strtotime($r['publishTime']) : time();
            $gr->Language = (string)($r['originalText']['languageCode'] ?? '');
            $gr->PlaceID = (string)$cfg->GooglePlaceID;
            $gr->write();
            $count++;
        }

        echo "Imported: {$count}\n";
    }
}
