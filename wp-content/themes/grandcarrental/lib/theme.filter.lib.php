<?php
//Add more CURL timeout if purchase code is not registered
$is_verified_envato_purchase_code = grandcarrental_is_registered();

//Check if registered purchase code valid
if(empty($is_verified_envato_purchase_code)) {
  add_filter('http_request_args', 'grandcarrental_http_request_args', 100, 1);
  function grandcarrental_http_request_args($r) 
  {
    $r['timeout'] = 30;
    return $r;
  }
   
  add_action('http_api_curl', 'grandcarrental_http_api_curl', 100, 1);
  function grandcarrental_http_api_curl($handle) 
  {
    curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 30 );
    curl_setopt( $handle, CURLOPT_TIMEOUT, 30 );
  }
}

if(GRANDCARRENTAL_THEMEDEMO) {
  add_action( 'wp_enqueue_scripts', 'grandcarrental_juice_cleanse', 200 );
  function grandcarrental_juice_cleanse() {
  
    wp_dequeue_style('wp-block-library');
  
    // This also removes some inline CSS variables for colors since 5.9 - global-styles-inline-css
    wp_dequeue_style('global-styles');
  
    // WooCommerce - you can remove the following if you don't use Woocommerce
    wp_dequeue_style('wc-block-style');
    wp_dequeue_style('wc-blocks-vendors-style');
    wp_dequeue_style('wc-blocks-style'); 
  }
}

//Remove one click demo import plugin from admin menus
function grandcarrental_plugin_page_setup( $default_settings ) {
	$default_settings['parent_slug'] = 'themes.php';
	$default_settings['page_title']  = esc_html__( 'Demo Import' , 'grandcarrental' );
	$default_settings['menu_title']  = esc_html__( 'Import Demo Content' , 'grandcarrental' );
	$default_settings['capability']  = 'import';
	$default_settings['menu_slug']   = 'tg-one-click-demo-import';

	return $default_settings;
}
add_filter( 'pt-ocdi/plugin_page_setup', 'grandcarrental_plugin_page_setup' );

function grandcarrental_menu_page_removing() {
    remove_submenu_page( 'themes.php', 'tg-one-click-demo-import' );
}
add_action( 'admin_menu', 'grandcarrental_menu_page_removing', 99 );
	
$is_verified_envato_purchase_code = false;

//Get verified purchase code data
$is_verified_envato_purchase_code = grandcarrental_is_registered();

