<?php
/*
Plugin Name: Ajax search
Plugin URI: http://sandorkovacs84.wordpress.com/
Description: Search your posts and pages live
Author: Sandor Kovacs
Version: 1.1.1
Author URI: http://sandorkovacs84.wordpress.com/
*/
 
 
class MXAjaxSearchWidget extends WP_Widget
{

  //  Init ajax search widget
  function MXAjaxSearchWidget()
  {
    $widget_ops = array(
      'classname'   => 'MXAjaxSearch', 
      'description' => __('Simple Ajax Search')
    );

    $this->WP_Widget('MXAjaxSearchWidget', __('Simple ajax Search'), $widget_ops);

    add_action('wp_enqueue_scripts',  function () {
         wp_enqueue_script( 'jquery' );
    });

  }
 
  /**
   * Description : Build widget form ( Back-End)
   *
   * @param:   $instance - array 
   * 
   **/
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title    = $instance['title'];
?>
  <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">
      <?php _e('Title:') ?>
      <input class="widefat"
             id="<?php echo $this->get_field_id('title'); ?>"
             name="<?php echo $this->get_field_name('title'); ?>"
             type="text"
             value="<?php echo attribute_escape($title); ?>" />
      </label>
  </p>
<?php
  }
 
  /**
   * Description : Update widget form ( Back-End)
   *
   * @param :   $instance - array, $old_instance - array 
   * @return:   $instance - array
   **/
  function update($new_instance, $old_instance)
  {
    $instance          = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  /**
   * Description : Display AJax Widget  ( Front-End)
   *
   * @param :   $args - array,  $instance - array
   * 
   **/ 
  function widget($args, $instance)
  {
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
   **/ 
  function ajaxSearch()
  {
    
    $plugin_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
    ?>
    <!-- Instant search form -->
    <div id='mx-ajax-search'>
      <input type='text' name='my-s' id='my-s' style="width:100%;"/>
    </div>
    
    <!-- Display search results -->
    <div id='results'>
    </div>
    
    <script>
      jQuery('#my-s').keyup(function() {
        jQuery.ajax({
          'type':   'get',
          'url' :   '<?php echo $plugin_url.'ajax/simple-search.php'; ?>',
          'data':   's=' + jQuery('#my-s').val(),
          'success': function (result) {
            jQuery('#results').html(result);
          }
        })
      })
    </script>
    
    <?php
  }


 
}
add_action( 'widgets_init', create_function('', 'return register_widget("MXAjaxSearchWidget");') );

