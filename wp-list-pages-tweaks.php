<?php

/*
	Plugin Name: wp_list_pages tweaks
	Plugin URI: -
	Description: A few nifty tweaks for wp_list_pages
	Author: Hiranthi Molhoek-Herlaar | illutic WebDesign
	Version: 1.0
	Author URI: http://www.illutic-webdesign.nl
*/



/*
 Desc: add option 'wp_list_pages_tweaks'
 */
$wplp_defaults = array(
	'parent_in_submenu' 	=> 0,
	'remove_parent_link'	=> 0,
	'isparent_class'		=> 1
);
function wp_list_pages_install()
{
	global $wplp_defaults;
	
	add_option('wp_list_pages_tweaks', $wplp_defaults);
} // end wp_list_pages_install
register_activation_hook( __FILE__, 'wp_list_pages_install' );


/*
 Desc: add the tweaks to wp_list_pages
 */
function wp_list_pages_tweaks( $content )
{
	$defaults	= get_option('wp_list_pages_tweaks');
	
	if ( !in_the_loop() ) // wp_list_pages is not inside the loop
	{
		$parent		= ( $defaults['parent_in_submenu'] == 1 ) ? '<li class="$1 dupl-parent"><a$2>$3</a></li>' : '';
		$class		= ( $defaults['isparent_class'] == 1 ) ? ' is-parent' : '';
		
		$link		= '$2';
		#if ( $defaults['parent_in_submenu'] == 1  )
		#{ // only allow remove_parent_link to be 'href="#"' when the parent is included in the submenu
			$link		= ( $defaults['remove_parent_link'] == 1 ) ? ' href="#"' : '$2';
		#}
	}
	else // wp_list_pages is inside the loop
	{
		$parent	= '';
		$link	= '$2';
		$class	= '';
	}
	
	$regex		= '/<li class=\"(.*?)\"><a(.*?)>(.*?)<\/a>\\n(.*?)<ul class=\'children\'>/i';
	$replace	= '<li class="$1'.$class.'"><a'.$link.'>$3</a>$4<ul class="children">'.$parent;

	return preg_replace($regex, $replace, $content);
} // end wp_list_pages_tweaks
add_filter('wp_list_pages','wp_list_pages_tweaks',1);


/*
 Desc: add wp_list_pages tweaks to the admin menu
 */
function wp_list_pages_menu()
{	
	add_options_page('wp_list_pages tweaks', 'wp_list_pages tweaks', 'manage_options', 'wp_list_pages_tweaks', 'wp_list_pages_admin');
} // end wp_list_pages_admin
add_action('admin_menu', 'wp_list_pages_menu');


/*
 Desc: admin page
 */
