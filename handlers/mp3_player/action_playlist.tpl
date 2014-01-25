<?xml version="1.0" encoding="UTF-8"?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
<trackList>

	{foreach from=$files item=file}
		<track>
			<title>{$file.basename}</title>
			<location>{$song_url}</location>
			<cover>{$album_cover}</cover>
		</track>
	{/foreach}


</trackList>
</playlist>

