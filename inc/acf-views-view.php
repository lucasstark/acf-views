<?php

class ACF_Views_View {

	public $args;
	public $post_id;

	public function __construct( $args = array() ) {
		
		//Some of these args will be changed in the future, not all are required at this point. 
		$this->args = wp_parse_args( $args, array(
		    'id' => 'acf-form',
		    'post_id' => false,
		    'new_post' => false,
		    'field_groups' => false,
		    'fields' => false,
		    'post_title' => false,
		    'post_content' => false,
		    'return' => add_query_arg( 'updated', 'true', acf_get_current_url() ),
		    'html_before_fields' => '',
		    'html_after_fields' => '',
		    'submit_value' => __( "Update", 'acf' ),
		    'updated_message' => __( "Post updated", 'acf' ),
		    'label_placement' => 'top',
		    'instruction_placement' => 'label',
		    'field_el' => 'div',
		    'uploader' => 'wp'
		) );

		// filter post_id
		$this->args['post_id'] = acf_get_valid_post_id( $this->args['post_id'] );
		// load values from this post
		$this->post_id = $this->args['post_id'];

		if ( $this->post_id == 'new_post' ) {
			throw new Exception( __( 'Post ID must be set when using the acf_view function', 'acf-views' ) );
		}
		
		//This will eventually be moved from an action into a better object oriented rendering method. 
		add_action( "acf/views_render_field", array($this, 'on_render_field'), 0, 1 );
	}

	public function render( ) {
		acf_enqueue_scripts();
		
		do_action( 'acf/view', $this->args, $this );

		$post_id = $this->post_id;
		$args = $this->args;
		
		// vars
		$field_groups = array();
		$fields = array();

		// post_title
		if ( $args['post_title'] ) {

			$fields[] = acf_get_valid_field( array(
			    'name' => '_post_title',
			    'label' => 'Title',
			    'type' => 'text',
			    'value' => $post_id ? get_post_field( 'post_title', $post_id ) : '',
			    'required' => true
				) );
		}


		// post_content
		if ( $args['post_content'] ) {

			$fields[] = acf_get_valid_field( array(
			    'name' => '_post_content',
			    'label' => 'Content',
			    'type' => 'wysiwyg',
			    'value' => $post_id ? get_post_field( 'post_content', $post_id ) : ''
				) );
		}


		// specific fields
		if ( $args['fields'] ) {

			foreach ( $args['fields'] as $selector ) {

				// append field ($strict = false to allow for better compatibility with field names)
				$fields[] = acf_maybe_get_field( $selector, $post_id, false );
			}
		} elseif ( $args['field_groups'] ) {

			foreach ( $args['field_groups'] as $selector ) {

				$field_groups[] = acf_get_field_group( $selector );
			}
		} elseif ( $args['post_id'] == 'new_post' ) {

			$field_groups = acf_get_field_groups( array(
			    'post_type' => $args['new_post']['post_type']
				) );
		} else {

			$field_groups = acf_get_field_groups( array(
			    'post_id' => $args['post_id']
				) );
		}


		//load fields based on field groups
		if ( !empty( $field_groups ) ) {

			foreach ( $field_groups as $field_group ) {

				$field_group_fields = acf_get_fields( $field_group );

				if ( !empty( $field_group_fields ) ) {

					foreach ( array_keys( $field_group_fields ) as $i ) {

						$fields[] = acf_extract_var( $field_group_fields, $i );
					}
				}
			}
		}
		?>
		<div class="acf-fields acf-form-fields -<?php echo $args['label_placement']; ?>">

		<?php
		// html before fields
		echo $args['html_before_fields'];
		// render
		acf_views_render_fields( $post_id, $fields, $args['field_el'], $args['instruction_placement'] );
		// html after fields
		echo $args['html_after_fields'];
		?>

		</div><!-- acf-form-fields -->
		<?php
	}

	public function on_render_field( $field ) {
		switch ( $field['type'] ) {
			case 'color_pikcer':
			case 'date_picker':
			case 'email':
			case 'number':
			case 'text':
			case 'textarea':
			case 'url':
			case 'wysiwyg':
				$this->render_simple_field( $field );
				break;
			case 'repeater' :
				$this->render_repeater( $field );
				break;
			case 'flexible_content' :
				$this->render_flexible_content( $field );
				break;
			case 'checkbox':
				$this->render_options_field( $field );
				break;
			case 'select':
			case 'radio':
				$this->render_options_field( $field );
				break;
			case 'tab' :
				$this->render_tab_field( $field );
				break;
			default:
				$this->render_simple_field( $field );
				break;
		}
	}

	public function render_options_field( $field ) {

		echo '<div class="acf-output-wrap">';
		$value = acf_format_value( $field['value'], $this->post_id, $field );
		$output = array();
		if ( is_array( $value ) ) {
			foreach ( $value as $v ) {
				if ( isset( $field['choices'][$v] ) ) {
					$output[] = $field['choices'][$v];
				} else {
					$output[] = $value;
				}
			}
		} else {
			if ( isset( $field['choices'][$value] ) ) {
				$output[] = $field['choices'][$value];
			} else {
				$output[] = $value;
			}
		}

		echo strip_tags( implode( ', ', $output ), '' );
		echo '</div>';
	}

	public function render_simple_field( $field ) {
		echo '<div class="acf-output-wrap">';
		$value = acf_format_value( $field['value'], $this->post_id, $field );
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
		echo ( $value );
		echo '</div>';
	}

	public function render_repeater( $field ) {
		// field wrap
		$el = 'td';
		$before_fields = '';
		$after_fields = '';

		if ( $field['layout'] == 'row' ) {
			$el = 'div';
			$before_fields = '<td class="acf-fields -left">';
			$after_fields = '</td>';
		} elseif ( $field['layout'] == 'block' ) {
			$el = 'div';
			$before_fields = '<td class="acf-fields">';
			$after_fields = '</td>';
		}

		include ACF_Views()->plugin_path() . '/templates/field-repeater.php';
	}

	public function render_flexible_content( $field ) {
		// sort layouts into names
		$layouts = array();
		foreach ( $field['layouts'] as $k => $layout ) {
			$layouts[$layout['name']] = acf_extract_var( $field['layouts'], $k );
		}

		include ACF_Views()->plugin_path() . '/templates/field-flexible-content.php';
	}

	public function render_tab_field( $field ) {
		$atts = array(
		    'class' => 'acf-tab-output',
		    'data-placement' => $field['placement'],
		    'data-endpoint' => $field['endpoint']
		);

		echo '<div ' . acf_esc_attr( $atts ) . '>' . $field['label'] . '</div>';
	}

}
