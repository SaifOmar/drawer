<?php

namespace DSL;

class Renderer
{
	public static function render(array $ast): string
	{
		$shapes = self::renderShapes($ast, 0, []);
		$styles = self::renderShapeStyles($ast, 0, []);
		$newStyles  = implode('', $styles);
		$newShapes = implode('', $shapes);
		$html = self::renderHtmlPage($newStyles, $newShapes);

		file_put_contents('output.html', $html);

		return $html;
	}

	public static function renderShapes(array $ast, int $pointer, array $result): array
	{
		return match (true) {
			$pointer >= count($ast) => $result,
			default => self::renderShapes($ast, $pointer + 1, [...$result, self::renderShape($ast, $pointer)]),
		};
	}

	public static function renderShape(array $ast, int $pointer): string
	{
		$shape = $ast[$pointer];

		// Compute inline style: width, height, position, extra rules
		$props = $shape->properties;
		$left = $props['at'][0] ?? 0;
		$top = $props['at'][1] ?? 0;

		$styleParts = ["position: absolute", "left: {$left}px", "top: {$top}px"];

		if ($shape->name === 'circle' && isset($props['radius'])) {
			$radius = $props['radius'];
			$diameter = $radius * 2;
			$styleParts[] = "width: {$diameter}px";
			$styleParts[] = "height: {$diameter}px";
		}

		if ($shape->name === 'rectangle') {
			if (isset($props['width'])) {
				$styleParts[] = "width: {$props['width']}px";
			}
			if (isset($props['height'])) {
				$styleParts[] = "height: {$props['height']}px";
			}
		}


		$styleParts[] = "background: #3498db";
		// Add default background
		if (isset($props['color'])) {
			$styleParts[] = "background: {$props['color']}";
		}

		$inlineStyle = implode('; ', $styleParts);

		return "<div class='shape {$shape->name}' id='shape{$pointer}' style='{$inlineStyle}'></div>";
	}

	public static function renderShapeStyles(array $ast, int $pointer, array $result): array
	{
		return match (true) {
			$pointer >= count($ast) => $result,
			default => self::renderShapeStyles($ast, $pointer + 1, [...$result, self::renderShapeStyle($ast, $pointer)]),
		};
	}

	public static function renderShapeStyle(array $ast, int $pointer): string
	{
		$style = "
<style>
  body {
    position: relative;
    margin: 0;
    padding: 0;
    background: #f0f0f0;
    height: 100vh;
    overflow: hidden;
  }

  .shape {
    position: absolute;
    border: 2px solid #333;
  }

  .circle {
    border-radius: 50%;
  }

  .rectangle {
    /* no extra shape-specific rule; width & height come from inline style */
  }
</style>
";
		return $style;
	}

	public static function renderHtmlPage(string $styles, string $shapes): string
	{
		return "
<!DOCTYPE html>
<html lang=\"en\">
<head>
	<meta charset=\"UTF-8\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
	<title>Document</title>
	{$styles}
</head>
<body>
	{$shapes}
</body>
</html>
";
	}
}
