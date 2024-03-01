<?php

/**
 * @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 */

use JetBrains\PhpStorm\NoReturn;

const ROOT_PATH = __DIR__ . '/';

require_once ROOT_PATH . 'const.php';

function __cebe($class): void {
	$path = str_replace(
		['cebe\\markdown\\', '\\'],
		['', '/'],
		$class);
	$root = ROOT_PATH . 'vendor/cebe/markdown/' . $path . '.php';
	include_once $root;
}

function __cm($class): void {
	$path = str_replace(
		[
			'League\\CommonMark\\',
			'League\\Config\\',
			'League\\Event\\',
			'Psr\\EventDispatcher\\',
			'Nette\\Schema\\',
			'Nette\\',
			'Dflydev\\DotAccessData\\',
			'\\'
		],
		[
			'vendor/League/CommonMark/',
			'vendor/League/Config/',
			'vendor/League/Event/',
			'vendor/Psr/EventDispatcher/',
			'vendor/Nette/Schema/',
			'vendor/Nette/Utils/',
			'vendor/Dflydev/DotAccessData/',
			'/'
		],
		$class);
	$root = ROOT_PATH . $path . '.php';
	include_once $root;
}

function __pd_20($class): void {
	$path = str_replace([
		'Erusev\\Parsedown\\',
		'\\'
	],[
		'vendor/Parsedown/v2.0.0-dev/',
		'/'
	], $class);
	$root = ROOT_PATH . $path . '.php';
	include_once $root;
}

function __pd_17($class): void {
	$path = str_replace('\\', '/', $class);
	$root = ROOT_PATH . 'vendor/Parsedown/v1.7.4/' . $path . '.php';
	include_once $root;
}

function __pd_18($class): void {
	$path = str_replace('\\', '/', $class);
	$root = ROOT_PATH . 'vendor/Parsedown/v1.8.0-beta-7/' . $path . '.php';
	include_once $root;
}

#[NoReturn]
function ex(...$args): void {
	$parts = ['Error>', ...$args];
	fwrite(STDERR,
		implode(' ', $parts) .
		PHP_EOL
	);
	exit(1);
}

function register(string $parser): void {

	$callback = match($parser) {

		PARSER_PARSEDOWN_17 => '__pd_17',
		PARSER_PARSEDOWN_18 => '__pd_18',
		PARSER_PARSEDOWN_20 => '__pd_20',

		PARSER_COMMONMARK_DEF,
		PARSER_COMMONMARK_GFM,
		PARSER_COMMONMARK_ALL
			=> '__cm',

		PARSER_CEBE_MD,
		PARSER_CEBE_MD_GFM,
		PARSER_CEBE_MD_EXTRA
			=> '__cebe',
		default => null,
	};

	if ($callback === null) {
		ex('Unknown parser', $parser);
	}

	spl_autoload_register($callback);
}
