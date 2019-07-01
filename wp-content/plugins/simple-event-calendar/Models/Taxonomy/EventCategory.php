<?php

namespace GDCalendar\Models\Taxonomy;

use GDCalendar\Core\Term;

class EventCategory extends Term
{

    protected static $taxonomy = 'event_category';
    /**
     * @var \WP_Term
     */
    public $term;
    public function __construct($id = NULL) {
        if (isset($id) && is_numeric($id)) {
            $term = get_term($id);
            if ($term instanceof \WP_Term) {
                $this->term = $term;
                $this->id = $term->term_id;
                $this->name = $term->name;
                $this->slug = $term->slug;
                $this->description = $term->description;
                $this->parent = $term->parent;
            }
        }
    }

}