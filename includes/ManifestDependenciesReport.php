<?php
/**
 *
 * @addtogroup Extensions
 * @author Daren Welsh
 * @copyright © 2014 by Daren Welsh
 * @licence GNU GPL v3+
 */

namespace ManifestDependencies;
use ParserFunctionHelper\ParserFunctionHelper;

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
            'manifest launch date'  => '4',
            'manifest dock date'    => '5',
            'dependency'            => '6',
            'dependency start date' => '7'
         )
      );

   }

   // public function 

   public function render ( \Parser &$parser, $params ) {

      $manifestMission = $params['manifest mission'];
      $fromPage = $params['from page'];
      $itemOnManifest = $params['item on manifest'];
      $manifestLaunchDate = $params['manifest launch date'];
      $manifestDockDate = $params['manifest dock date'];
      $dependency = $params['dependency'];
      $dependencyStartDate = $params['dependency start date'];
   
      $itemOnManifestList = explode ( "," , $itemOnManifest );
      $itemOnManifestListModified = array_map (
         function($e){ 
            $eTrimmed = trim($e); 
            return "[[$eTrimmed]]"; },
         $itemOnManifestList
      );

      $dependencyList = explode ( "," , $dependency );
      $dependencyListModified = array_map (
         function($e){ 
            $eTrimmed = trim($e); 
            return "[[$eTrimmed]]"; },
         $dependencyList
      );


      // insert your logic here
      // don’t worry about how stuff gets into the params field
    
      // example:
      $output = "<tr>";
    
      $output .= "<td>" . implode (",<br />", $itemOnManifestListModified ) . "</td>";
      $output .= "<td>[[$fromPage]]</td>";
      $output .= "<td>$manifestDockDate</td>";
      $output .= "<td>" . implode (",<br />", $dependencyListModified ) . "</td>";
      $output .= "<td>$dependencyStartDate</td>";

      $output .= "</tr>";
    
      return $output;

   }

}
