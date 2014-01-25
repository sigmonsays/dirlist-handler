<?
	require_once("Smarty/libs/Smarty.class.php");

	class Directory_Listing_Handler {

		private $records; // an array of files
		public $record_data; // an array data items per record

		private $handlers; // an array of handler instances

		public function get_name() {
			return get_class($this);
		}
		/**
		 * constructor
		 */
		public function __construct($path) {

			$this->path = $path;
			
			$this->handlers = array();						// a list of handlers who we're instantiated


			$this->url_prefix = '/dirlist-preview';

			$this->record_data = array();

			$this->smarty = self::create_smarty();

		}

		static function create_smarty() {
			$smarty = new Smarty();
			$smarty->template_dir = dirname(__FILE__).'/../';
			return $smarty;
		}


		public static function  clean_class_name($class_name) {

			return ereg_replace('/[^a-z0-9_]+/', '', $class_name);
		}

		/**
		 * filter handler
		 * modify array by reference prior to rendering
		 **/
		 public function filter_files(&$files) {

		 	return TRUE;
		 }


		/**
		 * generate a specific url for a given path 
		 * $type
		 * 	directory - a url to open a directory
		 * 	preview - a url to preview content
		 * 	static - a url to static content
		 * 	content - a url to dynamically generated content
		 * $path
		 * $component
		 **/
		 public function generate_url($type, $path, $component = '') {

			if ($type == 'directory') {
				return $component;

			} else if ($type == 'preview') {
				return $this->url_prefix .'/content/:thumbnail:'.$path.'/'.$component;

			} else if ($type == 'static') {
				return $this->url_prefix .'/static/'.$path;

			} else if ($type == 'content') {
				return $this->url_prefix .'/content/:embed_video:'.$path;

			} else {
				return NULL;
			}
		 }

		/**
		 * render default index
		 */
		public function render_index() {
		}
		/**
		 * render default header
		 */
		public function render_header() {
			$html = '<h2>'.$this->get_title().'</h2>';
			return $html;
		}

		/**
		 * return the handler name
		 **/
		public function get_handler_name() {
			$handler_name = strtolower(get_class($this));
			$handler_name = str_replace('directory_listing_', '', $handler_name);
			$handler_name = str_replace('_handler', '', $handler_name);
			return $handler_name;
		}

		public function get_title() {
			$class_name = get_class($this);
			$title = ucwords(str_replace("_", " ", $class_name));
			$title = str_replace('Directory Listing ', '', $title);
			return $title;
		}

		/**
		 * render default footer
		 */
		public function render_footer() {
		}


		/**
		 * render HTML output for a file
		 **/
		public function render_file_partial($file) {
			return "File: ".$file['name']."<br>\n";
		}

		/**
		 * render HTML output for a directory
		 **/
		public function render_dir_partial($dir) {
			return "Dir: ".$dir['name']."<br>\n";
		}

		/** returns true if the given file is handled by the handler
		 **/
		 public function handles_file($record) {

		 	return TRUE;

		 }
		 public function handles_dir($record) {

		 	return TRUE;
		 }

		 /**
		  * test if a given class handler exists
		  **/
		 public function handler_exists($class_name) {
		 	if (empty($class_name)) return FALSE;

			$mod = str_replace('directory_listing_', '', strtolower($class_name));
		 	$mod = str_replace("_handler", "", $mod);  // strip trailing _handler

		 	$filename = "/handlers/$mod/$mod.php";
			$abs_path = realpath(dirname(__FILE__).'/..').$filename;
			if (file_exists($abs_path)) {
				return TRUE;
			}
			return FALSE;
		 }

		 public function get_handler_class_name($filename) {
		 	$c = str_replace("_", " ", basename($filename, '.php'));
			$c = ucwords($c);
			$c = str_replace(" ", "_", $c);
			return 'Directory_Listing_'.$c.'_Handler';
		 }

		 public function get_handler_include_path($class_name) {


		 	if (empty($class_name)) return FALSE;
			$mod = str_replace('directory_listing_', '', strtolower($class_name));
		 	$mod = str_replace("_handler", "", $mod);  // strip trailing _handler

		 	$filename = "handlers/$mod/$mod.php";
			return $filename;
		 }

		 public function load_handler($class_name) {


		 	$filename = $this->get_handler_include_path($class_name);
			if (!file_exists($filename)) throw new Exception("BIGGIE: $class_name :: $filename");
			require_once($filename);

			if (!$this->handlers[$class_name]) {
				$inst = new $class_name($this->path);
				$this->handlers[$class_name] = $inst;
			}
			return $this->handlers[$class_name];
		 }


			/** load a list of handlers available for the given mime types
			 *  major and major/minor formats supported
			 *
			 */
		 public function load_handlers_from_mime($mime_types) {

		 	// load all the handlers
			$files = glob("handlers/*/*.php");
			foreach($files AS $f) {
				require_once($f);
				$class_name = $this->get_handler_class_name($f);
				if (!class_exists($class_name)) continue;

				// check if this handler can handle any of these mime types
				$tmp_h = new $class_name($this->path);
				$handled = FALSE;
				foreach($mime_types AS $mt) {
					if ($tmp_h->handles_mime_type($mt)) {
						$handled = TRUE;
						break;
					}
				}
				if (!$handled) continue;

				$this->load_handler($class_name);

			}
			return $this->handlers;
		 }




		public function run() {

			$path = $this->path;

			$dl = new Directory_Listing();
			$this->records = $dl->get_listing($path);

			$ls = $this->records;

			// get all the mime types from the file listing
			$mime_types = array();
			if ($ls['file']) {
				foreach($ls['file'] AS $file) {
					if ($file['mime_major'] == '' || $file['mime_minor'] == '') continue;
					$mime_type = $file['mime_major']."/".$file['mime_minor'];
					$mime_types[$mime_type] = $mime_type;
				}
			}



			$this->load_handler("Directory_Listing_Default_Handler");
			$this->load_handler("Directory_Listing_Directory_Handler");


			$this->load_handlers_from_mime($mime_types);


			$this->smarty->assign("handlers", $this->handlers);

			// first process actions before any output, so they can halt script
			foreach($this->handlers AS $class_name => $handler) {
				$handler->run_handler_action($ls, $handler);
			}



			// run each handler
			$data = array();
			foreach($this->handlers AS $class_name => $handler) {
				$this->run_handler($ls, $handler);

				// prepare smarty for final output
				$this->smarty->assign("path", $this->path);
				$this->smarty->assign("handler", $handler);
				$this->smarty->assign("records", $this->records);

				$handler_name = $handler->get_handler_name();

				$template = "handlers/$handler_name/listing.tpl";
				$data[$handler_name] = $this->smarty->fetch($template);

			}

			// finally -- spit some HTML
			//

			$s = self::create_smarty();

			// build a clickable path nav
			$parts = explode("/", $_SERVER['SCRIPT_URL']);
			$parts_len = count($parts);
			$nav = array();
			for($i=0; $i<$parts_len; $i++) {
				$url = implode("/", array_slice($parts, 0, $i + 1));
				$name = $parts[$i];
				$n = compact('url',  'name');
				$nav[] = $n;
			}
			$s->assign("nav", $nav);

			$s->assign("main", $data);
			$s->assign("path", $this->path);
			$s->assign("handlers", $this->handlers);
			$template = "templates/listing.tpl";
			echo $s->fetch($template);
		}
		public function set($k, $v) {
			$this->record_data[$k] = $v;
		}
		public function get($k) {
			return $this->record_data[$k];
		}

		private function run_handler($ls, $handler) {

				$template = "handlers/".$handler->get_handler_name()."/index.tpl";
				if (file_exists($template)) {

					$handler->render_index();
					$smarty = self::create_smarty();
					foreach($handler->record_data AS $k => $v) {
						$smarty->assign($k,$v);
					}
					echo $smarty->fetch($template);

				}

				$handler->render_header();
		

				if ($ls['dir']) {
					foreach($ls['dir'] AS $i => $dir) {
						if ($handler->handles_dir($dir)) {
							$handler->render_dir_partial($dir);
							$this->records['dir'][$i] = array_merge($dir, $handler->record_data);
						}
					}
				}

				if ($ls['file']) {

					foreach($ls['file'] AS $i => $file) {
						if ($handler->handles_file($file)) {
							$handler->render_file_partial($file);
							$this->records['file'][$i] = array_merge($file, $handler->record_data);
						}
					}

				}


				$handler->render_footer();

		}

		public function run_handler_action($ls, $handler) {
			$action = $_REQUEST['action'];
			if ($action == '') return;
			$action_method = 'action_'.$action;
			if (is_callable(array($handler, $action_method))) {
				$data = $handler->$action_method($ls);

				$smarty = self::create_smarty();

				$template = "handlers/".$handler->get_handler_name()."/action_$action.tpl";
				echo $smarty->fetch($template);

			}
		}

	}
?>
