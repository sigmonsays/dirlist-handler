<?
	require_once("lib/lib_directory_listing.php");

	class Directory_Content_Handler {

		public function __construct($path) {
			$this->path = $path;
			$this->cache_dir = realpath(dirname(__FILE__).'/../cache');

			$this->url_prefix = '/dirlist-preview';

		}

		public function abort($msg) {
			echo "$msg\n";
			die;
		}
		public function run() {

			list($content_url, $type, $path) = explode(":", $_SERVER['SCRIPT_URL'], 3);

			if (!file_exists($path)) self::abort('no cache');

			$cache_key = self::build_cache_key($path, $type);

			$cache_basename = $cache_key.'.png';
			$cache_file = $this->cache_dir.'/'.$cache_basename;
			$cache_dir = dirname($cache_file);

			if (!file_exists($cache_dir)) {
				if (!mkdir($cache_dir, 0700, TRUE)) {
					self::abort("unable to mkdir $cache_dir");
					return FALSE;
				}
			}

			if (file_exists($cache_file)) {
				$success = TRUE;
			} else {
				$success = self::write_cache($cache_file, $path, $type);
			}


			if ($success) {

				/**
				 *
				$bytes = filesize($cache_file);
				header("Content-Type: image/png");
				header("Content-Length: $bytes");
				echo file_get_contents($cache_file);
				 *
				**/
				$cache_url = $this->url_prefix."/cache/".$cache_basename;

				header("Location: $cache_url");
			} else {

				self::abort("unable to write cache $path ($cache_file)");
			}
		}



		public function build_cache_key($path, $type) {
			$ret = $type.'/';
			$md5 = md5($path); // length 32
			$len = 8;
			$l = strlen($md5);
			for($i=0; $i<$l; $i+=$len) {
				$ret .= substr($md5, $i, $len);
				if ($i + $len < $l) $ret .= '/';
			}
			return $ret;
		}

		public function write_cache($cache_file, $path, $type) {
			$retval = FALSE;

			list($maj, $min) = Directory_Listing::get_mime_type($path);
			$mime_types = array(
				$maj.'/'.$min => $maj.'/'.$min
			);

			$dh = new Directory_Listing_Handler($path);

			$handlers = $dh->load_handlers_from_mime($mime_types);

			$handler = array_pop($handlers);

			if (!$handler) {
				self::abort("No handlers for $path :".print_r(compact('class_name', 'handlers', 'mime_types'), 1));
			}

			//echo "handler: $maj - $min - $path\n";

			if (is_callable(array($handler, 'write_cache'))) {
				$retval = $handler->write_cache($cache_file, $path, $type);
			} else {
				self::abort("no cache handler for $path");
			}

			if (!$retval) {
				// echo "warning: handler failed to write cache\n";
			}

			return $retval;
		}


	}

?>
