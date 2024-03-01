<?php

/**
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

require_once __DIR__ . '/autoload.php';

// Arguments:
// 1: parser
// 2: iterations
// 3: markdown file

if ($argc < 4) {
	ex('Expect 4 arguments, got 1');
}

$parser = $argv[1];
if (!knownParser($parser)) {
	ex('Unknown parser', $parser);
}

$arg = $argv[2];
if (!is_numeric($arg)) {
	ex('Iterations should be a number:', $arg);
}
$int = (int) $arg;
$iterations = min(max(1, $int), 20);
if ($iterations !== $int) {
	ex('Iterations should be in range of 1-20:', $int);
}

$mdFile = $argv[3];
if (!file_exists($mdFile)) {
	ex('MD file not found:', $mdFile);
}

$res = @file_get_contents($mdFile);
if ($res === false) {
	ex('Cannot read MD file:', $mdFile);
}
if (strlen($res) < 1) {
	ex('Empty MD file:', $mdFile);
}
$md = $res;

$parserRunner = match($parser) {

	PARSER_COMMONMARK_DEF =>
		static function (string $markdown) {
			$parser = new League\CommonMark\CommonMarkConverter();
			$parser->convert($markdown);
		},
	PARSER_COMMONMARK_GFM =>
		static function (string $markdown) {
			$parser = new League\CommonMark\GithubFlavoredMarkdownConverter();
			$parser->convert($markdown);
		},
	PARSER_COMMONMARK_ALL =>
		static function (string $markdown) {

			$environment = new League\CommonMark\Environment\Environment([
				'default_attributes' => [
					League\CommonMark\Extension\Table\Table::class => [
						'class' => 'table',
					],
				],
				'external_link' => [
					'internal_hosts' => 'www.example.com',
					'open_in_new_window' => true,
					'html_class' => 'external-link',
					'nofollow' => '',
					'noopener' => 'external',
					'noreferrer' => 'external',
				],
				'mentions' => [
					'github_handle' => [
						'prefix' => '@',
						'pattern' => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
						'generator' => 'https://github.com/%s',
					],
				],
			]);

			$environment->addExtension(new League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
			$environment->addExtension(new League\CommonMark\Extension\Attributes\AttributesExtension());
			$environment->addExtension(new League\CommonMark\Extension\Autolink\AutolinkExtension());
			$environment->addExtension(new League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension());
			$environment->addExtension(new League\CommonMark\Extension\DescriptionList\DescriptionListExtension());
			$environment->addExtension(new League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension());
			$environment->addExtension(new League\CommonMark\Extension\ExternalLink\ExternalLinkExtension());
			$environment->addExtension(new League\CommonMark\Extension\Footnote\FootnoteExtension());
			$environment->addExtension(new League\CommonMark\Extension\FrontMatter\FrontMatterExtension());
			$environment->addExtension(new League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension());
			$environment->addExtension(new League\CommonMark\Extension\Mention\MentionExtension());
			$environment->addExtension(new League\CommonMark\Extension\SmartPunct\SmartPunctExtension());
			$environment->addExtension(new League\CommonMark\Extension\Strikethrough\StrikethroughExtension());
			$environment->addExtension(new League\CommonMark\Extension\Table\TableExtension());
			$environment->addExtension(new League\CommonMark\Extension\TableOfContents\TableOfContentsExtension());
			$environment->addExtension(new League\CommonMark\Extension\TaskList\TaskListExtension());

			$parser = new League\CommonMark\MarkdownConverter($environment);
			$parser->convert($markdown);
		},

	PARSER_PARSEDOWN_17,
	PARSER_PARSEDOWN_18 =>
		static function ($markdown) {
			/** @noinspection PhpUndefinedClassInspection */
			$parser = new Parsedown();
			$parser->text($markdown);
		},

	PARSER_PARSEDOWN_20
		=> static function ($markdown) {
			$parser = new Erusev\Parsedown\Parsedown();
			$parser->toHtml($markdown);
		},

	PARSER_CEBE_MD => static function ($markdown) {
		/** @noinspection PhpUndefinedClassInspection, PhpUndefinedNamespaceInspection */
		$parser = new \cebe\markdown\Markdown();
		$parser->parse($markdown);
	},

	PARSER_CEBE_MD_GFM => static function ($markdown) {
		/** @noinspection PhpUndefinedClassInspection, PhpUndefinedNamespaceInspection */
		$parser = new \cebe\markdown\GithubMarkdown();
		$parser->parse($markdown);
	},

	PARSER_CEBE_MD_EXTRA => static function ($markdown) {
		/** @noinspection PhpUndefinedClassInspection, PhpUndefinedNamespaceInspection */
		$parser = new \cebe\markdown\MarkdownExtra();
		$parser->parse($markdown);
	},

	PARSER_CMARK_EXT => static function ($markdown) {
		/** @noinspection PhpUndefinedFunctionInspection, PhpUndefinedNamespaceInspection */
		$ast = \CommonMark\Parse($markdown);
		/** @noinspection PhpUndefinedFunctionInspection, PhpUndefinedNamespaceInspection */
		\CommonMark\Render\HTML($ast);
	},

	default => null,
};
if ($parserRunner === null) {
	ex('Unknown parser', $parser);
}

register($parser);

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
	echo '.';
	$parserRunner($md);
}
$end = microtime(true);

$cpu = ($end - $start) * 1000;
$cpu /= $iterations;
$cpu = round($cpu, 2);

$mem = memory_get_peak_usage();

fprintf(STDERR,"%.2f %d\n", $cpu, $mem);
exit(0);
