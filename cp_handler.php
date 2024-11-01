<?php

require_once('../../../wp-admin/admin.php');
require_once('./tscopper_db.php');

$db = '';
define ('TS_PRECONFIGURE', get_option('tscopper_preconfigure'));

if (!current_user_can('upload_files'))
  wp_die(__('You do not have permission to upload files.'));

// wp_enqueue_script('');
//wp_enqueue_style('');

cp_handler_scripts();
cp_handler_styles(); // Style sheets

@header('Content-type:'.get_option('html_type').'; charset='.get_option('blog_charset'));

tscopper_ajax_form();


function cp_handler_scripts()
{
  ?>
  <script>
  function tscopper_select_dropdown(ctrl) {
    albumid = ctrl.options[ctrl.selectedIndex].value;
    if (albumid != '') {          
      location.href="cp_handler.php?action=tscopper&albumid=" + albumid + "&amp;TB_iframe=true";
    }
  } 
  
  function tscopper_send_to_parent(output) {    
    var win = window.dialogArguments || opener || parent || top;
    win.send_to_editor(output); 
  }
  
  function tscopper_checkboxes() {
    var imageIds = document.form1.includeImage;
    list = '';
    if (imageIds.length > 0)  {
      for (i=0;i<imageIds.length;i++) {
        if (imageIds[i].checked) {
          if (i == 0)
            list = imageIds[i].value;
          else
            list += ',' + imageIds[i].value;
        }
      }
    }
    else list = imageIds.value;

    if (list != '') {
      template = '<?php echo esc_js(TS_PRECONFIGURE) ?>';
      tscopper_send_to_parent(template.replace("#ID#",list));
    }
    else {
      tscopper_send_to_parent('');
    }    
  }
  
  </script>
  <?php
  
}


function cp_handler_styles() 
{
  echo '<link href="'.WP_PLUGIN_URL.'/tscopper/style.css" type="text/css" media="all">';
  
}


function tscopper_ajax_form ()
{
   global $db;
   
   // Gallery
   //   Category
   //   Album
   // 
   echo '<form name="form1">';
   $db = new tsdb(TS_DBUSER, TS_DBPASS, TS_DBNAME, TS_DBSERVER);
   tscopper_categories();
   if (isset($_GET['albumid'])) {
     $parms = array("checkbox" => "true", "from" => "album", "id" => $_GET['albumid'], "col" => 4, "size" => "thumb");   
     $results = tscopper_album($parms);
     $block = tscopper_format_results_page($results, $parms); 
     echo $block;
   }
   
   $db->tsdb_close();
   echo '</form>';
   exit(); // otherwise the digit 0 appears in output
  
}

function tscopper_categories()
{
  global $db;
  $query = 'SELECT parent, cid, name, description FROM '.TS_TABLE_PREFIX.'categories AS categories WHERE categories.owner_id = 0 ORDER BY depth, name;';
  $results = $db->query($query);
  echo '<select name="album" onChange="tscopper_select_dropdown(this);">';
  tscopper_category_menu('0', $results,'--');
  tscopper_albums_without_category_menu('--');
  
  echo '</select>';
  ?>
  <a href="" class="thickbox button" onClick="tscopper_checkboxes();">Done</a>
  <a href="" class="thickbox button" onClick="tscopper_send_to_parent('');">Close</a>
  <?php
}

function tscopper_category_menu($parent, & $results, $indent)
{
  //echo '<ul>';
  foreach($results as $row) {
    
    if ($row['parent'] == $parent) {
      // All good.
      echo '<option value="">'.$indent.' '.$row['name'].'</option>';
      tscopper_category_menu($row['cid'], $results, '&nbsp;  '.$indent);
      tscopper_albums_menu($row['cid'], '&nbsp;  '.$indent);
    }
  }
  //echo '</ul>';
}



function tscopper_albums_menu($parent, $indent) {
  global $db;
  $query = 'SELECT category, aid, title, description FROM '.TS_TABLE_PREFIX.'albums AS albums WHERE albums.category = '.$parent.'  ORDER BY albums.title;';
  $results = $db->query($query);
  if (count($results) > 0) {
    //echo '<ul>';
    foreach($results as $row) {
      echo '<option value="'.$row['aid'].'" '.($_GET['albumid'] == $row['aid'] ? 'SELECTED':'').'>'.$indent.' '.$row['title'].'</option>';
    }
    //echo '</ul>';    
  }
}

function tscopper_albums_without_category_menu($indent) {
  global $db;
  $query = 'SELECT category, aid, title, description FROM '.TS_TABLE_PREFIX.'albums AS albums WHERE NOT EXISTS (SELECT cid FROM '.TS_TABLE_PREFIX.'categories AS categories WHERE categories.cid = albums.category)   ORDER BY albums.title;';
  $results = $db->query($query);
  if (count($results) > 0) {
    //echo '<ul>';
    foreach($results as $row) {
      echo '<option value="'.$row['aid'].'" '.($_GET['albumid'] == $row['aid'] ? 'SELECTED':'').'>'.$indent.' '.$row['title'].'</option>';
    }
    //echo '</ul>';    
  }
}
?>


?>