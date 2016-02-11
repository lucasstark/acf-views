<?php

/**
 * 
 * @param array $args: an array containing many options to customize the form
 * 			string		+ post_id: post id to get field groups from and save data to. Default is false
 * 			array		+ field_groups: an array containing field group ID's. If this option is set, 
 * 						  the post_id will not be used to dynamically find the field groups
 * 			array		+ form_attributes: an array containg attributes which will be added into the form tag
 * 			string		+ return: the return URL
 * 			string		+ html_before_fields: html inside form before fields
 * 			string		+ html_after_fields: html inside form after fields
 * 			string		+ submit_value: value of submit button
 * 			string		+ updated_message: default updated message. Can be false
 * @return \ACF_Views_View
 */
function acf_view( $args = array() ) {
	$view = new ACF_Views_View( $args );
	$view->render();
	return $view;
}

/*
 * The rest of these functions will be moved to class methods and will be updated to use templates rather than any form of HTML mixed with the PHP. 
 */

function acf_views_render_fields( $post_id = 0, $fields, $el = 'div', $instruction = 'label' ) {

	// bail early if no fields
	if ( empty( $fields ) ) {
		return false;
	}

	// remove corrupt fields
	$fields = array_filter( $fields );

	//Load all the values first
	foreach ( $fields as &$ref_field ) {
		// load value
		if ( $ref_field['value'] === null ) {
			$ref_field['value'] = acf_get_value( $post_id, $ref_field );
		}
	}

	$hiding_fields = false;
	// loop through fields
	foreach ( $fields as $field ) {

		// load value
		if ( $field['value'] === null ) {
			$field['value'] = acf_get_value( $post_id, $field );
		}
		// set prefix for correct post name (prefix + key)
		$field['prefix'] = 'acf';
		// render

		$show_field = true;
		if ( !empty( $field['conditional_logic'] ) ) {
			$show_field = ACF_Views_Conditional_Logic::show_field( $field, $fields );
		}

		if ( $field['type'] == 'tab' ) {
			if ( !$show_field ) {
				$hiding_fields = true;
			} else {
				$hiding_fields = false;
			}
		}

		if ( !$hiding_fields ) {
			if ( $show_field ) {
				if ( $field['type'] == 'repeater' ) {
					$x = 1;
				}

				acf_views_render_field_wrap( $field, $el, $instruction );
			}
		}
	}
}

function acf_views_render_field_wrap( $field, $el = 'div', $instruction = 'label' ) {

	// get valid field
	$field = acf_get_valid_field( $field );


	// prepare field for input
	$field = acf_prepare_field( $field );


	// el
	$elements = apply_filters( 'acf/render_field_wrap/elements', array(
	    'div' => 'div',
	    'tr' => 'td',
	    'ul' => 'li',
	    'ol' => 'li',
	    'dl' => 'dt',
	    'td' => 'div' // special case for sub field!
		) );


	// validate $el
	if ( !array_key_exists( $el, $elements ) ) {
		$el = 'div';
	}


	// wrapper
	$wrapper = array(
	    'id' => '',
	    'class' => 'acf-field',
	    'width' => '',
	    'style' => '',
	    'data-name' => $field['name'],
	    'data-type' => $field['type'],
	    'data-key' => '',
	);


	// add required
	if ( $field['required'] ) {
		$wrapper['data-required'] = 1;
	}


	// add type
	$wrapper['class'] .= " acf-output-{$field['type']}";


	// add key
	if ( $field['key'] ) {
		$wrapper['class'] .= " acf-field-{$field['key']}";
		$wrapper['data-key'] = $field['key'];
	}


	// replace
	$wrapper['class'] = str_replace( '_', '-', $wrapper['class'] );
	$wrapper['class'] = str_replace( 'field-field-', 'field-', $wrapper['class'] );


	// merge in atts
	$wrapper = acf_merge_atts( $wrapper, $field['wrapper'] );


	// add width
	$width = (int) acf_extract_var( $wrapper, 'width' );

	if ( $el == 'tr' || $el == 'td' ) {

		$width = 0;
	} elseif ( $width > 0 && $width < 100 ) {

		$wrapper['data-width'] = $width;
		$wrapper['style'] .= " width:{$width}%;";
	}


	// remove empty attributes
	foreach ( $wrapper as $k => $v ) {

		if ( $v == '' ) {

			unset( $wrapper[$k] );
		}
	}


	// vars
	$show_label = true;

	if ( $el == 'td' ) {

		$show_label = false;
	}
	?><<?php echo $el; ?> <?php echo acf_esc_attr( $wrapper ); ?>>
	<?php if ( $show_label ): ?>
		<<?php echo $elements[$el]; ?> class="acf-label">
		<label for="<?php echo $field['id']; ?>"><?php echo acf_get_field_label( $field ); ?></label>
		<?php if ( $instruction == 'label' && $field['instructions'] ): ?>
			<p class="description"><?php echo $field['instructions']; ?></p>
		<?php endif; ?>
		</<?php echo $elements[$el]; ?>>
	<?php endif; ?>
	<<?php echo $elements[$el]; ?> class="acf-input">

	<?php acf_views_render_field( $field ); ?>

	<?php if ( $instruction == 'field' && $field['instructions'] ): ?>
		<p class="description"><?php echo $field['instructions']; ?></p>
	<?php endif; ?>
	</<?php echo $elements[$el]; ?>>

	</<?php echo $el; ?>>
	<?php
}

function acf_views_render_field( $field = false ) {

	// get valid field
	$field = acf_get_valid_field( $field );


	// prepare field for input
	$field = acf_prepare_field( $field );


	// update $field['name']
	$field['name'] = $field['_input'];


	// create field specific html
	do_action( "acf/views_render_field", $field );
	do_action( "acf/views_render_field/type={$field['type']}", $field );
}
