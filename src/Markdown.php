<?php
namespace Apidoc;

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ext' . DIRECTORY_SEPARATOR . 'TextTable.php';

/**
 * Creates a markdown document based on the parsed documentation
 *
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package Apidoc
 * @version 1.00 (2014-04-04)
 * @license GNU Lesser Public License
 */
class Markdown {
	/** @var string The source path */
	private $srcPath = './';
	/** @var Apidoc\Parser */
	public $parser;
	/** @var array Array sections that should be displayed as table */
	private $arrTables = array(
		'param' => 'URL parameters',
		'query' => 'Query parameters'
	);

	public function __construct() {
		$this->parser = new Parser();
	}

	/**
	 * Replace apidoc sections within a markdown document
	 *
	 * @param  string $md Markdown string
	 * @return string
	 */
	public function replace($md) {
		if ($len = preg_match_all('/([#]+)\[BEGIN-APIDOC\]\(([^\(\)]+)\)\s+(.*)\s[#]+\[END-APIDOC\]/is', $md, $matches)) {

			// Get all documentation sections
			for ($i = 0 ; $i < $len ; $i++) {
				$level   = strlen($matches[1][$i]);
				$file    = $matches[2][$i];
				$content = $matches[3][$i];

				// Parse the source file
				if (!is_readable($this->srcPath.$file))
					throw new \Exception('Source file not readable: '.$this->srcPath.$file);

				$sections = $this->parser->parseFile($this->srcPath.$file);

				// Get all route descriptions
				if ($chapters = preg_match_all('/\*\[(get|post|put|delete)\]\(([^\(\)]+)\)/is', $content, $routes)) {
					$marker = array();
					for ($y = 0 ; $y < $chapters ; $y++) {
						$marker[$y] = array(
							'pos'    => strpos($content, $routes[0][$y]),
							'len'    => strlen($routes[0][$y]),
							'method' => $routes[1][$y],
							'route'  => $routes[2][$y]
						);

						if ($y > 0) {
							$marker[$y-1]['content'] = trim(substr($content, $marker[$y-1]['pos'] + $marker[$y-1]['len'], $marker[$y]['pos'] - $marker[$y-1]['pos'] - $marker[$y-1]['len']));
						}
					}
					$marker[$y-1]['content'] = trim(substr($content, $marker[$y-1]['pos'] + $marker[$y-1]['len']));
				}

				// Go through all sections and replace generate the markdown
				$replace = array();
				foreach ($sections as $section) {
					foreach ($marker as $m) {
						if (
							strtolower($m['method']) == strtolower($section['method'])
							&& strtolower($m['route']) == strtolower($section['route'])
						) {
							$section['description'] .= ($section['description'] == '' ? '' : "\r\n\r\n") . $m['content'];
							break;
						}
					}
					$replace[] = $this->renderSection($section, $level);
				}

				$md = str_replace($matches[0][$i], implode("\r\n", $replace), $md);
			}
		}
		return $md;
	}

	/**
	 * Render a individual section
	 *
	 * @param  array $section Section array [method, route, description, ...]
	 * @param  int   $level   The header level
	 * @return string
	 */
	protected function renderSection($section, $level) {
		$res = str_repeat('#', $level).' '.strtoupper($section['method']).': '.$section['route'].' '.str_repeat('#', $level)
		       ."\r\n\r\n".$section['description']."\r\n\r\n";

		if (isset($section['return'])) {
			$res .= "\r\n\r\n".'Returns `'.$section['return']['type'].'`'
			        .($section['return']['description'] == '' ? '' : ': '.$section['return']['description'])."\r\n\r\n";
		}

		foreach ($this->arrTables as $key => $label) {
			if (isset($section[$key]) && is_array($section[$key])) {
				$converter = new \TextTable(false, $section[$key], array());
				$res .= str_repeat('#', $level+1).' '.$label.' '.str_repeat('#', $level+1)."\r\n\r\n".$converter->render()."\r\n";
			}
		}

		return $res;
	}

	/**
	 * Specifies all command names that should be displayed as table
	 *
	 * @param array $arrTables
	 */
	public function setTables($arrTables) {
		$this->$arrTables = $arrTables;
	}

	/**
	 * Set the source path
	 *
	 * @param string $path
	 */
	public function setSrcPath($path) {
		$this->srcPath = realpath($path).DIRECTORY_SEPARATOR;
	}
}

