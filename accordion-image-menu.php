<?php
/*
Plugin Name: Accordion Image Menu
Plugin URI: http://web-argument.com/accordion-image-menu-plugin
Description: Versatile Accordion Image Menu. Allows to use your medium size attached images as links. You can use it for recent posts and for pages and categories combined.
Version: 1.0
Author: Alain Gonzalez
Author URI: http://web-argument.com/
*/

function a_m_image_url($the_parent){

$attachments = get_children( array(
				'post_parent' => $the_parent, 
				'post_type' => 'attachment', 
				'post_mime_type' => 'image', 
				'order' => 'DESC', 
				'numberposts' => 1) );
				
				if($attachments == true) :
					foreach($attachments as $id => $attachment) :
						$img = wp_get_attachment_image_src($id, 'medium');
					endforeach;		
				endif;
								
				return $img[0]; 

}


function a_image_m_items(){

$options = get_option('a_i_m');
	
/*****************************  Recent Posts *********************************/
		
	if ($options['a_m_type'] == rp){
	
			$i = 1;	
	
			$my_query =new WP_Query(array('category__in'=>$options['a_cat_p'],'showposts'=>$options['a_post_number']));
	
			while ($my_query->have_posts()) : $my_query->the_post(); 	
				
				$the_image = a_m_image_url(get_the_ID());
				$the_title = get_the_title();
				$the_link = get_permalink(get_the_ID());
				
				if (isset($the_image))	$item[$i] = array("img" => $the_image,"title" => $the_title,"link" => $the_link);
				 
				$i ++;			
	  
			endwhile; 
	
	
	} else if ($options['a_m_type'] == cp){	

					
/*****************************  Pages *********************************/
		if(isset($options['a_pag_or'])){
			foreach ($options['a_pag_or'] as $m_pages => $order){
			 
				 if (is_numeric($order) and ($order != 0)) {
					 
					$the_image = a_m_image_url($m_pages);
					$the_title = get_the_title($m_pages);
					$the_link = get_permalink($m_pages);	
					
					if (isset($the_image))	$item[$order] = array("img" => $the_image,"title" => $the_title,"link" => $the_link);			
				
				}
				
			}		
         }
/*****************************  Categories *********************************/
         if(isset($options['a_cat_or'])){
			foreach ($options['a_cat_or'] as $m_cat => $order){
			 
				 if (is_numeric($order) and ($order != 0)) {			 
					 
						$my_query =new WP_Query(array('cat'=>$m_cat));							
							
							$the_image = "";							
										
							while (   ($my_query->have_posts()) and ($the_image == "") ) : $my_query->the_post();
																		
								$the_image = a_m_image_url(get_the_ID());								
								foreach((get_the_category()) as $category) $the_title = $category->cat_name;
								$the_link = get_category_link($m_cat);	
								
								if (isset($the_image))	$item[$order] = array("img" => $the_image,"title" => $the_title,"link" => $the_link);					
					  
							endwhile;
				}		
			}
		}	

	}		
	
	global $a_m_image_items;	
	$a_m_image_items = count($item);
	
	return $item;

}



/*****************************************************************************
                     The shortcode
******************************************************************************/

function a_image_menu_func($atts) {

	$options = get_option('a_i_m');
	$effect = $options['a_effect'];
	if (!(isset($effect))) $effect = "Back";
	$m_width = $options['a_m_width'];
	if (!(isset($m_width))) $m_width = get_option('medium_size_w');
	$open_height = $options['a_open_height'];
	if (!(isset($open_height))) $open_height = get_option('medium_size_h');
	$closed_height= $options['a_closed_height'];
	if (!(isset($closed_height))) $closed_height = 100;
	$inc_title = $options['a_inc_title'];

	extract(shortcode_atts(array('post_id' => $post	), $atts));		
	
	$the_items = a_image_m_items();
	
	if(isset($the_items)) {
		ksort($the_items);
			
		//echo "<pre>".htmlspecialchars(  print_r($options,true)  )."</pre>";
		
		$image_menu = "<div id=\"imageMenu\">";
		$image_menu .= "<ul>";	
		
		foreach ($the_items as $the_item){
		  
		  $image_menu .= "<li>";
		  $image_menu .= "<a href=\"".$the_item['link']."\" title = \"".$the_item['title']."\" style=\"background-image:url(".$the_item['img'].")\" >";
			  
		  if (isset($inc_title)) $image_menu .= "<span class=\"vai_title_shadow\" >".$the_item['title']."</span><span class=\"vai_title\" >".$the_item['title']."</span>";
		  
		  $image_menu .= " &nbsp;</a></li>";
			
		}	
		$image_menu .= "</ul>";
		$image_menu .= "</div>";	
		$image_menu .= "<script type=\"text/javascript\">";
		
		$image_menu .= "jQuery(\"#imageMenu a\").hover(  function () {    jQuery(this).children().fadeOut();  },  function () {   jQuery(this).children().fadeIn();  });";	
		
		$image_menu .= "window.addEvent('domready', function(){";
		$image_menu .= "var myMenu = new ImageMenu($$('#imageMenu a'),{openHeight:".$open_height.", transition: Fx.Transitions.".$effect.".easeOut, duration:".$options['a_dur'].", onOpen:function(e,i){window.open(e);}});  });";
		$image_menu .= "</script>";	
		
		return $image_menu;
	}
}

