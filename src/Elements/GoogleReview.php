<?php

namespace anytech\googlereviews\Elements;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\SiteConfig\SiteConfig;
use anytech\googlereviews\Models\GoogleReview as ReviewModel;

class GoogleReview extends BaseElement {
    private static $table_name = 'ElementGoogleReview';
    private static $icon = 'font-icon-circle-star';
    private static $singular_name = 'Google Reviews';
    private static $plural_name = 'Google Reviews';

    private static $db = [
        'LimitReviews' => 'Int',
        'MinStars' => 'Int',
        'ShowAvatar' => 'Boolean',
        'ShowRelativeTime' => 'Boolean',
        'OrderBy' => 'Enum("Newest,HighestRated","Newest")'
    ];

    public function getType() {
        return 'Google Reviews';
    }

    public function populateDefaults() {
        $this->LimitReviews = 6;
        $this->MinStars = 0;
        $this->ShowAvatar = true;
        $this->ShowRelativeTime = true;
        $this->OrderBy = 'Newest';
        parent::populateDefaults();
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Settings', [
            NumericField::create('LimitReviews', 'Max reviews to show'),
            DropdownField::create('MinStars', 'Minimum stars', [
                0 => 'No filter',
                1 => '1★',
                2 => '2★',
                3 => '3★',
                4 => '4★',
                5 => '5★'
            ]),
            DropdownField::create('OrderBy', 'Order', [
                'Newest' => 'Newest',
                'HighestRated' => 'Highest rated'
            ])
        ]);
        return $fields;
    }

    public function Reviews() {
        $place = SiteConfig::current_site_config()->GooglePlaceID;
        $list = ReviewModel::get()->filter('PlaceID', $place);
        if ($this->MinStars > 0) $list = $list->filter('Rating:GreaterThanOrEqual', $this->MinStars);
        if ($this->OrderBy === 'HighestRated') $list = $list->sort(['Rating' => 'DESC', 'TimeUnix' => 'DESC']);
        return $list->limit($this->LimitReviews ?: 6);
    }

    public function forTemplate($holder = true) {
        return parent::forTemplate($holder);
    }

    public function getRenderTemplates($holder = false) {
        return array_merge(['GoogleReviews'], parent::getRenderTemplates($holder));
    }
}
