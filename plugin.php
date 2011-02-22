<?php
/* Plugin name: Active Page Widgets
   Plugin URI: http://www.owlwatch.com
   Author: Mark Fabrizio
   Author URI: http://www.owlwatch.com
   Version: 1.00
   Description: Allows activation / deactivation of widgets and sidebars at a per post/page level.
   Max WP Version: 3.0.1

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( dirname(__FILE__).'/functions.php' );

if( !is_admin() ) add_action('wp_head', 'apw_init');
function apw_init()
{
	if( is_page() || is_single() ){
		global $post;
		apw_setup_post($post->ID);
	}
	else if( is_category() || is_tag() ){
		global $wp_query;
		$obj = $wp_query->get_queried_object();
		apw_setup_term($obj->term_id);
	}
	else if( is_home() ){
		apw_setup_type('home');
	}
	else if( is_search() ){
		apw_setup_type('search');
	}
	else{
		apw_setup_type('default');
	}
}

add_action('admin_menu', 'apw_admin_menu' );
function apw_admin_menu()
{
	add_theme_page( __( 'Active Page Widgets', 'active-page-widgets' ), __( 'Active Page Widgets', 'active-page-widgets'),
						'manage_options', 'active-page-widgets', 'apw_options' );
}

function apw_options()
{
	global $hook_suffix;
	$subpage = @$_REQUEST['subpage'];
	if( !$subpage ) $subpage = 'default';
	apw_process($subpage);
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 style="border-bottom: 1px solid #ccc; padding-bottom: 0;">
			<a href="?page=active-page-widgets" class="nav-tab <?php echo $subpage=='default'?'nav-tab-active':''; ?>">Defaults</a>
			<a href="?page=active-page-widgets&subpage=home" class="nav-tab <?php echo $subpage=='home'?'nav-tab-active':''; ?>">Home</a>
			<a href="?page=active-page-widgets&subpage=search" class="nav-tab <?php echo $subpage=='search'?'nav-tab-active':''; ?>">Search</a>
		</h2>
		<p>Set the default widget and sidebar settings</p>
		<form method="post" id="post">
			<input type="hidden" name="page" value="active-page-widgets" />
			<?php apw_form(apw_get_sidebars_widgets($subpage)); ?>
			<input type="submit" value="Save Settings" class="button-primary" />
		</form>
	</div>
	<?php
}

add_action('admin_init', 'apw_admin_init');
function apw_admin_init()
{
    add_meta_box('active_widgets','Active Page Widgets','apw_block_inner','page','side','low');
    add_meta_box('active_widgets','Active Page Widgets','apw_block_inner','post','side','low');	
}

add_action('admin_print_styles-post.php', 'apw_admin_print_styles');
add_action('admin_print_styles-post-new.php', 'apw_admin_print_styles');
add_action('admin_print_styles-edit-tags.php', 'apw_admin_print_styles');
add_action('admin_print_styles-appearance_page_active-page-widgets', 'apw_admin_print_styles');
function apw_admin_print_styles()
{
	
	$url = plugins_url('/css/active-page-widgets.css', __FILE__ );
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $url; ?>" />
	<?php
}

add_action('admin_print_scripts-tools.php', 'apw_admin_print_scripts');
add_action('admin_print_scripts-post.php', 'apw_admin_print_scripts');
add_action('admin_print_scripts-appearance_page_active-page-widgets', 'apw_admin_print_scripts');
function apw_admin_print_scripts()
{
	$url = plugins_url('/js/active-page-widgets.js', __FILE__ );
	wp_enqueue_script('apw', $url, array('jquery'), '1.0' );
}

add_action('save_post', 'apw_save_post', 10, 2);
function apw_save_post($post_ID, $post)
{
	apw_process('post', $post_ID);
	
}
function apw_block_inner()
{
	global $post_ID;
	$values = true;
	if( $post_ID ){
		$values = apw_get_post_sidebars_widgets($post_ID);
	}
	apw_form($values);
}

add_action('edit_category_form_fields', 'apw_tag_edit_form');
add_action('edit_tag_form_fields', 'apw_tag_edit_form');
function apw_tag_edit_form()
{
	global $tag_ID;
	
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label><?php _e('Active Widgets') ?></label>
		</th>
		<td>
			<?php apw_form( apw_get_term_sidebars_widgets($tag_ID) ); ?>
		</td>
	</tr>
	<?php
}

add_action('add_tag_form_fields', 'apw_tag_add_form');
add_action('category_add_form_fields', 'apw_tag_add_form');
function apw_tag_add_form()
{
	?>
	<div class="form-field">
		<label>Active Widgets</label>
		<?php apw_form( apw_get_defaults() ); ?>
	</div>
	<?php
}

add_action('edited_term', 'apw_edited_term', 10, 3);
add_action('created_term', 'apw_edited_term', 10, 3);
function apw_edited_term($term_id, $tt_id, $taxonomy)
{
	// need to save for terms, and implement in the wp hook
	apw_process( 'term', $term_id );
}

