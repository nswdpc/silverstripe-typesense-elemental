<%-- Override this template in your project or theme as required --%>
<div class="content-element__content<% if $Style %> $StyleVariant<% end_if %>">
    <% if $ShowTitle && $Title %>
        <h2 class="content-element__title">{$Title}</h2>
    <% end_if %>

    {$SearchForm}

</div>
