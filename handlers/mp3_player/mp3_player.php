<?
	class Directory_Listing_Mp3_Player_Handler extends Directory_Listing_Handler {

		public function handles_mime_type($mt) {
			list($mime_major, $mime_minor) = explode("/", $mt);
			if ($mime_major == 'audio' ) return TRUE;
			return FALSE;
		}

		public function handles_file($record) {
			$ext = strtolower(strrchr($record['name'], '.'));
			return ($ext == '.mp3');
		}

		public function get_title() {
			return "MP3 Audio";
		}


		public function action_playlist() {
			header("Content-Type: text/plain");

			$dh = new Directory_Listing();
			$ls = $dh->get_listing($this->path);

			// print_r($ls);
			$album_cover = NULL;
			foreach($ls['file'] AS $i => $record) {
				if ($record['mime_major'] == 'image' && $record['name'][0] != '.') {
					$album_cover = $record['name'];
					break;
				}
			}
			$this->set("album_cover", $album_cover);

			$prefix = 'http://'.$_SERVER['SERVER_NAME'].'/'.$_SERVER['SCRIPT_URL'];
			$this->set("prefix", $prefix);

			// get songs in directory
			$tmp_ls = glob($this->path.'/*.mp3');
			foreach($tmp_ls AS $filename) {

				$song_url =  $prefix.basename($filename);

				$file = array(
					'song_url' => $song_url,
					'basename' => basename($filename),
				);

				$files[] = $file;
			}




			$this->set("files", $files);


			exit;
		}


		public function render_index() {

			$swf_playlist_url = '?action=playlist';
			$this->set("swf_playlist_url", $swf_playlist_url);

			$swf_url = $this->generate_url('static', 'xspf_player/xspf_player.swf');
			$swf_url .= '?playlist_url='.$playlist_url;
			$this->set("swf_playlist", $swf_url);

		}



		public function render_file_partial($record) {

			$path = $this->generate_url('directory', $this->path, $record['name']);
			$this->set("url", $path);


			$swf_url = $this->generate_url('static', 'button_player/musicplayer.swf');
			$swf_url .= '?song_url='.$path;
			$this->set("swf_url", $swf_url);

		}

	}
?>
