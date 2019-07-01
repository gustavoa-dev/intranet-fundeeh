<?php

namespace GDCalendar\Core;


abstract class PostTypeModel
{
    /**
     * @var string
     */
    protected static $plugin_id = 'gd_calendar';

    /**
     * @var string
     */
    protected static $post_type;

    /**
     * Current Object post's id in wp_posts table
     * @var int
     */
    private $id;

    /**
     * Current Object post's data in wp_posts table
     * @var \WP_Post
     */

    private $post_data;

    /** @var  string[] */
    protected static $meta_keys;

    /**
     * PostTypeModel constructor
     * @param null $id
     * @throws \Exception
     */
    public function __construct($id = null )
    {
        if( !empty($id) ){
            if( absint( $id ) != $id ){
                throw new \Exception('ID must be non negative integer');
            }

            $this->id = (int) $id;
            $this->post_data = get_post($this->id);

            if( !empty( static::$meta_keys ) ):
                foreach(static::$meta_keys as $key ):
                    $function_name = 'set_'. $key;
                    $meta_key = self::$plugin_id . '_' . static::$post_type . '_' . $key;

                    if( method_exists( $this, $function_name ) ){
                        $meta_value = get_post_meta( $this->id, $meta_key, true );

                        if( $meta_value ){
                            call_user_func( array( $this, $function_name ), $meta_value );
                        }
                    }

                endforeach;
            endif;
        }
    }

    /**
     * @return int
     */
    public function get_id(){
        return $this->id;
    }

    /**
     * @return \WP_Post
     */
    public function get_post_data(){
        return $this->post_data;
    }

    /**
     * @return string
     */
    public static function get_post_type(){
        return static::$post_type;
    }

    /**
     * Retrieve all posts for this post type
     * @return static[]|bool
     */
    public static function all(){
        return static::get();
    }

    /**
     * @param array $args
     * @return static[]|bool
     */
    public static function get( $args = array() ){
        $args = wp_parse_args( $args, array(
            'post_type' => static::$post_type,
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $query = new \WP_Query($args);

        if( ! $query->have_posts() ){
            return false;
        }

        $objects = array();
        $object_posts = $query->get_posts();
        foreach( $object_posts as $object_post ){
            $objects[] = new static( $object_post->ID );
        }

        return $objects;
    }

    /**
     * @param array $keys
     * @return bool
     * @throws \Exception
     */
    public function save_meta( $keys = array()){
        if( null === $this->id ){
            throw new \Exception('Cannot save metadata for not existing post');
        }

        if( empty($keys) ){
            $keys = static::$meta_keys;
        }

        $updated = array();

        foreach ($keys as $key){
            if( null !== $this->$key){
                $updated[] = update_post_meta(
                    $this->id,
                    self::$plugin_id . '_' . static::$post_type . '_' . $key,
                    $this->$key
                );
            }
        }

        return ( !in_array( false, $updated ) );
    }

    /**
     * @param $key
     * @param $value
     */

    public function add_meta_value( $key, $value )
    {
        add_post_meta($this->id, self::$plugin_id . '_' . static::$post_type . '_' . $key, $value);
    }

    /**
     * @param $key
     * @param bool $value
     */

    public function delete_meta_value( $key, $value = false )
    {
        delete_post_meta($this->id, self::$plugin_id . '_' . static::$post_type . '_' . $key, $value);
    }

    /**
     * @param $key
     * @return array
     */

    public function get_meta_value($key){
        return get_post_meta($this->id, self::$plugin_id . '_' . static::$post_type . '_' .$key);
    }

    public function get_edit_link()
    {
        if(null === $this->id){
            return null;
        }
        return get_edit_post_link($this->id);
    }

    abstract public function save();
}