<?php
App::import('Lib', 'AssetCompress.AssetFilterInterface');

class LessCss extends AssetFilter {

	protected $_settings = array(
		'ext' => '.less',
		'path' => '/usr/local/bin/node',
		'node_path' => '/usr/local/lib/node_modules'
	);

/**
 * Runs `lessc` against any files that match the configured extension.
 *
 * @param string $filename The name of the input file.
 * @param string $input The content of the file.
 */
	public function input($filename, $input) {
		if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
			return $input;
		}

		$tmpfile = tempnam(sys_get_temp_dir(), 'asset_compress_less');
		$this->_generateScript($tmpfile, $input);

		$bin = $this->_settings['path'] . ' ' . $tmpfile;
		$env = array('NODE_PATH' => $this->_settings['node_path']);
		$return = $this->_runCmd($bin, '', $env);
		unlink($tmpfile);
		return $return;
	}

	protected function _generateScript($file, $input) {
		$text = <<<JS
var less = require('less'),
	sys = require('sys');

var parser = new less.Parser();
parser.parse(%s, function (e, tree) {
	if (e) {
		less.writeError(e);
		process.exit(1)
	}
	sys.print(tree.toCSS());
	process.exit(0);
});
JS;
		file_put_contents($file, sprintf($text, json_encode($input)));
	}
}
