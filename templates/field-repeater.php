<div class="acf-repeater -<?php echo $field['layout']; ?>">
	<table class="acf-table">
		<?php if ( $field['layout'] == 'table' ): ?>
			<thead>
				<tr>
					

					<?php
					foreach ( $field['sub_fields'] as $sub_field ):

						$atts = array(
						    'class' => 'acf-th',
						    'data-key' => $sub_field['key'],
						);


						// add type
						$atts['class'] .= ' acf-th-' . $sub_field['type'];


						// Add custom width
						if ( $sub_field['wrapper']['width'] ) {

							$atts['data-width'] = $sub_field['wrapper']['width'];
						}
						?>
						<th <?php acf_esc_attr_e( $atts ); ?>>
							<?php echo acf_get_field_label( $sub_field ); ?>
							<?php if ( $sub_field['instructions'] ): ?>
					<p class="description"><?php echo $sub_field['instructions']; ?></p>
				<?php endif; ?>
				</th>

			<?php endforeach; ?>
			</tr>
			</thead>
		<?php endif; ?>

		<tbody>
			<?php
			foreach ( $field['value'] as $i => $row ):
				if ( $i === 'acfcloneindex' ):
					continue;
				endif;

				$row_class = 'acf-row';
				?>
				<tr class="<?php echo $row_class; ?>" data-id="<?php echo $i; ?>">

					

					<?php echo $before_fields; ?>

					<?php
					foreach ( $field['sub_fields'] as $sub_field ):

						// prevent repeater field from creating multiple conditional logic items for each row
						if ( $i !== 'acfcloneindex' ) {
							$sub_field['conditional_logic'] = 0;
						}


						// add value
						if ( isset( $row[$sub_field['key']] ) ) {
							// this is a normal value
							$sub_field['value'] = $row[$sub_field['key']];
						} elseif ( isset( $sub_field['default_value'] ) ) {

							// no value, but this sub field has a default value
							$sub_field['value'] = $sub_field['default_value'];
						}


						// update prefix to allow for nested values
						$sub_field['prefix'] = "{$field['name']}[{$i}]";

						// render input
						acf_views_render_field_wrap( $sub_field, $el );
						?>

					<?php endforeach; ?>

					<?php echo $after_fields; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

