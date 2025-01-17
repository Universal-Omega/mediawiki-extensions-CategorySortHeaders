<?php

/**
 * Pretty much based on UppercaseCollation from core.
 *
 * Collation that is case-insensitive, and allows specify
 * custom 'first-character' headings for category pages.
 */

use MediaWiki\MediaWikiServices;

class CustomHeaderCollation extends Collation {
	public static function onRegistration() {
		global $wgCategoryCollation;

		$wgCategoryCollation = 'CustomHeaderCollation';
	}


	/**
	 * @param string $collationName
	 * @param object $collationObject
	 * @return bool
	 */
	public static function onCategorySortHeadersSetup( $collationName, &$collationObject ) {
		if ( $collationName === 'CustomHeaderCollation' ) {
			$collationObject = new self;

			return false;
		}

		return true;
	}

	
	/**
	 * @param string $string
	 * @return string
	 */
	public function getSortKey( $string ) {
		global $wgCategorySortHeaderAppendPageNameToKey;

		$contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();

		$matches = [];
		if ( preg_match( '/^\^([^\n^]*)\^(.*)$/Ds', $string, $matches ) ) {
			if ( $matches[1] === '' ) {
				$matches[1] = ' ';
			}

			$part1 = $contentLanguage->firstChar( $contentLanguage->uc( $matches[1] ) );
			$part2 = $matches[1];
			$part3prefix = '';

			if ( $wgCategorySortHeaderAppendPageNameToKey ) {
				// This is kind of ugly, and seems wrong
				// because it shouldn't be the collations
				// job to do this type of thing (but then
				// again it shouldn't be doing headers either).

				// See Title::getCategorySortkey if you're
				// mystified by what this does.
				$trimmed = trim( $matches[2], "\n" );
				if ( $trimmed !== $matches[2] ) {
					$part3prefix = $trimmed;
				}
			}

			$part3 = $contentLanguage->uc( $part3prefix . $matches[2] );
		} else {
			// Ordinay sortkey, no header info.
			$part3 = $contentLanguage->uc( $string );
			$part1 = $part2 = $contentLanguage->firstChar( $part3 );
		}

		return $part1 . '^' . $part2 . '^' . $part3;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function getFirstLetter( $string ) {
		# Stolen from UppercaseCollation
		# not sure when this could actually happen.
		if ( $string[0] === "\0" ) {
			$string = substr( $string, 1 );
		}

		$m = [];
		if ( preg_match( '/^\^([^\n^]*)\^/', $string, $m ) ) {
			return $m[1];
		} else { // Probably shouldn't happen
			$contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();

			return $contentLanguage->ucfirst( $contentLanguage->firstChar( $string ) );
		}
	}
}
