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