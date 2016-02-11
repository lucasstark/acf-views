
<div>
	<div class="values">
		<?php if ( !empty( $field['value'] ) ): ?>
			<?php foreach ( $field['value'] as $i => $value ): ?>
				<?php
				// validate
				if ( empty( $layouts[$value['acf_fc_layout']] ) ) {
					continue;
				}
				$layout = $layouts[$value['acf_fc_layout']];
				include 'field-flexible-content-layout.php';
				?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>