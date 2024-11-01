<?php

/*
Plugin Name: tscopper
Plugin URI: http://www.tophamsoftware.com/tscopper
Description: Retrieve images from Coppermine
Version: 0.9.4
Author: Topham Software - Mark Topham
Author URI: http://www.tophamsoftware.com
*/

/*
Copyright 2010 - Topham Software

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

/*
Usage:
  [tscopper key=value key2=value ...]
    from  (album, category, gallery, image)
    id    (album id, category id, image id)
    name  (album name, image name, etc)
    limit (maximum images to return, if any)
    order (new, random)
*/

require_once('tscopper_db.php');
$db = '';
define ('TS_DEFAULT_SLIDESHOW','shadowbox[#Post-ID#]');
define ('TS_DEFAULT_PRECONFIGURE','[tscopper from="image" id="#ID#"][/tscopper]');
define ('TS_DBUSER', get_option('tscopper_dbuser'));
define ('TS_DBPASS', get_option('tscopper_dbpass'));
define ('TS_DBSERVER', get_option('tscopper_dbserver'));
define ('TS_DBNAME', get_option('tscopper_dbname'));
define ('TS_TABLE_PREFIX', get_option('tscopper_table_prefix'));
define ('TS_GALLERY', get_option('tscopper_gallery'));
define ('TS_SLIDESHOW_TOOL', get_option('tscopper_slideshow_tool',TS_DEFAULT_SLIDESHOW));
define ('TS_PRECONFIGURE', get_option('tscopper_preconfigure',TS_DEFAULT_PRECONFIGURE));

add_shortcode('tscopper', 'tscopper_process');
add_action('wp_print_styles', 'tscopper_style');
add_action('admin_menu', 'tscopper_admin_menu');
//
//
// Add 'Coppermine' button to media button

add_filter('media_buttons_context', 'tscopper_media_button');
add_action('wp_ajax_tscopper', 'tscopper_ajax_form');
//
function tscopper_media_button($context)
{
  $context .= '<a class="thickbox button" href="'.WP_PLUGIN_URL.'/tscopper/cp_handler.php?action=tscopper&amp;TB_iframe=true" title="Coppermine Gallery - Select Images">Coppermine</a>';
  return $context;
}

function tscopper_ajax_css() 
{
  echo '<link ref="stylesheet" href="'.WP_PLUGIN_URL.'/tscopper/style.css" type="text/css" medial="all">';
}

function tscopper_admin_menu() {
  add_options_page('TS Copper Settings','tscopper','manage_options','com.tophamsoftware.wordpress.tscopper','tscopper_plugin_options');  
}

function tscopper_plugin_options() {
  if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient Privledges.'));
  }
    
  $ts_dbuser = get_option('tscopper_dbuser');
  $ts_dbpass = get_option('tscopper_dbpass');
  $ts_dbserver = get_option('tscopper_dbserver');
  $ts_dbname = get_option('tscopper_dbname');
  $ts_table_prefix = get_option('tscopper_table_prefix');
  $ts_gallery = get_option('tscopper_gallery');
  $ts_slideshow_tool = get_option('tscopper_slideshow_tool', TS_DEFAULT_SLIDESHOW);
  $ts_preconfigure = get_option('tscopper_preconfigure', TS_DEFAULT_PRECONFIGURE);
  
  $hidden_field_name = 'tscopper_hidden_submit';
  
  if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {
    $ts_dbuser = $_POST['ts_dbuser']; 
    $ts_dbpass = $_POST['ts_dbpass'];
    $ts_dbserver = $_POST['ts_dbserver'];
    $ts_dbname = $_POST['ts_dbname'];
    $ts_dbuser = $_POST['ts_dbuser'];
    $ts_gallery = $_POST['ts_gallery'];
    $ts_table_prefix = $_POST['ts_table_prefix'];
    $ts_slideshow_tool = $_POST['ts_slideshow_tool'];
    $ts_preconfigure = $_POST['ts_preconfigure'];
    
    update_option('tscopper_dbuser',$_POST['ts_dbuser']);
    update_option('tscopper_dbpass',$_POST['ts_dbpass']);
    update_option('tscopper_dbserver',$_POST['ts_dbserver']);
    update_option('tscopper_dbname',$_POST['ts_dbname']);
    update_option('tscopper_table_prefix',$_POST['ts_table_prefix']);
    update_option('tscopper_gallery',$_POST['ts_gallery']);
    update_option('tscopper_slideshow_tool', $_POST['ts_slideshow_tool']);
    update_option('tscopper_preconfigure', $_POST['ts_preconfigure']);
 ?>
 <div class="updated"><p><strong><?php _e('Settings saved.', 'tscopper'); ?></strong></p></div>
 <?php    
  }
  // Display settings screen
  
  echo '<div class="wrap">';
  echo '<h2>'. __('TS Copper Settings', 'tscopper').'</h2>';
  ?>
