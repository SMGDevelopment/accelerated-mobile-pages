<?php
function amp_archive_title(){
	global $redux_builder_amp;
	if( is_author() ){
		$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
		if( true == ampforwp_gravatar_checker($curauth->user_email) ){
			$curauth_url = get_avatar_url( $curauth->user_email, array('size'=>180) );
			if($curauth_url){ ?>
				<div class="amp-wp-content author-img">
					<amp-img src="<?php echo esc_url($curauth_url); ?>" width="90" height="90" layout="responsive"></amp-img>
				</div>
			<?php }
	}
}
	if ( is_archive() ) {
		$description = $sanitizer = $arch_desc = '';
	    the_archive_title( '<h3 class="amp-archive-title">', '</h3>' );
	    $description 	= get_the_archive_description();
		$sanitizer = new AMPFORWP_Content( $description, array(), 
			apply_filters( 'ampforwp_content_sanitizers',
				array( 
					'AMP_Style_Sanitizer' 		=> array(),
					'AMP_Blacklist_Sanitizer' 	=> array(),
					'AMP_Img_Sanitizer' 		=> array(),
					'AMP_Video_Sanitizer' 		=> array(),
					'AMP_Audio_Sanitizer' 		=> array(),
					'AMP_Iframe_Sanitizer' 		=> array(
						'add_placeholder' 		=> true,
					)
				) ) );
		$arch_desc 		= $sanitizer->get_amp_content();
			if( $arch_desc ) {  
				if ( get_query_var( 'paged' ) ) {
		        $paged = get_query_var('paged');
		    } elseif ( get_query_var( 'page' ) ) {
		        $paged = get_query_var('page');
		    } else {
		        $paged = 1;
		    }
				if($paged <= '1') {?>
					<div class="amp-archive-desc">
						<?php echo $arch_desc ; ?>
				    </div> <?php
				}
			}
	}
	if( is_category() && 1 == $redux_builder_amp['ampforwp-sub-categories-support'] ){
		$parent_cat_id 	= '';
	    $cat_childs		= array();
	    $parent_cat_id 	= get_queried_object_id();
	 	$cat_childs 	= get_terms( array(
	  						'taxonomy' => get_queried_object()->taxonomy,
	  						'parent'   => $parent_cat_id )
						);
		if( !empty( $cat_childs ) ){
			echo "<div class='amp-sub-archives'><ul>";
			foreach ($cat_childs as $cat_child ) {
				 echo '<li><a href="' . get_term_link( $cat_child ) . '">' . $cat_child->name . '</a></li>'; 
			}
			echo "</ul></div>";
		}
	}
	if(is_search()){
		$label = 'You searched for:';
		if(function_exists('ampforwp_translation')){
			$label = ampforwp_translation( $redux_builder_amp['amp-translator-search-text'], 'You searched for:');
		}
		echo '<h3 class="amp-loop-label">'.$label . '  ' . get_search_query().'</h3>';
	}
}

