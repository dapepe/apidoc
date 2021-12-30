<?php

header('Content-type: text/plain');

include '../src/parser.php';
include '../src/markdown.php';

$p = new Apidoc\Parser();
$raw = $p->parseFile('sample/sample.src.php');
print_r($raw);
die();

$m = new Apidoc\Markdown();
$m->setSrcPath('sample');
echo $m->replace(file_get_contents('sample/sample.doc.md'));
