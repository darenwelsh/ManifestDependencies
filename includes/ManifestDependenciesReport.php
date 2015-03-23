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
         $parser,
         'manifestdependenciesreport',
         array( 'content' => '' ),
         array()
      );

   }

   public function render ( \Parser &$parser, $params ) {

      // $manifestMission = $params[‘manifest mission’];
      // $fromPage = $params[‘from page’];
      // $itemOnMission = $params[‘item on mission’];
      // $manifestLaunchDate = $params[‘manifest launch date’];
      // $dependency = $params[‘dependency’];
    
      // insert your logic here
      // don’t worry about how stuff gets into the params field
    
      // example:
      $output = "this template returns manifest info from [[$manifestMission]] ";
    
      $output .= "which is launching on $manifestLaunchDate";
    
      return $output;
      // return "";

   }

}
