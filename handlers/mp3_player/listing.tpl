


<h3>Track</h3>
<table>
{foreach from=$records.file item=r}

	{if $r.mime_major == 'audio'}
		<tr>
			<td><a href="{$r.url}">{$r.name}</a></td>
			<td>
				<object type="application/x-shockwave-flash" width="17" height="17" data="{$r.swf_url}">
				<param name="movie" value="{$r.swf_url}" />
				</object>
			</td>
		</tr>
	{/if}
{/foreach}
</table>

