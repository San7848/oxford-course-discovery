<?php
// Filter system with strategy pattern implementation
interface Oxford_Filter_Interface {
    public function apply(array $args, $value);
    public function get_name();
    public function get_label();
}

// Base filter class
abstract class Oxford_Base_Filter implements Oxford_Filter_Interface {
    protected $name;
    protected $label;
    
    public function get_name() {
        return $this->name;
    }
    
    public function get_label() {
        return $this->label;
    }
}

// Text Search Filter
class Oxford_Text_Search_Filter extends Oxford_Base_Filter {
    public function __construct() {
        $this->name = 'text_search';
        $this->label = 'Search Courses';
    }
    
    public function apply(array $args, $value) {
        if (!empty($value)) {
            $args['s'] = sanitize_text_field($value);
        }
        return $args;
    }
}

// Provider Filter
class Oxford_Provider_Filter extends Oxford_Base_Filter {
    public function __construct() {
        $this->name = 'provider';
        $this->label = 'Providers';
    }
    
    public function apply(array $args, $value) {
        if (!empty($value) && is_array($value)) {
            $provider_ids = array_map('intval', $value);
            $args['meta_query'][] = [
                'key' => 'providers',
                'value' => $provider_ids,
                'compare' => 'IN',
                'type' => 'NUMERIC'
            ];
        }
        return $args;
    }
}

// Location Filter
class Oxford_Location_Filter extends Oxford_Base_Filter {
    public function apply(array $args, $value) {
        if (!empty($value)) {
            $args['meta_query'][] = [
                'key' => 'location',
                'value' => is_array($value) ? implode(',', $value) : $value,
                'compare' => 'LIKE'
            ];
        }
        return $args;
    }
}



// Date Filter
class Oxford_Date_Filter extends Oxford_Base_Filter {
    public function apply(array $args, $value) {
        if (!empty($value) && is_array($value)) {
            $args['meta_query'][] = [
                'key' => 'start_date',
                'value' => $value,
                'compare' => 'IN'
            ];
        }
        return $args;
    }
}



// Category Filter
class Oxford_Category_Filter extends Oxford_Base_Filter {
    public function __construct() {
        $this->name = 'category';
        $this->label = 'Categories';
    }
    
    public function apply(array $args, $value) {
        if (!empty($value) && is_array($value)) {
            $args['tax_query'][] = [
                'taxonomy' => 'course_category',
                'field' => 'term_id',
                'terms' => array_map('intval', $value),
                'operator' => 'IN'
            ];
        }
        return $args;
    }
}

// Filter Manager
class Oxford_Filter_Manager {
    private $filters = [];
    
    public function __construct() {
        $this->register_filters();
    }
    
    private function register_filters() {
        $this->add_filter(new Oxford_Text_Search_Filter());
        $this->add_filter(new Oxford_Provider_Filter());
        $this->add_filter(new Oxford_Location_Filter());
        $this->add_filter(new Oxford_Date_Filter());
        $this->add_filter(new Oxford_Category_Filter());
    }
    
    public function add_filter(Oxford_Filter_Interface $filter) {
        $this->filters[$filter->get_name()] = $filter;
    }
    
    public function apply_filters($filters_data) {
    $args = [
        'post_type' => 'course',
        'posts_per_page' => -1,
        'meta_query' => ['relation' => 'AND'],
        'tax_query' => [],
    ];

    // Text search
    if (!empty($filters_data['text_search'])) {
        $args['s'] = sanitize_text_field($filters_data['text_search']);
    }

    // Apply other filters
    foreach ($filters_data as $filter_name => $filter_value) {
        if (isset($this->filters[$filter_name]) && !empty($filter_value)) {
            $args = $this->filters[$filter_name]->apply($args, $filter_value);
        }
    }

    // Cleanup
    if (count($args['meta_query']) === 1) {
        unset($args['meta_query']);
    }

    if (empty($args['tax_query'])) {
        unset($args['tax_query']);
    }

    return $args;
}

    
    public function get_filter($name) {
        return $this->filters[$name] ?? null;
    }
    
    public function get_all_filters() {
        return $this->filters;
    }
}


// Initialize filter system
add_action('init', 'oxford_init_filter_system');
function oxford_init_filter_system() {
    global $oxford_filter_manager;
    $oxford_filter_manager = new Oxford_Filter_Manager();
}