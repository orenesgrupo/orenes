<?php
defined('ABSPATH') || exit;


/* Comentarios
**
** Deshabilita los comentarios en todo el sitio web
*/

add_action('admin_init', function () {
	global $pagenow;

	if ($pagenow === 'edit-comments.php') {
		wp_safe_redirect(admin_url());
		exit;
	}

	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

	foreach (get_post_types() as $type) {
		if (post_type_supports($type, 'comments')) {
			remove_post_type_support($type, 'comments');
			remove_post_type_support($type, 'trackbacks');
		}
	}
});

// Front y admin: cerrar siempre y ocultar listados
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);

// Menú de admin: quitar Comentarios
add_action('admin_menu', function () {
	remove_menu_page('edit-comments.php');
}, 999);

// Barra de admin: quitar icono de Comentarios
add_action('admin_bar_menu', function ($wp_admin_bar) {
	$wp_admin_bar->remove_node('comments');
}, 60);
