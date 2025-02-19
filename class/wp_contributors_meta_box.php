<?php
/**
 * Register a meta box using a class.
 */
class WP_Contributors_Meta_Box {

    /**
     * Constructor.
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        }
        add_action('the_content', array( $this, 'woco_display_contributors'));
        add_action('wp_enqueue_scripts', array( $this, 'woco_enqueue_style'));

    }
    
    /**
     * Meta box initialization.
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
        add_action( 'save_post',      array( $this, 'woco_save_custom_meta_box' ), 10, 3 );
        add_action( 'save_post',      array( $this, 'woco_save_custom_meta_box' ), 10, 3 );
    }
    
    /**
     * Display Contributors on frontend.
     */
    function woco_display_contributors($content){
        
        if (!is_single()){
            return $content;
        }
        
        $woco_authors = get_post_meta(get_the_ID(), "woco_authors", true);
     
        if(empty($woco_authors)){
            return $content;
        }
        $html = '<h3 class="woco_heading">'.esc_html__('Contributors','wordpress-contributors').'</h3>' ;
        $html .= '<ul class="woco_authors">';
        
        
        
        
        foreach($woco_authors as $woco_user_id) {
            $woco_user = get_user_by( 'ID', $woco_user_id );
            
            $html .= '<li><a href="'.get_author_posts_url($woco_user_id).'">'.get_avatar( $woco_user_id, 24 ). '<span class="woco_authors_label">'. $woco_user->display_name.'</span></a></li>';
            
        }
        $html .= '</ul>';
        $content .= $html;
        return $content;
    }

    /**
     * Add style on frontend.
     */
    public function woco_enqueue_style() {
        if (!is_single()){
            return $content;
        }
        wp_register_style( 'woco-plugin', plugins_url( '../css/wordpress-contributors.css', __FILE__ ) );
        wp_enqueue_style( 'woco-plugin' );
    }
    



    /**
     * Adds the meta box.
     */
    public function add_metabox() {
        add_meta_box(
            'woco_post_metabox',
            __( 'Post Contributors', 'textdomain' ),
            array( $this, 'woco_display_post_metabox' ),
            'post',
            'normal',
            'default'
        );

    }

    /**
     * Renders the meta box.
     */
    public function woco_display_post_metabox($object) {
        wp_nonce_field(basename(__FILE__), "woco-meta-box-nonce");
        $woco_authors = get_post_meta($object->ID, "woco_authors", true);
        if(empty($woco_authors)){
            $woco_authors = array();
        }
        $authors = get_users(
                    array(
                        'role' => 'author',
                        'orderby' => 'display_name',
                        'order' => 'ASC'
                        )
                    );
        ?>
        <div>
        <h4><?php echo esc_html__('Select Authors','wordpress-contributors') ?></h4>
            <?php
            if(empty($authors)){
                echo esc_html__('No authors available to select.','wordpress-contributors');
            }
            ?>
        <div class="woco-authors">
        <?php
              foreach($authors as $user)
              { ?>
                  <label for="woco_authors"> <input name="woco_authors[]" type="checkbox" value="<?php echo $user->ID; ?>"  <?php if(in_array($user->ID,$woco_authors)) { echo 'checked'; } ?> >
                  <?php echo $user->display_name; ?></label>
                  <br/>
        <?php } ?>
        </div>
        <?php
    }

    /**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function woco_save_custom_meta_box($post_id, $post, $update)
    {
        // Check if nonce is valid.
        if (!isset($_POST["woco-meta-box-nonce"]) || !wp_verify_nonce($_POST["woco-meta-box-nonce"], basename(__FILE__)))
            return $post_id;
        
        // Check if user has permissions to save data.
        if(!current_user_can("edit_post", $post_id))
            return $post_id;

        
        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
        
        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Check if it post only.
        $slug = "post";
        if($slug != $post->post_type)
            return $post_id;

        $woco_authors = "";

        // check selected authors data.
        if(isset($_POST["woco_authors"]))
        {
            $woco_authors = $_POST["woco_authors"];
        }
        update_post_meta($post_id, "woco_authors", $woco_authors);
    }
}

new WP_Contributors_Meta_Box();
