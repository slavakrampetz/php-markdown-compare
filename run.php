<?php

/** @noinspection UnknownInspectionInspection */

require_once __DIR__ . '/const.php';

use JetBrains\PhpStorm\NoReturn;

$config = [
	'md' => __DIR__ . '/sample.md',
	'iterations' => 20,
	'parser' => [],
	'csv' => false,
	'flags' => [
		'memory' => false,
	],
];

#[NoReturn]
function usage(string $format, ...$args): void {
	$msg = '';
	if (!empty($format)) {
		$msg = vsprintf("Error: $format", $args) . PHP_EOL . PHP_EOL;
	}
	fwrite(STDERR,
		<<<EOD
${msg}Usage: php run.php [options] [flags]
Options:
  --md         file      default: sample.md
  --iterations num       default: 20
  --parser     name      default: all
  --csv        file      default: disabled
Flags:
  -memory                default: off

EOD
	);
	exit(1);
}

function checkRequirements(): void {
	if (extension_loaded('xdebug')) {
		$ver = explode('.', phpversion('xdebug'));
		$major = $ver[0];
		if ($major === '3') {
			$mode = ini_get('xdebug.mode');
			if ($mode !== 'off' && $mode !== '') {
				fwrite(STDERR,
					'The xdebug extension is loaded, this can skew benchmarks. ' .
					'Disable it for accurate results: -d xdebug.mode=off' .
					PHP_EOL . PHP_EOL);
				exit(1);
			}
		} else {
			$modes =
				ini_get('xdebug.remote_enable') .
				ini_get('xdebug.default_enable') .
				ini_get('xdebug.profiler_enable') .
				ini_get('xdebug.auto_trace') .
				ini_get('xdebug.coverage_enable');
			if ($modes !== '00000') {
				fwrite(STDERR,
					'The xdebug extension is loaded, this can skew benchmarks. ' .
					'Disable it for accurate results in php.ini' .
					PHP_EOL . PHP_EOL);
				exit(1);
			}
		}
	}

	if (!function_exists('proc_open')) {
		usage('isolation requires proc_open');
	}
}

// Parse arguments
function readParams(array $argv): array {

	$config = [
		'md' => __DIR__ . '/sample.md',
		'iterations' => 20,
		'parser' => [],
		'csv' => false,
		'flags' => [
			'isolate' => false,
			'memory' => false,
		],
	];

	checkRequirements();

	array_shift($argv);
	while ($key = array_shift($argv)) {

		if ($key[0] !== '-') {
			usage("expect option or flag, got: %s", $key);
		}

		if ($key[1] === '-') {

			$key = substr($key, 2);

			if (!isset($config[$key])) {
				usage('invalid option %s', $key);
			}

			if (is_array($config[$key])) {
				$config[$key][] = array_shift($argv);
			} else {
				$config[$key] = array_shift($argv);
			}
			continue;
		}

		$key = substr($key, 1);
		if (!isset($config['flags'][$key])) {
			usage('invalid flag %s', $key);
		}

		$config['flags'][$key] = true;
	}

	$config['iterations'] = max($config['iterations'], 20);
	if ($config['md'] !== '-' && !file_exists($config['md'])) {
		usage('cannot read input %s', $config['md']);
	}

	if ($config['csv'] !== false) {

		$stream = $config['csv'];
		$fd = @fopen($stream,'wb');
		if (!is_resource($fd)) {
			usage('cannot fopen(%s) for writing', $config['csv']);
		}

		define('CSVOUT', $fd);
	}

	if (!file_exists($config['md'])) {
		usage('cannot find MD file %s', $config['md']);
	}

	$res = file_get_contents($config['md']);
	if (false === $res) {
		usage('cannot read MD file %s', $config['md']);
	}

	if (count($config['parser'])) {
		foreach ($config['parser'] as $parser) {
			if (!knownParser($parser)) {
				usage('unsupported parser specified: %s', $parser);
			}
		}
	} else {
		$config['parser'] = getAllParsers();
	}

	return $config;
}

function run(array $config, string $parser): array {
	$argv = "$parser {$config['iterations']} {$config['md']}";
	$script = 'php benchmark.php';

	$proc = proc_open("$script $argv", [
		0 => STDIN,
		1 => STDOUT,
		2 => ['pipe', 'w'],
	], $pipes);

	if (!is_resource($proc)) {
		fprintf(STDERR, "failed to open process\n");
		exit(1);
	}

	$result = stream_get_contents($pipes[2]);
	fclose($pipes[2]);

	if (proc_close($proc) !== 0) {
		fprintf(STDERR, "failed to close process\n");
		fprintf(STDERR, "%s\n", $result);

		exit(1);
	}

	return explode(" ", $result);
}

function display(string $title, array $results, string $sfx, int $divider = 1): void {
	$position = 1;
	$top = 0;

	echo $title, PHP_EOL;

	asort($results);

	if ($divider > 100) {
		$dec = 0;
	} else {
		$dec = ($divider > 10 ? 1 : 2);
	}
	$ds = ',';
	$ts = '.';

	foreach ($results as $name => $result) {

		$res = number_format($result/$divider, $dec, $ds, $ts);

		if ($top === 0) {
			$top = $result;
			$d = 'top';
		} else {
			$diff = $result - $top;

			$prc = $diff / $top;
			if ($prc < .3) {
				$d = '+' . number_format($prc * 100, 1, $ds, $ts) . '%';
			} else {
				$d = 'x' . number_format($result/$top, 1, $ds, $ts);
			}
		}

		printf(
			"  % 2d. %- 20s % 13s $sfx  % 9s\n",
			$position++,
			$name,
			$res,
			$d
		);
	}
}


$config = readParams($argv);

printf(
	"Running Benchmarks Isolated, %d Implementations, %d Iterations:\n",
	count($config['parser']), $config['iterations']
);

$cpu = [];
$mem = [];
foreach ($config['parser'] as $parser) {
	printf("   %- 30s ", $parser);
	[$cpu[$parser], $mem[$parser]] = run($config, $parser);
	echo PHP_EOL;
	usleep(300000);
}

display('Benchmark Results, CPU:',
	$cpu,'ms'
);

if ($config['flags']['memory']) {
	display('Benchmark Results, Peak Memory:',
		$mem, 'kB', 1000
	);
}

// Export results
if ($config['csv']) {
	fputcsv(CSVOUT, ['Name', 'CPU/ms', 'MEM/byte']);
	foreach ($config['parser'] as $parser) {
		fputcsv(CSVOUT, [$parser, $cpu[$parser], trim($mem[$parser])]);
	}
	fflush(CSVOUT);
	fclose(CSVOUT);
}
