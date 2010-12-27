<?php
/*
Plugin Name: Rollover Themes
Plugin URI: http://dsader.snowotherway.org
Description: Replaces default Appearance->Themes page. Themes list 100 themes per page, but only one screenshot until mouse rollover preview. 
Author: David Sader
Version: 3.0.1.4
Author URI: http://dsader.snowotherway.org

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/ 

// TODO make Network Options
define( 'DS_THEMES_PER_PAGE','10' ); 
define( 'DS_THEMES_DISABLE_ORIGINAL_MENU', 'TRUE' );
define( 'DS_THEMES_SHOW_SCREENSHOT_THUMB', 'TRUE' );
//define( 'WP_DEFAULT_THEME', 'aeros' );
//------------------------------------------------------------------------//
//---Hooks----------------------------------------------------------------//
//------------------------------------------------------------------------//
add_action( 'admin_menu', 'ds_rollover_themes' );
if( strpos($_SERVER['REQUEST_URI'], 'themes_table' ) ) {
	add_action( 'admin_init', 'ds_themes_update' );
	add_action( 'admin_enqueue_scripts', 'ds_replace_theme_context_help' );
	add_action( 'admin_enqueue_scripts', 'ds_replace_theme_preview_scripts' );
}
//------------------------------------------------------------------------//
//---Menu Functions-------------------------------------------------------//
//------------------------------------------------------------------------//
function ds_replace_theme_preview_scripts() {
	add_thickbox();
	wp_enqueue_script( 'theme-preview' );	
}

function ds_replace_theme_context_help() {
if ( current_user_can( 'switch_themes' ) ) :
global $current_screen;
$help = '<p>' . __( 'Aside from the default theme included with your WordPress installation, themes are designed and developed by third parties.' ) . '</p>';
$help .= '<p>' . __( 'You can see your active theme at the top of the screen. Below are the other themes you have installed that are not currently in use. You can see what your site would look like with one of these themes by clicking the Preview link. To change themes, click the Activate link.' ) . '</p>';
if ( current_user_can( 'install_themes' ) )
	$help .= '<p>' . sprintf(__( 'If you would like to see more themes to choose from, click on the &#8220;Install Themes&#8221; tab and you will be able to browse or search for additional themes from the <a href="%s" target="_blank">WordPress.org Theme Directory</a>. Themes in the WordPress.org Theme Directory are designed and developed by third parties, and are licensed under the GNU General Public License, version 2, just like WordPress. Oh, and they&#8217;re free!' ), 'http://wordpress.org/extend/themes/' ) . '</p>';

$help .= '<p><strong>' . __( 'For more information:' ) . '</strong></p>';
$help .= '<p>' . __( '<a href="http://codex.wordpress.org/Using_Themes" target="_blank">Documentation on Using Themes</a>' ) . '</p>';
$help .= '<p>' . __( '<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>' ) . '</p>';
add_contextual_help($current_screen, $help);

endif;
}

if( DS_THEMES_DISABLE_ORIGINAL_MENU == 'TRUE' ) {
	add_action( '_admin_menu', 'ds_unset_themes_submenu' );
	add_action( 'admin_head', 'ds_unset_themes_icon_css' );

function ds_unset_themes_submenu() {
	global $submenu;
	//unset the existing themes.php menu
	if( current_user_can( 'switch_themes' ) ) { 
		if(!empty( $submenu['themes.php'] ) ) {
		foreach( $submenu['themes.php'] as $key => $sm) {
			if( __($sm[0]) == "Themes" || $sm[2] == "themes.php" ) {
				unset( $submenu['themes.php'][$key] );
				break;
				}
			}
		}
	$location = add_query_arg( 'page', 'themes_table', 'widgets.php' );
	if( strpos($_SERVER['REQUEST_URI'], '/themes.php' ) )	wp_redirect($location);	
	}
}

function ds_unset_themes_icon_css() {
	echo "<style type=\"text/css\">#icon-widgets{background:transparent url(". admin_url() . "images/icons32.png) no-repeat -11px -5px;}</style>";
}
}
function ds_rollover_themes() {

	$page = add_theme_page( 'Themes', 'Themes', 'switch_themes', 'themes_table', 'ds_themes' );

}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
function ds_themes_update() {

if ( current_user_can( 'switch_themes' ) && isset($_GET['action']) ) {
	if ( 'activate' == $_GET['action']) {
		check_admin_referer( 'switch-theme_' . $_GET['template']);
		switch_theme($_GET['template'], $_GET['stylesheet']);
		$location = add_query_arg( 'activated', 'true', wp_get_referer() );
		wp_redirect($location);
		exit;
	} else if ( 'delete' == $_GET['action'] ) {
		check_admin_referer( 'delete-theme_' . $_GET['template']);
		if ( !current_user_can( 'delete_themes' ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		delete_theme($_GET['template']);
		$location = add_query_arg( 'deleted', 'true', wp_get_referer() );
		wp_redirect($location);
		exit;
	}
}

}
function ds_themes() {
if ( !current_user_can('switch_themes') && !current_user_can('edit_theme_options') )
	wp_die( __( 'Cheatin&#8217; uh?' ) );

$title = __( 'Manage Themes' );
$parent_file = 'themes.php';


require_once( './admin-header.php' );
if ( is_multisite() && current_user_can('edit_themes') ) {
	?><div id="message0" class="updated"><p><?php printf( __('Administrator: new themes must be activated in the <a href="%s">Network Themes</a> screen before they appear here.'), admin_url( 'ms-themes.php') ); ?></p></div><?php
}

if ( ! validate_current_theme() ) : ?>
<div id="message1" class="updated"><p><?php _e('The active theme is broken.  Reverting to the default theme.'); ?></p></div>
<?php elseif ( isset($_GET['activated']) ) :
		if ( isset($wp_registered_sidebars) && count( (array) $wp_registered_sidebars ) && current_user_can('edit_theme_options') ) { ?>
<div id="message2" class="updated"><p><?php printf( __('New theme activated. This theme supports widgets, please visit the <a href="%s">widgets settings</a> screen to configure them.'), admin_url( 'widgets.php' ) ); ?></p></div><?php
		} else { ?>
<div id="message2" class="updated"><p><?php printf( __( 'New theme activated. <a href="%s">Visit site</a>' ), home_url( '/' ) ); ?></p></div><?php
		}
	elseif ( isset($_GET['deleted']) ) : ?>
<div id="message3" class="updated"><p><?php _e('Theme deleted.') ?></p></div>
<?php endif; 

$themes = get_allowed_themes();
$ct = current_theme_info();
unset($themes[$ct->name]);


$unfiltered_theme_total = count( $themes );
if ($_POST['tag']) {
	if($_POST['tag'] == 'all' ) {
		$themes = $themes;
		} else {
	$showbytag = $_POST['tag'];
        $filltered = array();
	foreach ( $themes as $id=>$t ) {
		if ( in_array($showbytag, $t['Tags']) ) {
			$filltered[$id] = $t;
		}
	}
	$themes = $filltered;
		}

}
if ($_GET['tag']) {
	if($_GET['tag'] == 'all' ) {
		$themes = $themes;
		} else {
	$showbytag = $_GET['tag'];
        $filltered = array();
	foreach ( $themes as $id=>$t ) {
		if ( in_array($showbytag, $t['Tags']) ) {
			$filltered[$id] = $t;
		}
	}
	$themes = $filltered;
		}

}

uksort( $themes, "strnatcasecmp" );
$theme_total = count( $themes );
$per_page = DS_THEMES_PER_PAGE;

if ( isset( $_GET['pagenum'] ) )
	$page = absint( $_GET['pagenum'] );

if ( empty($page) )
	$page = 1;

$start = $offset = ( $page - 1 ) * $per_page;

$page_links = paginate_links( array(

	'base' => add_query_arg( array('pagenum' => '%#%', 'tag'=> $showbytag )) . '#themenav',
	'format' => '',
	'prev_text' => __( '&laquo;' ),
	'next_text' => __( '&raquo;' ),
	'total' => ceil($theme_total / $per_page),
	'current' => $page
) );
$altags = array();
foreach ($themes as $t){
	foreach ($t['Tags'] as $id =>$tag) {
		$altags[$tag]=(int)$altags[$tag]+1;
	}
}
arsort($altags);
				if ($_POST['tag']) {
					$selected = $_POST['tag'];
				} elseif($_GET['tag']) {
					$selected = $_GET['tag'];
				}
				
				$tag_filter = '';
				foreach ( $altags as $name => $count ) {
					if ( $selected == $name) {
					$tag_filter = '<option selected="selected" value="'.$name.'">'.$name.' ('.$count.')</option>';
					} else {
					$tag_filter .= '<option value="'.$name.'">'.$name.' ('.$count.')</option>';
					}
				}
				
$themes = array_slice( $themes, $start, $per_page );

?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><a href="?page=themes_table" class="nav-tab nav-tab-active"><?php echo esc_html( $title ); ?></a><?php if ( current_user_can( 'install_themes' ) ) { ?><a href="theme-install.php" class="nav-tab"><?php echo esc_html_x( 'Install Themes', 'theme' ); ?></a><?php } ?></h2>

<h3><?php _e( 'Current Theme' ); ?></h3>
<div id="current-theme">
<?php if ( $ct->screenshot ) : ?>
<img src="<?php echo $ct->theme_root_uri . '/' . $ct->stylesheet . '/' . $ct->screenshot; ?>" alt="<?php _e('Current theme preview'); ?>" />
<?php endif; ?>
<h4><?php
	/* translators: 1: theme title, 2: theme version, 3: theme author */
	printf(__('%1$s %2$s by %3$s'), $ct->title, $ct->version, $ct->author) ; ?></h4>
