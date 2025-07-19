<?php

namespace DSL;

use function ctype_alpha;

class Token
{
	public  function __construct(public  readonly  string $type, public readonly string $value) {}
}
class Tokenizer
{

	public static $keywords = ['circle', 'square', 'triangle', 'rectangle'];

	public static $identifiers = ['color', 'at', 'width', 'height', 'radius', 'length', 'x', 'y', 'z', 'fillColor', 'strokeColor', 'strokeWidth',];

	public static $values = ['red', 'green', 'blue'];

	public static $punctuations = ['(', ')', ',', '=', "'",];
	public static function tokenize(string $input, int $pointer = 0, array $tokens = []): array
	{
		if ($pointer >= strlen($input) - 1) {
			return $tokens;
		}
		$whiteSpacePointer = self::skipWhiteSpace($input, $pointer);
		$newPointer = self::splitToken($input, $whiteSpacePointer, self::getType($input[$whiteSpacePointer]));
		return match (true) {
			$pointer >= strlen($input) - 1 => $tokens,
			default => self::tokenize($input, $newPointer, [...$tokens, self::consumeToken($input, $whiteSpacePointer, $newPointer)])
		};
	}
	private static function getType(string $token)
	{
		return match (true) {
			ctype_alpha($token) => "string",
			ctype_digit($token) => "digit",
			self::isPunctuation($token) => "punctuation",
			self::isWhiteSpace($token) => "blank",
			default => 'unknown',
		};
	}
	private static function splitToken(string $input, int $pointer, string $lastTokenType = 'string'): int
	{
		if ($pointer >= strlen($input) - 1) {
			return $pointer;
		}
		$token = $input[$pointer];
		$newTokenType = self::getType($token);
		return match (true) {
			self::isWhiteSpace($input[$pointer]) => $pointer,
			$lastTokenType !== $newTokenType => $pointer,
			default =>  self::splitToken($input, $pointer + 1, $newTokenType),
		};
	}
	private static function consumeToken(string $input, int $start, int $end): Token
	{
		$token =  substr($input, $start, $end - $start);
		return match (true) {
			self::isKeyword($token) => new Token(type: 'keyword', value: $token),
			self::isIdentifier($token) => new Token(type: 'identifier', value: $token),
			self::isPunctuation($token) => new Token(type: 'punctuation', value: $token),
			self::isDigit($token) => new Token(type: 'value', value: $token),
			self::isValue($token) => new Token(type: 'value', value: $token),
			default => new Token(type: 'unknown', value: $token),
		};
	}
	private static function skipWhiteSpace(string $input, int $pointer): int
	{
		return match (true) {
			self::isWhiteSpace($input[$pointer]) => self::skipWhiteSpace($input, $pointer + 1),
			default  => $pointer,
		};
	}
	private static function isWhiteSpace(string $token): bool
	{
		return preg_match('/^\s$/', $token) === 1;
	}
	private static function isKeyword(string $token): bool
	{
		return in_array($token, self::$keywords);
	}
	private static function isIdentifier(string $token): bool
	{
		return in_array($token, self::$identifiers);
	}
	private static function isValue(string $token): bool
	{
		return in_array($token, self::$values);
	}
	private static function isPunctuation(string $token): bool
	{
		return in_array($token, self::$punctuations);
	}
	private static function isDigit(string $token): bool
	{
		return preg_match('/^\d+$/', $token) === 1;
	}
}
