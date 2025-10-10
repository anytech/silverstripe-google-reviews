# anytech/silverstripe-google-reviews

Elemental block for **SilverStripe 5** to import and display Google Reviews using the **Google Places API v1**.

---

## Features

- Imports Google Reviews automatically with a CronTask  
- Displays reviews in a configurable Elemental block  
- Caches API responses using `Psr\SimpleCache`  
- Lets you set minimum star ratings, order (newest or highest), and number of reviews  
- Configurable in SiteConfig (API key, Place ID, language, and rating filter)

---

## Requirements

- `silverstripe/framework` ^5  
- `dnadesign/silverstripe-elemental` ^5  
- `silverstripe/crontask` ^3  

---

## Installation

```bash
composer require anytech/silverstripe-google-reviews
Then rebuild your database and flush the cache:

bash
Copy code
vendor/bin/sake dev/build flush=all
Configuration
Already included _config/anytech-googlereviews.yml:

yaml
Copy code
---
Name: anytech-googlereviews
After:
  - '#coreconfig'
  - '#elementalconfig'
---
SilverStripe\SiteConfig\SiteConfig:
  extensions:
    - anytech\googlereviews\Extensions\SiteConfigGoogleReviewsExtension

SilverStripe\Dev\CronTask\CronTaskController:
  tasks:
    - anytech\googlereviews\Tasks\GoogleReviewsSyncTask

SilverStripe\Core\Injector\Injector:
  Psr\SimpleCache\CacheInterface.appGoogleReviews:
    factory: SilverStripe\Core\Cache\CacheFactory
    constructor:
      namespace: "appGoogleReviews"
Setup
In the CMS, go to Settings → Google Reviews.

Enter:

Places API Key

Place ID

Language (e.g. en or en-CA)

Optional: Minimum Rating to Import

Enable the Google Places API in your Google Cloud Console project.
Activate the API here

Cron Sync
The task anytech\googlereviews\Tasks\GoogleReviewsSyncTask runs automatically via CronTask to fetch new reviews.

Run it manually if required:

bash
Copy code
vendor/bin/sake dev/tasks/GoogleReviewsSyncTask
Elemental Block
Class: anytech\googlereviews\Elements\GoogleReview

CMS Options
Maximum reviews to show

Minimum star filter

Order: Newest or Highest Rated

Toggle avatar and relative time display

Template
Create:
themes/<yourtheme>/templates/anytech/googlereviews/Elements/GoogleReview.ss

ss
Copy code
<div class="google-reviews">
  <% loop $Reviews %>
    <div class="review">
      <div class="review-header">
        <% if $ShowAvatar && $ProfilePhotoUrl %>
          <img src="$ProfilePhotoUrl" alt="$AuthorName" />
        <% end_if %>
        <strong>$AuthorName</strong>
        <span class="rating">$Rating ★</span>
      </div>
      <p>$Text</p>
      <% if $ShowRelativeTime %>
        <small>$RelativeTimeDescription</small>
      <% end_if %>
    </div>
  <% end_loop %>
</div>
Include the optional CSS file:
client/css/google-reviews.css

Troubleshooting
API Error Example
If you see a JSON error like:

css
Copy code
{
  "error": {
    "code": 403,
    "message": "Places API (New) has not been used in this project or it is disabled...",
    "status": "PERMISSION_DENIED"
  }
}
Enable the API here:
https://console.developers.google.com/apis/api/places.googleapis.com/overview

Then wait several minutes and retry.

Manual Debugging
The sync task will echo any Google API errors found in the JSON response for easier troubleshooting.

Directory Structure
css
Copy code
src/
 ├── Elements/
 │   └── GoogleReview.php
 ├── Extensions/
 │   └── SiteConfigGoogleReviewsExtension.php
 ├── Models/
 │   └── GoogleReview.php
 └── Tasks/
     └── GoogleReviewsSyncTask.php
client/
 └── css/
     └── google-reviews.css
_config/
 └── anytech-googlereviews.yml
License
MIT License
© Kayne Middleton — Anytech.ca