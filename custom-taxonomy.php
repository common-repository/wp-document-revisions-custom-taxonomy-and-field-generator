<?php
/*
Plugin Name: WP Document Revisions Custom Taxonomy Generator
Plugin URI: http://wordpress.org/extend/plugins/wp-document-revisions-custom-taxonomy-and-field-generator/
Description: Generates Custom Taxonomies for use with WP Document Revisions
Version: 0.1.1
Author: Benjamin J. Balter
Author URI: http://ben.balter.com
License: GPL2
*/


class DocumentRevisionCustomTaxonomies {
	
	//defaults
	static $instance;
	private $plugin_uri = '';
	private $author = 'Benjamin J. Balter';
	private $author_uri = 'http://ben.balter.com';
	public $name = 'Document Custom Field';
	public $name_plural = 'Document Custom Fields';
	public $slug = 'document_custom_field';
	public $version = '0.1';
	public $plugin = '';
	public $type = '';
	
	/**
	 * Substantiates the class and adds UI hooks
	 * @since 0.1
	 */
	function __construct() {
		
        self::$instance = $this;
		add_action( 'admin_menu', array( &$this, 'register_ui' ) );
		add_action( 'load-settings_page_wp_document_revisions_custom_taxonomies', array( &$this, 'ui_submit' ) );
		
	}

	/**
	 * Adds Menu UI
	 * @since 0.1
	 */
	function register_ui() {
		add_submenu_page( 'options-general.php', 'WP Document Revisions Custom Fields', 'Document Fields', 'manage_options', 'wp_document_revisions_custom_taxonomies', array(&$this, 'ui') );

	}
	
	/**
	 * Helper function to parse existing plugins
	 * Assumes plugins are in the WP_PLUGINS_DIR root
	 * @since 0.1
	 * @returns array assoc. array of plugins and properties
	 */
	function get_existing() {
	
		//init array
		$output = array();
		
		//open plugin dir and loop through each file
		$dir = opendir( WP_PLUGIN_DIR );
		while (false !== ($file = readdir( $dir ) ) ) {

        	$contents = file_get_contents( WP_PLUGIN_DIR . '/' . $file );
        	
        	//if we can't get the contents, kick
        	if ( !$contents )
        		continue;
        	
        	//attempt to parse
        	if ( !$data = $this->parse_plugin_file( $contents ) )
        		continue;
        	
        	//unserialize the plugin data
        	$data = unserialize( $data );
        	
        	//push into output array
        	$output[ $data['slug'] ] = $data;
    	}
	
		return $output;
	}
	
