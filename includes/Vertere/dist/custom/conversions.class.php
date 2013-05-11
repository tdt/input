<?php
/*
 * Class for custom conversion methods
 */
class Conversions {
    /*
     * Converts a value from feet to metres
     */
	public static function feet_to_metres($value) {
		return ($value * 0.3048);
	}

	public static function metres_to_feet($value) {
		return ($value * 3.2808);
	}
    
    public static function to_xsd_date($value) {
        $months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
        
        $matches = array();
        preg_match_all("/([0-9]{2})\s*?([a-zA-Z]{3})\s*?([0-9]{4})\s*?[0-9]{2}:[0-9]{2}:[0-9]{2}/", $value, $matches);

        $day = $matches[1][0];
        $month = str_pad(array_search($matches[2][0],$months) + 1, 2, "0",STR_PAD_LEFT);
        $year = $matches[3][0];

        $date = "$year-$month-$day";

        return $date;
    }
    
    public static function to_xsd_time($value) {
        $matches = array();
        preg_match_all("/[0-9]{2}\s*?[a-zA-Z]{3}\s*?[0-9]{4}\s*?([0-9]{2}:[0-9]{2}:[0-9]{2})/", $value, $matches);

        $time = $matches[1][0] . "+02:00";

        return $time; 
    }
    
    public static function to_xsd_dateTime($value) {
        $months = array("Jan","Feb","Mar","Apr","Mai","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
        
        $matches = array();
        preg_match_all("/([0-9]{2})\s*?([a-zA-Z]{3})\s*?([0-9]{4})\s*?([0-9]{2}:[0-9]{2}:[0-9]{2})/", $value, $matches);

        $day = $matches[1][0];
        $month = str_pad(array_search($matches[2][0],$months) + 1, 2, "0",STR_PAD_LEFT);
        $year = $matches[3][0];
        
        $time = $matches[4][0];

        $date = "$day-$month-$year" . "T$time+02:00";
        echo "$value was turned into $date";
        return $date;
    }
    
    /*
     * Example pass-through function
     */
    public static function mirror($value) {
		return $value;
	}
}