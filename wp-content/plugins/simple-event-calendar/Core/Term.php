<?php

namespace GDCalendar\Core;

class Term
{
    protected static $taxonomy;
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var int
     */
    protected $parent = 0;

    /**
     * @return int
     */
    public function get_id() {
        return $this->id;
    }
    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
    /**
     * @return string
     */
    public function get_slug() {
        return $this->slug;
    }
    /**
     * @return string
     */
    public static function get_taxonomy() {
        return static::$taxonomy;
    }
    /**
     * @return string
     */
    public function get_description() {
        return $this->description;
    }
    /**
     * @return int
     */
    public function get_parent() {
        return $this->parent;
    }

    /**
     * @return static[]|null
     */
    public static function all(){
        return static::get();
    }
    /**
     * @param array $args
     * @return static[]|null
     */
    public static function get( $args = array() ){
        $args = wp_parse_args($args, array(
            'taxonomy'=> static::$taxonomy,
            'hide_empty' => true, //don't show empty terms
            'number' => 0, //get all
        ));
        $terms = get_terms($args);
        if(empty($terms)){
            return null;
        }
        $result = array();
        foreach($terms as $term){
            $result[] = new static($term->term_id);
        }
        return $result;
    }
}