	/**
	 * Callback to generate the UI
	 * @since 0.1
	 */
	function ui() { 
	
	//if posting, save
	if ( $this->plugin != '') 
		$save = $this->save_plugin(); 
	
	?>
	<div class="wrap">
		<?php $fields = $this->get_existing(); 
		if ( isset( $_GET['existing'] ) && array_key_exists( $_GET['existing'] , $fields ) ) {
			$this->name = $fields[ $_GET['existing'] ]['name'];
			$this->name_plural = $fields[ $_GET['existing'] ]['name_plural'];
			$this->slug = $fields[ $_GET['existing'] ]['slug'];
			$this->type = $fields[ $_GET['existing'] ]['type'];
		}
		?>
		<div style="float:right; margin-top: 1em;">Edit Existing Field: <select name="existing" id="existing">
		<option></option>
		<?php foreach ( $fields as $field ) { ?>
			<option value="<?php echo $field['slug']; ?>" <?php if ( isset( $_GET['existing'] ) ) selected( $_GET['existing'], $field['slug'] ); ?><?php if ( isset( $_POST['slug'] ) ) selected( $_POST['slug'], $field['slug'] ); ?>><?php echo $field['name']; ?></option>
		<?php } ?>
		</select></div>
		<script>
			jQuery(document).ready(function($){ 
				$('#existing').change(function(){ 
					top.location.href = '<?php echo admin_url( 'options-general.php?page=wp_document_revisions_custom_taxonomies'); ?>' +  '&existing=' + $(this).val();
				});
				
				$('#name').change(function(){
					if ( $('#name_plural').val() == '<?php echo $this->name_plural; ?>' )
						$('#name_plural').val( $('#name').val() + 's' );
					if ( $('#slug').val() == '<?php echo $this->slug; ?>' )
						$('#slug').val( $('#name').val().toLowerCase().replace(/[^a-z9-9_]/, '') );
				});
			});
		</script>
		<h2>Custom Document Fields</h2>
	<?php
		if ( $this->plugin != '' && !$save ) { ?>
			<div class="error clear"><p>WordPress tried to save the custom field, but did not have the necessary permissions to do so.<br />
			Please copy the below into <code><?php echo WP_PLUGIN_DIR; ?>/wp-document-revisions-<?php echo $this->slug; ?>-custom-field.php</code></p></div>
			<textarea style="width:100%; height: 200px;"><?php echo $this->plugin; ?></textarea>
		<?php } else if ( $this->plugin != '' ) { ?>
			<div class="updated"><p>Plugin successfully saved to <code><?php echo WP_PLUGIN_DIR; ?>/wp-document-revisions-<?php echo $this->slug; ?>-custom-field.php</code>. <br />Please be sure to <a href="<?php echo admin_url( 'plugins.php' ); ?>">Activate the plugin</a>, or if you would like, <a href="options-general.php?page=wp_document_revisions_custom_taxonomies">add another custom field</a>.</p></div>
		<?php } ?>
		<form method="post" action=<?php echo admin_url( 'options-general.php?page=wp_document_revisions_custom_taxonomies'); ?>> 
  			<table class="form-table">
  			      <tr valign="top">
  			      		<th scope="row">Field Name</th>
  			      		<td><input class="regular-text" type="text" id="name" name="name" value="<?php echo esc_attr($this->name); ?>" /><Br />
  			      		<span class="description">(Singular label for taxonomy, e.g., Department)</span></td>
  			      </tr>
  			      <tr>
  			      		<th scope="row">Field Name (Plural)</th>
  			      		<td><input class="regular-text" type="text" id="name_plural" name="name_plural" value="<?php echo esc_attr($this->name_plural); ?>" /><Br />
  			      		<span class="description">(Plural label for taxonomy, e.g., Departments)</span></td></td>  			      
  			      </tr>
  			        <tr>
  			      		<th scope="row">Slug</th>
  			      		<td><input class="regular-text" type="text" id="slug" name="slug" value="<?php echo esc_attr($this->slug); ?>" /><Br />
  			      		<span class="description">(Internal name, all lowercase, numbers letters and underscores only, e.g., department)</span></td></td>  			      
  			      </tr>
  			       <tr>
  			      		<th scope="row">Type</th>
  			      		<td><select name="type">
  			      			<option></option>
  			      			<option value="hierarchical" <?php echo selected( $this->type, 'hierarchical' ); ?>'>Hierarchical (like categories)</option>
  			      			<option value="freeform" <?php echo selected( $this->type, 'freeform' ); ?>>Freeform (like tags)</option>
  			      			<option value="exclusive" <?php echo selected( $this->type, 'exclusive' ); ?>>Choose only one</option>
  			      			<option value="text" <?php echo selected( $this->type, 'text' ); ?>>Text Field</option>
  			      			<option value="user" <?php echo selected( $this->type, 'user' ); ?>>Select a User</option>
  			      		</select><Br />
  			      		<span class="description"></span></td></td>  			      
  			      </tr>
  			</table>
  			<?php wp_nonce_field('document_revisions_custom_taxonomy'); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php	
	}

	/**
	 * Generates the text of a plugin file
	 * @since 0.1
	 * @returns string the plugin content
	 */
	function generate_plugin() { 
		
		//buffer output to return as a string
		ob_start();
		
		//open PHP tag and output headers
		echo "<?php\n";
		?>
/*
Plugin Name: <?php echo $this->name; ?> Custom Document Field
Plugin URI: <?php echo $this->plugin_uri; ?>

Description: Creates <?php echo strtolower( $this->name ); ?> custom taxonomy for use with WP Document Revisions
Version: <?php echo $this->version; ?>

Author: <?php echo $this->author; ?>

Author URI: <?php echo $this->author_uri; ?>

License: GPL2
*/
<?php

	//call proper function depending on type
	switch( $this->type ) {
		case 'text':
			$this->text();
		break;
		case 'user':
			$this->user();
		break;
		case 'exclusive':
			$this->ct();
			$this->exclusive();
		break;
		default:
			$this->ct();
		break;		
	}
		//append signature	
		$this->signature();
	
		//close tag and clear buffer to string
		echo "?>";
		$output = ob_get_contents();
		ob_end_clean();
		return $output;	
	}
	