$amp_q = '';
$count = 1;
function call_loops_standard($data=array()){
	global $amp_q;
	$post_type = get_post_type();
	if (get_query_var( 'paged' ) ) {
	    $paged = get_query_var('paged');
	} elseif ( get_query_var( 'page' ) ) {
	    $paged = get_query_var('page');
	} else {
	    $paged = 1;
	}
	
	$qobj_taxonomy = $qobj_term_id = "";

	if ( is_archive() ) {
		$exclude_ids = get_option('ampforwp_exclude_post');
		$qobj = get_queried_object();
		if ( $qobj  ){
			$qobj_taxonomy 	= $qobj->taxonomy;
			$qobj_term_id 	= $qobj->term_id;
		}
		$args =  array(
			'post_type'           => $post_type,
			'orderby'             => 'date',
			'ignore_sticky_posts' => 1,
			'tax_query' => array(
				array(
					'taxonomy' 	=> $qobj_taxonomy,
					'field' 	=> 'id',
					'terms' 	=> $qobj_term_id,
					//    using a slug is also possible
					//    'field' => 'slug', 
					//    'terms' => $qobj->name
		        )
		    ),
			'paged'         => esc_attr($paged),
			'post__not_in' 	=> $exclude_ids,
			'has_password' 	=> false ,
			'post_status'	=> 'publish'
		  );
	}
	if ( is_home() ) {
		$exclude_ids = get_option('ampforwp_exclude_post');

		$args = array(
			'post_type'           => 'post',
			'orderby'             => 'date',
			'paged'               => esc_attr($paged),
			'post__not_in' 		  => $exclude_ids,
			'has_password'		  => false ,
			'post_status'		  => 'publish'
		);
	}

	if ( is_search() ) {
		$exclude_ids = get_option('ampforwp_exclude_post');
		$args = array(
			's' 				  => get_search_query() ,
			'ignore_sticky_posts' => 1,
			'paged'               => esc_attr($paged),
			'post__not_in' 		  => $exclude_ids,
			'has_password' 		  => false ,
			'post_status'		  => 'publish'
		);
	}
	if(is_author()){
		$exclude_ids = get_option('ampforwp_exclude_post');
		$author = get_user_by( 'slug', get_query_var( 'author_name' ) );
		$args =  array(
			'author'        	  =>  $author->ID,
			'post_type'           => 'post',
			'orderby'             => 'date',
			'ignore_sticky_posts' => 1,
			'paged'               => esc_attr($paged),
			'post__not_in' 		  => $exclude_ids,
			'has_password' 		  => false ,
			'post_status'		  => 'publish'
		  );
	}
	if( is_single() ) {
		global $post;
		$exclude_ids = get_option('ampforwp_exclude_post');
		$exclude_ids[] = $post->ID;
		$args =  array(
			'post_type'           => 'post',
			'orderby'             => 'date',
			'ignore_sticky_posts' => 1,
			'paged'               => esc_attr($paged),
			'post__not_in' 		  => $exclude_ids,
			'has_password' 		  => false ,
			'post_status'		  => 'publish'
		  );
	}
	if( isset( $data['post_to_show'] ) && $data['post_to_show']>0 ){
		$args['posts_per_page'] = $data['post_to_show'];
	}
	if( isset( $data['offset'] ) && $data['offset']>0 ){
		$args['offset'] = $data['offset'];
	}
	if( isset( $data['posts_per_page'] ) && $data['posts_per_page']>0 ){
		$args['posts_per_page'] = $data['posts_per_page'];
	}
	
	$filtered_args = apply_filters('ampforwp_query_args', $args);
	$amp_q = new WP_Query( $filtered_args );

	// If Relevanssi is available and this is a search, pass the query to Relevanssi
	// for improved search results. 2018-07-03 Mikko Saari (mikko@mikkosaari.fi)
	if ( is_search() && function_exists( 'relevanssi_do_query' ) ) {
		relevanssi_do_query( $amp_q );
	}
}
//call_loops_standered();
/****
 * AMP Loop Functions
 */
//add_action("init", 'call_loops_standered');
	
function amp_loop($selection,$data=array()){
	global $amp_q;
	if(empty($amp_q) || is_null($amp_q)){
		call_loops_standard($data);
        echo "<div class='loop-wrapper'>";
	}
	if ( !isset($ampLoopData['no_data']) ) :
		switch($selection){
			case 'start':
				return amp_start_loop();
			break;	
			case 'end':
				return amp_end_loop();
			break;		
		}
	else : // If no posts exist.
		 return false;
	endif; // End loop.
}

function amp_start_loop(){
	global $amp_q, $count;
	$post_status = $amp_q->have_posts();
	$amp_q->the_post();
	do_action('ampforwp_between_loop',$count);
    $count++;
	return $post_status;
}
function amp_end_loop(){
	global $amp_q;
	wp_reset_postdata();
    echo "</div>";
}

function amp_reset_loop(){
	global $amp_q;
	$amp_q = '';
	return "";
}

