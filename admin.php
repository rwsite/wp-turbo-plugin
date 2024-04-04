<?php
/**
 * Admin page template
 */
?>

<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <p>Ссылка на RSS канал для Yandex Turbo Pages:
        <strong>
        <a href="<?= get_site_url(null, '/feed/turbo/'); ?>" target="_blank"> <?= get_site_url(null, '/feed/turbo/');?></a>
        </strong>
        Используйте параметр <code>?page={num}</code> для получения пагинации.
    </p>
    <form id="turbo_options" action="<?= admin_url("options.php") ?>" method="post">
        <?php
        settings_fields('turbo_options');
        do_settings_sections('turbo');
        submit_button('Сохранить настройки', 'primary', 'turbo_options_submit');
        ?>
    </form>
    
    <h2>Если у Вас ошибка 404 при обращении к /feed/turbo/ после активации плагина:</h2>
    <p>Возможно ошибка из-за пересохранения постоянных ссылок, Вы можете сбросить правила перезаписи.
        Если Вы не уверены в том, что делаете - <strong>обязательно сделайте резервное копирование перед нажатием ссылки "Cбросить кеш роутов"</strong>.
        Нажатие на ссылку выполнит код: <code>global $wp_rewrite; $wp_rewrite->flush_rules();</code></p>
    <a href="<?= admin_url('options-general.php?page=turbo&flush_rules=true') ?>" class="button button-secondary">Cбросить кеш роутов</a>
    
    
    <h2>Сбросить кеш всех фидов</h2>
    <p>Нажатие на ссылку сбросит кеш.</p>
    
    <a href="<?= admin_url('options-general.php?page=turbo&clear_feeds_cache=true') ?>" class="button button-secondary">Сбросить кеш</a>
    
    <?php
    
    if (isset($_GET['flush_rules']) && 'true' === $_GET['flush_rules']) {
        \turbo\WP_Turbo_Plugin::flush_rules();
        echo "<div class='alert alert-info'><p>Сброс кеша роутов выполнен.</p></div>";
    }
    
    ## Очистка кэша всех фидов в WordPress
    if( isset($_GET['clear_feeds_cache']) ){
        global $wpdb;
        $cleared = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient%\_feed\_%'" );
        if($cleared) {
            echo "<div class='alert alert-info'><p>Сброс выполнен.</p></div>";
        } else {
            echo "<div class='alert alert-info'><p>Ошибка сброса.</p></div>";
        }
    }
    ?>
</div>