	/**
	 * CB to process submission of UI
	 * @since 0.1
	 */
	function ui_submit() {
		
		//verify POSTing
		if ( !$_POST )
			return;
		
		//check referer
		check_admin_referer('document_revisions_custom_taxonomy');
		
		//set up defaults and parse args
		$defaults = array( 
					'name' => $this->name,
					'name_plural' => $this->name_plural,
					'slug' => $this->slug,
					'type' => 'hierarchical',
					);
		
		$args = wp_parse_args( $_POST, $defaults );
		
		//filter data and store
		$this->name = ucwords( trim( $args['name'] ) );
		$this->name_plural = ucwords( trim( $args['name_plural']  ) );
		$this->slug = preg_replace('/[^a-z0-9_]/', '', strtolower( str_replace( ' ', '_', trim( $args['slug'] ) ) ) );
		$this->type = $args['type'];
	
		//generate plugin
		$this->plugin = $this->generate_plugin();
			
	}
	
	/**
	 * Pulls signature from file
	 * @since 0.1
	 * @param string $file the plugin content
	 * @returns string the parsed plugin
	 */
	function parse_plugin_file( $file ) {
		
		$pattern = '#/[\*]{35}.*?[A-Za-z0-9 \r]{34}(.*)[\*]{36}/#sm';
	
		$result = preg_match( $pattern, $file, $matches );
		
		if ( !$result )
			return false;
			
		return trim( $matches[1] );

	}
	
	/**
	 * Attempts to write plugin file to disk
	 * @since 0.1
	 * @returns bool success/fail
	 */
	function save_plugin() {
	
		return file_put_contents( WP_PLUGIN_DIR . '/wp-document-revisions-' . $this->slug . '-custom-field.php', $this->plugin );
	
	}
	
	/**
	 * Generates Custom Taxonomy plugins
	 * @since 0.1
	 */
	function ct() { ?>
	
	/**
 	 * Callback to register <?php echo $this->name; ?> custom document taxonomy
 	 */
		function wp_document_revisions_register_<?php echo $this->slug; ?>_ct() {
	
			$labels = array(
			  'name' => _x( '<?php echo $this->name_plural; ?>', 'taxonomy general name' ),
			  'singular_name' => _x( '<?php echo $this->name; ?>', 'taxonomy singular name' ),
			  'search_items' =>  __( 'Search <?php echo $this->name_plural; ?>' ),
			  'all_items' => __( 'All <?php echo $this->name_plural; ?>' ),
			  'parent_item' => __( 'Parent <?php echo $this->name; ?>' ),
			  'parent_item_colon' => __( 'Parent <?php echo $this->name; ?>:' ),
			  'edit_item' => __( 'Edit <?php echo $this->name; ?>' ), 
			  'update_item' => __( 'Update <?php echo $this->name; ?>' ),
			  'add_new_item' => __( 'Add New <?php echo $this->name; ?>' ),
			  'new_item_name' => __( 'New <?php echo $this->name; ?> Name' ),
			  'menu_name' => __( '<?php echo $this->name; ?>' ),
			); 	
			
			register_taxonomy('document_<?php echo $this->slug; ?>',array('document'), array(
			  'hierarchical' => <?php echo ($this->type == 'freeform') ? 'false' : 'true' ?>,
			  'labels' => $labels,
			  'show_ui' => true,
			  'public' => false,
			  'rewrite' =>false,
			));	
		  
		}
	  
	 //add action hook
	add_action( 'init', 'wp_document_revisions_register_<?php echo $this->slug; ?>_ct');
	
 	<?php 
 	}
 	
