<?php

namespace anytech\googlereviews\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;

class SiteConfigGoogleReviewsExtension extends DataExtension {
    private static $db = [
        'GooglePlacesAPIKey' => 'Varchar(255)',
        'GooglePlaceID' => 'Varchar(255)',
        'GoogleReviewsLanguage' => 'Varchar(10)',
        'GoogleReviewsMinRating' => 'Int'
    ];

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldsToTab('Root.GoogleReviews', [
            LiteralField::create('GRHelp', '<p>Enter your Google Places API key and Place ID.</p>'),
            TextField::create('GooglePlacesAPIKey', 'Places API Key'),
            TextField::create('GooglePlaceID', 'Place ID'),
            TextField::create('GoogleReviewsLanguage', 'Language (e.g. en, en-CA)')->setValue('en'),
            DropdownField::create('GoogleReviewsMinRating', 'Minimum rating to import', [
                1 => '1★',
                2 => '2★',
                3 => '3★',
                4 => '4★',
                5 => '5★'
            ])->setEmptyString('None')->setValue(0)
        ]);
    }
}
