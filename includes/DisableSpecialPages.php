<?php
/**
 * Extension allows wiki administrators to make a special page unavailable
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>
 * @copyright © 2006 Rob Church
 * @licence GNU General Public Licence 2.0 or later
 */

namespace MediaWiki\Extension;

use MediaWiki\MediaWikiServices;
use Title;
use SpecialPage;
use MediaWiki\SpecialPage\SpecialPageFactory as SpecialPageFactory;

class DisableSpecialPages {

	/**
	 * Hook handler for the SpecialPage_initList hook - the main logic happens here.
	 *
	 * We iterate over the config variable, try to construct a Title for each listed page (if any),
	 * and if that's successful, we make sure that it is 1) not a whitelisted page and 2) is a real
	 * special page; and if so, the page gets unset from the list of special pages.
	 * Simple as that!
	 *
	 * @param array &$list List of all registered special pages
	 */
	public static function onSpecialPage_initList( &$list ) {
		global $wgDisabledSpecialPages;

		if ( $wgDisabledSpecialPages !== [] ) {
			foreach ( $wgDisabledSpecialPages as $page ) {
				$title = Title::makeTitle( NS_SPECIAL, $page );
				if ( !$title ) {
					continue;
				}

				$canonicalName = MediaWikiServices::getInstance()->getSpecialPageFactory()->resolveAlias( $title->getDBkey() )[0];
				if ( !self::isWhitelisted( $canonicalName ) && isset( $list[$canonicalName] ) ) {
					unset( $list[$canonicalName] );
				}
			}
		}
	}

	/**
	 * Is the given special page (name) a whitelisted special page, i.e. one that cannot be disabled?
	 *
	 * @param string $title Canonical special page name, such as "Userlogout" or "Recentchangeslinked" etc.
	 * @return bool True if it is whitelisted, otherwise false
	 */
	public static function isWhitelisted( $title ) {
		static $whitelist = [ 'Search', 'Userlogin', 'Userlogout' ];
		return in_array( $title, $whitelist );
	}

}