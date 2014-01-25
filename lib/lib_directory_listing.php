<?
	require_once("Smarty/libs/Smarty.class.php");



	class Directory_Listing {
		static $template_dir = "views/"; 
		static $smarty_instance;

		function get_smarty() {
			if (!self::$smarty_instance) {
				$smarty = new Smarty();
				$smarty->template_dir = self::$template_dir;
				self::$smarty_instance = $smarty;	
			}
			return self::$smarty_instance;
		}

		public function __construct() {
			$this->smarty = self::get_smarty();
		}

		function get_listing($path) {
			$retval = array();

			$file_types = array();

			$d = opendir($path);
			if (!$d) return NULL;

			$retval['path'] = $path;

			while ($f = readdir($d)) {
				if ($f == "." || $f == "..") continue;
				$file_type = filetype("$path/$f");

				list($mime_major, $mime_minor) = self::get_mime_type("$path/$f");

				$detail = array(
					'name' => $f,
					'mime_major' => $mime_major,
					'mime_minor' => $mime_minor,
				);

				$st = stat("$path/$f");
				if (!$st) $st = array();
				foreach($st AS $k => $v) {
					if (is_int($k)) continue;
					$detail[$k] = $v;
				}

				if ($retval[$file_type]) {
					$retval[$file_type][] = $detail;
				} else {
					$file_types[] = $file_type;
					$retval[$file_type][] = $detail;
				}

			}

			// sort each type
			foreach($file_types AS $file_type) {
				usort($retval[$file_type], array($this, '_default_sort'));
			}

			return $retval;
		}

		private function _default_sort($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		}

		public static function get_mime_type($file) {
			$dirty_mime_type = exec("file -bi ".escapeshellarg($file));
			$retval = explode("/", $dirty_mime_type);
			$c = count($retval);
			for($i=0; $i<$c; $i++) {
				$p = strpos($retval[$i], ';');
				if ($p !== FALSE) {
					$retval[$i] = substr($retval[$i], 0, $p);
				}
			}
			return $retval;
		}

	}
?>
