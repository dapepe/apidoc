<?php
namespace Apidoc;

/**
 * Creates a markdown document based on the parsed documentation
 *
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package Apidoc
 * @version 1.00 (2014-04-04)
 * @license GNU Lesser Public License
 */
class Blueprint extends Markdown {

	protected function renderSection($section, $level) {
		return (string) (new BlueprintSection($section, $level));
	}
}

class BlueprintSection {
	private $lines = [];
	private $level = 1;
	private $section;

	public function __construct($section, $level) {
		$this->section = $section;
		$this->level = $level;
	}

	public function build() {
		$res = $this->addHeadline(strtoupper($section['method']).': '.$section['route'].' '.str_repeat('#', $level));
		$this->addLine($section['description']);

		foreach ($this->arrTables as $key => $label) {
			if (isset($section[$key]) && is_array($section[$key])) {
				$converter = new \TextTable(false, $section[$key], array());
				$res .= str_repeat('#', $level+1).' '.$label.' '.str_repeat('#', $level+1)."\r\n\r\n".$converter->render()."\r\n";
			}
		}

		$this->addHeadline('Retrieve '.$Singular.' [GET]', 3);
		$this->addRequest([ 'Authorization' => $this->token ]);
		$this->addResponse(200, 'application/json', $details);
		$this->addResponse(404, 'application/json', [ 'error' => "Could not find item #2645 in $this->id (or no permission to access it)." ]);
		$this->addHeadline("List Associates [/$this->id/{id}/associates/{?entity}]", 2);
		$this->addLine("Lists all associated objects for a single $Singular.");
		$this->addNode([
			'Parameters' => [
				'id'     => "(required, number) ... Numeric `id` of the $Singular",
				'entity' => '(optional, string) ... Limit the associates list to a specific entity'
			],
		]);
		$this->addHeadline('Retrieve Associates [GET]', 3);
		$this->addRequest([ 'Authorization' => $this->token ]);
		$this->addResponse(200, 'application/json', [
			'result' => [
				'entity' => 'notes',
				'index' => '32',
				'relation' => '',
				'creationdate' => '1418388147'
			], [
				'entity' => 'campaigns',
				'index' => '7',
				'relation' => 'Initial contact',
				'creationdate' => '1418388156'
			]
		]);

		$this->addHeadline("Edit/Create Associates [/$this->id/{id}/associates/{entity}/{index}{?relation}]", 2);
		$this->addLine('Add or change an association.');
		$this->addNode([
			'Parameters' => [
				'id'       => "(required, number) ... Numeric `id` of the $Singular",
				'entity'   => '(required, string) ... Name of the associated entity',
				'index'    => '(required, string) ... ID of the associated resource',
				'relation' => '(optional, string) ... Relationship description'
			],
		]);
		$this->addHeadline('Add an Association [PUT]', 3);
		$this->addRequest([ 'Authorization' => $this->token ]);
		$this->addResponse(201, 'application/json', [ 'result' => 1 ]);
		$this->addHeadline('Update an Association [POST]', 3);
		$this->addRequest([ 'Authorization' => $this->token ]);
		$this->addResponse(200, 'application/json', [ 'result' => true ]);
		$this->addHeadline('Remove an Association [DELETE]', 3);
		$this->addRequest([ 'Authorization' => $this->token ]);
		$this->addResponse(200, 'application/json', [ 'result' => true ]);

		if ( isset($this->meta['fields']['picbinfile']) ) {
			$this->addHeadline("Picture Management [/$this->id/{id}/picture{?disposition}]", 2);
			$this->addLine('Download pictures.');
			$this->addNode([
				'Parameters' => [
					'id'          => "(required, number) ... Numeric `id` of the $Singular",
					'disposition' => '(optional, string) ... Determines the `Content-Disposition` header value. Must be `inline` (default) or `attachment`.',
				],
			]);
			$this->addHeadline('Get a picbinfile [GET]', 3);
			$this->addRequest([ 'Authorization' => $this->token ]);
			$this->addResponse(200, 'image/jpeg', 'ÿØÿà..JFIF...');
			$this->addResponse(404, 'application/json', [ 'error' => 'No picture associated.' ]);
		}

	}

	public function __toString() {
		return implode("\r\n", $this->lines);
	}

	public function addHeadline($text, $level=1) {
		$this->addLine();
		$this->addLine(str_repeat('#', $level).' '.$text);
	}

	public function addLine($line='') {
		$this->lines[] = $line;
	}

	public function addGroup($docFile) {
		$g = new ApiBlueprintGroup($docFile, $this);
		$this->groups[] = $g;
		return $g;
	}

	public function toText() {
		return implode("\r\n", $this->lines);
	}

	public function addHeaders(array $headers, $level = 0) {
		$this->addLine();
		$this->addLine('+ Headers', $level);
		$this->addLine();

		foreach ($headers as $name => $value)
			$this->addLine($name.': '.$value, $level + 2);

		$this->addLine();
	}

	public function addNode($node, $level=0) {
		$this->addLine();

		foreach ($node as $key => $value) {
			$this->addLine('+ '.( is_int($key) ? '' : $key ).( is_array($value) ? '' : ' '.$value ), $level);
			if (is_array($value) && $value) {
				$this->addNode($value, $level + 1);
				$this->addLine();
			}
		}
	}

	public function addData($data, $level=2) {
		if (!$data)
			return;

		$json = is_array($data) ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $data;
		$lines = preg_split('/[\r\n|\r|\n]/', $json);
		foreach ($lines as $line) {
			$this->addLine($line, $level);
		}
	}

	public function addResponse($code, $contenttype = false, $data = [], array $headers = []) {
		$this->addNode(['Response' => $code.($contenttype ? ' ('.$contenttype.')' : '')]);

		if ( !empty($headers) )
			$this->addHeaders($headers, 1);

		if ( empty($data) ) {
			$this->addLine();
		} else {
			$this->addLine();
			$this->addLine('+ Body', 1);
			$this->addLine();
			$this->addData($data, 3);
		}
	}

	public function addRequest(array $headers = null, $data = []) {
		$this->addLine();
		$this->addLine('+ Request');

		if ( !empty($headers) )
			$this->addHeaders($headers, 1);

		if ( empty($data) )
			return;

		$this->addLine('+ Body', 1);
		$this->addLine();
		$this->addData($data, 3);
	}
}
