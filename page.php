<?php
/**
 * The template for displaying any single page.
 *
 */

	get_header();

?>
	<div id="primary" class="row-fluid">
		<div id="content" role="main" class="span8 offset2">
			<?php

				if (have_posts()) {

					while (have_posts()) {

						the_post();

			?>
			<article class="post">
				<div class="the-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php

					}

				}

			?>
		</div>
	</div>
<?php get_footer(); ?>