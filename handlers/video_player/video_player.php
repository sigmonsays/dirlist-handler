<?
	class Directory_Listing_Video_Player_Handler extends Directory_Listing_Handler {

		public function handles_mime_type($mime_type) {
			$retval = FALSE;
			list($mime_major, $mime_minor) = explode("/", $mime_type);
			if ($mime_major == 'video') {
				$retval = TRUE;
			}
			return $retval;
		}

		public function get_title() {
			return "Videos";
		}

		public function render_file_partial($record) {
			$this->set("image_url", $this->generate_url('preview', $this->path, $record['name']));


			$player_swf_url = 'http://'.$_SERVER['SERVER_NAME'].$this->generate_url('static', 'mediaplayer/mediaplayer.swf');
			$this->set("player_swf_url", $player_swf_url);


			$flv_video_url = $this->generate_url('content', $this->path.'/'.$record['name']);
			$this->set("flv_video_url", $flv_video_url);

			// test with static content
			// $this->set("flv_video_url", $this->generate_url('static', 'mediaplayer/afraid.flv'));
		}

		static $video_types = array(
			'.avi' => TRUE,
		);
		private function mplayer_identify($path) {
			$cmd = "mplayer -ao null -vo null -identify -frames 0 ".escapeshellarg($path). " 2>/dev/null | grep ^ID_";
			$output = array();
			exec($cmd, $lines);
			$props = array();
			foreach($lines AS $line) {
				$p = strpos($line, '=');
				if ($p === FALSE) continue;
				$k = substr($line, 0, $p);
				$props[$k] = substr($line, $p + 1);
			}
			return $props;
		}

		public function write_cache($cache_file, $path, $type) {

			if ($type == 'thumbnail') {
				$retval = $this->write_cache_thumbnail($cache_file, $path, $type);

			} else if ($type == 'embed_video') {
				$retval = $this->write_cache_embed_video($cache_file, $path, $type);
			} else {
				die("unhandled cache type: $type");
			}

			return $retval;
		}
		public function write_cache_embed_video($cache_file, $path, $type) {

			$retval = FALSE;

			$command  = "ffmpeg -i ".escapeshellarg($path) ." -y -ar 44100 -ab 64 -f flv -s 320x240 - 2>/dev/null ";

			$fp = popen($command, 'rb');

			if (!$fp) exit;

			while ($buf = fread($fp, 4096)) {
				echo $buf;
			}

			pclose($fp);

			exit;

			return $retval;
		}

		public function write_cache_thumbnail($cache_file, $path, $type) {

			if (!file_exists($tmp_dir = "/tmp/dirlist-preview/video")) {
				mkdir($tmp_dir, 0755, TRUE);
			}

			$img = $this->load_handler("Directory_Listing_Image_Thumbnail_Handler");

			$cwd = getcwd();
			chdir($tmp_dir);

			$files = glob('*.png');
			foreach($files AS $file) {
				unlink($file);
			}

			$video_info = self::mplayer_identify($path);
			$length = intval($video_info['ID_LENGTH']);

			if ($length == 0) {
				$seek = 5; // try 1 seconds --  what else can I do?
			} else {
				$seek = intval($length * 0.01);
			}

			$cmd = 'mplayer -nosound -vo png:z=9 -frames 2 '
				. '-ss '.$seek
				. ' '.escapeshellarg($path);

			exec($cmd, $output);

			$files = glob('*.png');
			$frame = array_pop($files);

			if (!$frame) {
				die("no frame found; command: $cmd");
			}

			$retval = $img->create_thumbnail($frame, $cache_file);

			// cleanup
			$files = glob('*.png');
			foreach($files AS $file) {
				unlink($file);
			}

			return $retval;
		}

	}
?>
