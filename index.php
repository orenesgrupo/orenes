<?php get_header(); ?>
	<div id="primary" class="row-fluid">
		<div id="content" role="main" class="span8 offset2">
			<?php

				if (have_posts()) {

					while (have_posts()) {

						the_post();

			?>
			<article class="post">
				<?php the_post_thumbnail('large');  ?>
				<h1 class="title">
					<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php the_title(); ?>
					</a>
				</h1>
				<div class="post-meta">
					<?php the_time('m/d/Y'); ?>
				</div>
				<div class="the-content">
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				</div>
				<div class="meta clearfix">
					<div class="category">
						<?php echo get_the_category_list(); ?>
					</div>
					<div class="tags">
						<?php echo get_the_tag_list( '| &nbsp;', '&nbsp;' ); ?>
					</div>
				</div>
			</article>
			<?php

					}

			?>
			<div id="pagination" class="clearfix">
				<div class="past-page"><?php previous_posts_link( 'newer' ); ?></div>
				<div class="next-page"><?php next_posts_link( 'older' ); ?></div>
			</div>
			<?php

				}

			?>
		</div>
	</div>
	<?php get_footer(); ?>