function wp_list_pages_admin()
{
	global $wlpt_options, $wplp_defaults;
	
	if ( 'save' == $_POST['action'] )
	{
		$save_options = array();
		foreach ( $wplp_defaults as $default => $d )
		{
			$val = ( $_POST[ $default ] ) ? 1 : 0;
	    	$save_options[$default] = $val;
		}
		
		update_option('wp_list_pages_tweaks', $save_options);
			
	    $saved = true;
	
	}
	else if( 'reset' == $_POST['action'] )
	{
		global $wplp_defaults;
	    delete_option('wp_list_pages_tweaks');
		add_option('wp_list_pages_tweaks', $wplp_defaults);
	
	    $reset = true;
	}
	
	if ( $saved == true ) { echo '<div id="message" class="updated fade"><p><strong>'.__('Settings saved', 'wpltweaks').'.</strong></p></div>'; }
	if ( $reset == true ) { echo '<div id="message" class="updated fade"><p><strong>'.__('Settings reset', 'wpltweaks').'</strong></p></div>'; }
	
	$saved_options = get_option('wp_list_pages_tweaks');
	?>
	<div class="wrap">
	<h2><?php _e('Settings for wp_list_pages tweaks','wpltweaks'); ?></h2>
	
	<div class="postbox-container" style="width:70%;">
	<form method="post" class="form">
	<?php
	foreach ($wlpt_options as $value)
	{
		switch ( $value['type'] )
		{
			case "open":
			?>
	        <table class="widefat">
	        
			<?php break;
			
			case "close": ?>
			</table><br />
	        
			<?php
	        break;
			
			case "title": ?>
	        <thead><tr valign="top">
	            <th scope="col" colspan="2"><?php echo $value['name']; ?></th>
	        </tr></thead>
	        <tbody>
	        
			<?php break;
			
			case 'text': ?>
	        
	        <tr valign="top">
	            <th scope="row" width="200"><label for="<?php echo $value['id']; ?>"><strong><?php echo $value['name']; ?></strong></label></th>
	            <td><input style="width:400px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( $saved_options[$value['id']] != "") { echo $saved_options[$value['id']]; } else { echo $value['std']; } ?>" />
	            <br /><span class="setting-description"><?php echo $value['desc']; ?></span></td>
	        </tr>
			<?php
			break;
			
			case 'textarea': ?>
			
	        <tr valign="top">
	            <th scope="row" width="200"><label for="<?php echo $value['id']; ?>"><strong><?php echo $value['name']; ?></strong></label></th>
	            <td><textarea name="<?php echo $value['id']; ?>" style="width:400px; height:200px;" type="<?php echo $value['type']; ?>" cols="" rows=""><?php if ( $saved_options[$value['id']] != "") { echo stripslashes($saved_options[$value['id']]); } else { echo stripslashes($value['std']); } ?></textarea>
	            <br /><span class="setting-description"><?php echo $value['desc']; ?></span></td>
	        </tr>
			<?php
			break;
			
			case 'select': ?>
	        
	        <tr valign="top">
	            <th scope="row" width="200"><label for="<?php echo $value['id']; ?>"><strong><?php echo $value['name']; ?></strong></label></th>
	            <td><select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"><?php foreach ($value['options'] as $option) { ?><option<?php if ( $saved_options[$value['id']] == $option) { echo ' selected="selected"'; } elseif ($option == $value['std']) { echo ' selected="selected"'; } ?>><?php echo $option; ?></option><?php } ?></select>
	            <br /><span class="setting-description"><?php echo $value['desc']; ?></span></td>
	        </tr>
			<?php
			break;
			
			case "checkbox": ?>
	        
	        <tr valign="top">
	            <th scope="row" width="200"><label for="<?php echo $value['id']; ?>"><strong><?php echo $value['name']; ?></strong></label></th>
	            <td><? if($saved_options[$value['id']] == 1){ $checked = "checked=\"checked\""; }else{ $checked = ""; } ?><input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="1" <?php echo $checked; ?> />
	            <br /><span class="setting-description"><?php echo $value['desc']; ?></span></td>
	        </tr>
			<?php
	        break;
		
		}
	}
	
	?></tbody></table>
	<p class="submit alignleft">
	<input name="save" type="submit" value="<?php _e('Save changes','wpltweaks'); ?>" class="button-primary" />
	<input type="hidden" name="action" value="save" />
	</p>
	</form>
	<form method="post">
	<p class="submit alignright">
	<input name="reset" type="submit" value="<?php _e('Reset','wpltweaks'); ?>" class="button-secondary reset" />
	<input type="hidden" name="action" value="reset" />
	</p>
	</form></div>
	</div>
	
	

<?php
} // end wp_list_pages_admin



$wlpt_options = array (
	array( 'type' 	=> 'open' ),
	
	array(
		'name'		=> __('Settings for wp_list_pages tweaks','wpltweaks'),
		'type'		=> 'title'
	),
	
	array(
		"name"		=> __('Parent in submenu','wpltweaks'),
		"desc"		=> __('Should the parent be displayed in its own submenu?','wpltweaks'),
		"id"		=> 'parent_in_submenu',
		"type"		=> 'checkbox'
	),
	
	
	array(
		"name"		=> __('Remove parent link','wpltweaks'),
		"desc"		=> __('Remove the link of the parent item inside the toplevel? (the URL is being replaced by #, so the a-tag does still exist for CSS purposes).','wpltweaks'),
		"id"		=> 'remove_parent_link',
		"type"		=> 'checkbox'
	),
	
	
	array(
		"name"		=> __('Add an is-parent class?','wpltweaks'),
		"desc"		=> __('Should the is-parent class be added to the parent in the toplevel? - <em>Only true if the item actually has child-pages.</em>','wpltweaks'),
		"id"		=> 'isparent_class',
		"type"		=> 'checkbox'
	),
	
	array( 'type' 	=> 'close' )	
); // end array



/*
 Desc: delete the wp_list_pages_tweaks on deinstall
 */
function wp_list_pages_deinstall()
{
	delete_option('wp_list_pages_tweaks');
} // end wp_list_pages_deinstall
register_deactivation_hook(__FILE____, 'wp_list_pages_deinstall');

?>