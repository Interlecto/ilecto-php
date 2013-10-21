<?php
class il_output extends Attributer {
	public $page;
	function __construct($page) {
		Attributer::__construct('page');
		$this->page = $page;
	}

	function on_html($content) {
		$this->content = $content->html();
		$this->title = $content->get('title',$this->page->get('line/clean'));
	}

	function make($content) {
		$this->on_html($content);
?><!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title><?php echo $this->title ?></title>
</head>
<body>
<?php echo $this->content ?>
</body>
</html><?php
	}

	function blank() {
		echo "<pre>";
		print_r($this);
		echo "</pre>\n";
	}
};
?>
