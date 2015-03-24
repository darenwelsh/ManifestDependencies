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
         array( 'manifest mission' => '' ),
         array( 'from page' => '' ),
         array( 'item on manifest' => '' ),
         array( 'manifest launch date' => '' ),
         array( 'manifest dock date' => '' ),
         array( 'dependency' => '' ),
         array( 'dependency start date' => '' ),
         array()
      );

   }

   public function render ( \Parser &$parser, $params ) {

      $manifestMission = $params['manifest mission'];
      $fromPage = $params['from page'];
      $itemOnMission = $params['item on manifest'];
      $manifestLaunchDate = $params['manifest launch date'];
      $manifestDockDate = $params['manifest dock date'];
      $dependency = $params['dependency'];
      $dependencyStartDate = $params['dependency start date'];
    
      // insert your logic here
      // don’t worry about how stuff gets into the params field
    
      // example:
      $output = "this template returns manifest info from [[$manifestMission]] ";
    
      $output .= "which is launching on $manifestLaunchDate";
    
      return $output;
      // return "";

   }

}