<p class="theme-description"><?php echo $ct->description; ?></p>
<?php if ( current_user_can('edit_themes') && $ct->parent_theme ) { ?>
	<p><?php printf(__('The template files are located in <code>%2$s</code>. The stylesheet files are located in <code>%3$s</code>. <strong>%4$s</strong> uses templates from <strong>%5$s</strong>. Changes made to the templates will affect both themes.'), $ct->title, str_replace( WP_CONTENT_DIR, '', $ct->template_dir ), str_replace( WP_CONTENT_DIR, '', $ct->stylesheet_dir ), $ct->title, $ct->parent_theme); ?></p>
<?php } else { ?>
	<p><?php printf(__('All of this theme&#8217;s files are located in <code>%2$s</code>.'), $ct->title, str_replace( WP_CONTENT_DIR, '', $ct->template_dir ), str_replace( WP_CONTENT_DIR, '', $ct->stylesheet_dir ) ); ?></p>
<?php } ?>
<?php if ( $ct->tags ) : 
		echo '<p>'. __( 'Tags: ' ); 
			$action_tags = array(); 
 		foreach ($ct->tags as $tag) {
 			$tag_link = add_query_arg( 'tag', $tag, '?page='.$_GET['page'] );
			$action_tags[] = '<a href="'.$tag_link.'">'.$tag.'</a>';
			}
		echo implode ( ' | ', $action_tags ).'</p>';
	endif;
