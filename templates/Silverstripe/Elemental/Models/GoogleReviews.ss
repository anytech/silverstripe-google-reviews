<div class="element-google-reviews">
  <% if $Title %><h3>$Title</h3><% end_if %>
  <div class="reviews-grid">
    <% loop $Reviews %>
      <article class="review-card">
        <header class="review-head">
          <% if $Top.Element.ShowAvatar && $AuthorPhotoURL %>
            <img class="avatar" src="$AuthorPhotoURL" alt="$AuthorName.ATT">
          <% end_if %>
          <div class="meta">
            <strong class="author"><% if $AuthorURL %><a href="$AuthorURL" rel="nofollow noopener" target="_blank">$AuthorName</a><% else %>$AuthorName<% end_if %></strong>
            <% if $Top.Element.ShowRelativeTime && $RelativeTime %>
              <span class="when">$RelativeTime</span>
            <% end_if %>
            <span class="stars" aria-label="Rating $Rating out of 5">
              <% loop 1..$Rating %>★<% end_loop %><% loop 1..${5-$Rating} %>☆<% end_loop %>
            </span>
          </div>
        </header>
        <p class="text">$Text.XML</p>
      </article>
    <% end_loop %>
  </div>
  <link rel="stylesheet" href="$resourceURL('anytech/silverstripe-google-reviews:client/css/google-reviews.css')">
</div>
