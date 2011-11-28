<?php

include_once '../../../../wp-load.php';

$str = addslashes($_POST['s']);

function filter_where( $where = '')
{
  global $str;
  
  $where .= " AND post_title LIKE '%$str%' ";
  
  return $where;
}

 
add_filter('posts_where', 'filter_where');

$ajax_query = new WP_Query('post_status=published');

remove_filter('posts_where', 'filter_where');

while ($ajax_query->have_posts()) : $ajax_query->the_post();
?>
  <a href='<?php the_permalink(); ?>'><?php the_title(); ?></a>
<?php
  echo '<br/>';
 
endwhile;

