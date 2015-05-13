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
            'dependency'            => '6'  //dependency1,dependency1StartDate (Y-m-d);dependency2,dependency2StartDate (Y-m-d); ...
            // 'dependency start date' => '7'
         )
      );

   }

   public function sortNullLast ( $x, $y ) {
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
         $dependencyArrayPieces = explode(',', $value); //Dependency name,Dependency date
         $datePiece = $dependencyArrayPieces[1];
         if ( $datePiece != NULL ) {
          $dependencies[$dependencyArrayPieces[0]] = date('Y-m-d', strtotime($datePiece) );
         } else {
          $dependencies[$dependencyArrayPieces[0]] = $datePiece;
         }
      }
      unset($value);

      // User-defined sort to treat NULL as latest date
      uasort( $dependencies, "self::sortNullLast" );
   
      //Format item on manifest to be wiki link
      $itemOnManifestList = explode ( "," , $itemOnManifest );
      $itemOnManifestListModified = array_map (
         function($e){ 
            $eTrimmed = trim($e); 
            return "[[$eTrimmed]]"; },
         $itemOnManifestList
      );

      //LOGIC TO COLOR ROWS
      $rowColor = "transparent";

      foreach ($dependencies as $name => $date) {
        $dateTime = strtotime($date);
        if ( $date != NULL && strtotime($date) < strtotime($manifestDockDate) ) {
          $rowColor = "red"; 
        } else if ( $rowColor != "red" && $date != NULL && strtotime($date) < strtotime( $manifestDockDate ) + strtotime("+$yellowMargin days") ) {
          $rowColor = "yellow"; 
        }
      }
      unset($value);


#
# VARIABLE DEFINITIONS
#
# Initialize variables firstDependency, firstDependencyStartDate, and firstDependencyType
# Check if there is a list of "required for" items. 
# If there is a list, first set the firstDependency to the first item
# if that first item has a Start date, use it
# else if that first item has a crew Dock date, use it
# else, don't set firstDependencyStartDate
#
# Next, loop through the list
# If the Dependency has a Start date
# check if the current firstDependencyStartDate is null
# if so, set this item as the new firstDependency
# else If this item's Start date < firstDependency Start date
# set it as "firstDependency" and set its Start date as "firstDependencyStartDate"
#
# Else, loop through the Dependency's categories
# If one of those categories is Category:Crew
# and If there is a mission with that crew with a Dock date,
# check if the current firstDependencyStartDate is null
# if so, set this crew as the new firstDependency
# else If this crew's Dock date < firstDependency Start date
# set it as "firstDependency" and set its Dock date as "firstDependencyStartDate"
#
# Else, if there is only one "required for",  
# set it as the "firstDependency" 
# and set "firstDependencyStartDate" to the Start date or Dock date accordingly
#
# Variables available to use:
# firstDependency
# firstDependencyStartDate
# firstDependencyType (null or crew)
#

      // $firstDependency = array_shift(array_values($array));
      $firstDependencyStartDate = null;
      $firstDependencyType      = null;