function amp_pagination($args =array()) {
	global $amp_q, $wp_query, $redux_builder_amp;

	if (get_query_var( 'paged' ) ) {
	    $paged = get_query_var('paged');
	} elseif ( get_query_var( 'page' ) ) {
	    $paged = get_query_var('page');
	} else {
	    $paged = 1;
	}
	$pre_link = '';
	if(!isset($args['previous_text']) || $args['previous_text']==''){
		$args['previous_text'] = 'Show previous Posts';
	}
	if(!isset($args['next_text']) || $args['next_text']==''){
		$args['next_text'] = 'Show more Posts';
	}
    if ( $paged > 1 ) { 
      $pre_link = '<div class="left">'.get_previous_posts_link( ampforwp_translation($redux_builder_amp['amp-translator-show-previous-posts-text'], $args['previous_text'] ) ) .'</div>';
    }

    if ( $wp_query->max_num_pages > 1 ) { 
	    echo '<div class="loop-pagination">
	      <div class="right">'. get_next_posts_link( ampforwp_translation($redux_builder_amp['amp-translator-show-more-posts-text'] , $args['next_text']), $amp_q->max_num_pages ) .'</div>
	        '.$pre_link.'
	      <div class="clearfix"></div>
	    </div>';
	}
}

/***
* Get Title of post
*/
function amp_loop_title($data=array()){
	$data = array_filter($data);
	$tag = 'h2';
	if(isset($data['tag']) && $data['tag']!=""){
		$tag = $data['tag'];
	}
	$attributes = 'class="loop-title"';
	if(isset($data['attributes']) && $data['attributes']!=""){
		$attributes = $data['attributes'];
	}
	echo '<'.$tag.' '.$attributes.'>';
		if(!isset($data['link']) ){
			echo '<a href="'. amp_loop_permalink(true) .'">';
		}
	echo the_title('','',false);
	
		if(!isset($data['link']) ){
			echo  '</a>';
		}
	echo '</'.$tag.'>';
}

function amp_loop_date($args=array()){
	global $redux_builder_amp;
    if ( 2 == $redux_builder_amp['ampforwp-post-date-format'] ) {
    	$args['format'] = 'traditional';
    }
	if(isset($args['format']) && $args['format']=='traditional'){
		$post_date = esc_html( get_the_date() ) . ' '.esc_html( get_the_time());
    }else{
    	$post_date =  human_time_diff(
    						get_the_time('U', get_the_ID() ), 
    						current_time('timestamp') ) .' '. ampforwp_translation( $redux_builder_amp['amp-translator-ago-date-text'],
    						'ago');
    }
    echo '<div class="loop-date">'.$post_date.'</div>';
}

function amp_loop_excerpt($no_of_words=15,$tag = 'p'){
	//excerpt
	global $post,$redux_builder_amp;
	if(has_excerpt()){
		$content = get_the_excerpt();
	}else{
		$content = get_the_content();
	}
	$content =  strip_shortcodes( $content );
	if(isset($redux_builder_amp['ampforwp-homepage-loop-readmore-link']) && $redux_builder_amp['ampforwp-homepage-loop-readmore-link'] == 1) {
		echo '<'.$tag.'>'. wp_trim_words(  $content, $no_of_words ) .' '.'<a href="'. esc_url(ampforwp_url_controller(get_permalink($post->ID))) . '">'. ampforwp_translation($redux_builder_amp['amp-translator-read-more'],'Read More') . '</a></'.$tag.'>';

	}else{
		echo '<'.$tag.'>'. wp_trim_words(  $content, $no_of_words ) .'</'.$tag.'>';
	}
	
}

function amp_loop_all_content($tag = 'p'){
	$fullContent = strip_shortcodes( get_the_content() );
	echo '<'.$tag.'>'. $fullContent .'</'.$tag.'>';
}

