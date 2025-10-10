# anytech/silverstripe-google-reviews

Elemental block for **SilverStripe 5** to import and display Google Reviews using the **Google Places API v1**.

---

## Features
- Imports Google Reviews automatically via a CronTask
- Displays reviews in a configurable Elemental block
- Caches API responses using `Psr\SimpleCache`
- Controls for minimum star rating, newest/highest order, and result limit
- SiteConfig fields for API key, Place ID, language, and rating filter

---

## Requirements
- `silverstripe/framework` ^5  
- `dnadesign/silverstripe-elemental` ^5  
- `silverstripe/crontask` ^3  

---

## Installation
    composer require anytech/silverstripe-google-reviews

Rebuild and flush:
    vendor/bin/sake dev/build flush=all

---

## Configuration
Already included `_config/anytech-googlereviews.yml`:

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

---

## Setup
1. In CMS, go to **Settings → Google Reviews**.  
2. Enter:
   - Places API Key
   - Place ID
   - Language (e.g. `en`, `en-CA`)
   - Optional: Minimum rating to import
3. Enable **Places API (New)** in Google Cloud:  
   https://console.developers.google.com/apis/api/places.googleapis.com/overview

---

## Cron Sync
Task class: `anytech\googlereviews\Tasks\GoogleReviewsSyncTask`

Run manually:
    vendor/bin/sake dev/tasks/GoogleReviewsSyncTask

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
Create `themes/<yourtheme>/templates/GoogleReviews.ss`:

<div class="element-google-reviews">
  <% if $ShowTitle %><h3>$Title</h3><% end_if %>

  <div class="reviews-grid">
    <% loop $Reviews %>
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
              <% if $Rating >= 1 %>★<% end_if %>
              <% if $Rating >= 2 %>★<% end_if %>
              <% if $Rating >= 3 %>★<% end_if %>
              <% if $Rating >= 4 %>★<% end_if %>
              <% if $Rating >= 5 %>★<% end_if %>

              <% if $Rating < 5 %>
                <% if $Rating < 5 %><% if $Rating < 4 %><% if $Rating < 3 %><% if $Rating < 2 %><% if $Rating < 1 %><% end_if %><% end_if %><% end_if %><% end_if %><% end_if %>
              <% end_if %>
            </span>
          </div>
        </header>

        <p class="text">$Text.XML</p>
      </article>
    <% end_loop %>
  </div>
  <link rel="stylesheet" href="$resourceURL('anytech/silverstripe-google-reviews:client/css/google-reviews.css')">
</div>

Optional CSS:
- `client/css/google-reviews.css` (exposed via `extra.expose`)

---

## Error Handling
If Google returns an error JSON block, the sync task echoes the code, status, and message for debugging. Example:

    {
      "error": {
        "code": 403,
        "status": "PERMISSION_DENIED",
        "message": "Places API (New) has not been used in this project or it is disabled…"
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
      Tasks/
        GoogleReviewsSyncTask.php
    client/
      css/
        google-reviews.css
    _config/
      anytech-googlereviews.yml
    composer.json
    README.md

---

## License
MIT License — © Kayne Middleton

### MIT summary
- Permissive. Use, modify, distribute, and sublicense commercially.  
- Must keep copyright and license notice.  
- Provided “as is,” no warranty or liability.
