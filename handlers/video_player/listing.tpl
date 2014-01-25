<h2>Video</h2>
<table>
{foreach from=$records.file item=r}

	{if $r.image_url}
		<tr>
			<td>
				<a title="{$r.name}" href="{$r.url}"><img src="{$r.image_url}" border="0"></a>
			</td>
			<td>
				<!-- start video player -->
				<embed
				src="{$r.player_swf_url}"
				width="320"
				height="240"
				allowfullscreen="true"
				flashvars="type=flv&height=240&width=320&file={$r.flv_video_url}"
				/>
				<!-- stop video player -->
			</td>

			<td>{$r.mime_major}/{$r.mime_minor}</td>
			<td>{$r.size} bytes </td>
		</tr>
	{/if}
{/foreach}


</table>