//if verified envato purchase code
if($is_verified_envato_purchase_code)
{
	function grandcarrental_import_files() {
	  return array(
	    array(
	      'import_file_name'             => 'Classic',
	      'import_file_url'            => 'https://assets.themegoods.com/demo/grandcarrental/importer/demo.xml',
	      'import_widget_file_url'     => 'https://assets.themegoods.com/demo/grandcarrental/importer/demo.wie',
	      'import_customizer_file_url' => 'https://assets.themegoods.com/demo/grandcarrental/importer/demo.dat',
	      'preview_url'                  => 'https://grandcarrentalv1.themegoods.com',
	    ),
	  );
	}
	add_filter( 'pt-ocdi/import_files', 'grandcarrental_import_files' );
	
	function grandcarrental_confirmation_dialog_options ( $options ) {
		return array_merge( $options, array(
			'width'       => 300,
			'dialogClass' => 'wp-dialog',
			'resizable'   => false,
			'height'      => 'auto',
			'modal'       => true,
		) );
	}
	add_filter( 'pt-ocdi/confirmation_dialog_options', 'grandcarrental_confirmation_dialog_options', 10, 1 );
	
	function grandcarrental_before_widgets_import( $selected_import ) {
		//Add demo custom sidebar
		if ( function_exists('register_sidebar') )
		{
		    register_sidebar(array('id' => 'faq-sidebar', 'name' => 'FAQ Sidebar'));
		}
	}
	add_action( 'pt-ocdi/before_widgets_import', 'grandcarrental_before_widgets_import' );
	
	function grandcarrental_after_import( $selected_import ) {
		switch($selected_import['import_file_name'])
		{
			default:
			case 'Classic':
				// Assign menus to their locations.
				$main_menu = get_term_by( 'name', 'Main Menu', 'nav_menu' );
				$footer_menu = get_term_by( 'name', 'Footer Menu', 'nav_menu' );
			
				/*set_theme_mod( 'nav_menu_locations', array(
						'primary-menu' => $main_menu->term_id,
						'side-menu' => $main_menu->term_id,
						'footer-menu' => $footer_menu->term_id,
					)
				);*/
				
			break;
		}
		
		//Import Revolution Slider if activate
		if(class_exists('RevSlider'))
		{
			$slider_array = array();
			
			switch($selected_import['import_file_name'])
	    	{
		    	case 'Classic':
		    	default:
		    		$slider_array = array(
		    			get_template_directory() ."/cache/demos/xml/demo1/home-4-slider.zip",
		    		);
		    	break;
	    	}
	    	
	    	if(!empty($slider_array))
	    	{
		    	require_once ABSPATH . 'wp-admin/includes/file.php';
				  $obj_revslider = new RevSlider();
				
				  foreach($slider_array as $revslider_filepath)
				  {
					  $obj_revslider->importSliderFromPost(true,true,$revslider_filepath);
				  }
			}
		}
		
		// Assign front page
		switch($selected_import['import_file_name'])
		{
			default:
			case 'Classic':
				$front_page_id = get_page_by_title( 'Home 1' );
			break;
		}
		
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_page_id->ID );
    
    // Assign Woocommerce related page
    $shop_page_id = get_page_by_title( 'Shop' );
    update_option( 'woocommerce_shop_page_id', $shop_page_id->ID );
    
    $cart_page_id = get_page_by_title( 'Cart' );
    update_option( 'woocommerce_cart_page_id', $cart_page_id->ID );
    
    $checkout_page_id = get_page_by_title( 'Checkout' );
    update_option( 'woocommerce_checkout_page_id', $checkout_page_id->ID );
    
    $myaccount_page_id = get_page_by_title( 'My account' );
    update_option( 'woocommerce_myaccount_page_id', $myaccount_page_id->ID );
    
    // 'Hello World!' post
    wp_delete_post( 3, true );
    
    // 'Sample page' page
    wp_delete_post( 5, true );
      
    //Set permalink
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
    
    //Set the option
    update_option( "rewrite_rules", FALSE ); 
    
    //Flush the rules and tell it to write htaccess
    $wp_rewrite->flush_rules( true );
    
    //Update custom field URLs
    grandcarrental_update_urls(array('custom'), $selected_import['preview_url'], home_url());
	}
	add_action( 'pt-ocdi/after_import', 'grandcarrental_after_import' );
	add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );
}
	
// Disable Gutenberg
add_filter( 'gutenberg_can_edit_post_type', '__return_false' );
add_filter( 'use_block_editor_for_post_type', '__return_false' );
// Disable "Try Gutenberg" panel
remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );

function grandcarrental_tag_cloud_filter($args = array()) {
   $args['smallest'] = 13;
   $args['largest'] = 13;
   $args['unit'] = 'px';
   return $args;
}

add_filter('widget_tag_cloud_args', 'grandcarrental_tag_cloud_filter', 90);

//Control post excerpt length
function grandcarrental_custom_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'grandcarrental_custom_excerpt_length', 200 );

// remove version query string from scripts and stylesheets
function grandcarrental_remove_script_styles_version( $src ){
    return remove_query_arg( 'ver', $src );
}
add_filter( 'script_loader_src', 'grandcarrental_remove_script_styles_version' );
add_filter( 'style_loader_src', 'grandcarrental_remove_script_styles_version' );


function grandcarrental_theme_queue_js(){
  if (!is_admin()){
    if (!is_page() AND is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
      wp_enqueue_script( 'comment-reply' );
    }
  }
}
add_action('get_header', 'grandcarrental_theme_queue_js');


