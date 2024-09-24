<%-- Override this template in your project or theme as required --%>
<div class="content-element__content<% if $Style %> $StyleVariant<% end_if %>">
    <% if $ShowTitle && $Title %>
        <h2 class="content-element__title">{$Title}</h2>
    <% end_if %>

    <div class="search-outer">

        <div class="search-form">
            {$SearchForm}
        </div>

        <div class="search-results">
            <% if $SearchResults %>
                <% loop $SearchResults %>
                    {$Me}
                <% end_loop %>
            <% else %>
                <p>No results</p>
            <% end_if %>
        </div>

    </div>

</div>
