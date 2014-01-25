<h2>Images</h2>
<table>
{foreach from=$records.file item=r}
	{if $r.img_url}
		<tr>
			<td><a title="{$r.name}" href="{$r.name}"><img src="{$r.img_url}" border=0 /></a></td>
		</tr>
	{/if}
{/foreach}

</table>