function grandcarrental_add_meta_tags() {
    $post = grandcarrental_get_wp_post();
    
    echo '<meta charset="'.get_bloginfo( 'charset' ).'" />';
    
    //Check if responsive layout is enabled
    $tg_mobile_responsive = kirki_get_option('tg_mobile_responsive');
	
	if(!empty($tg_mobile_responsive))
	{
		echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />';
	}
	
	//meta for phone number link on mobile
	echo '<meta name="format-detection" content="telephone=no">';
    
    //check if single post then add meta description and keywords
    if (is_single()) 
    {
        //Prepare data for Facebook opengraph sharing
        if(has_post_thumbnail(get_the_ID(), 'grandcarrental-blog'))
		{
		    $image_id = get_post_thumbnail_id(get_the_ID());
		    $fb_thumb = wp_get_attachment_image_src($image_id, 'grandcarrental-blog', true);
		}
	
		if(isset($fb_thumb[0]) && !empty($fb_thumb[0]))
		{
			$post_content = get_post_field('post_excerpt', $post->ID);
			
			echo '<meta property="og:type" content="article" />';
			echo '<meta property="og:image" content="'.esc_url($fb_thumb[0]).'"/>';
			echo '<meta property="og:title" content="'.esc_attr(get_the_title()).'"/>';
			echo '<meta property="og:url" content="'.esc_url(get_permalink($post->ID)).'"/>';
			echo '<meta property="og:description" content="'.esc_attr(strip_tags($post_content)).'"/>';
		}
    }
}
add_action( 'wp_head', 'grandcarrental_add_meta_tags' , 2 );

function grandcarrental_body_class_names($classes) {

	if(is_page())
	{
		$tg_post = grandcarrental_get_wp_post();
		$ppb_enable = get_post_meta($tg_post->ID, 'ppb_enable', true);
		if(!empty($ppb_enable))
		{
			$classes[] = 'ppb_enable';
		}
	}
	
	//Check if boxed layout is enable
	$tg_boxed = kirki_get_option('tg_boxed');
	if((GRANDCARRENTAL_THEMEDEMO && isset($_GET['boxed']) && !empty($_GET['boxed'])) OR !empty($tg_boxed))
	{
		$classes[] = esc_attr('tg_boxed');
	}

	return $classes;
}

//Now add test class to the filter
add_filter('body_class','grandcarrental_body_class_names');

add_filter('redirect_canonical','custom_disable_redirect_canonical');
function custom_disable_redirect_canonical($redirect_url) {if (is_paged() && is_singular()) $redirect_url = false; return $redirect_url; }

add_action( 'admin_enqueue_scripts', 'grandcarrental_admin_pointers_header' );

function grandcarrental_admin_pointers_header() {
   if ( grandcarrental_admin_pointers_check() ) {
      add_action( 'admin_print_footer_scripts', 'grandcarrental_admin_pointers_footer' );

      wp_enqueue_script( 'wp-pointer' );
      wp_enqueue_style( 'wp-pointer' );
   }
}

function grandcarrental_admin_pointers_check() {
   $admin_pointers = grandcarrental_admin_pointers();
   foreach ( $admin_pointers as $pointer => $array ) {
      if ( $array['active'] )
         return true;
   }
}

function grandcarrental_admin_pointers_footer() {
   $admin_pointers = grandcarrental_admin_pointers();
   ?>
<script type="text/javascript">
/* <![CDATA[ */
( function($) {
   <?php
   foreach ( $admin_pointers as $pointer => $array ) {
      if ( $array['active'] ) {
         ?>
         $( '<?php echo esc_js($array['anchor_id']); ?>' ).pointer( {
            content: '<?php echo $array['content']; ?>',
            position: {
            edge: '<?php echo esc_js($array['edge']); ?>',
            align: '<?php echo esc_js($array['align']); ?>'
         },
            close: function() {
               $.post( ajaxurl, {
                  pointer: '<?php echo esc_js($pointer); ?>',
                  action: 'dismiss-wp-pointer'
               } );
            }
         } ).pointer( 'open' );
         <?php
      }
   }
   ?>
} )(jQuery);
/* ]]> */
</script>
   <?php
}

