<?php
// vars
$order = 0;
$el = 'div';
$div = array(
    'class' => 'layout',
    'data-id' => $i,
    'data-layout' => $layout['name']
);

// clone
if ( is_numeric( $i ) ) {
	$order = $i + 1;
} else {
	$div['class'] .= ' acf-clone';
}
?>
<div <?php acf_esc_attr_e( $div ); ?>>

	<div class="acf-fc-layout-handle">
		<span class="fc-layout-order"><?php echo $order; ?></span> <?php echo $layout['label']; ?>
	</div>
	
	<?php if ( !empty( $layout['sub_fields'] ) ): ?>

		<?php
		if ( $layout['display'] == 'table' ):

			// update vars
			$el = 'td';
			?>
			<table class="acf-table">

				<thead>
					<tr>
						<?php
						foreach ( $layout['sub_fields'] as $sub_field ):

							$atts = array(
							    'class' => "acf-th acf-th-{$sub_field['name']}",
							    'data-key' => $sub_field['key'],
							);


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

				<tbody>
				<?php else: ?>
				<div class="acf-fields <?php if ( $layout['display'] == 'row' ): ?>-left<?php endif; ?>">
				<?php endif; ?>

				<?php
				// loop though sub fields
				foreach ( $layout['sub_fields'] as $sub_field ) {

					// prevent repeater field from creating multiple conditional logic items for each row
					if ( $i !== 'acfcloneindex' ) {
						$sub_field['conditional_logic'] = 0;
					}


					// add value
					if ( isset( $value[$sub_field['key']] ) ) {
						// this is a normal value
						$sub_field['value'] = $value[$sub_field['key']];
					} elseif ( isset( $sub_field['default_value'] ) ) {
						// no value, but this sub field has a default value
						$sub_field['value'] = $sub_field['default_value'];
					}


					// update prefix to allow for nested values
					$sub_field['prefix'] = "{$field['name']}[{$i}]";

					// render input
					acf_views_render_field_wrap( $sub_field, $el );
				}
				?>

				<?php if ( $layout['display'] == 'table' ): ?>
					</tbody>
			</table>
		<?php else: ?>
		</div>
	<?php endif; ?>

<?php endif; ?>

</div>
<?php

