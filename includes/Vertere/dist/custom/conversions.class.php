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
    
    /*
     * Example pass-through function
     */
    public static function mirror($value) {
		return $value;
	}
}