/*
{{#vardefine:firstDependencyStartDate|null}}{{#vardefine:firstDependencyType|null}}{{#if: {{#pos:{{{5|}}}|,}} | 
   {{#vardefine:firstDependency|{{#explode:{{{5|}}}|,|0}}}}
   {{#if: {{#show: {{#var:firstDependency}} |?Start date}}
      | {{#vardefine:firstDependencyStartDate|{{#show: {{#var:firstDependency}} |?Start date}}}}
      | {{#if: {{#arraymap: {{#show:{{#var:firstDependency}}|?Category|link=none}}
             |,
             |VARXY | 
                {{#ifeq: VARXY | Category:Crew | 
                   {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::{{#var:firstDependency}}]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
                 | }} 
             |,
            }} 
         | {{#vardefine:firstDependencyStartDate|{{#arraymap: {{#show:{{#var:firstDependency}}|?Category|link=none}}
             |,
             |VARXY | 
                {{#ifeq: VARXY | Category:Crew | 
                   {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::{{#var:firstDependency}}]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
                 | }} 
             |,
            }} }}{{#vardefine:firstDependencyType|crew}}
         | 
      }}
   }}
   {{#arraymap:{{{5|}}}|,|VARXX|
      {{#if: {{#show: VARXX | ?Start date}} | 
         {{#ifeq:{{#var:firstDependencyStartDate}}|null
         | {{#vardefine:firstDependency|VARXX}}{{#vardefine:firstDependencyStartDate|{{#show: VARXX |?Start date}}}}{{#vardefine:firstDependencyType|null}}
         | {{#ifeq: {{#expr: {{#time: YmdHis | {{#show: VARXX | ?Start date}} }} < {{#time: YmdHis | {{#var:firstDependencyStartDate}} }} }} | 1
            | {{#vardefine:firstDependency|VARXX}}{{#vardefine:firstDependencyStartDate|{{#show: VARXX |?Start date}}}}{{#vardefine:firstDependencyType|null}}
            | }}
         }}
      | {{#if: {{#arraymap: {{#show:VARXX|?Category|link=none}}
             |,
             |VARXY | 
                {{#ifeq: VARXY | Category:Crew | 
                   {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::VARXX]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
                 | }} 
             |,
            }}
         | {{#ifeq:{{#var:firstDependencyStartDate}}|null
            | {{#vardefine:firstDependency|VARXX}}{{#vardefine:firstDependencyStartDate|{{#ask: [[Category:Missions with Dock Dates]] [[Has crew::VARXX]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}}}{{#vardefine:firstDependencyType|crew}}
            | {{#ifeq: {{#expr: {{#time: YmdHis | {{#arraymap: {{#show:VARXX|?Category|link=none}}
                |,
                |VARXY | 
                   {{#ifeq: VARXY | Category:Crew | 
                      {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::VARXX]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
                    | }} 
                |,
               }} }} < {{#time: YmdHis | {{#var:firstDependencyStartDate}} }} }} | 1
            | {{#vardefine:firstDependency|VARXX}}{{#vardefine:firstDependencyStartDate|{{#arraymap: {{#show:VARXX|?Category|link=none}}
                |,
                |VARXY | 
                   {{#ifeq: VARXY | Category:Crew | 
                      {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::VARXX]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
                    | }} 
                |,
               }}}}{{#vardefine:firstDependencyType|crew}}
            | }} 
         }}
         | 
         }}
      }}
   |,}}
| {{#vardefine:firstDependency|{{{5|}}}}}
   {{#if: {{#show: {{#var:firstDependency}} | ?Start date}} 
   | {{#vardefine:firstDependencyStartDate|{{#show: {{#var:firstDependency}} |?Start date}}}} 
   | {{#if: {{#arraymap: {{#show:{{#var:firstDependency}}|?Category|link=none}}
          |,
          |VARXY | 
             {{#ifeq: VARXY | Category:Crew | 
                {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::{{#var:firstDependency}}]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
              | }} 
          |,
         }}
      | {{#vardefine:firstDependencyStartDate|{{#arraymap: {{#show:{{#var:firstDependency}}|?Category|link=none}}
          |,
          |VARXY | 
             {{#ifeq: VARXY | Category:Crew | 
                {{#ask: [[Category:Missions with Dock Dates]] [[Has crew::{{#var:firstDependency}}]]|?Dock date=|mainlabel=-|sort=Dock date|order=desc|limit=1|searchlabel=|link=none}}
              | }} 
          |,
         }}}}{{#vardefine:firstDependencyType|crew}}
      }}
   }}
}}
*/
    
      // OUTPUT
      // $output = "$manifestMission; $fromPage; $itemOnManifest; $manifestLaunchDate; $manifestDockDate; $dependency; $dependencyStartDate;;";
      // $output = "";
      // $output = "$manifestMission; $fromPage; $itemOnManifest; $manifestLaunchDate; $manifestDockDate; $dependencies@@";


      // $output .= var_dump($newDependencies);
      // $output .= var_export($dependencies);

      $output = "<tr style=\"background-color:$rowColor;\">";
    
      $output .= "<td>" . implode (",<br />", $itemOnManifestListModified ) . "</td>";
      $output .= "<td>[[$fromPage]]</td>";
      $output .= "<td>" . date('Y-m-d',strtotime($manifestDockDate)) . "</td>";
      // $output .= "<td>" . implode (",<br />", $dependencyListModified ) . "</td>";
      // $output .= "<td>" . implode (",<br />", $dependencies ) . "</td>";

      //Dependencies
      $output .= "<td>";
      $i = 1;
      foreach ($dependencies as $name => $date) {
         $output .= "[[" . $name . "]]";
         if ( count($dependencies) > 1 ){
            $output .= "<br />";
         }
         $i++;
      }
      unset($value);
      $output .= "</td>";

      //Dependency Dates
      $output .= "<td>";
      $i = 1;
      foreach ($dependencies as $name => $date) {
          if( $date ){
             // $output .= $date;
            // $dateNew = strtotime($date);
            $dateNew = strtotime($date);
             $output .= date('Y-m-d',$dateNew);
          }else{
             $output .= "no date";
          }
         if ( count($dependencies) > 1 ){
            $output .= "<br />";
         }
         $i++;
      }
      unset($value);
      $output .= "</td>";

      $output .= "</tr>";
    
      return $output;

   }

}
