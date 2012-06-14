<?php

	include_once '../../../../wp-load.php';

	function filter_where( $where = '')
	{
    	$str = addslashes($_GET['s']);

	    $where .= " AND post_title LIKE '%$str%' ";
	  
	    return $where;
	}

	add_filter('posts_where', 'filter_where');

	$ajax_query = new WP_Query('post_status=published');

	remove_filter('posts_where', 'filter_where');

	/* The LOOP */
	echo '<ul>';
	while ($ajax_query->have_posts()) : $ajax_query->the_post();
		?>
        <li>
		  <a href='<?php the_permalink(); ?>'><?php the_title(); ?></a>
		</li>
		<?php
		 
	endwhile;
    echo '</ul>';
