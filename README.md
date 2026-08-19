# anytech/silverstripe-google-reviews

Elemental block for **SilverStripe 6** to import and display Google Reviews using the **Google Places API v1**.

SilverStripe 5 support lives on the `ss5` branch (tagged `2.x`).

---

## Features
- Imports Google Reviews automatically via a CronTask
- Displays reviews in a configurable Elemental block
- Controls for minimum star rating, newest/highest order, and result limit
- SiteConfig fields for API key, Place ID, language, and rating filter

---

## Requirements
- `php` ^8.3
- `silverstripe/framework` ^6.1
- `silverstripe/siteconfig` ^6
- `dnadesign/silverstripe-elemental` ^6
- `silverstripe/crontask` ^4

---

## Installation
    composer require anytech/silverstripe-google-reviews

Rebuild and flush:
    vendor/bin/sake db:build --flush

---

## Configuration
Already included `_config/config.yml`:

    ---
    Name: anytech-googlereviews
    After:
    - '#coreconfig'
    - '#elemental'
    ---
    SilverStripe\SiteConfig\SiteConfig:
      extensions:
      - anytech\googlereviews\Extensions\SiteConfigGoogleReviewsExtension

---

## Setup
1. In CMS, go to **Settings -> Google Reviews**.
2. Enter:
   - Places API Key
   - Place ID
   - Language (e.g. `en`, `en-CA`)
   - Optional: Minimum rating to import
3. Enable **Places API (New)** in Google Cloud:
   https://console.developers.google.com/apis/api/places.googleapis.com/overview

---

## Sync
Task class: `anytech\googlereviews\Tasks\GoogleReviewsSyncTask`

Run manually from the CLI:
    vendor/bin/sake tasks:google-reviews-sync

Or in the browser:
    /dev/tasks/google-reviews-sync

The task also implements `SilverStripe\CronTask\Interfaces\CronTask`, so it is picked up
automatically by `silverstripe/crontask`. Run the scheduler once a minute from the system crontab:

    * * * * * /usr/bin/php /path/to/site/current/vendor/bin/sake cron-task

Default schedule is 03:00 daily. Override it in your project YAML:

    anytech\googlereviews\Tasks\GoogleReviewsSyncTask:
      schedule: '0 */6 * * *'

---

## Elemental Block
Class: `anytech\googlereviews\Elements\GoogleReview`

### CMS Options
- Max reviews to show
- Minimum stars
- Order: Newest / Highest Rated
- Show avatar
- Show relative time

### Template
The module ships `templates/anytech/googlereviews/Elements/GoogleReview.ss`. To override it,
create `themes/<yourtheme>/templates/GoogleReviews.ss`:

    <div class="element-google-reviews">
      <% if $ShowTitle %><h3>$Title</h3><% end_if %>

      <div class="reviews-grid">
        <% loop $FilteredReviews %>
          <article class="review-card">
            <header class="review-head">
              <% if $Top.ShowAvatar && $AuthorPhotoURL %>
                <img class="avatar" src="$AuthorPhotoURL" alt="$AuthorName.ATT">
              <% end_if %>

              <div class="meta">
                <strong class="author">
                  <% if $AuthorURL %>
                    <a href="$AuthorURL" rel="nofollow noopener" target="_blank">$AuthorName</a>
                  <% else %>
                    $AuthorName
                  <% end_if %>
                </strong>

                <% if $Top.ShowRelativeTime && $RelativeTime %>
                  <span class="when">$RelativeTime</span>
                <% end_if %>

                <span class="stars" aria-label="Rating $Rating out of 5">
                  <% if $Rating >= 1 %>*<% end_if %>
                  <% if $Rating >= 2 %>*<% end_if %>
                  <% if $Rating >= 3 %>*<% end_if %>
                  <% if $Rating >= 4 %>*<% end_if %>
                  <% if $Rating >= 5 %>*<% end_if %>
                </span>
              </div>
            </header>

            <p class="text">$Text.XML</p>
          </article>
        <% end_loop %>
      </div>
    </div>

Optional CSS:
- `client/css/google-reviews.css` (exposed via `extra.expose`)

---

## Error Handling
The sync task fails with a non-zero exit code and prints the Google error code, status and message.
Example cause:

    {
      "error": {
        "code": 403,
        "status": "PERMISSION_DENIED",
        "message": "Places API (New) has not been used in this project or it is disabled"
      }
    }

Fix by enabling the API and retrying after propagation.

---

## Directory Structure
    src/
      Elements/
        GoogleReview.php
      Extensions/
        SiteConfigGoogleReviewsExtension.php
      Models/
        GoogleReview.php
      Services/
        GoogleReviewsClient.php
      Tasks/
        GoogleReviewsSyncTask.php
    client/
      css/
        google-reviews.css
    templates/
      anytech/googlereviews/Elements/
        GoogleReview.ss
    _config/
      config.yml
    composer.json
    README.md

---

## License
MIT License - Kayne Middleton

### MIT summary
- Permissive. Use, modify, distribute, and sublicense commercially.
- Must keep copyright and license notice.
- Provided "as is", no warranty or liability.