 	/**
 	 * Generates user field plugins
 	 * @since 0.1
 	 */
 	function user() { ?>
 	
 	/**
 	 * Callback to register <?php echo $this->name; ?> metabox
 	 */
 	function wp_document_revisions_register_<?php echo $this->slug; ?>_metabox() {
 		add_meta_box( 'document_<?php echo $this->slug; ?>', '<?php echo $this->name; ?>', 'wp_document_revisions_<?php echo $this->slug; ?>_cb', 'document');
 	}
 	
 	//add action hook
 	add_action( 'add_meta_boxes', 'wp_document_revisions_register_<?php echo $this->slug; ?>_metabox' );
 	 
 	/**
 	 * Callback to store <?php echo $this->name; ?> field
 	 */
 	function  wp_document_revisions_save_<?php echo $this->slug; ?>( $post_id ) {
 		//verify this is not an autosave
 		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      			return;
      		
      	//verify nonce
      	if ( !wp_verify_nonce( $_POST['document_<?php echo $this->slug; ?>_nonce'], plugin_basename( __FILE__ ) ) )
      			return;
      		
      	//verify permissions
      	if ( !current_user_can( 'edit_post', $post_id ) )
       		return;
      		
      	//store the data 			
       	update_post_meta( $post_id, 'document_<?php echo $this->slug; ?>', $_POST['document_<?php echo $this->slug; ?>'], true);
 	}
 	
	add_action( 'save_post', 'wp_document_revisions_save_<?php echo $this->slug; ?>', 10, 1 ); 
 	
 	/**
 	 * Callback to display <?php echo $this->name; ?> metabox
 	 */
 	function wp_document_revisions_<?php echo $this->slug; ?>_cb( $post) {
 		global $wpdb;
		
		//output nonce field
		wp_nonce_field( plugin_basename( __FILE__ ), 'document_<?php echo $this->slug; ?>_nonce' );
		
		//get list of authors
		$authors = $wpdb->get_results("SELECT display_name, user_nicename from $wpdb->users ORDER BY display_name");
		
		//output label
		echo '<label for="document_<?php echo $this->slug; ?>"><?php echo $this->name; ?></label>:';
		
		//output <?php echo $this->name; ?> dropdown
		echo '<select name="document_<?php echo $this->slug; ?>" id="document_<?php echo $this->slug; ?>" style="margin-left: 25px;">';
		foreach ( $authors as $author )
			echo '<option value="' . $author->user_nicename . '" ' . selected( $author->user_nicename, get_post_meta( $post->ID, 'document_<?php echo $this->slug; ?>' , true ) ) . ' >' .  $author->display_name . '</option>';
		echo '</select>';
 	}
 	
 	<?php }
 	
 	/**
 	 * Generates text field plugins
 	 * @since 0.1
 	 */
 	function text() { ?>
 	
 	 	/**
 		 * Callback to register <?php echo $this->name; ?> metabox
 		 */
 	 	function wp_document_revisions_register_<?php echo $this->slug; ?>_metabox() {
			add_meta_box( 'document_<?php echo $this->slug; ?>', '<?php echo $this->name; ?>', 'wp_document_revisions_<?php echo $this->slug; ?>_cb', 'document');
 		}
 		
 		//add action hook
	 	add_action( 'add_meta_boxes', 'wp_document_revisions_register_<?php echo $this->slug; ?>_metabox' );

 		/**
 		 * Callback to store <?php echo $this->name; ?> field
 		 */
		function wp_document_revisions_save_<?php echo $this->slug; ?>( $post_id ) {
 			
 			//verify this is not an autosave
 			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      			return;
      		
      		//verify nonce
      		if ( !wp_verify_nonce( $_POST['document_<?php echo $this->slug; ?>_nonce'], plugin_basename( __FILE__ ) ) )
      			return;
      		
      		//verify permissions
      		if ( !current_user_can( 'edit_post', $post_id ) )
       			return;
      		
      		//store the data 			
       		update_post_meta( $post_id, 'document_<?php echo $this->slug; ?>', $_POST['document_<?php echo $this->slug; ?>'], true);
 		
 		}
 		
 		add_action( 'save_post', 'wp_document_revisions_save_<?php echo $this->slug; ?>', 10, 1 ); 
 	
 		/**
 		 * Callback to display <?php echo $this->name; ?> metabox
 		 */
 		function wp_document_revisions_<?php echo $this->slug; ?>_cb( ) {
 			global $post;
 			
 			//create nonce field
			wp_nonce_field( plugin_basename( __FILE__ ), 'document_<?php echo $this->slug; ?>_nonce' );
		
			//output label
			echo '<label for="document_<?php echo $this->slug; ?>"><?php echo $this->name; ?></label>:';
			
			//output <?php echo $this->name; ?> field
			echo '<input type="text" name="document_<?php echo $this->slug; ?>" id="document_<?php echo $this->slug; ?>" style="margin-left:25px;"';
			echo ' value="' . get_post_meta($post->ID, 'document_<?php echo $this->slug; ?>', true) . '" />';
			
 		} 		
 		
 	<?php }
 	
