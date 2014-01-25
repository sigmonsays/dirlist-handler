<h2>Default Listing</h2>
<table>

{foreach from=$records.dir item=r}
	<tr>
		<td><a href="{$r.url}">{$r.name}</a></td>
	</tr>
{/foreach}

{foreach from=$records.file item=r}
	<tr>
		<td><a href="{$r.url}">{$r.name}</a></td>
		<td>{$r.mime_major}/{$r.mime_minor}</td>
		<td>{$r.mtime}</td>
		<td>{$r.size}</td>
	</tr>
{/foreach}


</table>

