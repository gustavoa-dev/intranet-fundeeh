<?php

/**
 * This template displays the public-facing aspects of the widget.
 */
?>

<div class="acadp acadp-widget-locations">
	<?php if( 'dropdown' == $query_args['template'] ) : ?>
    	<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
        	<select class="form-control" name="acadp_locations" onchange="if ( '' != this.options[ this.selectedIndex ].value ) { this.form.submit() };">
							<option value="">-- <?php _e( 'Select a location', 'advanced-classifieds-and-directory-pro' ); ?> --</option>
            	<?php echo $locations; ?>
            </select>
        </form>
    <?php else :
		echo $locations;
	endif; ?>
</div>