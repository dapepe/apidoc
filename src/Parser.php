<?php
namespace Apidoc;

/**
 * Parses the documentation sections of a source file
 *
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package Apidoc
 * @version 1.00 (2014-04-04)
 * @license GNU Lesser Public License
 */
class Parser {
	/** @var string Start of an apiDoc comment */
	private $strCommentStart = '\/\*\*';
	/** @var string End of an apiDoc comment */
	private $strCommentEnd = '\*\/';
	/** @var string End of an apiDoc comment */
	private $strCommands = 'param|query|cmd|example|description|method|route|see|return|depreciated|requires|demo';

	private $arrCommands = [
		'route' => ['route', 'Route']
	];

	/** @var array Commands that should be handled as parameter list */
	private $arrListCommands = array('param', 'query');
	/** @var array Template array for a new node */
	private $arrNodeTemplate = array();

	/**
	 * Specifies all allowed source commands
	 *
	 * @param array $arrCommands
	 */
	public function setCommands($arrCommands) {
		$this->strCommands = implode('|', $arrCommands);
	}

	/**
	 * Specifies all command names that resemble parameter lists
	 *
	 * @param array $arrCommands
	 */
	public function setListCommands($arrCommands) {
		$this->arrListCommands = $arrCommands;
	}

	/**
	 * Sets the template array for a new node
	 *
	 * @param array $arrTemplate
	 */
	public function setNodeTemplate($arrTemplate) {
		$this->arrNodeTemplate = $arrTemplate;
	}

	/**
	 * Sets the wrapper strings for the documentation section
	 *
	 * @param string $strCommentStart
	 * @param string $strCommentEnd
	 */
	public function setWrapper($strCommentStart, $strCommentEnd) {
		$this->strCommentStart = preg_quote($strCommentStart, '/');
		$this->strCommentEnd = preg_quote($strCommentEnd, '/');
	}

	/**
	 * Parses a "param" command
	 *
	 * @param string $strValue
	 * @return array {name: STRING, type: STRING, optional: BOOL, default: STRING, description: STRING}
	 */
	public function parseParam($strValue) {
		if (false === preg_match_all('/(\{([A-Za-z\|\.\/ ,]+)\})?[ |\t]?+([^ ^\t]+)?([ |\t]+(.*))?$/', $strValue, $arrParamMatch))
			return $strValue;

		if ($arrParamMatch[2][0] == '')
			return $strValue;

		$arrParam = array(
			'name' => $arrParamMatch[3][0],
			'type' => $arrParamMatch[2][0],
			'description' => $arrParamMatch[5][0],
			'optional' => true
		);
		if (substr($arrParam['name'], -1) == '*') {
			$arrParam['name'] = substr($arrParam['name'], 0, -1);
			$arrParam['optional'] = false;
		}
		preg_match_all('/^([A-Za-z]+)([\s]?+:[\s]?+(.*))?/U', $arrParam['type'], $arrType);
		if ( !empty($arrType[3][0]) and ($arrType[3][0] != '') ) {
			$arrParam['type'] = $arrType[1][0];
			$arrParam['default'] = $arrType[3][0];
		}

		return $arrParam;
	}

	/**
	 * Parses a source file
	 *
	 * @param string $strFilename The source file contents
	 * @throws \Exception
	 */
	public function parseFile($strFilename) {
		if (!file_exists($strFilename))
			throw new \Exception('Source file does not exist: '.$strFilename);

		return $this->parse(file_get_contents($strFilename));
	}

	/**
	 * Parses a source
	 *
	 * @param string $strSource The source string
	 * @return array
	 */
	public function parse($strSource) {
		preg_match_all('/'.$this->strCommentStart.'(.*)'.$this->strCommentEnd.'/Us', $strSource, $arrMatch);

		$arrNodes = array();
		$arrSegments = $arrMatch[1];

		if (is_array($arrSegments)) {
			foreach ($arrSegments as $strSegment) {
				preg_match_all('/\*?[\s]?+(.*)\n/U', $strSegment, $arrSegmentMatch);

				$arrNode = $this->arrNodeTemplate;

				foreach ($arrSegmentMatch[1] as $strLine) {
					$arrProp = array();
					preg_match_all('/@('.$this->strCommands.')[\s]?+(.*)?$/U', $strLine, $arrComment);

					if ($arrComment && isset($arrComment[1][0])) {
						$tag = strtolower($arrComment[1][0]);
						$value = isset($arrComment[2][0]) ? trim($arrComment[2][0]) : true;

						if (in_array($tag, $this->arrListCommands))
							$arrNode[$tag][] = $this->parseParam($value);
						elseif ($tag == 'return') {
							$arrNode['return'] = $this->parseParam($value);
							if ($arrNode['return']['name'] != '')
								$arrNode['return']['description'] = $arrNode['return']['name'].' '.$arrNode['return']['description'];
							unset($arrNode['return']['name']);
							unset($arrNode['return']['optional']);
						} else
							$arrNode[$tag] = $value;
					}
				}

				$arrNodes[] = $arrNode;
			}
		}

		return $arrNodes;
	}
}
