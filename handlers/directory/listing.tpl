<h2>Directory List</h2>
<table>
{foreach from=$records.dir item=r}
	<tr>
		<td><a href="{$r.url}">{$r.name}</a></td>
		<td>{$r.mtime|date_format}</td>
	</tr>
{foreachelse}
No directories
{/foreach}
</table>