theme_update_available($ct); ?>

</div>

<div class="clear"></div>
<?php
if ( ! current_user_can( 'switch_themes' ) ) {
	echo '</div>';
//	require( './admin-footer.php' );
	exit;
}
?>
<form method="POST" action="?page=themes_table">
<h2><?php echo $theme_total.' Available Themes'; ?></h2>
<div class="tablenav">
<span class="alignleft">					
	<select name="tag" id="tag">
		<option value="all">Show all <?php echo $unfiltered_theme_total; ?> themes</option>
			<?php echo $tag_filter; ?>
	</select>
  <input type="submit" class="button-secondary" value="Filter by Tag" />
</span>
<?php if ( $theme_total ) { ?>

<?php if ( $page_links ) : ?>
	<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
	number_format_i18n( $start + 1 ),
	number_format_i18n( min( $page * $per_page, $theme_total ) ),
	number_format_i18n( $theme_total ),
	$page_links
); echo $page_links_text; ?>
	</div>
<?php endif; ?>
</div>
</form>
<?php
// Snowsuits go here instead
$theme_names = array_keys($themes);
natcasesort($theme_names);

?>

<table class="widefat">
	<thead>
	<tr>
		<th>Theme</th>
		<th>Description</th>
		<?php 
if ( is_multisite() && current_user_can( 'edit_themes' ) ) {
			 ?>
			<th>Version</th>
			<th>Author</th>
		<?php } ?>
		<th>Action</th>
	</tr>
	</thead>
			<tbody id="plugins">

