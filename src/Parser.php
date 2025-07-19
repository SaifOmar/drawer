<?php

namespace DSL;


class Property
{
	public static $availableProps = ['color', 'width', 'height', 'radius', 'border', 'background', 'opacity', 'x', 'y'];
	public function __construct(public readonly string $prop, public readonly string $value) {}
}
class Shape
{
	public function __construct(public readonly string $name, public readonly array $properties) {}
}
class Parser
{
	public static function parse(array $input): array
	{
		return self::parseShapes(self::splitIntoArrays($input, 0, [],  []), 0, []);
	}

	private static function splitIntoArrays(array $input, int $pointer, array $array, array $res, int $check = 0): array
	{
		return match (true) {
			$pointer >=  count($input) => [...$res, $array],
			$check === 2 => self::splitIntoArrays($input, $pointer + 1, [], [...$res, $array], 0),
			$input[$pointer]->type === 'keyword' && !empty($array) =>  self::splitIntoArrays($input, $pointer + 1, [$input[$pointer]], [...$res, $array], $check + 1),
			default => self::splitIntoArrays($input, $pointer + 1, [...$array, $input[$pointer]],  $res, $check)
		};
	}

	private static function parseShapes(array $shapesArray, int $pointer,  array $shapes): array
	{

		return match (true) {
			$pointer >= count($shapesArray) => $shapes,
			default => self::parseShapes($shapesArray, $pointer + 1, [...$shapes, self::parseShape($shapesArray[$pointer], 0, [])])
		};
	}

	private static function parseShape(array $input, int $pointer = 0, array $data = []): Shape
	{
		if ($pointer >= count($input)) {
			return new Shape($data['name'], $data['properties'] ?? []);
		}

		$token = $input[$pointer];

		return match ($token->type) {
			'keyword' => self::parseShape(
				$input,
				$pointer + 1,
				['name' => $token->value, 'properties' => []]
			),
			'identifier' => (
				$token->value === 'at' &&
				isset($input[$pointer + 4]) &&
				$input[$pointer + 1]->type === 'punctuation' && $input[$pointer + 1]->value === '(' &&
				$input[$pointer + 2]->type === 'value' &&
				$input[$pointer + 3]->type === 'punctuation' && $input[$pointer + 3]->value === ',' &&
				$input[$pointer + 4]->type === 'value'
				? self::parseShape(
					$input,
					$pointer + 6, // skip 'at', '(', value, ',', value, ')'
					[
						...$data,
						'properties' => [
							...($data['properties'] ?? []),
							'at' => [
								$input[$pointer + 2]->value,
								$input[$pointer + 4]->value
							]
						]
					]
				)
				: (
					isset($input[$pointer + 2]) && $input[$pointer + 1]->type === 'punctuation' && $input[$pointer + 2]->type === 'value'
					? self::parseShape(
						$input,
						$pointer + 3,
						[
							...$data,
							'properties' => [
								...($data['properties'] ?? []),
								$token->value => $input[$pointer + 2]->value
							]
						]
					)
					: self::parseShape($input, $pointer + 1, $data)
				)
			),
			default => self::parseShape($input, $pointer + 1, $data),
		};
	}
}
