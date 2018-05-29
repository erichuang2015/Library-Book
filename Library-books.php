<?php
/*
Plugin Name: Library Books
Description: Plugin for Create New CPT(Custom Post Type) with the Name Library Book.To get book_type post use shortcode [library_book] in page.This Plugin gives you ajax search functionality. 
Version: 1.0
Author: Jayesh Borase
*/
?>
<?php 
add_action( 'init', 'create_books' );

function create_books() {
	
	register_post_type( 'book_type',
        array(
            'labels' => array(
                'name' => 'Library Books',
                'singular_name' => 'Library Book',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Book',
                'edit' => 'Edit',
                'edit_item' => 'Edit Book',
                'new_item' => 'New Book',
                'view' => 'View',
                'view_item' => 'View Book',
                'search_items' => 'Search Book',
                'not_found' => 'No Books found',
                'not_found_in_trash' => 'No Books found in Trash',
                'parent' => 'Parent Book'
            ),
			'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail','page-attributes'),
            'taxonomies' => array( 'post_tag', 'category' ),
            'menu_icon' => plugins_url( 'images/logo.png', __FILE__ ),
            'has_archive' => true
        )
    );
}

//load js and css from plugin directory
function myplugin_scripts() {
    wp_enqueue_style( 'custom-css', plugins_url( '/css/custom.css', __FILE__ ) );
    wp_enqueue_style( 'bootstrap-min-css', plugins_url( '/css/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_script( 'book-min-js', plugins_url( '/js/book-min.js', __FILE__ ));
	wp_enqueue_script( 'jquery-min-js', plugins_url( '/js/jquery.min.js', __FILE__ ));
	wp_enqueue_script( 'bootstrap-min-js', plugins_url( '/js/bootstrap.min.js', __FILE__ ));
}
add_action( 'wp_enqueue_scripts', 'myplugin_scripts' );

//Register book library meta admin side
add_action( 'admin_init', 'library_book_admin' );
function library_book_admin() {
    add_meta_box( 'library_book_meta_box',
        'Book Detail',
        'display_library_book_meta_box',
        'book_type',
		'normal',
		'high'
    );
}

function display_library_book_meta_box( $library_book ) 
{
    // Retrieve current name of the Book Author and Book price.
    $book_author = esc_html( get_post_meta( $library_book->ID, 'book_author', true ) );
	$book_price = esc_html( get_post_meta( $library_book->ID, 'book_price', true ) );
    ?>
    <table>
        <tr>
            <td style="width: 100%">Book Author</td>
            <td><input type="text" size="80" name="library_book_author_name" value="<?php echo $book_author; ?>" /></td>
        </tr>
		<tr>
            <td style="width: 100%">Price</td>
            <td><input type="text" size="80" name="library_book_price" value="<?php echo $book_price; ?>" /></td>
        </tr>
    </table>
    <?php
}

//save the fields
add_action( 'save_post', 'add_library_book_fields', 10, 2 );

function add_library_book_fields( $library_book_id, $library_book )
{
   if ( $library_book->post_type == 'book_type' ) {
        if ( isset( $_POST['library_book_author_name'] ) && $_POST['library_book_author_name'] != '' ) {
            update_post_meta( $library_book_id, 'book_author', $_POST['library_book_author_name'] );
        }
		 if ( isset( $_POST['library_book_price'] ) && $_POST['library_book_price'] != '' ) {
            update_post_meta( $library_book_id, 'book_price', $_POST['library_book_price'] );
        }
    }
}

// ajax action fetch the data
add_action( 'wp_footer', 'ajax_fetch' );
function ajax_fetch() {
?>
<script type="text/javascript">
function fetch(){

    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'post',
        data: { action: 'data_fetch', keyword: jQuery('#keyword').val() },
        success: function(data) {
            jQuery('.book_detail').html( data );
        }
    });
	
}
</script>

<?php
}

