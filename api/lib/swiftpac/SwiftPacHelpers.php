<?php
// namespace lib\SwiftPac;

// class SwiftPacHelpers {
	function arrayToObject($array) {
		if (! is_array ( $array )) {
			return $array;
		}
		
		$object = new stdClass ();
		if (is_array ( $array ) && count ( $array ) > 0) {
			foreach ( $array as $name => $value ) {
				$name = strtolower ( trim ( $name ) );
				if (! empty ( $name )) {
					$object->$name = arrayToObject ( $value );
				}
			}
			return $object;
		} else {
			return FALSE;
		}
	}
// }
?>