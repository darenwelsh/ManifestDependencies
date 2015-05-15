<?php
/**
 *
 * @addtogroup Extensions
 * @author Daren Welsh
 * @copyright © 2014 by Daren Welsh
 * @licence GNU GPL v3+
 */

/* NOTES

Ref https://github.com/jamesmontalvo3/MediaWiki-ApprovedRevs/blob/master/includes/ApprovedRevs_body.php#L597
Learn how to query from within extension

http://php.net/manual/en/datetime.format.php
http://php.net/manual/en/function.date.php

*/

namespace ManifestDependencies;
use ParserFunctionHelper\ParserFunctionHelper;

// This class generates the MD Report one row at a time. So each object is actually just one row.
class ManifestDependenciesReport extends ParserFunctionHelper {


   public function __construct ( \Parser &$parser ) {

      parent::__construct(
         $parser, // mediawiki parser object
         'manifestdependenciesreport', // parser function name
         array( // unnamed parameters (numeric index params)
         ),
         array( // named parameters AKA "named index params" (empty array)
            'manifest mission'      => '1', //subobject
            'from page'             => '2',
            'item on manifest'      => '3',
            'manifest launch date'  => '4', // (Y-m-d)
            'manifest dock date'    => '5', // (Y-m-d)
            'dependency'            => '6'  //dependency1,dependency1StartDate (Y-m-d),dependency1DockDate (Y-m-d);...
            // 'dependency start date' => '7'
         )
      );

   }

   public function sortNullLast ( $a, $b ) {
      $x = $a["date"];
      $y = $b["date"];
      if( $x == $y ){
        return 0;
      } else if ( $x > $y && $y != NULL ){ //neither is NULL
        return 1;
      } else if ( $x == NULL ) {
        return 1;
      } else {
        return -1;
      }
   }

    //Pass in SMW property and do stuff (stolen from James for modification)
    public static function smwPropertyEqualsCurrentUser ( $userProperty ) {
      global $wgTitle, $wgUser;
          
      if ( ! class_exists('SMWHooks') ) // if semantic not installed
        die('Semantic MediaWiki must be installed to use the ApprovedRevs "Property" definition.');
      else {  
        $valueDis = smwfGetStore()->getPropertyValues( 
          new SMWDIWikiPage( $wgTitle->getDBkey(), $wgTitle->getNamespace(), '' ),
          new SMWDIProperty( SMWPropertyValue::makeUserProperty( $userProperty )->getDBkey() ) );   // trim($userProperty)
        
        foreach ($valueDis as $valueDI) {
          if ( ! $valueDI instanceof SMWDIWikiPage )
            throw new Exception('ApprovedRevs "Property" permissions must use Semantic MediaWiki properties of type "Page"');
          if ( $valueDI->getTitle()->getText() == $wgUser->getUserPage()->getText() )
            return true;
        }
      }
      return false;
    }

   public function render ( \Parser &$parser, $params ) {

      //Variables
      // $yellowMargin = strtotime("+30 days");
      $yellowMargin = 30;
      $manifestMission = $params['manifest mission'];
      $fromPage = $params['from page'];
      $itemOnManifest = $params['item on manifest'];
      $manifestLaunchDate = $params['manifest launch date'];
      $manifestDockDate = $params['manifest dock date'];
      $dependency = $params['dependency'];

      //Split dependencies out to dependency => date
      $dependencyArray = explode(';', $dependency); //Split each dependency apart
      foreach ($dependencyArray as &$value) {
         $dependencyArrayPieces = explode(',', $value); //Dependency name,Dependency start date,Dependency dock date
         $dependencies[$dependencyArrayPieces[0]]["name"] = $dependencyArrayPieces[0];
         if ( $dependencyArrayPieces[1] != NULL ) {
          $dependencies[$dependencyArrayPieces[0]]["date"] = date('Y-m-d', strtotime($dependencyArrayPieces[1]) );
         } else if ( $dependencyArrayPieces[2] != NULL ) {
          $dependencies[$dependencyArrayPieces[0]]["date"] = date('Y-m-d', strtotime($dependencyArrayPieces[2]) );
          $dependencies[$dependencyArrayPieces[0]]["type"] = "crew";
         } else {
          $dependencies[$dependencyArrayPieces[0]]["date"] = $dependencyArrayPieces[1];
         }
      }
      unset($value);

      // User-defined sort to treat NULL as latest date
      uasort( $dependencies, "self::sortNullLast" );

      //Format item on manifest to be wiki link
      //Not sure if this is needed any more
      // $itemOnManifestList = explode ( "," , $itemOnManifest );
      // $itemOnManifestListModified = array_map (
      //    function($e){ 
      //       $eTrimmed = trim($e); 
      //       return "[[$eTrimmed]]"; },
      //    $itemOnManifestList
      // );

      //LOGIC TO COLOR ROWS
      $rowColor = "transparent";
      foreach ($dependencies as $dep ) {
        $date = $dep["date"];
        $dateTime = strtotime($date);
        $manifestDockDateTime = strtotime($manifestDockDate);
        $ff = strtotime("$manifestDockDate + $yellowMargin days");
        if ( $date != NULL && strtotime($date) < strtotime($manifestDockDate) ) {
          $rowColor = "red"; 
        } else if ( $rowColor != "red" && $dep["type"] != "crew" && $date != NULL && strtotime($date) < strtotime("$manifestDockDate + $yellowMargin days") ) {
          $rowColor = "yellow"; 
        }
      }
      unset($dep);
    
      // OUTPUT
      $output = "<tr style=\"background-color:$rowColor;\">";
    
      // $output .= "<td>" . implode (",<br />", $itemOnManifestListModified ) . "</td>"; //not sure if this method is necessary any more
      $output .= "<td>[[" . $itemOnManifest . "]]</td>";
      $output .= "<td>[[$fromPage]]</td>";
      $output .= "<td>" . date('d F Y',strtotime($manifestDockDate)) . "</td>";

      //Dependencies
      $output .= "<td>";
      $i = 1;
      foreach ($dependencies as $dep) {
         $output .= "[[" . $dep["name"] . "]]";
         if ( count($dep) > 1 ){
            $output .= "<br />";
         }
         $i++;
      }
      unset($dep);
      $output .= "</td>";

      //Dependency Dates
      $output .= "<td>";
      $i = 1;
      foreach ($dependencies as $dep) {
        $date = $dep["date"];
        if( $date ){
          $dateNew = strtotime($date);
           $output .= date('d F Y',$dateNew);
        }else{
           $output .= "no date";
        }
       if ( count($dep) > 1 ){
          $output .= "<br />";
       }
       $i++;
      }
      unset($dep);
      $output .= "</td>";

      $output .= "</tr>";
    
      return $output;

   }

}