add_shortcode('a_image_menu', 'a_image_menu_func');

add_action('admin_menu', 'a_img_menu_set');


/****************************************************************************************************
                                      Header                                                                                       
/****************************************************************************************************/


function a_image_menu_head() {	

	$options = get_option('a_i_m');
	
	$effect = $options['a_effect'];
	if (!(isset($effect))) $effect = "Back";
	$m_width = $options['a_m_width'];
	if (!(isset($m_width))) $m_width = get_option('medium_size_w');
	$open_height = $options['a_open_height'];
	if (!(isset($open_height))) $open_height = get_option('medium_size_h');
	$closed_height= $options['a_closed_height'];
	if (!(isset($closed_height))) $closed_height = 100;
	$m_height = count(a_image_m_items()) * ($closed_height+2);

    $a_image_menu_header =  "\n<!-- Accordion Image Menu -->\n";		
    $a_image_menu_header .= "<script type=\"text/javascript\" src=\"".get_bloginfo('url')."/wp-content/plugins/accordion-image-menu/js/mootools.js\"></script>\n";
	$a_image_menu_header .= "<script type=\"text/javascript\" src=\"".get_bloginfo('url')."/wp-content/plugins/accordion-image-menu/js/imageMenuVert.js\"></script>\n";			
	$a_image_menu_header .= "\t<link href=\"".get_bloginfo('url')."/wp-content/plugins/accordion-image-menu/css/vimageMenu.css\" rel=\"stylesheet\" type=\"text/css\" />\n";	
	
	$a_image_menu_header .= "<style type=\"text/css\">\n";
	$a_image_menu_header .= "#imageMenu ul {\n";
	$a_image_menu_header .= "height: ".$m_height."px;\n";
	$a_image_menu_header .= "width: ".$m_width."px;\n";
	$a_image_menu_header .= "}\n";
	$a_image_menu_header .= "#imageMenu ul li a {\n";
	$a_image_menu_header .= "height: ".$closed_height."px;\n";
	$a_image_menu_header .= "width: ".$m_width."px;\n";
	$a_image_menu_header .= "}\n";
	$a_image_menu_header .= "#imageMenu {\n";
	$a_image_menu_header .= "height: ".$m_height."px;\n";
	$a_image_menu_header .= "width: ".$m_width."px;\n";
	$a_image_menu_header .= "}\n";
	$a_image_menu_header .= "</style>\n";
            
print($a_image_menu_header);
}

add_action('wp_head', 'a_image_menu_head');
wp_enqueue_script('jquery'); 


/*****************************************************************************
                     The widget
******************************************************************************/

function a_image_menu_w_register() {

	function a_image_menu_w_op() {
	
	?>
	<p>	You can edit the Menu Image Widget under Settings/ Accordion Image Menu </p>
	<?php
	
	}
	
	function a_image_menu_widget() {
	
		echo $before_widget; 
		echo do_shortcode('[a_image_menu]');
		echo $after_widget; 
		
	}
	register_sidebar_widget('Accordion Image Menu', 'a_image_menu_widget');
	register_widget_control('Accordion Image Menu', 'a_image_menu_w_op','250','300');	  
	register_sidebar_widget('Accordion Image Menu','a_image_menu_widget');

}
add_action('init', 'a_image_menu_w_register');

/********************************************************************************
                                       Settings                                                                                        
/********************************************************************************/

function a_img_menu_set() {
    add_options_page('Accordion Image Menu', 'Accordion Image Menu', 10, 'accordion-image-menu', 'a_image_menu_page');	 
}