function grandcarrental_admin_pointers() {
   $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
   $prefix = 'grandcarrental_admin_pointers';

   //Page help pointers
   $content_builder_content = '<h3>Content Builder</h3>';
   $content_builder_content .= '<p>Basically you can use WordPress visual editor to create page content but theme also has another way to create page content. By using Content Builder, you would be ale to drag&drop each content block without coding knowledge. Click here to enable Content Builder.</p>';
   
   $page_options_content = '<h3>Page Options</h3>';
   $page_options_content .= '<p>You can customise various options for this page including menu styling, page templates etc.</p>';
   
   $page_featured_image_content = '<h3>Page Featured Image</h3>';
   $page_featured_image_content .= '<p>Upload or select featured image for this page to displays it as background header.</p>';
   
   //Post help pointers
   $post_options_content = '<h3>Post Options</h3>';
   $post_options_content .= '<p>You can customise various options for this post including its layout and featured content type.</p>';
   
   $post_featured_image_content = '<h3>Post Featured Image (*Required)</h3>';
   $post_featured_image_content .= '<p>Upload or select featured image for this post to displays it as post image on blog, archive, category, tag and search pages.</p>';
   
   //Gallery help pointers
   $gallery_images_content = '<h3>Gallery Images</h3>';
   $gallery_images_content .= '<p>Upload or select for this gallery. You can select multiple images to upload using SHIFT or CTRL keys.</p>';
   
   $gallery_options_content = '<h3>Gallery Options</h3>';
   $gallery_options_content .= '<p>You can customise various options for this gallery including gallery template, password and gallery images file.</p>';
   
   $gallery_featured_image_content = '<h3>Gallery Featured Image (*Required)</h3>';
   $gallery_featured_image_content .= '<p>Upload or select featured image for this gallery to displays it as gallery image on gallery archive pages. If featured image is not selected, this gallery will not display on gallery archive page.</p>';
   
   //car help pointers
   $car_options_content = '<h3>Car Options</h3>';
   $car_options_content .= '<p>You can customise various options for this car including price, booking form and other informations about this car.</p>';
   
   $car_tags_content = '<h3>Car Tags</h3>';
   $car_tags_content .= '<p>You can assign tags for each cars and car tags will be used to get similar cars on single car page.</p>';
   
   $car_brands_content = '<h3>Car Brands</h3>';
   $car_brands_content .= '<p>You can assign brands for each cars for example BMW, Audi</p>';
   
   $car_types_content = '<h3>Car Types</h3>';
   $car_types_content .= '<p>You can assign types for each cars for example Sedan, SUV</p>';
   
   $car_featured_image_content = '<h3>Car Featured Image (*Required)</h3>';
   $car_featured_image_content .= '<p>Upload or select featured image for this car to displays it as featured image on car archive pages.</p>';
   
   //Testimonials help pointers
   $testimonials_options_content = '<h3>Testimonials Options</h3>';
   $testimonials_options_content .= '<p>You can customise various options for this testimonial including customer name, position, company etc.</p>';
   
   $testimonials_featured_image_content = '<h3>Testimonials Featured Image</h3>';
   $testimonials_featured_image_content .= '<p>Upload or select featured image for this testimonial to displays it as customer photo.</p>';
   
   //Team Member help pointers
   $team_options_content = '<h3>Team Member Options</h3>';
   $team_options_content .= '<p>You can customise various options for this team member including position and social profiles URL.</p>';
   
   $team_featured_image_content = '<h3>Team Member Featured Image</h3>';
   $team_featured_image_content .= '<p>Upload or select featured image for this team member to displays it as team member photo.</p>';

   $tg_pointer_arr = array(
   
   	  //Page help pointers
      $prefix . '_content_builder' => array(
         'content' => $content_builder_content,
         'anchor_id' => '#enable_builder',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_content_builder', $dismissed ) )
      ),
      
      $prefix . '_page_options' => array(
         'content' => $page_options_content,
         'anchor_id' => 'body.post-type-page #page_option_page_menu_transparent',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_page_options', $dismissed ) )
      ),
      
      $prefix . '_page_featured_image' => array(
         'content' => $page_featured_image_content,
         'anchor_id' => 'body.post-type-page #set-post-thumbnail',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_page_featured_image', $dismissed ) )
      ),
      
      //Post help pointers
      $prefix . '_post_options' => array(
         'content' => $post_options_content,
         'anchor_id' => 'body.post-type-post #post_option_post_layout',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_post_options', $dismissed ) )
      ),
      
      $prefix . '_post_featured_image' => array(
         'content' => $post_featured_image_content,
         'anchor_id' => 'body.post-type-post #set-post-thumbnail',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_post_featured_image', $dismissed ) )
      ),
      
      //Gallery help pointers
      $prefix . '_gallery_images' => array(
         'content' => $gallery_images_content,
         'anchor_id' => 'body.post-type-galleries #wpsimplegallery_container',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_gallery_images', $dismissed ) )
      ),
      
      //car help pointers
      $prefix . '_car_options' => array(
         'content' => $car_options_content,
         'anchor_id' => 'body.post-type-car #metabox #post_option_car_passengers',
         'edge' => 'bottom',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_car_options', $dismissed ) )
      ),
      
      $prefix . '_car_tags' => array(
         'content' => $car_tags_content,
         'anchor_id' => 'body.post-type-car #tagsdiv-cartag',
         'edge' => 'right',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_car_tags', $dismissed ) )
      ),
      
      $prefix . '_car_brands' => array(
         'content' => $car_brands_content,
         'anchor_id' => 'body.post-type-car #tagsdiv-carbrand',
         'edge' => 'right',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_car_brands', $dismissed ) )
      ),
      
      $prefix . '_car_types' => array(
         'content' => $car_types_content,
         'anchor_id' => 'body.post-type-car #tagsdiv-cartype',
         'edge' => 'right',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_car_types', $dismissed ) )
      ),
      
      $prefix . '_car_featured_image' => array(
         'content' => $car_featured_image_content,
         'anchor_id' => 'body.post-type-car #set-post-thumbnail',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_car_featured_image', $dismissed ) )
      ),
      
      //Testimonials help pointers
      $prefix . '_testimonials_options' => array(
         'content' => $testimonials_options_content,
         'anchor_id' => 'body.post-type-testimonials #metabox #post_option_testimonial_name',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_testimonials_options', $dismissed ) )
      ),
      
      $prefix . '_testimonials_featured_image' => array(
         'content' => $testimonials_featured_image_content,
         'anchor_id' => 'body.post-type-testimonials #set-post-thumbnail',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_testimonials_featured_image', $dismissed ) )
      ),
      
      //Team Member help pointers
      $prefix . '_team_options' => array(
         'content' => $team_options_content,
         'anchor_id' => 'body.post-type-team #metabox #post_option_team_position',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_team_options', $dismissed ) )
      ),
      
      $prefix . '_team_featured_image' => array(
         'content' => $team_featured_image_content,
         'anchor_id' => 'body.post-type-team #set-post-thumbnail',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . '_team_featured_image', $dismissed ) )
      ),
   );

   return $tg_pointer_arr;
}

add_action( 'enqueue_block_editor_assets', 'grandcarrental_custom_link_injection_to_gutenberg_toolbar' );
 
function grandcarrental_custom_link_injection_to_gutenberg_toolbar(){
   global $post_type, $post;

   if ( is_object( $post )  && ($post_type == 'page' OR $post_type == 'portfolios')) {
      wp_enqueue_script( 'grandcarrental-custom-link-in-toolbar', get_template_directory_uri() . '/functions/gutenberg/custom-link-in-toolbar.js', array(), '', true );   
   }
   
}
?>