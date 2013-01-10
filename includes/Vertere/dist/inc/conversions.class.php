<?php

class Conversions {

	public static function feet_to_metres($value) {
		return ($value * 0.3048);
	}

	public static function metres_to_feet($value) {
		return ($value * 3.2808);
	}
}