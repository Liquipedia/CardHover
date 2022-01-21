<?php

namespace Liquipedia\Extension\CardHover\Hooks;

use HtmlArmor;
use Mediawiki\Hook\BeforePageDisplayHook;
use Mediawiki\Hook\ThumbnailBeforeProduceHTMLHook;
use MediaWiki\Linker\Hook\HtmlPageLinkRendererBeginHook;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MWNamespace;
use OutputPage;
use ThumbnailImage;
use Title;

class MainHookHandler implements
	BeforePageDisplayHook,
	HtmlPageLinkRendererBeginHook,
	ThumbnailBeforeProduceHTMLHook
{

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( 'ext.cardHover' );
	}

	/**
	 *
	 * @var array
	 */
	private $filePaths = [];

	/**
	 * @param LinkTarget $target
	 * @return string
	 */
	private function getFilePath( $target ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$text = $target->getText();
		if ( !array_key_exists( $text, $this->filePaths ) ) {
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
				$this->filePaths[ $text ] = $result;
			} else {
				$this->filePaths[ $text ] = '';
			}
		}
		return $this->filePaths[ $text ];
	}

	/**
	 * @param LinkRenderer $linkRenderer
	 * @param LinkTarget $target
	 * @param string|HtmlArmor|null &$text
	 * @param array &$customAttribs
	 * @param array &$query
	 * @param string|null &$ret
	 */
	public function onHtmlPageLinkRendererBegin( $linkRenderer, $target, &$text,
		&$customAttribs, &$query, &$ret
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
			$url = $this->getFilePath( $target );
			if ( !empty( $url ) ) {
				$customAttribs[ 'data-img' ] = $url;
				if ( array_key_exists( 'class', $customAttribs ) ) {
					$customAttribs[ 'class' ] .= ' hovercard';
				} else {
					$customAttribs[ 'class' ] = 'hovercard';
				}
			}
		}
	}

	/**
	 * @param ThumbnailImage $thumbnail
	 * @param array &$attribs
	 * @param array &$linkAttribs
	 */
	public function onThumbnailBeforeProduceHTML( $thumbnail, &$attribs,
		&$linkAttribs
	) {
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
				$url = $this->getFilePath( $target );
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

}