add_action('wp_ajax_data_fetch' , 'data_fetch');
add_action('wp_ajax_nopriv_data_fetch','data_fetch');
function data_fetch(){
	global $wpdb;
	$search_field = esc_attr( $_POST['keyword'] );
	//SQL search query from database
	$sql = "select * from wp_posts WP join wp_postmeta WPM on (WP.ID = WPM.post_id) where post_type = 'book_type' AND (meta_value like '%".$search_field."%' or post_content like '%".$search_field."%' or post_title like '%".$search_field."%')";
    
	$querysearch_data = $wpdb->get_results($sql);
	$querysearch = array();     
	$i = 0;    
	$key_array = array();         
	foreach($querysearch_data as $val) {        
	if (!in_array($val->ID, $key_array)) {             
		$key_array[$i] = $val->ID;             
		$querysearch[$i] = $val;
	}         
		$i++;
	}
	
	echo '<div id="content" class="library_book" role="main">';
	if ($querysearch):
	global $post;
	
	foreach($querysearch as $post):
	
	$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
	
	if($featured_img_url == ''){
		$featured_img_url .= plugins_url( 'images/No_Image.png', __FILE__ );
	}
	?>
		<div class="row book_detail">
			<article id="post-<?php echo get_the_ID();?>">
				<div class="col-sm-4">
					<header class="entry-header">
						<?php //$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); ?>
						<div class="image_section">
							<img src="<?php echo $featured_img_url ?>">
						</div>
					</header>
				</div>
				<div class="col-sm-8">
					<div class="book_header">
						<a href="<?php echo get_the_permalink() ?>">
							<?php echo get_the_title() ?>
						</a><br />
						<strong>Author: </strong>
						<?php echo get_post_meta( get_the_ID(), 'book_author', true ) ?><br />
						<strong>Price: </strong>
						<?php echo get_post_meta( get_the_ID(), 'book_price', true ) ?>
					</div>
					<div class="book_content"> 
						<?php echo $post->post_content; ?>
					</div>
				</div>
			</article>
		</div>
<?php 
	endforeach; 
	else : 
		echo '<h2 class="center">No Post Available</h2>';
    endif; 
echo '</div>';
	die();
}

// created a shortcode to get the records
add_shortcode( 'library_book', 'display_custom_post_type' );

function display_custom_post_type(){
	$args = array(
        'post_type' => 'book_type',
        'post_status' => 'publish'
    );
	$string = '';
	$query = new WP_Query( $args );
	if( $query->have_posts() ){
		$string .= 	'<div id="content" class="library_book" role="main">';
		$string .=	'<input type="text" name="keyword" id="keyword" onkeyup="fetch()" placeholder="Search"></input>
					<div class="row book_detail">';
		
		while( $query->have_posts() ){
			$query->the_post();
			
			$featured_img_url = '';
			if(has_post_thumbnail()){
				$featured_img_url .= get_the_post_thumbnail_url(get_the_ID(),'full');
			}
			else{
				$featured_img_url .= plugins_url( 'images/No_Image.png', __FILE__ );
			}
			//$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
			$string .= '
						<article id="post-'.get_the_ID().'">
							<div class="col-sm-4">
								<header class="entry-header">
									<div class="image_section">
										<img src="'.$featured_img_url.'">
									</div>
								</header>
							</div>
							<div class="col-sm-8">
								<div class="book_header">
									<a href="'.get_the_permalink().'">'.get_the_title().'</a><br />
									<strong>Author: </strong>
										'. get_post_meta( get_the_ID(), 'book_author', true ) .'<br />
									<strong>Price: </strong>
										'. get_post_meta( get_the_ID(), 'book_price', true ) .'
								</div>
								<div class="book_content">'.get_the_content().'</div>
							</div>
						</article>
						';
		}
		$string .= '</div></div>';
	}
	wp_reset_postdata();
	return $string;
}