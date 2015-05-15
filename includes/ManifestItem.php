<?php
/**
 *
 * @addtogroup Extensions
 * @author Daren Welsh
 * @copyright © 2014 by Daren Welsh
 * @licence GNU GPL v3+
 */

/* NOTES



*/

namespace ManifestDependencies;
use ParserFunctionHelper\ParserFunctionHelper;

// This class generates the list of items manifested on a mission one row at a time for an EVA page. So each object is actually just one row.
class ManifestItem extends ParserFunctionHelper {


   public function __construct ( \Parser &$parser ) {

      parent::__construct(
         $parser, // mediawiki parser object
         'manifestitem', // parser function name
         array( // unnamed parameters (numeric index params)
         ),
         array( // named parameters AKA "named index params" (empty array)
            'manifest mission'      => '1', //subobject
            'from page'             => '2',
            'item on manifest'      => '3',
            'manifest launch date'  => '4', // (Y-m-d)
            'manifest dock date'    => '5', // (Y-m-d)
            'dependency'            => '6',  //dependency1,dependency1StartDate (Y-m-d),dependency1DockDate (Y-m-d);...
            // 'item title'            => '7',
            // 'part number'           => '8',
            // 'serial number'         => '9',
            // 'quantity'              => '10',
            // 'updown'                => '11',
            // 'notes'                 => '12'
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
    
      $output .= "<td style='vertical-align:top;text-align=center;'>[[" . $itemOnManifest . "]]</td>";
      $output .= "<td>[[$fromPage]]";
      $output .= " (" . date('d F Y',strtotime($manifestDockDate)) . ")</td>";

      $output .= "</tr>";
    
      return $output;

   }

}
