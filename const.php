<?php

const PARSER_COMMONMARK_DEF = 'cm';
const PARSER_COMMONMARK_GFM = 'cm-gfm';
const PARSER_COMMONMARK_ALL = 'cm-all';

const PARSER_PARSEDOWN_17   = 'pd-17';
const PARSER_PARSEDOWN_18   = 'pd-18';
const PARSER_PARSEDOWN_20   = 'pd-20';

const PARSER_CEBE_MD        = 'cebe-md';
const PARSER_CEBE_MD_GFM    = 'cebe-md-gfm';
const PARSER_CEBE_MD_EXTRA  = 'cebe-md-extra';

const PARSER_CMARK_EXT      = 'cmark';


function knownParser(string $parser): bool {
	return match ($parser) {
		PARSER_COMMONMARK_DEF,
		PARSER_COMMONMARK_GFM,
		PARSER_COMMONMARK_ALL,
		PARSER_PARSEDOWN_17,
		PARSER_PARSEDOWN_18,
		PARSER_PARSEDOWN_20,
		PARSER_CEBE_MD,
		PARSER_CEBE_MD_GFM,
		PARSER_CEBE_MD_EXTRA
			=> true,
		default
			=> $parser === PARSER_CMARK_EXT
				 && extension_loaded('cmark'),
	};
}

function getAllParsers(): array {
	$res = [
		PARSER_COMMONMARK_DEF,
		PARSER_COMMONMARK_GFM,
		PARSER_COMMONMARK_ALL,
		PARSER_PARSEDOWN_17,
		PARSER_PARSEDOWN_18,
		PARSER_PARSEDOWN_20,
		PARSER_CEBE_MD,
		PARSER_CEBE_MD_GFM,
		PARSER_CEBE_MD_EXTRA
	];
	if (extension_loaded('cmark')) {
		$res[] = PARSER_CMARK_EXT;
	}
	return $res;
}
