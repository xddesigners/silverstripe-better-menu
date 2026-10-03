<%-- Better Menu: grouped, collapsible CMS menu. Overrides only this include; the rest of the
     native sidebar (collapse toggle + version indicator) is untouched. Groups with 2+ present
     items become collapsible parents; single-item groups and ungrouped items render as rows. --%>
<ul class="cms-menu__list">
	<% loop $GroupedMainMenu %>
		<% if $Children %>
			<li class="$LinkingMode $FirstLast children cms-menu__group<% if $LinkingMode != 'link' %> opened<% end_if %>" id="Menu-$Code" title="$Title.ATT">
				<a href="$Link" $AttributesHTML aria-label="$Title">
					<% if $IconClass %>
						<span class="menu__icon $IconClass" aria-hidden="true"></span>
					<% else %>
						<span class="menu__icon font-icon-menu-modeladmin" aria-hidden="true"></span>
					<% end_if %>
					<span class="text">$Title</span>
				</a>
				<button type="button" class="cms-menu__group-toggle" aria-label="<%t XD\\BetterMenu.Toggle 'Toggle group' %>">
					<i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
				</button>
				<ul class="cms-menu__group-items">
					<% loop $Children %>
						<li class="$LinkingMode $FirstLast" id="Menu-$Code" title="$Title.ATT">
							<a href="$Link" $AttributesHTML aria-label="$Title">
								<% if $IconClass %>
									<span class="menu__icon $IconClass" aria-hidden="true"></span>
								<% else_if $HasCSSIcon %>
									<span class="menu__icon icon icon-16 icon-{$Icon}" aria-hidden="true">&nbsp;</span>
								<% else %>
									<span class="menu__icon font-icon-menu-modeladmin" aria-hidden="true"></span>
								<% end_if %>
								<span class="text">$Title</span>
							</a>
						</li>
					<% end_loop %>
				</ul>
			</li>
		<% else %>
			<li class="$LinkingMode $FirstLast" id="Menu-$Code" title="$Title.ATT">
				<a href="$Link" $AttributesHTML aria-label="$Title">
					<% if $IconClass %>
						<span class="menu__icon $IconClass" aria-hidden="true"></span>
					<% else_if $HasCSSIcon %>
						<span class="menu__icon icon icon-16 icon-{$Icon}" aria-hidden="true">&nbsp;</span>
					<% else %>
						<span class="menu__icon font-icon-menu-modeladmin" aria-hidden="true"></span>
					<% end_if %>
					<span class="text">$Title</span>
				</a>
			</li>
		<% end_if %>
	<% end_loop %>
</ul>
