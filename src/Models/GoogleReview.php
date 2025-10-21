<?php

namespace anytech\googlereviews\Models;

use SilverStripe\ORM\DataObject;

class GoogleReview extends DataObject {
    private static $table_name = 'GoogleReview';

    private static $db = [
        'GoogleReviewID' => 'Varchar(255)',
        'AuthorName' => 'Varchar(255)',
        'AuthorURL' => 'Varchar(512)',
        'AuthorPhotoURL' => 'Varchar(512)',
        'Rating' => 'Int',
        'Text' => 'Text',
        'RelativeTime' => 'Varchar(255)',
        'TimeUnix' => 'Int',
        'Language' => 'Varchar(10)',
        'PlaceID' => 'Varchar(255)'
    ];

    private static $indexes = [
        'GoogleReviewID' => true,
        'PlaceID' => true
    ];

    private static $default_sort = '"TimeUnix" DESC';

    private static $summary_fields = [
        'AuthorName',
        'Rating',
        'RelativeTime',
        'ShortText' => 'Text'
    ];

    private static $has_one = [
        'Element' => \anytech\googlereviews\Elements\GoogleReview::class
    ];

    private static $cascade_deletes = [
        'Element'
    ];

    public function getShortText() {
        $t = trim((string)$this->Text);
        return mb_strlen($t) > 80 ? mb_substr($t, 0, 80) . '…' : $t;
    }
}