<?php
foreach ($theme_names as $theme_name) {
	$template = $themes[$theme_name]['Template'];
	$stylesheet = $themes[$theme_name]['Stylesheet'];
	$title = $themes[$theme_name]['Title'];
	$version = $themes[$theme_name]['Version'];
	$description = $themes[$theme_name]['Description'];
	$author = $themes[$theme_name]['Author'];
	$screenshot = $themes[$theme_name]['Screenshot'];
	$stylesheet_dir = $themes[$theme_name]['Stylesheet Dir'];
	$template_dir = $themes[$theme_name]['Template Dir'];
	$parent_theme = $themes[$theme_name]['Parent Theme'];
	$theme_root = $themes[$theme_name]['Theme Root'];
	$theme_root_uri = $themes[$theme_name]['Theme Root URI'];
	$preview_link = esc_url( get_option( 'home' ) . '/' );
	if ( is_ssl() )
		$preview_link = str_replace( 'http://', 'https://', $preview_link );
	$preview_link = htmlspecialchars( add_query_arg( array( 'preview' => 1, 'template' => $template, 'stylesheet' => $stylesheet, 'TB_iframe' => 'true' ), $preview_link ) );
	$preview_text = esc_attr( sprintf( __( 'Preview of &#8220;%s&#8221;' ), $title ) );
	$tags = $themes[$theme_name]['Tags'];
	$thickbox_class = 'thickbox thickbox-preview';
	$activate_link = wp_nonce_url( "?page=themes_table&amp;action=activate&amp;template=".urlencode($template)."&amp;stylesheet=".urlencode($stylesheet), 'switch-theme_' . $template );
	$activate_text = esc_attr( sprintf( __( 'Activate &#8220;%s&#8221;' ), $title ) );
	$actions = array();
	$actions[] = '<a href="' . $activate_link .  '" class="activatelink" title="' . $activate_text . '">' . __( 'Activate' ) . '</a>';
	$mouseover = 'onMouseOver="preview( \'' . $theme_root_uri . '/' . $stylesheet . '/' . $screenshot.'\', getTopPosition(this) );" onMouseOut="unpreview();"';
	$actions[] = '<a href="' . $preview_link . '" class="thickbox thickbox-preview" title="' . esc_attr(sprintf(__( 'Preview &#8220;%s&#8221;' ), $theme_name ) ) . '" ' . $mouseover . '>' . __( 'Preview' ) . '</a>';
	if ( current_user_can( 'delete_themes' ) )
		$actions[] = '<a class="submitdelete deletion" href="' . wp_nonce_url( "themes.php?action=delete&amp;template=$stylesheet", 'delete-theme_' . $stylesheet ) . '" onclick="' . "return confirm( '" . esc_js( sprintf( __( "You are about to delete this theme '%s'\n  'Cancel' to stop, 'OK' to delete."), $theme_name ) ) . "' );" . '">' . __( 'Delete' ) . '</a>';
	$actions = apply_filters( 'theme_action_links', $actions, $themes[$theme_name]);

	$actions = implode ( '&nbsp;|&nbsp;', $actions );

	$alt = $alt == '' ? 'alternate' : '';


echo '<tr class="' . $alt . '">';
if( DS_THEMES_SHOW_SCREENSHOT_THUMB == 'TRUE' ) {
	echo '<td><a class="' . $thickbox_class . '" href="' . $preview_link .'" title="' . $preview_text . '" ' . $mouseover . '>"' . $title .'"<br /><img src="' . $theme_root_uri . '/' . $stylesheet . '/' . $screenshot.'" height="60" class="alignleft" /></a></td>';
} else {
echo '<td><a class="' . $thickbox_class . '" href="' . $preview_link .'" title="' . $preview_text . '" ' . $mouseover . '>"' . $title .'"</a></td>';
}
?>
<td><p class="description"><?php echo $description; ?></p>
	<?php if ( current_user_can( 'edit_themes' ) && $parent_theme ) {
	/* translators: 1: theme title, 2:  template dir, 3: stylesheet_dir, 4: theme title, 5: parent_theme */ ?>
	<p><?php printf(__( 'The template files are located in <code>%2$s</code>. The stylesheet files are located in <code>%3$s</code>. <strong>%4$s</strong> uses templates from <strong>%5$s</strong>. Changes made to the templates will affect both themes.' ), $title, str_replace( WP_CONTENT_DIR, '', $template_dir ), str_replace( WP_CONTENT_DIR, '', $stylesheet_dir ), $title, $parent_theme); ?></p>
<?php } else { ?>
	<p><?php printf(__( 'All of this theme&#8217;s files are located in <code>%2$s</code>.' ), $title, str_replace( WP_CONTENT_DIR, '', $template_dir ), str_replace( WP_CONTENT_DIR, '', $stylesheet_dir ) ); ?></p>
<?php } 

	
	if ( $tags ) : 
		echo '<p>'. __( 'Tags: ' );  
			$action_tags = array();
 		foreach ($tags as $tag) {
 			$tag_link = add_query_arg( 'tag', $tag, '?page='.$_GET['page'] );
			$action_tags[] = '<a href="'.$tag_link.'">'.$tag.'</a>';
			}
		echo implode ( ' | ', $action_tags ).'</p>';
	endif;
?>
</td>
<?php
if ( is_multisite() && current_user_can( 'edit_themes' ) ) {
	?>
<td><?php echo "$version"; ?></td>
<td><?php echo "$author"; ?></td>
<?php } ?>

<td><span class='action-links'><?php echo $actions ?></span>
</td>
</tr>
<?php } ?>
</tbody>
</table>
<div class="tablenav">
	<span class="alignleft">
	<?php
	$activate_default = wp_nonce_url( "?page=themes_table&amp;action=activate&amp;template=".WP_DEFAULT_THEME."&amp;stylesheet=".WP_DEFAULT_THEME."", 'switch-theme_'.WP_DEFAULT_THEME.'' );	?>	
	<a class="button-secondary" href="<?php echo $activate_default; ?>" class="delete"><?php _e( 'Activate Default Theme' ); ?></a>
	</span>
