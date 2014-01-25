
<h2>

	{foreach from=$nav item=n}
		<a href="{$n.url}">{$n.name}</a>
	{/foreach}
</h2>

<div>

	{foreach from=$handlers item=handler}

		[ <a href="javascript:void(0);" onclick="doTab('{$handler->get_handler_name()}');">{$handler->get_title()}</a> ] 
	{/foreach}

</div>

{foreach from=$main key=k item=content}
	<div id="tab-content-{$k}" style="display: none;">
	{$content}
	</div>
{/foreach}


<script language="javascript">
{literal}
	var previous_tab;
	function doTab(t) {
		var e = document.getElementById('tab-content-'+t);
		if (!e) {
			alert('whoops: tab ' + t + ' not found')
			return;
		}
		if (previous_tab) {
			previous_tab.style.display = 'none'
		}
		e.style.display = ''
		previous_tab = e
	}

{/literal}

	doTab('{$k}')
</script>
