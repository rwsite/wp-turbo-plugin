<?php
/**
 * Template Name: Yandex Turbo RSS
 */

/** @var $post WP_Post */
global $post;

/** @var WP_Post[] $posts */
$posts = get_posts([
                       'numberposts'    => -1,
                       'posts_per_page' => get_option('turbo_options', ['per_page' => '500'])['per_page'],
                       'paged'          => get_query_var('page'),
                   ]);

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
?>
<rss
        xmlns:yandex="http://news.yandex.ru"
        xmlns:media="http://search.yahoo.com/mrss/"
        xmlns:turbo="http://turbo.yandex.ru"
        version="2.0"
>
    <channel>
        <title><?php bloginfo( 'name' ); ?></title>
        <link><?php bloginfo( 'url' ); ?></link>
        <description><?php bloginfo( 'description' ); ?></description>
        <language><?php bloginfo( 'language' ); ?></language>
        
        <yandex:related type="infinity">
            <?php query_posts('orderby=rand&showposts=5');
                if (have_posts()) : while (have_posts()) : the_post();
                ?>
                <link url="<?php the_permalink(); ?>"><?php the_title(); ?></link>
                <?php endwhile;
            endif; ?>
        </yandex:related>
        
        <?php
        $options = get_option('turbo_options');
        echo (isset($options['turbo_metrika_code'])) ? '<turbo:analytics type="Yandex" id="' . $options['turbo_metrika_code'] . '"></turbo:analytics>' : '';
        echo (isset($options['turbo_google_code'])) ? '<turbo:analytics type="Google" id="' . $options['turbo_google_code'] . '"></turbo:analytics>' : '';
        echo (isset($options['turbo_mailru_code'])) ? '<turbo:analytics type="MailRu" id="' . $options['turbo_mailru_code'] . '"></turbo:analytics>' : '';
        echo (isset($options['turbo_rambler_code'])) ? '<turbo:analytics type="Rambler" id="' . $options['turbo_rambler_code'] . '"></turbo:analytics>' : '';//*/
        
        $rlinks = $options['turbo_related_html'] ?? '';
        
        foreach ($posts as $post):
            ?>
            <item turbo="true">
                <title><?php echo $post->post_title; ?></title>
                <link><?php echo get_post_permalink($post); ?></link>
                <author><?php the_author_meta('display_name', $post->post_author); ?></author>
                <category><?php the_category(', ', 'single', $post->ID); ?></category>
                <pubDate><?php echo get_feed_build_date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
                <turbo:content><![CDATA[<?php the_content(); ?>]]></turbo:content>
                <?php echo !empty($rlinks) ? '<yandex:related>' . $rlinks . '</yandex:related>' : '' ?>
            </item>
        <?php
        endforeach;
        ?>
    </channel>
</rss>