<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<table class="form-table">
<tbody>
<tr valign="top"><th scope="row"><label for="dbuser"><?php _e("DB User:", 'tscopper'); ?></label></th><td><input type="text" name="ts_dbuser" value="<?php echo esc_attr(stripslashes($ts_dbuser)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="dbpass"><?php _e("DB Pass:", 'tscopper'); ?></label></th><td><input type="text" name="ts_dbpass" value="<?php echo esc_attr(stripslashes($ts_dbpass)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="dbserver"><?php _e("DB Server:", 'tscopper'); ?></label></th><td><input type="text" name="ts_dbserver" value="<?php echo esc_attr(stripslashes($ts_dbserver)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="dbname"><?php _e("DB Name:", 'tscopper'); ?></label></th><td><input type="text" name="ts_dbname" value="<?php echo esc_attr(stripslashes($ts_dbname)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="tableprefix"><?php _e("Table Prefix:", 'tscopper'); ?></label></th><td><input type="text" name="ts_table_prefix" value="<?php echo esc_attr(stripslashes($ts_table_prefix)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="galleryurl"><?php _e("Gallery URL:", 'tscopper'); ?></label></th><td><input type="text" name="ts_gallery" value="<?php echo esc_attr(stripslashes($ts_gallery)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="slideshowtool"><?php _e("Slideshow Tool: (Lightbox[#Page-ID#] or shadowbox[#Page-ID#])", 'tscopper'); ?></label></th><td><input type="text" name="ts_slideshow_tool" value="<?php echo esc_attr(stripslashes($ts_slideshow_tool)); ?>" size="60"></td></tr>
<tr valign="top"><th scope="row"><label for="preconfigured"><?php _e("Preconfigured Default: (edit with care)", 'tscopper'); ?></label></th><td><input type="text" name="ts_preconfigure" value="<?php echo esc_attr(stripslashes($ts_preconfigure)); ?>" size="120"></td></tr></tbody>
</table>
<hr />
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>
<?php
  echo '</div>';
}


function tscopper_process ($parms, $content, $code)
{

   global $db;
  
   // At this point, $parms contains keys and values from the tag
   // expected keys:
   // from, [id | name], [limit], [order]
   // from (album, category, gallery, image)
   // name (name of album, category)
   // id (id of album, image, category)  
   // order (new, random; default is 'natural' order)
   //  
   
   if (isset($parms['from'])) {
     $db = new tsdb(TS_DBUSER, TS_DBPASS, TS_DBNAME, TS_DBSERVER);
     switch ($parms['from']) {
       case "album":
         $results = tscopper_album($parms); 
         break;
       case "category":
         $results = tscopper_category($parms);
         break;
       case "gallery":
         $results = tscopper_gallery($parms);
         break;
       case "image":
         $results = tscopper_image($parms);
         break;
     }
     $db->tsdb_close();

     $block = tscopper_format_results($results, $parms, $content);
     
     return $block;
   } else {
     // 
     return '[tscopper tag is malformed]';
   }
   
}

function tscopper_album(array $parms) 
{
  global $db;
  // Use Query by Name
  // Use Query by Album id (aid)
  //echo 'parms:'.var_dump($parms);
  $out = '';
  if (isset($parms['id']) OR isset($parms['name'])) {
    if (isset($parms['order'])) {
      switch($parms['order']) {
        case 'new':
          $order = ' ORDER BY pid DESC ';
          break;
        case 'random':
          $order = ' ORDER BY RAND() ';
          break;
      }
    }
    else 
      $order = '';

    if (isset($parms['limit'])) {
      $limit = ' LIMIT '.(int)$parms['limit'];
    } 
    else
      $limit = '';
    if (isset($parms['id'])) {
      // keywords
        $keyword_query = '';
        $keyword_words = array();
      if (isset($parms['keywords'])) {
        //$keyword_words = explode(" ",$parms['keywords']);
        foreach (explode(";",$parms['keywords']) as $word) {
          $keyword_query .= " AND (";
          $keyword_query .= " (pictures.keywords = ? ) ";
          $keyword_query .= "  OR ";
          $keyword_query .= " (pictures.keywords LIKE CONCAT('%;', ? , '%')) ";
          $keyword_query .= "  OR ";
          $keyword_query .= "  (pictures.keywords LIKE CONCAT('%', ? ,';%'))";
          $keyword_query .= ") ";
          array_push($keyword_words, $word);
          array_push($keyword_words, $word);
          array_push($keyword_words, $word);
        }
      }
      //
      $results = $db->query(
          "SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid ".
          "FROM ".TS_TABLE_PREFIX."pictures AS pictures ".
          "  INNER JOIN ".TS_TABLE_PREFIX."albums AS albums ON (albums.aid = pictures.aid) ".
          "WHERE albums.aid = ? ".
    $keyword_query.
          " UNION ".
          "SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid ".
          "FROM ".TS_TABLE_PREFIX."pictures AS pictures ".
          "  INNER JOIN ".TS_TABLE_PREFIX."albums AS albums ON (pictures.keywords <> '' AND (".
          "   (pictures.keywords = albums.keyword) OR ".
          "   (pictures.keywords LIKE CONCAT('%;',albums.keyword,'%')) OR ".
          "   (pictures.keywords LIKE CONCAT('%',albums.keyword,';%'))) ".
          ") WHERE albums.keyword <> '' AND albums.aid = ? ".
          $keyword_query.
          $order.$limit.';'
          ,array_merge(array($parms['id']), $keyword_words,array($parms['id']), $keyword_words));

    } else if (isset($parms['name'])) {
      return array();
      //return 'album by name not implemented';
    } //
    
    return $results;
  } else {
    return array();
    
  }
  return array();
}

function tscopper_category(array $parms)
{
  // SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid 
  //   FROM TS_TABLE_PREFIX.pictures AS pictures
  //   INNER JOIN TS_TABLE_PREFIX.albums AS albums ON (pictures.aid = albums.aid) 
  //   WHERE albums.category = ?
  //   
  global $db;

  if (isset($parms['id']) ) {
    $array_of_ids = explode(",",$parms['id']);
    if (isset($parms['order'])) {
      switch($parms['order']) {
        case 'new':
          $order = ' ORDER BY pid DESC ';
          break;
        case 'random':
          $order = ' ORDER BY RAND() ';
          break;
      }
    }
    else { 
      $order = '';
    }

    if (isset($parms['limit'])) {
      $limit = ' LIMIT '.(int)$parms['limit'];
    } 
    else {
      $limit = '';
    }
    $i = 0;
    $query_parm = '';
    foreach($array_of_ids as $picId) {
      $i++;
      $query_parm .= ($i == 1) ? ' albums.category = ? ' : ' OR albums.category = ? ';      
    }
    $query = 'SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid ';
    $query .= '  FROM '.TS_TABLE_PREFIX.'pictures AS pictures ';
    $query .= ' INNER JOIN '.TS_TABLE_PREFIX.'albums AS albums ON (pictures.aid = albums.aid) ';     
    $query .= '  WHERE '.$query_parm;
    $query .= $order;
    $query .= $limit;
    $query .= ';';
    $results = $db->query($query, $array_of_ids);

    return $results; 
  }
  return array();
}

function tscopper_gallery(array $parms)
{
  // SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid 
  //   FROM TS_TABLE_PREFIX.pictures AS pictures
  //   INNER JOIN TS_TABLE_PREFIX.albums AS albums ON (pictures.aid = albums.aid) 
  //   
  global $db;

    $array_of_ids = explode(",",$parms['id']);
    if (isset($parms['order'])) {
      switch($parms['order']) {
        case 'new':
          $order = ' ORDER BY pid DESC ';
          break;
        case 'random':
          $order = ' ORDER BY RAND() ';
          break;
      }
    }
    else { 
      $order = '';
    }

    if (isset($parms['limit'])) {
      $limit = ' LIMIT '.(int)$parms['limit'];
    } 
    else {
      $limit = '';
    }

    $query = 'SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid ';
    $query .= '  FROM '.TS_TABLE_PREFIX.'pictures AS pictures ';
    $query .= ' INNER JOIN '.TS_TABLE_PREFIX.'albums AS albums ON (pictures.aid = albums.aid) ';     
    $query .= '  WHERE albums.visibility=0 ';
    $query .= $order;
    $query .= $limit;
    $query .= ';';
    
    $results = $db->query($query, array());
    return $results;
}

function tscopper_image(array $parms)
{
  // SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid 
  //   FROM TS_TABLE_PREFIX.pictures AS pictures
  //   WHERE pictures.pid = ?
  //   
  global $db;

  if (isset($parms['id']) ) {
    $array_of_ids = explode(",",$parms['id']);
    if (isset($parms['order'])) {
      switch($parms['order']) {
        case 'new':
          $order = ' ORDER BY pid DESC ';
          break;
        case 'random':
          $order = ' ORDER BY RAND() ';
          break;
      }
    }
    else { 
      $order = '';
    }

    if (isset($parms['limit'])) {
      $limit = ' LIMIT '.(int)$parms['limit'];
    } 
    else {
      $limit = '';
    }
    $i = 0;
    $query_parm = '';
    foreach($array_of_ids as $picId) {
      $i++;
      $query_parm .= ($i == 1) ? ' pictures.pid = ? ' : ' OR pictures.pid = ? ';      
    }
    $query = 'SELECT pictures.filepath, pictures.filename, pictures.title, pictures.pid ';
    $query .= '  FROM '.TS_TABLE_PREFIX.'pictures AS pictures ';
    $query .= '  WHERE '.$query_parm;
    $query .= $order;
    $query .= $limit;
    $query .= ';';

    $results = $db->query($query, $array_of_ids);
    
    return $results;
  }
  return array();
}

function tscopper_format_results(array $results, array $parms, $content = "")
{
  if (is_feed()) 
    return tscopper_format_results_feed($results, $parms, $content);
  else
    return tscopper_format_results_page($results, $parms, $content);
}

function tscopper_format_results_feed(array $results, array $parms, $content = "")
{
  //<span class="tscopper">
  //<a href="http://www.scrappyfriends.com/Gallery/albums/displayimage.php?pos=-pid"></a>
  $out = '';
  $max_col = isset($parms['col']) ? (int)$parms['col'] : 0;
  $current_col = 0;
  $classprefix = isset($parms['classprefix']) ? rawurlencode($parms['classprefix']) : 'tscopper';
  //$out .= tscopper_style();
  
  $out .= '<div class="'.$classprefix.'">';
  
  $out .='<ul class="'.$classprefix.'">';
  foreach($results as $row) {
    $uri_size = TS_GALLERY.'albums/'.$row['filepath'].rawurlencode(isset($parms['size']) ? $parms['size'].'_' :'').$row['filename'];
    $uri = TS_GALLERY.'albums/'.$row['filepath'].$row['filename'];
      $rel = TS_SLIDESHOW_TOOL;
      $rel = str_replace('#Page-ID#', get_the_ID(), $rel);
      $rel = str_replace('#Post-ID#', get_the_ID(), $rel);
      
      $out .= '<li class="'.$classprefix.'" '.
              //(isset($parms['height']) ? ' height="'.rawurlencode($parms['height']).'"' : '').
              //(isset($parms['width']) ? ' width="'.rawurlencode($parms['width']).'"' : '').
              '>'.
              '<a href="'.$uri.'" title="'. 
             str_replace('"','&quot;', $row['title']).'" rel="'.$rel.'">'.
             '<img class="'.$classprefix.'" src="'.$uri_size.'" title="'.str_replace('"','&quot;', $row['title']).'" '.             
             (isset($parms['width']) ? 'width="'.urlencode($parms['width']).'" ' : '').
             (isset($parms['height']) ? 'height="'.urlencode($parms['height']).'" ' : '').
             '/>'.
             '</a></li>';
  }
  $out .= '</ul>'; // tscopperSpanRow
  $out .= '<div class="tscopper-content">'.$content.'</div>';
  $out .= '</div>';
  return $out;

}


function tscopper_format_results_page(array $results, array $parms, $content = "")
{
  //<span class="tscopper">
  //<a href="http://www.scrappyfriends.com/Gallery/albums/displayimage.php?pos=-pid"></a>
  $out = '';
  
  $max_col = isset($parms['col']) ? (int)$parms['col'] : 0;
  $current_col = 0;
  $classprefix = isset($parms['classprefix']) ? rawurlencode($parms['classprefix']) : 'tscopper';
  //$out .= tscopper_style();
  $out .= '<div class="'.$classprefix.'">';
  $out .= '<table class="'.$classprefix.'">';
  
  $out .='<tbody class="'.$classprefix.'">';
  foreach($results as $row) {
    $uri_size = TS_GALLERY.'albums/'.$row['filepath'].rawurlencode(isset($parms['size']) ? $parms['size'].'_' :'').$row['filename'];
    $uri = TS_GALLERY.'albums/'.$row['filepath'].$row['filename'];
        
    if ($max_col > 0 && $current_col % $max_col == 0) 
      $out .= ($current_col > 0 ? '</tr>':'').'<tr class="'.$classprefix.'">';
      $rel = TS_SLIDESHOW_TOOL;
      $rel = str_replace('#Page-ID#', get_the_ID(), $rel);
      $rel = str_replace('#Post-ID#', get_the_ID(), $rel);
      $out .= '<td class="'.$classprefix.'" '.
              (isset($parms['height']) ? ' height="'.rawurlencode($parms['height']).'"' : '').
              (isset($parms['width']) ? ' width="'.rawurlencode($parms['width']).'"' : '').
              '>'.
              '<a href="'.$uri.'" title="'. 
             str_replace('"','&quot;', $row['title']).'" rel="'.$rel.'">'.
             '<img class="'.$classprefix.'" src="'.$uri_size.'" title="'.str_replace('"','&quot;', $row['title']).'" '.             
             //(isset($parms['width']) ? 'width="'.urlencode($parms['width']).'" ' : '').
             //(isset($parms['height']) ? 'height="'.urlencode($parms['height']).'" ' : '').
             '/>'.
             '</a>';
       if (isset($parms['checkbox'])) {
         $out .= '<input type="checkbox" name="includeImage" value="'.$row['pid'].'"></input>';
       }
       $out .= '</td>';

    $current_col++;
  }
  $out .= '</tr>'; // tscopperSpanRow
  $out .= '</tbody>';
  $out .= '</table>'; // tscopperSpan
  $out .= '<div class="tscopper-content">'.$content.'</div>';
  $out .= '</div>';
//  var_dump($results);
  return $out;

}

function tscopper_style() 
{
  $myUrl = WP_PLUGIN_URL.'/tscopper/style.css';
  $myFile = WP_PLUGIN_DIR.'/tscopper/style.css';
  if ( file_exists($myFile)) {
    wp_register_style('tscopperStyle', $myUrl);
    wp_enqueue_style('tscopperStyle');
  }
}

?>
