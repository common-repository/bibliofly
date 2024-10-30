<?php 
/*
Plugin Name: BiblioFly
Plugin URI: http://www.deborahmcdonnell.com/
Description: Allows management of a bibliography through WordPress. Stores data in the database, features a variety of functions to allow display of publications in a list (eg for the sidebar) or on a page. Styled through CSS. 
Version: 0.34
Author: deborah mcdonnell
Author URI: http://www.deborahmcdonnell.com/
*/

/*
	BiblioFly
	Copyright 2005 Deborah McDonnell
	http://www.deborahmcdonnell.com/

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
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


// BiblioFly Options
$bibliofly_version = "0.34";

// Install or upgrade the program
if(function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__, 'bibliofly_install');
} else {
	add_action('activate_bibliofly.php', 'bibliofly_install');
}

function bibliofly_install () {
   global $wpdb, $bibliofly_version;
	 
   $table_name = $wpdb->prefix . "bibliofly";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
		{
			 // Create a brand new install
	   		$sql = "CREATE TABLE ".$table_name." (
			     biblioid INT(11) NOT NULL AUTO_INCREMENT,
					 title TEXT NOT NULL,
					 subtitle TEXT NOT NULL,
					 titleurl VARCHAR(255) NOT NULL,
					 source VARCHAR(255) NOT NULL,
					 sourceissue VARCHAR(255) NOT NULL,
					 imgsrc VARCHAR(255) NOT NULL,
					 excerpt TEXT NOT NULL,
					 biblionotes TEXT NOT NULL,
					 bibliotype ENUM('novel','short','poem','text','article') NOT NULL,
					 published ENUM('yes','no') NOT NULL,
					 visible ENUM('yes','no') NOT NULL,
				 UNIQUE KEY ID (biblioid)
				 );"; 

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
		
		add_option("bibliofly_version", $bibliofly_version, "Installed BiblioFly version");
		
		echo "<div class=\"updated\"><p>BiblioFly " . $bibliofly_version . " has been successfully installed. The plugin has created a database table named " . $table_name . ".<br /><br />If you deactivate the plugin later and want to remove the data, make sure to delete this table.</p></div>";
		}
		
		// Upgrade from a previous version
		$installed_version = get_option("bibliofly_version");

		if( $installed_version != $bibliofly_version || empty($installed_version) ) 
		{

	   		$sql = "CREATE TABLE ".$table_name." (
			     biblioid INT(11) NOT NULL AUTO_INCREMENT,
					 title TEXT NOT NULL,
					 subtitle TEXT NOT NULL,
					 titleurl VARCHAR(255) NOT NULL,
					 source VARCHAR(255) NOT NULL,
					 sourceissue VARCHAR(255) NOT NULL,
					 imgsrc VARCHAR(255) NOT NULL,
					 excerpt TEXT NOT NULL,
					 biblionotes TEXT NOT NULL,
					 bibliotype ENUM('novel','short','poem','text','article') NOT NULL,
					 published ENUM('yes','no') NOT NULL,
					 visible ENUM('yes','no') NOT NULL,
				 UNIQUE KEY ID (biblioid)
				 );"; 

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

    update_option( "bibliofly_version", $bibliofly_version ); 
	  
				echo "<div class=\"updated\"><p>biblioFly has been successfully updated to version " . $bibliofly_version . ". This version now includes an <a href=\"options-general.php?page=bibliofly.php\">Options submenu</a> where you can set global defaults for the biblio meters. Enjoy!</p></div>";
				

		}

}

// Add the management page to the administration panel; sink function for 'admin_menu' hook
function bibliofly_admin_menu()
{
	add_management_page('BiblioFly', 'Bibliography', 8, bibliofly, 'bibliofly_manage');
}

// Handles the BiblioFly management page
function bibliofly_manage()
{
	global $wpdb;

	$updateaction = !empty($_REQUEST['updateaction']) ? $_REQUEST['updateaction'] : '';
	$biblioid = !empty($_REQUEST['biblioid']) ? $_REQUEST['biblioid'] : '';
	
	if (isset($_REQUEST['action']) ):
		if ($_REQUEST['action'] == 'delete_biblio') 
		{
			$biblioid = intval($_GET['biblioid']);
			if (empty($biblioid))
			{
				?><div class="error"><p><strong>Failure:</strong> No Biblio-ID given. I guess I deleted nothing successfully.</p></div><?php
			}
			else
			{
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "bibliofly WHERE biblioid = '" . $biblioid . "'");
				$sql = "SELECT biblioid FROM " . $wpdb->prefix . "bibliofly WHERE biblioid = '" . $biblioid . "'";
				$check = $wpdb->get_results($sql);
				if ( empty($check) || empty($check[0]->biblioid) )
				{
					?><div class="updated"><p>Biblio Entry <?php echo $biblioid; ?> deleted successfully.</p></div><?php
				}
				else
				{
					?><div class="error"><p><strong>Failure:</strong> Ninjas proved my kung-fu to be too weak to delete that entry.</p></div><?php
				}
			}
		} // end delete_biblio block
	endif;
	
	if ( $updateaction == 'update_biblio' )
	{
		$title = !empty($_REQUEST['biblio_title']) ? $_REQUEST['biblio_title'] : '';
		$subtitle = !empty($_REQUEST['biblio_subtitle']) ? $_REQUEST['biblio_subtitle'] : '';		
		$titleurl = !empty($_REQUEST['biblio_titleurl']) ? $_REQUEST['biblio_titleurl'] : '';
		$source = !empty($_REQUEST['biblio_source']) ? $_REQUEST['biblio_source'] : '';
		$sourceissue = !empty($_REQUEST['biblio_sourceissue']) ? $_REQUEST['biblio_sourceissue'] : '';
		$imgsrc = !empty($_REQUEST['biblio_imgsrc']) ? $_REQUEST['biblio_imgsrc'] : '';
		$excerpt = !empty($_REQUEST['biblio_excerpt']) ? $_REQUEST['biblio_excerpt'] : '';
		$biblionotes = !empty($_REQUEST['biblio_notes']) ? $_REQUEST['biblio_notes'] : '';		
		$bibliotype = !empty($_REQUEST['biblio_bibliotype']) ? $_REQUEST['biblio_bibliotype'] : '';
		$published =!empty($_REQUEST['biblio_published']) ? $_REQUEST['biblio_published'] : '';
		$visible = !empty($_REQUEST['biblio_visible']) ? $_REQUEST['biblio_visible'] : '';
		
		if ( empty($biblioid) )
		{
			?><div class="error"><p><strong>Failure:</strong> No biblio-id given. Can't save nothing. Giving up...</p></div><?php
		}
		else
		{
			$sql = "UPDATE " . $wpdb->prefix . "bibliofly SET title = '" . $title . "', subtitle = '" . $subtitle . "', titleurl = '" . $titleurl . "', source = '" . $source . "', sourceissue = '" . $sourceissue . "', imgsrc = '" . $imgsrc . "', excerpt = '" . $excerpt . "', biblionotes = '" . $biblionotes . "', bibliotype = '" . $bibliotype . "', published = '" . $published . "', visible = '" . $visible . "' WHERE biblioid = '" . $biblioid . "'";
			$wpdb->get_results($sql);
			$sql = "SELECT biblioid FROM " . $wpdb->prefix . "bibliofly WHERE title = '" . $title . "' and subtitle = '" . $subtitle . "' and titleurl = '" . $titleurl . "' and source = '" . $source . "' and sourceissue = '" . $sourceissue . "' and bibliotype = '" . $bibliotype . "' and published = '" . $published . "' and visible = '" . $visible . "' LIMIT 1";
			$check = $wpdb->get_results($sql);
			if ( empty($check) || empty($check[0]->biblioid) )
			{
				?><div class="error"><p><strong>Failure:</strong> The Evil Monkey Overlord wouldn't let me update your entry. Try again?</p></div><?php
			}
			else
			{
				?><div class="updated"><p>Biblio <?php echo $biblioid; ?> updated successfully.</p></div><?php
			}
		}
	} // end update_biblio block
	elseif ( $updateaction == 'add_biblio' )
	{
		$title = !empty($_REQUEST['biblio_title']) ? $_REQUEST['biblio_title'] : '';
		$subtitle = !empty($_REQUEST['biblio_subtitle']) ? $_REQUEST['biblio_subtitle'] : '';
		$titleurl = !empty($_REQUEST['biblio_titleurl']) ? $_REQUEST['biblio_titleurl'] : '';
		$source = !empty($_REQUEST['biblio_source']) ? $_REQUEST['biblio_source'] : '';
		$sourceissue = !empty($_REQUEST['biblio_sourceissue']) ? $_REQUEST['biblio_sourceissue'] : '';
		$imgsrc = !empty($_REQUEST['biblio_imgsrc']) ? $_REQUEST['biblio_imgsrc'] : '';
		$excerpt = !empty($_REQUEST['biblio_excerpt']) ? $_REQUEST['biblio_excerpt'] : '';
		$biblionotes = !empty($_REQUEST['biblio_notes']) ? $_REQUEST['biblio_notes'] : '';		
		$bibliotype = !empty($_REQUEST['biblio_bibliotype']) ? $_REQUEST['biblio_bibliotype'] : '';
		$published = !empty($_REQUEST['biblio_published']) ? $_REQUEST['biblio_published'] : '';
		$visible = !empty($_REQUEST['biblio_visible']) ? $_REQUEST['biblio_visible'] : '';
		
		$sql = "INSERT INTO " . $wpdb->prefix . "bibliofly SET title = '" . $title . "', subtitle = '" . $subtitle . "', titleurl = '" . $titleurl . "', source = '" . $source . "', sourceissue = '" . $sourceissue . "', imgsrc = '" . $imgsrc . "', excerpt = '" . $excerpt . "', biblionotes = '" . $biblionotes . "', bibliotype = '" . $bibliotype . "', published = '" . $published . "', visible = '" . $visible . "'";
		$wpdb->get_results($sql);
		$sql = "SELECT biblioid FROM " . $wpdb->prefix . "bibliofly WHERE title = '" . $title . "' and subtitle = '" . $subtitle . "' and titleurl = '" . $titleurl . "' and source = '" . $source . "' and sourceissue = '" . $sourceissue . "' and bibliotype = '" . $bibliotype . "' and published = '" . $published . "' and visible = '" . $visible . "'";
		$check = $wpdb->get_results($sql);
		if ( empty($check) || empty($check[0]->biblioid) )
		{
			?><div class="error"><p><strong>Failure:</strong> Holy crap you destroyed the internet! That, or something else went wrong when I tried to insert the entry. Try again? </p></div><?php
		}
		else
		{
			?><div class="updated"><p>Biblio ID <?php echo $check[0]->biblioid;?> added successfully.</p></div><?php
		}
	} // end add_biblio block
	?>

	<div class=wrap>
	<?php
	if ( $_REQUEST['action'] == 'edit_biblio' )
	{
		?>
		<h2><?php _e('Edit Biblio'); ?></h2>
		<?php
		if ( empty($biblioid) )
		{
			echo "<div class=\"error\"><p>I didn't get an entry identifier from the query string. Giving up...</p></div>";
		}
		else
		{
			bibliofly_editform('update_biblio', $biblioid);
		}	
	}
	else
	{
		?>
		<h2><?php _e('Add Entry'); ?></h2>
		<?php bibliofly_editform(); ?>
	
		<h2><?php _e('Manage Bibliography'); ?></h2>
		<?php
			bibliofly_displaylist();
	}
	?>
	</div><?php

}

// Displays the list of bibliofly entries
function bibliofly_displaylist() 
{
	global $wpdb;
	
	$biblios = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bibliofly ORDER BY biblioid DESC");
	
	if ( !empty($biblios) )
	{
		?>
		<table width="100%" cellpadding="3" cellspacing="3">
			<tr>
				<th scope="col"><?php _e('ID') ?></th>
				<th scope="col"><?php _e('Title') ?></th>
				<th scope="col"><?php _e('URL') ?></th>
				<th scope="col"><?php _e('Source') ?></th>
				<th scope="col"><?php _e('Issue') ?></th>
				<th scope="col"><?php _e('Image') ?></th>
				<th scope="col"><?php _e('Type') ?></th>
				<th scope="col"><?php _e('Published') ?></th>
				<th scope="col"><?php _e('Visible') ?></th>
				<th scope="col"><?php _e('Edit') ?></th>
				<th scope="col"><?php _e('Delete') ?></th>
			</tr>
		<?php
		$class = '';
		foreach ( $biblios as $biblio )
		{
			$class = ($class == 'alternate') ? '' : 'alternate';
			?>
			<tr class="<?php echo $class; ?>">
				<th scope="row"><?php echo $biblio->biblioid; ?></th>
				<td><?php echo $biblio->title ?></td>
				<td><?php echo $biblio->titleurl!='' ? 'Yes' : 'No'; ?></td>
				<td><?php echo $biblio->source; ?></td>
				<td><?php echo $biblio->sourceissue; ?></td>
				<td><?php echo $biblio->imgsrc!='' ? 'Yes' : 'No'; ?></td>
				<td><?php echo $biblio->bibliotype; ?></td>
				<td><?php echo $biblio->published=='yes' ? 'Yes' : 'No'; ?></td>
				<td><?php echo $biblio->visible=='yes' ? 'Yes' : 'No'; ?></td>
				<td><a href="tools.php?page=<?=basename(__FILE__)?>&amp;action=edit_biblio&amp;biblioid=<?php echo $biblio->biblioid;?>" class="edit"><?php echo __('Edit'); ?></a></td>
				<td><a href="tools.php?page=<?=basename(__FILE__)?>&amp;action=delete_biblio&amp;biblioid=<?php echo $biblio->biblioid;?>" class="delete" onclick="return confirm('Are you sure you want to delete this entry?')"><?php echo __('Delete'); ?></a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	else
	{
		?>
		<p><?php _e("You haven't entered any biblio entries yet.") ?></p>
		<?php	
	}
}

// Displays the add/edit form
function bibliofly_editform($mode='add_biblio', $biblioid=false)
{
	global $wpdb;
	$data = false;
	
	if ( $biblioid !== false )
	{
		// this next line makes me about 200 times cooler than you.
		if ( intval($biblioid) != $biblioid )
		{
			echo "<div class=\"error\"><p>Bad Monkey! No banana!</p></div>";
			return;
		}
		else
		{
			$data = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bibliofly WHERE biblioid = '" . $biblioid . " LIMIT 1'");
			if ( empty($data) )
			{
				echo "<div class=\"error\"><p>I couldn't find a biblio entry linked up with that identifier. Giving up...</p></div>";
				return;
			}
			$data = $data[0];
		}	
	}
	
	?>
	<form name="biblioform" id="biblioform" class="wrap" method="post" action="">
		<input type="hidden" name="updateaction" value="<?php echo $mode?>">
		<input type="hidden" name="biblioid" value="<?php echo $biblioid?>">
	
		<div id="item_manager">
			<div style="float: left; width: 98%; clear: both;" class="top">

				<div style="float: right; width: 150px;" class="top">
				<fieldset class="small"><legend><?php _e('Biblio Type'); ?></legend>
					<input type="radio" name="biblio_bibliotype" class="input" value="novel" 
					<?php if ( empty($data) || $data->bibliotype=='novel' ) echo "checked" ?>/> Novel 
					<br />
					<input type="radio" name="biblio_bibliotype" class="input" value="short" 
					<?php if ( !empty($data) && $data->bibliotype=='short' ) echo "checked" ?>/> Short Fiction 
					<br />
					<input type="radio" name="biblio_bibliotype" class="input" value="poem" 
					<?php if ( !empty($data) && $data->bibliotype=='poem' ) echo "checked" ?>/> Poem 
					<br />
					<input type="radio" name="biblio_bibliotype" class="input" value="text" 
					<?php if ( !empty($data) && $data->bibliotype=='text' ) echo "checked" ?>/> Non-Fiction Book 
					<br />
					<input type="radio" name="biblio_bibliotype" class="input" value="article" 
					<?php if ( !empty($data) && $data->bibliotype=='article' ) echo "checked" ?>/> Article / Essay 
				</fieldset>
					<br />

				<fieldset class="small"><legend><?php _e('Published'); ?></legend>
					<input type="radio" name="biblio_published" class="input" value="yes" 
					<?php if ( empty($data) || $data->published=='yes' ) echo "checked" ?>/> Yes
					<br />
					<input type="radio" name="biblio_published" class="input" value="no" 
					<?php if ( !empty($data) && $data->published=='no' ) echo "checked" ?>/> No
				</fieldset>
					<br />

				<fieldset class="small"><legend><?php _e('Visible'); ?></legend>
					<input type="radio" name="biblio_visible" class="input" value="yes" 
					<?php if ( empty($data) || $data->visible=='yes' ) echo "checked" ?>/> Yes
					<br />
					<input type="radio" name="biblio_visible" class="input" value="no" 
					<?php if ( !empty($data) && $data->visible=='no' ) echo "checked" ?>/> No
				</fieldset>
				</div>

				<!-- List URL -->
				<fieldset class="small"><legend><?php _e('Title'); ?></legend>
					<input type="text" name="biblio_title" class="input" size=45 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->title); ?>" />
				</fieldset>
				
				<fieldset class="small"><legend><?php _e('Subtitle'); ?></legend>
					<input type="text" name="biblio_subtitle" class="input" size=45 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->subtitle); ?>" />
				</fieldset>

				<fieldset class="small"><legend><?php _e('URL'); ?></legend>
					<input type="text" name="biblio_titleurl" class="input" size=45 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->titleurl); ?>" />
				</fieldset>
				
				<fieldset class="small"><legend><?php _e('Source'); ?></legend>
					<input type="text" name="biblio_source" class="input" size=45 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->source); ?>" />
				</fieldset>

				<fieldset class="small"><legend><?php _e('Issue'); ?></legend>
					<input type="text" name="biblio_sourceissue" class="input" size=45 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->sourceissue); ?>" />
				</fieldset>

				<fieldset class="small"><legend><?php _e('Image'); ?></legend>
					<input type="text" name="biblio_imgsrc" class="input" size=45 value="<?php if ( !empty($data) ) echo htmlspecialchars($data->imgsrc); ?>" />
				</fieldset>

				<fieldset class="small"><legend><?php _e('Excerpt'); ?></legend>
					<textarea name="biblio_excerpt" class="input" cols=45 rows=7><?php if ( !empty($data) ) echo htmlspecialchars($data->excerpt); ?></textarea>
				</fieldset>

				<fieldset class="small"><legend><?php _e('Notes'); ?></legend>
					<textarea name="biblio_notes" class="input" cols=45 rows=7><?php if ( !empty($data) ) echo htmlspecialchars($data->biblionotes); ?></textarea>
				</fieldset>

				<br />
				<input type="submit" name="save" class="button bold" value="Save &raquo;" />

			</div>
			<div style="clear:both; height:1px;">&nbsp;</div>
		</div>
	</form>
	<?php
}

// BiblioFly Functions

// Print small biblio entry (eg title and link only, for use in sidebar)
function bf_printsmall($data, $class='class="bf_small"')
{ ?>
	<li "<?php echo $class ?>">
		<?php if ( !empty($data->titleurl) && $data->published=="yes" ) { _e('<a href="'); echo ($data->titleurl); _e('" title="Published by '); echo ($data->source); _e('">'); }
		echo nl2br($data->title); 
		if ( !empty($data->titleurl) && $data->published=="yes" ) { _e('</a>'); } 
		if ( $data->published == "no" ) { _e(' (forthcoming)'); } ?>
	</li> <?php
}

// Print full biblio entry 
function bf_printfull($data, $enclose='class="bf_enclose"', $image='class="bf_image"', $entry='class="bf_entry"',$excerpt='class="bf_excerpt"',$titleclass='class="bf_entry_title"')
{ 
	?>
	<div <?php echo $enclose ?>><?php
	if (!empty($data->imgsrc))  
	{ ?>
		<div <?php echo $image; ?>><img src="<?php echo ($data->imgsrc); ?>" alt="<?php echo ($data->title); ?>"></div><?php 
	} ?>
	<div <?php echo $entry; ?>>
		<?php if ( !empty($data->titleurl) && $data->published=="yes" ) { _e('<a href="'); echo ($data->titleurl); _e('">'); }
		?><div <?php echo $titleclass ?>><?php echo nl2br($data->title); ?>
		<?php if ( !empty($data->subtitle) && is_page() ) { _e(' ('); echo nl2br($data->subtitle); _e(')'); } ?>
		</div><?php 
		if ( !empty($data->titleurl) && $data->published="yes" ) { _e('</a>'); }
		if ($data->published=="no" && !is_page() ) { _e(' &#8212; forthcoming &#8212; '); } else { _e(' &#8212; '); }
		echo ($data->source); 
		if ( !empty($data->sourceissue) ) { _e(' ('); echo ($data->sourceissue); _e(')'); } ?>
	<?php if ( !empty($data->excerpt) ) 
		{ ?>
		<div <?php echo $excerpt ?>><?php echo nl2br($data->excerpt);?></div>
		<?php } ?>
	<?php if ( !empty($data->biblionotes) ) 
		{ ?>
		<div><?php echo nl2br($data->biblionotes);?></div>
		<?php } ?>
	</div>
	</div>
	<div class="brush"></div>
	<?php
}

// Print random bibliography entry (defaults to a small printout; enter "full" if you want otherwise)
function bf_random($full="small", $class='class="bf_small"', $enclose='class="bf_enclose"', $image='class="bf_image"', $entry='class="bf_entry"',$excerpt='class="bf_excerpt"')
{
	global $wpdb;
	$table_name = $wpdb->prefix . "bibliofly";
	
	if ($full == "full") 
	{
		$sql = "select * from " . $table_name . " where visible='yes'";
		$result = $wpdb->get_results($sql);
		if ( !empty($result) ) bf_printfull($result[mt_rand(0, count($result)-1)],$enclose,$image,$entry,$excerpt);
	} else {
		$sql = "select title, titleurl, source, published from " . $table_name . " where visible='yes'";
		$result = $wpdb->get_results($sql);
		if ( !empty($result) ) bf_printsmall($result[mt_rand(0, count($result)-1)],$class);
	}
}

// Print specific bibliography entry
function bf_specific($id, $full="small", $class='class="bf_small"', $enclose='class="bf_enclose"', $image='class="bf_image"', $entry='class="bf_entry"',$excerpt='class="bf_excerpt"',$titleclass='class="bf_entry_title"')
{
	global $wpdb;
	$table_name = $wpdb->prefix . "bibliofly";
	
	if ($full=="full")
	{
		$sql = "select * from " . $table_name . " where biblioid='{$id}'";
		$result = $wpdb->get_results($sql);
		if ( !empty($result) ) bf_printfull($result[0],$enclose,$image,$entry,$excerpt);
	} else {
		$sql = "select title, subtitle, titleurl, source, published from " . $table_name . " where biblioid='{$id}'";
		$result = $wpdb->get_results($sql);
		if ( !empty($result) ) bf_printsmall($result[0],$class);
	}	
}

// Print the last 'x' bibliography entries using the printsmall function (defaults to all if no limit specified)
function bf_recent($limit = -1, $class='class="bf_small"') 
{
    global $wpdb;
		$table_name = $wpdb->prefix . "bibliofly";

		$the_link = '#';
    $sql = "SELECT title, subtitle, titleurl, source, published FROM " . $table_name . " WHERE visible='yes' ORDER BY 'biblioid' DESC";
    if ($limit != -1) $sql .= " LIMIT " . $limit;
		$results = $wpdb->get_results($sql);

		if ( !empty($results) )
		{
      $reverse = array_reverse($results);
			foreach ($reverse as $row) 
			{
					bf_printsmall($row,$class);
			}
		}
}

// Put together the embedfunction output
function bf_embedstring($bibliodata, $full="full", $class='class="bf_small"', $enclose='class="bf_enclose"', $image='class="bf_image"', $entry='class="bf_entry"',$excerpt='class="bf_excerpt"',$titleclass='class="bf_entry_title"',$subtitleclass='class="bf_entry_subtitle"',$sourceclass='class="bf_entry_source"')
{
	if ($full=="full") {
		$bibliocode ='<div ' . $enclose . '>';
		if (!empty($bibliodata->imgsrc)) {
			$bibliocode .= '<div ' . $image . '><img src="' . $bibliodata->imgsrc . '" alt="' . $bibliodata->title . '"></div>';
		}
		$bibliocode .= '<div ' . $entry . '>';
		if ( !empty($bibliodata->titleurl) && $bibliodata->published=="yes") { 
			$bibliocode .= '<a href="' . $bibliodata->titleurl . '">';
		}
		$bibliocode .= '<div ' . $titleclass . '>' . $bibliodata->title;
		if ( !empty($bibliodata->subtitle) && is_page() ) {
			$bibliocode .= '<span ' .$subtitleclass . '>' . $bibliodata->subtitle . '</span>';
		}
		if ( !empty($bibliodata->titleurl) && $bibliodata->published=="yes") { 
			$bibliocode .= '</a>';
		}
		if ($bibliodata->published=="no" && !is_page() ) $bibliocode .= ' &#8212; forthcoming';
		$bibliocode .= '<span ' . $sourceclass . '> &#8212; ' . $bibliodata->source;
		if ( !empty($bibliodata->sourceissue) ) $bibliocode .= ' (' . $bibliodata->sourceissue . ')';
		$bibliocode .= '</span></div>';
		if ( !empty($bibliodata->excerpt) ) $bibliocode .= '<div ' . $excerpt . '>' . nl2br($bibliodata->excerpt) . '</div>';
		if ( !empty($bibliodata->biblionotes) ) $bibliocode .= '<div>' . nl2br($bibliodata->biblionotes) . '</div>';
		$bibliocode .= '</div></div><div class="brush"></div>';
	} else {
		$bibliocode = '<p ' . $class . '>';
		if ( !empty($bibliodata->titleurl) && $bibliodata->published=="yes") {
			$bibliocode .= '<a href="' . $bibliodata->titleurl . '" title="Published by ' . $bibliodata->source . '">';
		}
		$bibliocode .= $bibliodata->title;
		if ( !empty($bibliodata->titleurl) && $bibliodata->published=="yes") {
			$bibliocode .= '</a>';
		}
		if ($bibliodata->published=="no" && !is_page() ) $bibliocode .= ' (forthcoming)';
		$bibliocode .='</p>';
	}
	return $bibliocode;
}

// Embed a biblio entry in a post or page
function bf_embedfunction($content)
{
	global $post, $wpdb;
	$table_name = $wpdb->prefix . "bibliofly";
	$full = "full";
	// tag taxonomy: [bfentry id=$id small] (where small is optional and, if omitted, full will be assumed)
	
	//step one: do a search for the full tag:
	if (preg_match_all("/\[bfentry.*\]/i", $content, $matches) ) {
		$data = $matches[0];
		
		//start the loop to go through the $matches[0] array and fill the $biblio array
		$i = 0;
		foreach ($data as $datum) {
			if (preg_match("/\sid=(\S*?)[\]\s]/i", $datum, $intmatches) ) {
				$biblioid = intval($intmatches[1]);
			}
			if (preg_match("/\bsmall\b/i", $datum) ) {
				$full = "small";
			}
			if ($full=="full") {
				$sql = "select * from " . $table_name . " where biblioid='{$biblioid}'";
			} else {
				$sql = "select title, titleurl, source, published from " . $table_name . " where biblioid='{$biblioid}'";
			}
			$results = $wpdb->get_results($sql);
			$biblio[$i] = bf_embedstring($results[0], $full);
			$i++;
			$full="full";
		}
		$content = str_replace($data, $biblio, $content);
	}
	return $content;
}

// Print all bibliography entries on to a page, sorted by type
// -- I can keep the old bf_page function, and just convert it into an embedded version.)
function bf_page($content) 
{
	global $wpdb, $post;
	$table_name = $wpdb->prefix . "bibliofly";
	$type = 0;
	$full="full";
	
	//tag taxonomy: [biblioflypage type=#] (where type (1 = novel, 2 = short, 3 = poem, 4 = text, 5 = article) is optional and, if omitted, '0' or all will be assumed)
	
	//step one: do a search for the full tag:
	if (preg_match_all("/\[biblioflypage.*\]/i", $content, $matches) ) 
	{
		$data = $matches[0];
		
		//start the loop to go through the $matches[0] array and fill the $bibliopage array
		$i = 0;
		foreach ($data as $datum) 
		{
			if (preg_match("/\stype=(\S*?)[\]\s]/i", $datum, $intmatches) ) 
			{
				$type = $intmatches[1];
			} 
			
			if ( empty($type) ) $type=0;
			
			if ( $type == 0) 
			{
//				select all by type; for each type, print the type heading, select the published and print those, then select the unpublished and print those under the subhead 'forthcoming'
				$sql_novel = $wpdb->get_results("SELECT biblioid FROM " . $table_name . " WHERE (bibliotype='novel' AND visible='yes')");
				if ( count($sql_novel)>0 ) 
				{
					$bibliopage[$i] .= "<h3 class=\"bibliofly\">Novels</h3>";
					$unpublished = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='novel' AND visible='yes' AND published='no') ORDER BY 'biblioid'");
					$unpublishedordered = array_reverse($unpublished);
					if ( !empty($unpublished) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">forthcoming</h4>";
						foreach ( $unpublishedordered as $row ) 
						{
							$bibliopage[$i] .=bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$published = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='novel' AND visible='yes' AND published='yes') ORDER BY 'biblioid'");
					$publishedordered = array_reverse($published);
					if ( !empty($published) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">available now</h4>";
						foreach ( $publishedordered as $row ) 
						{
							$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$bibliopage[$i] .= "<div class=\"sep\">&nbsp;</div>";
				}

				$sql_short = $wpdb->get_results("SELECT biblioid FROM " . $table_name . " WHERE (bibliotype='short' AND visible='yes')");
				if ( count($sql_short)>0 ) 
				{
					$bibliopage[$i] .= "<h3 class=\"bibliofly\">Short Fiction</h3>";
					$unpublished = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='short' AND visible='yes' AND published='no') ORDER BY 'biblioid'");
					$unpublishedordered = array_reverse($unpublished);
					if ( !empty($unpublished) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">forthcoming</h4>";
						foreach ( $unpublishedordered as $row ) 
						{
							$bibliopage[$i] .=bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$published = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='short' AND visible='yes' AND published='yes') ORDER BY 'biblioid'");
					$publishedordered = array_reverse($published);
					if ( !empty($published) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">available now</h4>";
						foreach ( $publishedordered as $row ) 
						{
							$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$bibliopage[$i] .= "<div class=\"sep\">&nbsp;</div>";
				}

				$sql_poem = $wpdb->get_results("SELECT biblioid FROM " . $table_name . " WHERE (bibliotype='poem' AND visible='yes')");
				if ( count($sql_poem)>0 ) 
				{
					$bibliopage[$i] .= "<h3 class=\"bibliofly\">Poems</h3>";
					$unpublished = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='poem' AND visible='yes' AND published='no') ORDER BY 'biblioid'");
					$unpublishedordered = array_reverse($unpublished);
					if ( !empty($unpublished) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">forthcoming</h4>";
						foreach ( $unpublishedordered as $row ) 
						{
							$bibliopage[$i] .=bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$published = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='poem' AND visible='yes' AND published='yes') ORDER BY 'biblioid'");
					$publishedordered = array_reverse($published);
					if ( !empty($published) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">available now</h4>";
						foreach ( $publishedordered as $row ) 
						{
							$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$bibliopage[$i] .= "<div class=\"sep\">&nbsp;</div>";
				}

				$sql_text = $wpdb->get_results("SELECT biblioid FROM " . $table_name . " WHERE (bibliotype='text' AND visible='yes')");
				if ( count($sql_text)>0 ) 
				{
					$bibliopage[$i] .= "<h3 class=\"bibliofly\">Non-Fiction Books</h3>";
					$unpublished = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='text' AND visible='yes' AND published='no') ORDER BY 'biblioid'");
					$unpublishedordered = array_reverse($unpublished);
					if ( !empty($unpublished) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">forthcoming</h4>";
						foreach ( $unpublishedordered as $row ) 
						{
							$bibliopage[$i] .=bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$published = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='text' AND visible='yes' AND published='yes') ORDER BY 'biblioid'");
					$publishedordered = array_reverse($published);
					if ( !empty($published) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">available now</h4>";
						foreach ( $publishedordered as $row ) 
						{
							$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$bibliopage[$i] .= "<div class=\"sep\">&nbsp;</div>";
				}

				$sql_essay = $wpdb->get_results("SELECT biblioid FROM " . $table_name . " WHERE (bibliotype='essay' AND visible='yes')");
				if ( count($sql_essay)>0 ) 
				{
					$bibliopage[$i] .= "<h3 class=\"bibliofly\">Articles</h3>";
					$unpublished = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='essay' AND visible='yes' AND published='no') ORDER BY 'biblioid'");
					$unpublishedordered = array_reverse($unpublished);
					if ( !empty($unpublished) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">forthcoming</h4>";
						foreach ( $unpublishedordered as $row ) 
						{
							$bibliopage[$i] .=bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
					$published = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='essay' AND visible='yes' AND published='yes') ORDER BY 'biblioid'");
					$publishedordered = array_reverse($published);
					if ( !empty($published) ) 
					{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">available now</h4>";
						foreach ( $publishedordered as $row ) 
						{
							$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
						}
					}
  				$bibliopage[$i] .= "<div class=\"sep\">&nbsp;</div>";
				}
			} 
			else 
			{
  			if ( $type == 1) 
  			{
  				$bibliotype = 'novel';
  				$biblioheader = 'Novels';
  			}
  			if ( $type == 2) 
  			{
  				$bibliotype = 'short';
  				$biblioheader = 'Short Fiction';
  			}
  			if ( $type == 3) 
  			{
  				$bibliotype = 'poem';
  				$biblioheader = 'Poems';
  			}
  			if ( $type == 4) 
  			{
  				$bibliotype = 'text';
  				$biblioheader = 'Non-Fiction Books';
  			}
  			if ( $type == 5) 
  			{
  				$bibliotype = 'essay';
  				$biblioheader = 'Articles';
  			}
  			
  			$sql = $wpdb->get_results("SELECT biblioid FROM " . $table_name . " WHERE (bibliotype='" . $bibliotype . "' AND visible='yes')");
  			if ( count($sql)>0 ) 
  			{
  				$bibliopage[$i] .="<h3 class=\"bibliofly\">" . $biblioheader . "</h3>";
  				$unpublished = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='" . $bibliotype . "' AND visible='yes' AND published='no') ORDER BY 'biblioid'");
					$unpublishedordered = array_reverse($unpublished);
  				if ( !empty($unpublished) ) 
  				{
  					$bibliopage[$i] .= "<h4 class=\"bibliofly\">forthcoming</h4>";
  					foreach ($unpublishedordered as $row) 
  					{
  						$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
  					}
  				}
  				$published = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (bibliotype='" . $bibliotype . "' AND visible='yes' AND published='yes') ORDER BY 'biblioid'");
					$publishedordered = array_reverse($published);
  				if ( !empty($published) ) 
  				{
						$bibliopage[$i] .= "<h4 class=\"bibliofly\">available now</h4>";
  					foreach ($publishedordered as $row) 
  					{
  						$bibliopage[$i] .= bf_embedstring($row,$full, 'class="bf_small"', 'class="bfpage"', 'class="bfpage_img"', 'class="bfpage_entry"','class="bfpage_excerpt"','class="bfpage_entry_title"','class="bfpage_entry_subtitle"','class="bfpage_entry_source"');
  					}
  				}
  				$bibliopage[$i] .= "<div class=\"sep\">&nbsp;</div>";
  			}
			}
			$i++;
			$type="0";
		}
		$content = str_replace($data, $bibliopage, $content);
	}
	return $content;
}

add_filter('the_content','bf_embedfunction');
add_filter('the_content','bf_page');

// Insert the bibliofly_admin_menu() sink into the plugin hook list for 'admin_menu'
add_action('admin_menu', 'bibliofly_admin_menu');
?>