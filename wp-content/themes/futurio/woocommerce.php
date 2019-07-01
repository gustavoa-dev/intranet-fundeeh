<?php 
get_header();

futurio_generate_header( true, true, true, false, false, true );
?>
<!-- start content container -->
<div class="row">
	<article class="col-md-<?php futurio_main_content_width_woo_columns(); ?> <?php futurio_sidebar_position( 'content-woo' ) ?>">
		<?php woocommerce_content(); ?>
	</article>       
	<?php get_sidebar( 'woo' ); ?>
</div>
<!-- end content container -->

<?php
get_footer();
