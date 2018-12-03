<?php

namespace Liquipedia\CardHover;

use MediaWiki\Linker\LinkRenderer;
use MWNamespace;
use Title;

class Hooks {

	private static $filePaths = [];

	private static function getFilePath( $target ) {
		$text = $target->getText();
		if ( !array_key_exists( $text, self::$filePaths ) ) {
			global $wgOut;
			$result = str_replace( '&#58;', ':', strip_tags( $wgOut->parseInline( '<p>{{#show:' . $target->getText() . '|?has filepath|link=none}}</p>' ) ) );
			if ( !empty( $result ) ) {
				self::$filePaths[ $text ] = $result;
			} else {
				self::$filePaths[ $text ] = '';
			}
		}
		return self::$filePaths[ $text ];
	}

	public static function onHtmlPageLinkRendererBegin( LinkRenderer $linkRenderer, $target, &$text, &$extraAttribs, &$query, &$ret ) {
		if ( empty( $query ) && $target instanceof Title && $target->getNamespace() === NS_MAIN && $target->exists() && in_array( MWNamespace::getCanonicalName( NS_CATEGORY ) . ':Cards', array_keys( $target->getParentCategories() ) ) ) {
			$url = self::getFilePath( $target );
			if ( !empty( $url ) ) {
				$extraAttribs[ 'data-img' ] = $url;
				if ( array_key_exists( 'class', $extraAttribs ) ) {
					$extraAttribs[ 'class' ] .= ' hovercard';
				} else {
					$extraAttribs[ 'class' ] = 'hovercard';
				}
			}
		}
	}

	public static function onLinkerMakeMediaLinkFile( $title, $file, &$html, &$attribs, &$ret ) {
		if ( $title instanceof Title && $title->getNamespace() === NS_MAIN && $title->exists() && in_array( MWNamespace::getCanonicalName( NS_CATEGORY ) . ':Cards', array_keys( $title->getParentCategories() ) ) ) {
			$url = self::getFilePath( $title );
			if ( !empty( $url ) ) {
				$attribs[ 'data-img' ] = $url;
				if ( array_key_exists( 'class', $extraAttribs ) ) {
					$attribs[ 'class' ] .= ' hovercard';
				} else {
					$attribs[ 'class' ] = 'hovercard';
				}
			}
		}
	}

	public static function onBeforePageDisplay( $out, $skin ) {
		$out->addModules( 'ext.cardHover' );
		return true;
	}

}
