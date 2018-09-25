<?php

namespace Liquipedia\CardHover;

use Parser;
use MediaWiki\Linker\LinkRenderer;
use MWNamespace;
use Title;

class Hooks {

	private static $filePaths = [];

	private static function getFilePath( $target ) {
		$text = $target->getText();
		if ( !array_key_exists( $text, self::$filePaths ) ) {
			global $wgParser;
			$parser = clone $wgParser;
			$result = $parser->parse( '{{#show:' . $target->getText() . '|?has filepath|link=none}}', $target, $parser->getOptions(), false );
			preg_match_all( '/<p>(.*?)<\/p>/', $result->getRawText(), $matches );
			self::$filePaths[ $text ] = $matches[ 1 ][ 0 ];
		}
		return self::$filePaths[ $text ];
	}

	public static function onHtmlPageLinkRendererBegin( LinkRenderer $linkRenderer, $target, &$text, &$extraAttribs, &$query, &$ret ) {
		if ( $target instanceof Title && $target->getNamespace() === NS_MAIN && $target->exists() && in_array( MWNamespace::getCanonicalName( NS_CATEGORY ) . ':Cards', array_keys( $target->getParentCategories() ) ) ) {
			$extraAttribs[ 'class' ] = 'hovercard';
			$extraAttribs[ 'data-img' ] = self::getFilePath( $target );
		}
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$out->addModules( 'ext.cardHover' );
		return true;
	}

}