function a_image_menu_page() {

$categories = get_categories();
$trans_type = array("Back","Bounce","Cubic","Elastic","Expo", "Linear","Pow","Quad","Quart","Quint","Sine");

$options = get_option('a_i_m');

	if(isset($_POST['Submit'])){
	
	//echo "<pre>".htmlspecialchars(  print_r($_POST,true)  )."</pre>";
	
		$newoptions['a_post_number'] = $_POST['post_number'];
		$newoptions['a_cat_p'] = $_POST['cat_p'];
		$newoptions['a_m_type'] = $_POST['m_type'];
		$newoptions['a_cat_or'] = $_POST['cat_or'];
		$newoptions['a_pag_or'] = $_POST['pag_or'];
		$newoptions['a_m_width'] = $_POST['m_width'];
		$newoptions['a_open_height'] = $_POST['open_height'];
		$newoptions['a_closed_height'] = $_POST['closed_height'];
		$newoptions['a_inc_title'] = $_POST['inc_title'];
		$newoptions['a_effect'] = $_POST['effect'];
		$newoptions['a_dur'] = $_POST['dur'];

		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('a_i_m', $options);			
		}		
        
?>
<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
         
<?php  }  

		$post_number = $options['a_post_number'];
		$cat_p = $options['a_cat_p'];
		$m_type = $options['a_m_type'];
		$cat_or = $options['a_cat_or'];
		$pag_or = $options['a_pag_or'];
		$m_width = $options['a_m_width'];
		$open_height = $options['a_open_height'];
		$closed_height= $options['a_closed_height'];
		$inc_title = $options['a_inc_title'];
		$effect = $options['a_effect'];
		$dur= $options['a_dur'];
		
?>	 	         

<script type="text/javascript">

 jQuery(document).ready(function(){
	 if (jQuery("input[name=m_type]:checked").val() == 'cp')
		jQuery("#a_menu_type_rp").hide();
	 else  if (jQuery("input[name=m_type]:checked").val() == 'rp')   
		jQuery("#a_menu_type_cp").hide();
	 else	{
	    jQuery("#a_menu_type_cp").hide();
		jQuery("#a_menu_type_rp").hide();
	}		
	 
	 jQuery("input[name=m_type]").click(function(){ 
		if (jQuery("input[name=m_type]:checked").val() == 'cp'){
			jQuery("#a_menu_type_rp").slideUp();
			jQuery("#a_menu_type_cp").slideDown();
		}else if (jQuery("input[name=m_type]:checked").val() == 'rp'){
			jQuery("#a_menu_type_cp").slideUp();
			jQuery("#a_menu_type_rp").slideDown();
		}  
	});

 });
</script>


<div class="wrap">   

<form method="post" name="options" target="_self">

<h2>Accordion Image Menu Setting</h2>

<h3>Use the Menu for:</h3>

<p><input name="m_type" type="radio" value="rp" <?php if($m_type=="rp") echo'checked' ?>/> <b>Recent Posts</b></p>

    <div id="a_menu_type_rp">
    
            <table width="100%" cellpadding="10" class="form-table">
            <tr>
            <td width="200" align="right"><input name="post_number" type="text" size="1" value="<?php if ($post_number=="") echo '5'; else echo $post_number ?>"/></td>
            <td align="left" scope="row">Number of Posts</td>
            </tr>
                       
            <tr>
            <td width="200" align="right"></td>
            <td align="left" scope="row"><b>On the categories</b></td>
            </tr>
            <?php               
              foreach ($categories as $cat) { ?>
              <tr valign="top">
                <td width="200" align="right"><input name="cat_p[<?php echo $cat->cat_ID ?>]" type="checkbox" value="<?php echo $cat->cat_ID ?>"
                <?php 
				if (isset($cat_p))	if (in_array($cat->cat_ID, $cat_p)) echo "checked"?>                                       
                /> 
                <td align="left" scope="row"><?php echo $cat->cat_name ?></td>
              </tr>
              <?php }  ?>
            
            </table>
    
    </div>

