<?php
/*
  Plugin Name: Ajax search
  Plugin URI: http://sandorkovacs84.wordpress.com/
  Description: Search your posts and pages live
  Author: Sandor Kovacs
  Version: 1.2.2
  Author URI: http://sandorkovacs84.wordpress.com/
 */

class MXAjaxSearchWidget extends WP_Widget {

    //  Init ajax search widget
    function MXAjaxSearchWidget() {
        $widget_ops = array(
            'classname' => 'MXAjaxSearch',
            'description' => __('Simple Ajax Search')
        );

        $this->WP_Widget('MXAjaxSearchWidget', __('Simple ajax Search'), $widget_ops);

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('jquery');
        });
    }

    /**
     * Description : Build widget form ( Back-End)
     *
     * @param:   $instance - array 
     * 
     * */
    function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e('Title:') ?>
                <input class="widefat"
                       id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>"
                       type="text"
                       value="<?php echo attribute_escape($title); ?>" 
                       />
            </label>
        </p>
        <?php
    }

    /**
     * Description : Update widget form ( Back-End)
     *
     * @param :   $instance - array, $old_instance - array 
     * @return:   $instance - array
     * */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    /**
     * Description : Display AJax Widget  ( Front-End)
     *
     * @param :   $args - array,  $instance - array
     * 
     * */
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if (!empty($title))
            echo $before_title . $title . $after_title;;

        // WIDGET CODE GOES HERE
        $this->ajaxSearch();

        echo $after_widget;
    }

    /**
     * Description : Ajax(Instant) Search 
     *
     * */
    function ajaxSearch() {

        $plugin_url = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
        ?>
        <!-- Instant search form -->
        <div id='mx-ajax-search'>
            <input type='text' name='my-s' id='my-s' style="width:100%;"  
                   value='<?php echo (isset($_COOKIE['ajaxsearch_value']) && strlen($_COOKIE['ajaxsearch_value']) > 1) ? $_COOKIE['ajaxsearch_value'] : '' ?>'
                   placeholder="<?php _e('Search') ?>" />
        </div>

        <!-- Display search results -->
        <div id='results'>
        </div>

        <script>

        </script>

        <?php
    }

}

add_action('widgets_init', create_function('', 'return register_widget("MXAjaxSearchWidget");'));

/* * ******************************************************************************************
 * DEFINE AJAX SECTION                                                                      *
 * ***************************************************************************************** */
add_action('wp_footer', function() {
    if (isset($_COOKIE['ajaxsearch_value']) && strlen($_COOKIE['ajaxsearch_value']) > 1) {
        ?>
        <script>
            jQuery(document).ready(function($) {
                ajaxsearch_search();
            });
        </script>
        <?php
    }
});


add_action('wp_head', function() {
    ?>
    <script>   ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';</script>
    <?php
});

add_action('wp_footer', 'ajaxsearch_javascript');

function ajaxsearch_javascript() {
    ?>
    <script>
        jQuery(document).ready(function($) {

            $('#my-s').keyup(function() {
                ajaxsearch_search();
            });

        });


        function ajaxsearch_search() {
            jQuery(document).ready(function($) {

                /* 1. TRIMS THE SUBMITTED VALUE LEFT AND RIGHT AND THE SEARCH STRING SHOULD HAVE AT LEAST 2 CHARS. */
                if (jQuery.trim(jQuery("#my-s").val()).length > 1)
                {

                    var data = {
                        'action': 'ajaxsearch',
                        's': $('#my-s').val()
                    };
                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    $.post(ajaxurl, data, function(result) {
                        $('#results').html(result);
                    });
                }
                else
                {
                    console.log('delete cookie');
                    /* 2. CLEARS THE RESULTS BOX
                     ONLY NEEDED IF YOU DELETE THE VALUES IN THE SEARCH BOX TO MAKE IT EMPTY */
                    jQuery('#results').html('');
                    // DELETE COOKIE
                    delete_cookie('ajaxsearch_value');
                }
            });

        }

        function createCookie(name, value, days) {
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                var expires = "; expires=" + date.toGMTString();
            }
            else
                var expires = "";
            document.cookie = name + "=" + value + expires + "; path=/";
        }

        function readCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ')
                    c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0)
                    return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        function delete_cookie(name) {
            createCookie(name, "", -1);
        }
    </script>
    <?php
}

add_action('wp_ajax_nopriv_ajaxsearch', 'ajaxsearch_callback');
add_action('wp_ajax_ajaxsearch', 'ajaxsearch_callback');

function ajaxsearch_callback() {
    $str = filter_input(INPUT_POST, 's');
    setcookie('ajaxsearch_value', $str, time() + 60 * 60 * 24, "/"); // save searched value in cookie

    function filter_where($where = '') {
        $str = filter_input(INPUT_POST, 's');
        $where .= " AND post_title LIKE '%$str%' ";
        return $where;
    }

    add_filter('posts_where', 'filter_where');
    $ajax_query = new WP_Query('post_status=publish');
    remove_filter('posts_where', 'filter_where');

    /* The LOOP */
    echo '<ul id="simple-ajax-search-result-list">';
    while ($ajax_query->have_posts()) : $ajax_query->the_post();
        ?>
        <li>
            <a href='<?php the_permalink(); ?>'><?php echo strtoupper(str_replace($str, '<strong>' . $str . '</strong>', strtolower(get_the_title()))); ?></a>
        </li>
        <?php
    endwhile;
    echo '</ul>';


    die(); // this is required to return a proper result
}