 	/**
 	 * Generates exclusive taxonomy plugins
 	 * @since 0.1
 	 */
 	function exclusive() { ?>
	
	/**
 	 * Callback to register <?php echo $this->name; ?> metabox
 	 */
 	function wp_document_revisions_register_<?php echo $this->slug; ?>_metabox() {
		
		//remove default metabox 		
 		remove_meta_box( 'document_<?php echo $this->slug; ?>div', 'document', 'side');

		//add custom metabox
 		add_meta_box( 'document_<?php echo $this->slug; ?>', '<?php echo $this->name; ?>', 'wp_document_revisions_<?php echo $this->slug; ?>_cb', 'document', 'side');
 	
 	}
 	
 	//add action hook
 	add_action( 'add_meta_boxes', 'wp_document_revisions_register_<?php echo $this->slug; ?>_metabox' );
 	
/**
 * Generates the <?php echo $this->name; ?> taxonomy radio inputs 
 * @params object $post the post object WP passes
 * @params object $box the meta box object WP passes (with our arg stuffed in there)
 */
function wp_document_revisions_<?php echo $this->slug; ?>_cb( $post ) {
	
	//get the taxonomy and labels		
	$taxonomy = get_taxonomy( 'document_<?php echo $this->slug; ?>' );
	
	//grab an array of all terms within our custom taxonomy, including empty terms
	$terms = get_terms( 'document_<?php echo $this->slug; ?>', array( 'hide_empty' => false ) );

	//garb the current selected term where applicable so we can select it
	$current = wp_get_object_terms( $post->ID, 'document_<?php echo $this->slug; ?>' );
	
	//loop through the terms
	foreach ($terms as $term) {
		
		//build the radio box with the term_id as its value
		echo '<input type="radio" name="document_<?php echo $this->slug; ?>" value="'.$term->term_id.'" id="'.$term->slug.'"';
		
		//if the post is already in this taxonomy, select it
		if ( isset( $current[0]->term_id ) )
			checked( $term->term_id, $current[0]->term_id );
		
		//build the label
		echo '> <label for="'.$term->slug.'">' . $term->name . '</label><br />'. "\r\n";
	}
		echo '<input type="radio" name="document_<?php echo $this->slug; ?>" value="" id="none" ';
		checked( empty($current[0]->term_id) );
		echo '/> <label for="none">None</label><br />'. "\r\n"; 
		<?php echo '?' . '>'; ?>
		
		<a href="#" id="add_document_<?php echo $this->slug; ?>_toggle">+ <?php echo '<' . '?php'; ?> echo $taxonomy->labels->add_new_item; ?></a>
		<div id="add_document_<?php echo $this->slug; ?>_div" style="display:none">
			<label for="new_document_<?php echo $this->slug; ?>"><?php echo '<' . '?php'; ?> echo $taxonomy->labels->singular_name; ?>:</label> 
			<input type="text" name="new_document_<?php echo $this->slug; ?>" id="new_document_<?php echo $this->slug; ?>" /><br />
			<input type="button" value="Add New" id="add_document_<?php echo $this->slug; ?>_button" />
			<img src="<?php echo '<' . '?php'; ?> echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="document_<?php echo $this->slug; ?>-ajax-loading" style="display:none;" alt="" />
		</div>
		<script>
			jQuery(document).ready(function($){
				$('#add_document_<?php echo $this->slug; ?>_toggle').click(function(event){
					$('#add_document_<?php echo $this->slug; ?>_div').toggle();
					event.preventDefault();
				});
				$('#add_document_<?php echo $this->slug; ?>_button').click(function() {
					$('#document_<?php echo $this->slug; ?>-ajax-loading').show();
					$.post('admin-ajax.php?action=add_document_<?php echo $this->slug; ?>', $('#new_document_<?php echo $this->slug; ?>, #new_document_<?php echo $this->slug; ?>_location, #_ajax_nonce-add-document_<?php echo $this->slug; ?>, #post_ID').serialize(), function(data) { 
						$('#document_<?php echo $this->slug; ?> .inside').html(data); 
						});
				});
			});
		</script>
	<?php echo '<' . '?php'; ?>

	//nonce is a funny word
	wp_nonce_field( 'add_document_<?php echo $this->slug; ?>', '_ajax_nonce-add-document_<?php echo $this->slug; ?>' );
	wp_nonce_field( 'document_<?php echo $this->slug; ?>', 'document_<?php echo $this->slug; ?>_nonce'); 
}

/**
 * Processes AJAX request to add new <?php echo $this->name; ?> terms
 * @since 1.2
 */
function wp_document_revisions_<?php echo $this->slug; ?>_ajax_add() {
	
	//pull the taxonomy from the action query var
	$type = substr($_GET['action'],4);
	
	//pull up the taxonomy details
	$taxonomy = get_taxonomy($type);
	
	//check the nonce
	check_ajax_referer( $_GET['action'] , '_ajax_nonce-add-document_<?php echo $this->slug; ?>' );
	
	//check user capabilities
	if ( !current_user_can( $taxonomy->cap->edit_terms ) )
		die('-1');

	//insert term
	$term = wp_insert_term( $_POST['new_document_<?php echo $this->slug; ?>'], 'document_<?php echo $this->slug; ?>' );
	
  	//get updated post to send to taxonomy box
	$post = get_post( $_POST['post_ID'] );
	
	//return the HTML of the updated metabox back to the user so they can use the new term
	wp_document_revisions_<?php echo $this->slug; ?>_cb( $post );
	exit();
}

add_action('wp_ajax_add_document_<?php echo $this->slug; ?>', 'wp_document_revisions_<?php echo $this->slug; ?>_ajax_add');

		/**
 		 * Callback to store <?php echo $this->name; ?> custom taxonomy
 		 */
		function wp_document_revisions_save_<?php echo $this->slug; ?>( $post_id ) {
 			
 			//verify this is not an autosave
 			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      			return;
      		
      		//verify nonce
      		if ( !wp_verify_nonce( $_POST['document_<?php echo $this->slug; ?>_nonce'], 'document_<?php echo $this->slug; ?>' ) )
      			return;
      		
      		//verify permissions
      		if ( !current_user_can( 'edit_post', $post_id ) )
       			return;
      		
      		//associate taxonomy with parent, not revision
			if ( wp_is_post_revision( $post_id ) )
				$post_id = wp_is_post_revision( $post_id );
      		
      		//store the data 			
       		wp_set_post_terms( $post_id,  $_POST['document_<?php echo $this->slug; ?>'], 'document_<?php echo $this->slug; ?>', false);
 		
 		}

 		add_action( 'save_post', 'wp_document_revisions_save_<?php echo $this->slug; ?>', 10, 1 ); 

<?php 
 	
 	}
 	
 	/**
 	 * Generates signature to append to files
 	 * @since 0.1
 	 */
 	function signature() { ?>
 	
 	
/***********************************
WP Document Revisions Custom Field
<?php echo serialize( array( 'name' => $this->name, 'name_plural' => $this->name_plural, 'slug' => $this->slug, 'type' => $this->type ) ); ?>

************************************/
 	<?php }
}

new DocumentRevisionCustomTaxonomies;

?>