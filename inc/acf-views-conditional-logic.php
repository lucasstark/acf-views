<?php

class ACF_Views_Conditional_Logic {

	public static function show_field( $field, $fields ) {
		$result = null;
		if ( isset( $field['conditional_logic'] ) && !empty( $field['conditional_logic'] ) ) {
			foreach ( $field['conditional_logic'] as $logic_group ) {
				$group_result = true;
				foreach ( $logic_group as $rule ) {
					$other_field = wp_list_filter( $fields, array('key' => $rule['field']) );
					$other_value = null;
					if ( $other_field ) {
						$other_field = array_shift( $other_field );
						$other_value = isset( $other_field['value'] ) ? $other_field['value'] : null;
					}
					switch ( $rule['operator'] ) {
						case '==' :
							if ( is_array( $other_value ) ) {
								$group_result &= ( in_array( $other_value, $rule['value'] ) );
							} else {
								$group_result &= ( $other_value == $rule['value'] );
							}
							break;
						case '!=' :
							if ( is_array( $other_value ) ) {
								$group_result &= (!in_array( $other_value, $rule['value'] ));
							} else {
								$group_result &= ($other_value != $rule['value']);
							}
							break;
						default:
							break;
					}
				}
				
				if ( $result == null ) {
					$result = $group_result;
				} else {
					$result |= $group_result;
				}
			}
		}

		return $result !== null ? $result : true;
	}

}
