<?php
include_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';

$str = addslashes($_GET['s']);
function filter_where($where = '') {
    $str = addslashes($_GET['s']);

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
