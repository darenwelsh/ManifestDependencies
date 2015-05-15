<?php
/** 
 * This is the Manifest Dependencies extension
 * See README.md.
 * 
 * Documentation: https://github.com/darenwelsh/ManifestDependencies
 * Support:       https://github.com/darenwelsh/ManifestDependencies
 * Source code:   https://github.com/darenwelsh/ManifestDependencies
 *
 * @file ManifestDependencies.php
 * @addtogroup Extensions
 * @author Daren Welsh
 * @copyright © 2014 by Daren Welsh
 * @licence GNU GPL v3+
 */

# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( ! defined( 'MEDIAWIKI' ) ) {
	die( 'ManifestDependencies extension' );
}

$GLOBALS['wgExtensionCredits']['parserhook'][] = array(
	'path'           => __FILE__,
	'name'           => 'ManifestDependencies',
	'namemsg'        => 'manifestdependencies-name',
	'url'            => 'http://github.com/darenwelsh/ManifestDependencies',
	'author'         => 'Daren Welsh',
	'descriptionmsg' => 'manifestdependencies-desc',
	'version'        => '0.1.0'
);

$GLOBALS['wgMessagesDirs']['ManifestDependencies'] = __DIR__ . '/i18n';
$GLOBALS['wgExtensionMessagesFiles']['ManifestDependenciesMagic'] = __DIR__ . '/Magic.php';

// Autoload for setup
$GLOBALS['wgAutoloadClasses']['ManifestDependencies\Setup'] = __DIR__ . '/includes/Setup.php';

// Autoload for each parser function
$GLOBALS['wgAutoloadClasses']['ManifestDependencies\ManifestDependenciesReport'] = __DIR__ . '/includes/ManifestDependenciesReport.php';
$GLOBALS['wgAutoloadClasses']['ManifestDependencies\EVAManifestDependencies'] = __DIR__ . '/includes/EVAManifestDependencies.php';

// Setup parser functions
$GLOBALS['wgHooks']['ParserFirstCallInit'][] = 'ManifestDependencies\Setup::setupParserFunctions';



// $ExtensionMeetingMinutesResourceTemplate = array(
// 	'localBasePath' => __DIR__ . '/modules',
// 	'remoteExtPath' => 'MeetingMinutes/modules',
// );

// $GLOBALS['wgResourceModules'] += array(

// 	'ext.meetingminutes.form' => $ExtensionMeetingMinutesResourceTemplate + array(
// 		'styles' => 'form/meeting-minutes.css',
// 		'scripts' => array( 'form/SF_MultipleInstanceRefire.js', 'form/meeting-minutes.js' ),
// 		// 'dependencies' => array( 'mediawiki.Uri' ),
// 	),

// 	'ext.meetingminutes.template' => $ExtensionMeetingMinutesResourceTemplate + array(
// 		'styles' => 'template/template.css',
// 	),

// );