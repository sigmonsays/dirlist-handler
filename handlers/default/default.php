<?
	class Directory_Listing_Default_Handler extends Directory_Listing_Handler {

		public function handles_mime_type($mt) {
			return TRUE;
		}

		public function handles_dir($record) {
			return TRUE;
		}
		public function handles_file($record) {
			return TRUE;
		}

		public function render_index() {
		}
	
		public function render_dir_partial($record) {
			$url = $this->generate_url('directory', $this->path, $record['name']);
			$this->set("url", $url);

		}

		public function render_file_partial($record) {
			$url = $this->generate_url('directory', $this->path, $record['name']);
			$this->set("url", $url);
			return $html;
		}

	}
?>