<?php if ( $page_links ) : ?>
	<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
	number_format_i18n( $start + 1 ),
	number_format_i18n( min( $page * $per_page, $theme_total ) ),
	number_format_i18n( $theme_total ),
	$page_links
); echo $page_links_text; ?>
	</div>
	<?php endif; ?>
	<br class="clear" />
</div>
	<br class="clear" />
<?php } else { ?>
</div>
	<br class="clear" />
	<p><?php
	if ( current_user_can( 'install_themes' ) )
		printf(__( 'You only have one theme installed right now. Live a little! You can choose from over 1,000 free themes in the WordPress.org Theme Directory at any time: just click on the <em><a href="%s">Install Themes</a></em> tab above.' ), 'theme-install.php' );
	else
		printf(__( 'Only the current theme is available to you. Contact the %s administrator for information about accessing additional themes.' ), get_site_option( 'site_name' ) );
	?></p>
<?php } // end if $theme_total?>


<script>
        var pendingOpen = 0;
        var open = 0;
        var pendingClose = 0;
        function preview(url, top) {
           document.getElementById( 'utpreviewbox' ).style.top=top-100 + "px";
           document.getElementById( 'previewimage' ).src=url;
           pendingOpen = 1;
           pendingClose = 0;
           setTimeout( 'showpreview()', 1000);
        }
        function unpreview() {
          pendingClose = 1;
          setTimeout( 'hidepreview()', 400);
        }
        function showpreview() {
           if (pendingOpen == 0) { return; }
           var p = document.getElementById( 'utpreviewbox' );
           p.style.visibility='visible';
        }
        function hidepreview() {
           if  (pendingClose == 0 ) {return;}
           pendingClose = 0;
           pendingOpen = 0;
           var p = document.getElementById( 'utpreviewbox' );
           p.style.visibility='hidden';
        }
        function getTopPosition(e) {
           p = -100;
           while (e!=null) {
              p += e.offsetTop;
              e = e.offsetParent;
           }
           return p;
        }
      </script>

      <style>
         #utpreviewbox {
             visibility: hidden;      
             background-color: white;
             border: 5px solid #cccccc;
             z-index: 1000; 
             position:absolute; 
             left:10%
            }
      </style> 
      <div id="utpreviewbox">
         <img id="previewimage" border="0" src="http://www.google.com/intl/en/images/logo.gif" />
      </div>
<?php
// List broken themes, if any.
$broken_themes = get_broken_themes();
if ( current_user_can( 'edit_themes' ) && count( $broken_themes ) ) {
?>

<h2><?php _e( 'Broken Themes' ); ?> <?php if ( is_multisite() ) _e( '(Super Admin only)' ); ?></h2>
<p><?php _e( 'The following themes are installed but incomplete. Themes must have a stylesheet and a template.' ); ?></p>

<table id="broken-themes">
	<tr>
		<th><?php _e( 'Name' ); ?></th>
		<th><?php _e( 'Description' ); ?></th>
	</tr>
<?php
	$theme = '';

	$theme_names = array_keys($broken_themes);
	natcasesort($theme_names);

	foreach ($theme_names as $theme_name) {
		$title = $broken_themes[$theme_name]['Title'];
		$description = $broken_themes[$theme_name]['Description'];

		$theme = ( 'class="alternate"' == $theme) ? '' : 'class="alternate"';
		echo "
		<tr $theme>
			 <td>$title</td>
			 <td>$description</td>
		</tr>";
	}
?>
</table>
<?php
}
?>
</div>
<?php 
}
?>