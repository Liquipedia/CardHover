<?php

namespace Liquipedia\CardHover;

use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MWNamespace;
use Title;

class Hooks {

	/**
	 *
	 * @var array
	 */
	private static $filePaths = [];

	/**
	 * @param LinkTarget $target
	 * @return string
	 */
	private static function getFilePath( $target ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$text = $target->getText();
		if ( !array_key_exists( $text, self::$filePaths ) ) {
			global $wgOut;
			$currentPageTitle = $wgOut->getTitle();
			if ( $currentPageTitle === null ) {
				$result = '';
			} elseif ( $currentPageTitle->getNamespace() >= NS_MAIN ) {
				$result = str_replace( '&#58;', ':', strip_tags(
						$wgOut->parseInlineAsInterface(
							'<p>{{#show:' . $target->getText() . '|?has filepath|link=none}}</p>'
						)
					) );
			} else {
				if ( $currentPageTitle->isSpecialPage() ) {
					$done = false;
					$whitelistedPages = $config->get( 'CardHoverWhitelistedPages' );
					foreach ( $whitelistedPages as $page ) {
						if ( $currentPageTitle->isSpecial( $page ) ) {
							$result = str_replace( '&#58;', ':', strip_tags(
									$wgOut->parseInlineAsInterface(
										'<p>{{#show:' . $target->getText() . '|?has filepath|link=none}}</p>'
									)
								) );
							$done = true;
						}
					}
					if ( !$done ) {
						$result = '';
					}
				} else {
					$result = '';
				}
			}
			if ( strpos( $result, $config->get( 'Server' ) ) !== 0 ) {
				$result = '';
			}
			if ( !empty( $result ) ) {
				self::$filePaths[ $text ] = $result;
			} else {
				self::$filePaths[ $text ] = '';
			}
		}
		return self::$filePaths[ $text ];
	}

	/**
	 * @param LinkRenderer $linkRenderer
	 * @param LinkTarget $target
	 * @param string &$text
	 * @param array &$extraAttribs
	 * @param array &$query
	 * @param string|null &$ret
	 */
	public static function onHtmlPageLinkRendererBegin(
		LinkRenderer $linkRenderer, $target, &$text, &$extraAttribs, &$query, &$ret
	) {
		if (
			empty( $query )
			&& $target instanceof Title
			&& $target->getNamespace() === NS_MAIN
			&& $target->exists()
			&& array_key_exists(
				MWNamespace::getCanonicalName( NS_CATEGORY ) . ':Cards',
				$target->getParentCategories()
			)
		) {
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

	/**
	 * @param Thumbnail $thumbnail
	 * @param array &$attribs
	 * @param array &$linkAttribs
	 */
	public static function onThumbnailBeforeProduceHTML( $thumbnail, &$attribs, &$linkAttribs ) {
		if ( is_array( $linkAttribs ) && array_key_exists( 'title', $linkAttribs ) ) {
			$target = Title::newFromText( $linkAttribs[ 'title' ], NS_MAIN );
			if (
				$target instanceof Title
				&& $target->getNamespace() === NS_MAIN
				&& $target->exists()
				&& array_key_exists(
					MWNamespace::getCanonicalName( NS_CATEGORY ) . ':Cards',
					$target->getParentCategories()
				)
			) {
				$url = self::getFilePath( $target );
				if ( !empty( $url ) ) {
					$linkAttribs[ 'data-img' ] = $url;
					if ( array_key_exists( 'class', $linkAttribs ) ) {
						$linkAttribs[ 'class' ] .= ' hovercard';
					} else {
						$linkAttribs[ 'class' ] = 'hovercard';
					}
				}
			}
		}
	}

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return bool
	 */
	public static function onBeforePageDisplay( $out, $skin ) {
		$out->addModules( 'ext.cardHover' );
		return true;
	}

}
