<?php

namespace Liquipedia\CardHover;

use Parser;

class Hooks {

	private static $executed = false;

	public static function onParserBeforeInternalParse( Parser &$parser, &$text ) {
		global $wgDBprefix;

		// This Hook is called multiple times in the parsing process, ensure this function is only executed
		if ( self::$executed ) {
			return $parser;
		}
		self::$executed = true;

		$dbr = wfGetDB( DB_SLAVE );

		// Put ID of mainspace here
		$namespace_id = 0;

		/* $sql = "(SELECT p1.page_title \n"
		  . "FROM redirect\n"
		  . "INNER JOIN page p1 ON rd_from = p1.page_id\n"
		  . "INNER JOIN page p2 ON rd_title = p2.page_title\n"
		  . "WHERE p2.page_title IN (\n"
		  . "SELECT page_title\n"
		  . "FROM page\n"
		  . "LEFT JOIN categorylinks ON cl_from = page_id\n"
		  . "WHERE cl_to = 'Cards'\n"
		  . "AND page_namespace = ".$namespace_id." ))\n"
		  . "UNION\n"
		  . "(SELECT page_title\n"
		  . "FROM page\n"
		  . "INNER JOIN categorylinks ON cl_from = page_id\n"
		  . "WHERE cl_to = 'Cards'\n"
		  . "AND page_namespace = ".$namespace_id." )"; */

		$sql = "(SELECT p1.page_title
			FROM {$wgDBprefix}redirect
			INNER JOIN {$wgDBprefix}page p1 ON rd_from = p1.page_id
			INNER JOIN {$wgDBprefix}page p2 ON rd_title = p2.page_title
			WHERE p2.page_title IN (
				SELECT page_title
				FROM {$wgDBprefix}page
				LEFT JOIN {$wgDBprefix}categorylinks ON cl_from = page_id
				WHERE cl_to = 'Cards'
				AND page_namespace = \"$namespace_id\" ))
				UNION
				(SELECT page_title
				FROM {$wgDBprefix}page
				INNER JOIN {$wgDBprefix}categorylinks ON cl_from = page_id
				WHERE cl_to = 'Cards'
				AND page_namespace = \"$namespace_id\")";

		$result = $dbr->query( $sql );

		$cards = array();
		foreach ( $result as $r ) {
			// Save both "card name" and "card name|" to avoid regex
			// Store as key instead of value to allow efficient lookup
			$cards[ str_replace( '_', ' ', $r->page_title ) ] = true;
			$cards[ str_replace( '_', ' ', $r->page_title ) . '|' ] = true;
		}

		// get all internal links
		//preg_match_all('/\[\[(.*)\]\]/', $text, $internal_links);
		preg_match_all( '/\[\[(.*?)\]\]/', $text, $internal_links );
		$matches = array_unique( $internal_links[ 1 ] );
		// check if internal link links to card page
		foreach ( $matches as $match ) {
			// Internal link: [[Link|Arbitrary Name]]
			if ( strpos( $match, '|' ) !== false ) {
				$expl = explode( '|', $match );
				$cardmatch = $expl[ 0 ] . '|';
				if ( array_key_exists( $cardmatch, $cards ) ) {
					$replace = '<span class="hovercard" data-img="{{#show:' . $expl[ 0 ] . '|?has filepath|link=none}}">[[' . $match . ']]</span>';
					$text = str_replace( '[[' . $match . ']]', $replace, $text );
				}

				// Internal link: [[Link]]
			} else if ( array_key_exists( $match, $cards ) ) {
				$replace = '<span class="hovercard" data-img="{{#show:' . $match . '|?has filepath|link=none}}">[[' . $match . ']]</span>';
				$text = str_replace( '[[' . $match . ']]', $replace, $text );
			}
		}
		return $parser;
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$out->addModules( 'ext.cardHover' );
		return true;
	}

}
