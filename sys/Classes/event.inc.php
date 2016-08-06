<?php 
/*
**********************************************
ExtCalendar v2
Copyright (c) 2003-2005 Mohamed Moujami (Simo)
v1 originally written by Kristof De Jaeger
**********************************************
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; either version 2 of the License, or 
(at your option) any later version. 
**********************************************
File Description: event.inc.php - Event class
$Id: event.inc.php,v 1.8 2005/02/12 08:55:21 simoami Exp $
**********************************************
Get the latest version of ExtCalendar at:
http://extcal.sourceforge.net
**********************************************
*/


class Event {
    // Set default variables for all new objects

		// Info related variables
    var $title = "";
    var $description = "";
    var $contact = "";
    var $url = "";
    var $link = "";
    var $email = "";
    var $picture = "";
    var $color = "#000000"; // black color by default
    var $catName = "";
    var $catDesc = "";
		
		// Date related variables
    var $startDate = NULL;
    var $endDate = NULL;

		var $startDay = 0;
		var $startMonth = 0;
		var $startYear = 0;

		var $endDay = 0;
		var $endMonth = 0;
		var $endYear = 0;

    var $recurStartDate = NULL; // virtual start and end dates used for recurrent events
    var $recurEndDate = NULL;

		// Other info variables
		var $id = 0;
		var $catId = 0;
    var $status   = false;  // true means approved or active event                
    var $recType = NULL; // Recurrence type: daily, weekly, monthly, yearly or (null for non recurrent events)
		var $recInterval = 0; // Period of recurrence
		var $recEndDate = 0; // recurrence limit
    var $recEndType = 0; // recurrence end type: 0. repeat indefinitely, 1. repeat a number of occurrences, 2. repeat until date ($recEndDate)
    var $recEndCount = NULL; // number of occurrences for a recurrent event. Used in conjunction with $recEndType = 1
	
		function Event() {
      // defining the constructor
	    //global $CONFIG;
	    
	    if($eventId) $this->getEvent($eventId);
		}
		
		function isActive() {
			return $this->$this->status;
		}
		
		function isRecurrent() {
			return empty($this->recType)?false:true;
		}
		
		function setStartDate($start_date) {
			$this->startDate = $start_date;
			$this->startDay = (int)date("d", $this->startDate);
			$this->startMonth = (int)date("m", $this->startDate);
			$this->startYear = (int)date("Y", $this->startDate);
		}

		function setEndDate($end_date) {
			$this->endDate = $end_date;
			$this->endDay = (int)date("d", $this->endDate);
			$this->endMonth = (int)date("m", $this->endDate);
			$this->endYear = (int)date("Y", $this->endDate);
		}

		function isRecurrentOn($target_stamp) {
			global $CONFIG;
			if(!$this->isRecurrent()) return false;
			$day = $this->startDay;
			$month = $this->startMonth;
			$year = $this->startYear;
			$current_stamp = mktime(0,0,0,$month,$day,$year); // reproduce the start date without time info
			$duration = mktime(0,0,0,$this->endMonth,$this->endDay,$this->endYear) - mktime(0,0,0,$this->startMonth,$this->startDay,$this->startYear);
			
			if($this->recEndType == 2 && ($this->recEndDate + $duration < $target_stamp)) return false;

			$target_day = (int)date("d", $target_stamp);
			$target_month = (int)date("m", $target_stamp);
			$target_year = (int)date("Y", $target_stamp);
			$target_stamp = mktime(0,0,0,$target_month,$target_day,$target_year); // reformat the target date without time info
			$recur_count = 1;
			//echo "<p>(".$this->recEndType." != 1 && ".$current_stamp." <= ".$target_stamp.") || (".$this->recEndType." == 1 && ".$recur_count." <= ".$this->recEndCount.")";
			while(($this->recEndType != 1 && $current_stamp <= $target_stamp) || ($this->recEndType == 1 && $recur_count <= $this->recEndCount)) {
				switch($this->recType) {
					case "day":
							$current_stamp = mktime(0,0,0,$month,$day+=$this->recInterval,$year);
							break;
					case "week":
							$current_stamp = mktime(0,0,0,$month,$day+=$this->recInterval*7,$year);
							break;
					case "month":
							$current_stamp = mktime(0,0,0,$month+=$this->recInterval,$day,$year);
							break;
					case "year":
							$current_stamp = mktime(0,0,0,$month,$day,$year+=$this->recInterval);
							break;
					default:
				}
				//echo "GO!";
				$condition_all = $current_stamp <= $target_stamp;
				echo "<br>{$current_stamp} <= {$target_stamp} && ({$current_stamp} + {$duration}) >= {$target_stamp}";
				//$condition_bounds = ($current_stamp == $target_stamp || ($current_stamp + $duration) == $target_stamp);
				//$condition_start = $current_stamp == $target_stamp;
				if($condition_all) echo 1;
				if($condition_all && ($this->recEndType != 2 || $this->recEndDate >= $current_stamp))
				{
					$this->recurStartDate = $current_stamp;
					$this->recurEndDate = $current_stamp + $duration;
					return true;
				}
				$recur_count++;
			}
			
			// no match
			return false;
			
		}

		function get_icon($day_stamp,$start_stamp,$end_stamp) {
			
			$startbound = date('Ymd',$day_stamp) - date('Ymd',$start_stamp); // 0 means event starts same day
			$endbound = date('Ymd',$end_stamp) - date('Ymd',$day_stamp); // 0 means event ends same day
			//echo "STR:".date('Ymd',$start_stamp) . " STO:".date('Ymd',$end_stamp). " DAY:".date('Ymd',$day_stamp)."<br>";
			
			$image = "icon-event-onedate.gif"; // default event icon
			if(!$startbound && !$endbound) $image = "icon-event-onedate.gif";
			elseif(!$startbound && $endbound>0) $image = "icon-event-startdate.gif";
			elseif($startbound>0 && !$endbound) $image = "icon-event-enddate.gif";
			elseif($startbound>0 && $endbound>0) $image = "icon-event-middate.gif";
			//else return false;
			
			return $image;
		
		}
		
		function get_style($day_stamp,$start_stamp,$end_stamp) {
			
			$startbound = date('Ymd',$day_stamp) - date('Ymd',$start_stamp); // 0 means event starts same day
			$endbound = date('Ymd',$end_stamp) - date('Ymd',$day_stamp); // 0 means event ends same day
			
			$class = "eventfull"; // default event class
			if(!$startbound && !$endbound) $class = "eventfull";
			elseif(!$startbound && $endbound>0) $class = "eventstart";
			elseif($startbound>0 && !$endbound) $class = "eventend";
			elseif($startbound>0 && $endbound>0) $class = "eventmiddle";
			//else return false;
			
			return $class;
		
		}

}

?>