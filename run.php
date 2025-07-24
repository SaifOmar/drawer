<?php

use DSL\Tokenizer;
use DSL\Parser;
use DSL\Renderer;

require_once "src/Tokenizer.php";
require_once "src/Parser.php";
require_once "src/Renderer.php";
$text = file_get_contents('shapes.dsl');



$tokens = Tokenizer::tokenize($text);
file_put_contents('tokens.json', json_encode($tokens, JSON_PRETTY_PRINT));
// var_dump($tokens);

var_dump(gettype($tokens[0]));
$parsed = Parser::parse($tokens);
file_put_contents('parsed.json', json_encode($parsed, JSON_PRETTY_PRINT));
// var_dump($parsed);
$rendered = Renderer::render($parsed);
// var_dump($rendered);



// Renderer::renderShapes([Shape, Shape, ...]) => "<div>...</div>"
//
// Renderer::renderShape(Shape) => "<div style='...'></div>"
//
// Renderer::renderHTMLPage($body) => "<!DOCTYPE html>...$body...</html>"