<p><input name="m_type" type="radio" value="cp" <?php if($m_type=="cp") echo'checked' ?>/> <b>Categories and Pages</b></p>

    <div id="a_menu_type_cp">
    <p>To select a Category or a Page just fill out the "Order" field in front of the item (The Order is the vertical item position on the menu, if you use "0" or leave it "empty" the item will not be included)</p>
            <table width="100%" cellpadding="5" class="form-table">
              
              <tr valign="top">
                <td width="200" align="right"><b>Order</b></td>
                <td align="left" scope="row"><b>Categories</b></td>
              </tr>
            <?php    
             foreach ($categories as $cat) { ?>
              
                <td width="200" align="right">
                <input name="cat_or[<?php echo $cat->cat_ID ?>]" type="text" id="cat_or<?php echo $cat->cat_ID ?>" size="1" value="<?php echo $cat_or[$cat->cat_ID] ?>"/></td>
                <td align="left" scope="row"><?php echo $cat->cat_name ?></td>
              </tr>
              <?php }  
              $pages = get_pages(); 
              if (count($pages)!=0){			  
			  ?>
              <tr valign="top">
                <td width="200" align="right"></td>
                <td align="left" scope="row"><b>Pages</b></td>
              </tr>
            
             <?php 
              
              foreach ($pages as $pag) { ?>
              <tr valign="top">
                <td width="200" align="right"><input name="pag_or[<?php echo $pag->ID ?>]" type="text" id="pag_or<?php echo $pag->ID ?>" size="1" value="<?php echo $pag_or[$pag->ID] ?>"/></td>
                <td align="left" scope="row"><?php echo $pag->post_title ?></td>
              </tr>
            
              <?php }  ?>
            </select>
             <?php }  ?>
            </table>
            
    </div>


<hr />
<h3>Select the Image Menu Dimensions</h3>
<p>The menu use the Medium Size Images</p>
<table width="100%" cellpadding="10" class="form-table">

  <tr valign="top">
  	<td width="200" align="right">
  	  <input name="m_width" id="m_width" value="<?php if ($m_width != "") echo $m_width; else echo get_option('medium_size_w') ?>" size="2"/> 
  	  px
  	</td>
  	<td align="left" scope="row"><b>Width</b> of the Menu (medium size images width by default)</td>
  </tr>
 <tr valign="top">
  	<td width="200" align="right">
  	  <input name="open_height" id="open_height" value="<?php if ($open_height != "") echo $open_height; else echo 250 ?>" size="2"/> 
  	  px
  	</td>
  	<td align="left" scope="row"><b>Lines Height</b> when the menu is open (Is the height of the images when the mouse is over them)</td>
  </tr>  
  <tr valign="top">
  	<td width="200" align="right">
  	  <input name="closed_height" id="closed_height" value="<?php if ($closed_height != "") echo $closed_height; else echo "100" ?>" size="2"/>
px </td>
  	<td align="left" scope="row"><b>Lines Height</b> when the menu is closed (Is the height of the images when the menu is not activated)</td>
  </tr>  
</table>

<hr />
<h3> Menu Behaviour </h3>
<table width="100%" cellpadding="10" class="form-table">  
  <tr valign="top">
  	<td width="200" align="right">
  	  <input name="inc_title" type="checkbox" value="it" <?php if ($inc_title == "it") echo "checked=\"checked\"" ?>/>
  	</td>
  	<td align="left" scope="row">Include the Title</td>
  </tr>  
  <tr valign="top">
  	<td width="200" align="right">
  	  <select name="effect">
      <?php 
	  foreach($trans_type as $type_value){ ?>
	  <option value="<?php echo $type_value ?>" <?php if ($type_value == $effect) echo "selected" ?> ><?php echo $type_value ?></option>
	  <?php }?>
  	  </select>
  	</td>
  	<td align="left" scope="row">Transition Effect</td>
  <tr valign="top">
  	<td width="200" align="right">
  	  <input name="dur" value="<?php if ($dur != "") echo $dur; else echo "500" ?>" size="3"/>
  	</td>
  	<td align="left" scope="row">Duration (milliseconds)</td>
  </tr>   
</table>

<hr />
<h3>Use</h3>
 <p>You can use the Accordion Image Menu everywhere.</p>
<table width="100%" cellpadding="10" class="form-table">
   
  <tr valign="top">
    <td width="98" align="right">&nbsp;</td>
    <td width="1182" align="left" scope="row">On your Sidebar: <strong>As a Widget</strong></td>
  </tr>
  <tr valign="top">
    <td width="98" align="right">&nbsp;</td>
    <td align="left" scope="row">On the content using: <strong>[a_image_menu]</strong></td>
  </tr>
  <tr valign="top">
    <td width="98" align="right">&nbsp;</td>
    <td align="left" scope="row">On your theme php files using: <strong>echo do_shortcode('[a_image_menu]');</strong></td>
  </tr>
</table>

<hr />
<h3>Feedback</h3>
<p>If you find this plugin useful or have a suggestion please visit the <a href="http://web-argument.com/accordion-image-menu-plugin">plugin page</a>. All comments are welcome :)</p>

<p class="submit">
<input type="submit" name="Submit" value="Update" />
</p>

</form>
</div>

<?php } ?>