function amp_loop_permalink($return,$amp_query_var ='amp'){
	global $redux_builder_amp;
	if( $return ){
		return ampforwp_url_controller( get_permalink() ) ;
	}
	if ( isset($redux_builder_amp['ampforwp-single-related-posts-link']) && true == $redux_builder_amp['ampforwp-single-related-posts-link'] ) {
		echo get_permalink();
	}
	else
		echo ampforwp_url_controller( get_permalink() );
}
function amp_loop_image( $data=array() ) {
	global $ampLoopData, $counterOffset, $redux_builder_amp;
	if (ampforwp_has_post_thumbnail()  ) {

		$tag 				= 'div';
		$tag_class 			= '';
		$layout_responsive 	= '';
		$imageClass 		= '';
		$imageSize 			= 'thumbnail';

		if ( isset($data['tag']) && $data['tag'] != "" ) {
			$tag = $data['tag'];
		}

		if ( isset($data['responsive']) && $data['responsive'] != "" ) {
			$layout_responsive = 'layout=responsive';
			}

		if ( isset($data['tag_class']) && $data['tag_class'] != "" ) {
			$tag_class = $data['tag_class'];
		}
		if ( isset($data['image_class']) && $data['image_class'] != "" ) {
			$imageClass = $data['image_class'];
		}
		if ( isset($data['image_size']) && $data['image_size'] != "" ) {
			$imageSize = $data['image_size'];
		}
		$thumb_url = ampforwp_get_post_thumbnail('url', $imageSize);
		$thumb_width = ampforwp_get_post_thumbnail('width', $imageSize);
		$thumb_height = ampforwp_get_post_thumbnail('height', $imageSize);
		
		if ( isset($data['image_crop']) && $data['image_crop'] != "" ) {
			$width = $data['image_crop_width'];
			if ( empty($width) ) {
				$width = $thumb_width;
			}
			$height = $data['image_crop_height'];
			if ( empty($height) ) {
				$height = $thumb_height;
			}
			if ( isset($redux_builder_amp['ampforwp-retina-images']) && true == $redux_builder_amp['ampforwp-retina-images'] ) {
				$resolution = 2;
				if ( isset($redux_builder_amp['ampforwp-retina-images-res']) && $redux_builder_amp['ampforwp-retina-images-res'] ) {
					$resolution = $redux_builder_amp['ampforwp-retina-images-res'];
				}
				$width = $width * $resolution;
				$height = $height * $resolution;
			}
			$thumb_url_array = ampforwp_aq_resize( $thumb_url, $width, $height, true, false, true ); //resize & crop the image
			$thumb_url = $thumb_url_array[0];
			$thumb_width = $thumb_url_array[1];
			$thumb_height = $thumb_url_array[2];
		}
		if ( $thumb_url ) {
			$imageLink = amp_loop_permalink(true);
			$loopImageData = array("post_id"	=>get_the_ID(),
									"image_url"			=>$thumb_url,
									"width"				=>$thumb_width,
									"height"			=>$thumb_height,
									"layout_responsive"	=>$layout_responsive,
									"image_class"		=>$imageClass,
									"image_link"		=>$imageLink
									);
			$changesInImageData = apply_filters("ampforwp_loop_image_update",$loopImageData);
			if(!empty($changesInImageData) && is_array($changesInImageData)){
				$thumb_url			= $changesInImageData["image_url"];
				$thumb_width		= $changesInImageData["width"];
				$thumb_height		= $changesInImageData["height"];
				$layout_responsive	= $changesInImageData["layout_responsive"];
				$imageClass			= $changesInImageData["image_class"];
				$imageLink			= $changesInImageData["image_link"];
			}
			echo '<'.$tag.' class="loop-img '.$tag_class.'">';
			echo '<a href="'.$imageLink.'">';
			echo '<amp-img src="'. $thumb_url .'" width="'.$thumb_width.'" height="'.$thumb_height.'" '. $layout_responsive .' class="'.$imageClass.'"></amp-img>';
			echo '</a>';
			echo '</'.$tag.'>';
		}
     } 
} 

// Category
function amp_loop_category(){
	global $redux_builder_amp;
	if(count(get_the_category()) > 0){
		echo ' <ul class="loop-category">';
			foreach((get_the_category()) as $category) {
				if ( false == $redux_builder_amp['ampforwp-archive-support'] ) {
				echo '<li class="amp-cat-'. $category->term_id.'">'. $category->cat_name.'</li>';
				}else{
				echo '<li class="amp-cat-'. $category->term_id.'"><a href="'.ampforwp_url_controller( get_category_link( $category->term_id ) ).'">'. $category->cat_name.'</a></li>';	
				}
			}
		echo '</ul>';
	}
}
// author
function amp_loop_author($args = array()){
	 global $redux_builder_amp;
	if(function_exists('ampforwp_framework_get_author_box')){
		ampforwp_framework_get_author_box($args);
	}else{
		echo "";
	}
}