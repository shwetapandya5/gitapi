<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

/*
 * Admin Tools-> Example API Page
*/
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class gitapi {

    function __construct() {

        add_action( 'admin_enqueue_scripts', array($this, 'adminAssets'));
        add_action( 'carbon_fields_register_fields', array($this, 'crbThemeOptions'));
        add_action( 'plugins_loaded', array($this, 'crbLoad'));
        add_action( 'init',  array($this, 'customPostType'));
        register_activation_hook(EXA_PLUGIN, array($this, 'flushRewrites'));
        register_deactivation_hook(EXA_PLUGIN, array($this, 'flushPostType'));
        add_action('carbon_fields_theme_options_container_saved', array($this, 'getGitIssues'));

    }

    /* Register carbon fields */
    public function crbThemeOptions() {

        Container::make( 'theme_options', 'GIT Repo URL' )
            ->add_fields( array(
            Field::make( 'text', 'git_url' ),
            Field::make( 'text', 'page_num', 'Page Num' )
        ) );

        Container::make( 'post_meta', __( 'Post Options', 'crb' ) )
            ->where( 'post_type', '=', 'get_git_issue' )
            ->add_fields( array(
                Field::make( 'text', 'git_issue_num', 'ID' )
        ) );
    }

    /* Include autoload and boot carbon fields */
    public function crbLoad() {

        require_once(EXA_PLUGIN_DIR . '/vendor/autoload.php' );
        \Carbon_Fields\Carbon_Fields::boot();
    }

    /* Register Custom Post Type */
    public function customPostType() {

        register_post_type( 'get_git_issue',
            array(
                'labels' => array(
                    'name' => __( 'GIT Issues' ),
                    'singular_name' => __( 'GIT Issue' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'get_git_issue'),
                'show_in_rest' => true,
                'query_var' => true,
                'supports' => array(
                    'title',
                    'editor',
                    'excerpt',
                    'custom-fields',
                    'thumbnail',
                    'author',
                    'page-attributes'
                )
            )
        );
    }

    /*Call function of custom post type and Remove rewrite rules and then recreate rewrite rules.*/
    public function flushRewrites() {
            flush_rewrite_rules();
    }

    /*Delete post type*/
    public function flushPostType() {
          // Uninstallation stuff here
        global $wpdb;
        // delete all posts by post type.
        $sql = 'DELETE posts, pm
            FROM '. $wpdb->prefix .'posts AS posts 
            LEFT JOIN ' .$wpdb->prefix . 'postmeta AS pm ON pm.post_id = posts.ID
            WHERE posts.post_type = "get_git_issue"';
        $result = $wpdb->query($sql);
        unregister_post_type( 'get_git_issue' );
    }
    
    /*Include js files*/
    public function adminAssets() {
        if ( isset( $_GET['page'] ) && ! empty( $_GET['page'] )) {
            wp_enqueue_script('jquery', plugins_url('assets/jquery.js', __FILE__), false );
        }
    }

    /*API function returns curl array */
    public function curlData($url,$page){

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url."?page=".$page."&per_page=34",
            CURLOPT_HTTPHEADER => [
              "Accept: application/vnd.github.v3+json",
              "Content-Type: text/plain",
              "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 YaBrowser/16.3.0.7146 Yowser/2.5 Safari/537.36"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if($response){
            return $response;
        }else{
            return $err;
        }
    }

    /*Call function on form submit */
    public function getGitIssues(){

        $url = $_POST['carbon_fields_compact_input']['_git_url'];
        $page_num = $_POST['carbon_fields_compact_input']['_page_num'];

        if($page_num == ""){
             $page_num = 1;
        }

        if($url == ""){
            $url = "https://api.github.com/repos/htmlburger/carbon-fields/issues";
        }
        
        if($url){

            $response = $this->curlData($url,$page_num);

            if(!empty($response)){

                $response = json_decode($response);
                $data = array_column($response,'title','number');

                if(!empty($data)){
                    foreach ($data as $key=>$value) {
                       $post_id = wp_insert_post(array (
                            'post_type' => 'get_git_issue',
                            'post_title' => $value,
                            'post_content' => '',
                            'post_status' => 'publish',
                            'comment_status' => 'closed',
                            'ping_status' => 'closed',
                        ));

                       if($post_id){
                            carbon_set_post_meta($post_id, 'git_issue_num', $key);
                       }
                    }
                } 
                $page_num = $page_num + 1;
                $page_num = update_option('_page_num',$page_num);   
            }
          
        }
    }   
}

$api = new gitapi();

 ?>