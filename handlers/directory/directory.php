<?
	class Directory_Listing_Directory_Handler extends Directory_Listing_Handler {

		public function handles_mime_type($mt) {
			return TRUE;
		}

		public function get_title() {
			return "Directories";
		}

		public function handles_dir($record) {
			return TRUE;
		}
		public function handles_file($record) {
			return FALSE;
		}

		public function render_index() {
			$parent_url = $this->generate_url('parent', $this->path.'/..');
			$html = "<a href=\"$parent_url\">[parent]</a>";
			return $html;
		}

		public function render_dir_partial($record) {
			$path = $this->generate_url('directory', $this->path, $record['name']);
			$this->set("url", $path);
			return $html;
		}

		public function render_file_partial($record) {
			$path = $this->generate_url('directory', $this->path, $record['name']);
			$this->set("url", $path);
			return $html;
		}

	}
?>
