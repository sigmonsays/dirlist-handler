<?
	class Directory_Listing_Image_Thumbnail_Handler extends Directory_Listing_Handler {

		public function handles_mime_type($mt) {
			list($mime_major, $mime_minor) = explode("/", $mt);
			if ($mime_major == 'image') return TRUE;
			return FALSE;
		}

		public function handles_file($record) {
			if ($record['mime_major'] == 'image') return TRUE;
			return FALSE;
		}

		public function get_title() {
			return "Images";
		}

		public function render_header() {
			$html = "<div>";
			return $html;
		}
		public function render_footer() {
			$html = "</div>";
			return $html;
		}

		public function render_file_partial($record) {
			$url = $this->generate_url('directory', $this->path, $record['name']);
			$this->set("path", $url);

			$img_url = $this->generate_url('preview', $this->path, $record['name']);
			$this->set("img_url", $img_url);
		}

		static $image_types = array(
			'.jpg' => 'imagecreatefromjpeg',
			'.jpeg' => 'imagecreatefromjpeg',
			'.bmp' => 'imagecreatefromwbmp',
			'.gif' => 'imagecreatefromgif',
			'.png' => 'imagecreatefrompng',
			'.xbm' => 'imagecreatefromxbm',
			'.xpm' => 'imagecreatefromxpm',
		);
		public function load_image($path) {
			$ext = strtolower(strrchr($path, '.'));
			$image_create_func = self::$image_types[$ext];

			if (!function_exists($image_create_func)) {
				return NULL;
			}

			$imgres = $image_create_func($path);

			return $imgres;
		}

		public function write_cache($cache_file, $path) {
			return $this->create_thumbnail($path, $cache_file);
		}

		/**
		 * create a 300x300 thumbnail of $path saved at $destination
		 */
		public function create_thumbnail($path, $dest) {

				$imgres = self::load_image($path);

				if (!$imgres) {
					// echo "unable to load: $path\n";
					return NULL;
				}

				$img_info = getimagesize($path, $binary_info);

				list($width, $height) = $img_info;
				$mime_type = $img_info['mime'];

				$thumb_width = 300;
				$thumb_height = 300;

				$dst_x = $dst_y = 0;
				$src_x = $src_y = 0;

				$imgthumbres = imagecreatetruecolor($thumb_width, $thumb_height);
				imagecopyresampled($imgthumbres, $imgres, 
					$dst_x, $dst_y, 
					$src_x, $src_y, 
					$thumb_width /* dst_w */, $thumb_height /* dst_h */, 
					$width, /* src_w */ $height /* src_h */
				);
				imagepng($imgthumbres, $dest);
				imagedestroy($imgthumbres);
				imagedestroy($imgres);

				return TRUE;
		}

	}
?>
