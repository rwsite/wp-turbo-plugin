<?php
/**
 * Plugin Name: Yandex turbo feed
 * Description: Simple Yandex turbo feed. WordPress plugin.
 * Version: 1.0.1
 * Text Domain: turbo
 * Domain Path: /languages
 * Author: Aleksey Tikhomirov
 *
 * Requires at least: 4.6
 * Tested up to: 6.8
 * Requires PHP: 8.0+
 */

namespace turbo;

class WP_Turbo_Plugin
{
    public string $file;
    public string $dir;
    
    public function __construct()
    {
        $this->file = __FILE__;
        $this->dir = wp_normalize_path(dirname($this->file));
    }
    public function add_actions()
    {
        if(is_admin()) {
            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_init', [ $this, 'admin_init' ]);
            
            register_activation_hook( $this->file, function () {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                if ('ru' !== get_option('rss_language')) {
                    update_option('rss_language', 'ru');
                }
                static::flush_rules();
            });
            
            register_deactivation_hook( $this->file, function () {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                static::flush_rules();
            });
        }
        
        add_action('init', function (){
            add_feed('turbo', function (){
                load_template(realpath(plugin_dir_path( __FILE__ ) . '/template.php'));
            });
        });
    }
    
    
    public function admin_init() {
        add_filter('plugin_action_links', [$this, 'settings_link'], 2, 2);
        $this->settings();
    }
    
    public function admin_menu()
    {
        add_submenu_page('options-general.php',
            'Настройки "Yandex Turbo"',
            'Yandex Turbo',
            'manage_options',
            'turbo',
            function () {
                load_template($this->dir.'/admin.php', true, []);
            }
        );
    }
    
    public function settings_link($actions, $file) {
        if (str_contains($file, 'turbo')) {
            $actions['settings'] = '<a href="'.admin_url('options-general.php?page=turbo').'">' .__('Settings','turbo') .'</a>';
        }
        return $actions;
    }

    public function settings():void
    {
        register_setting('turbo_options', 'turbo_options',
            [$this, 'turbo_options_validate']);

        add_settings_section('turbo_counters','Введите ID счетчиков, для установки их в RSS канал.', '', 'turbo');
        add_settings_field('turbo_metrika_code', 'ID Яндекс Метрики:',
            [$this, 'turbo_metrika_field'], 'turbo', 'turbo_counters');
        add_settings_field('turbo_google_code', 'ID Google Analytics:',
            [$this, 'turbo_google_field'], 'turbo', 'turbo_counters');

        add_settings_section('turbo_related',
            'Блок со ссылками на другие новости источника: '
            .htmlspecialchars('<yandex:related>'),
            [$this, 'turbo_related_html'], 'turbo');
        add_settings_field('turbo_related_html', 'HTML код (до 10 ссылок):',
            [$this, 'turbo_related_field'], 'turbo', 'turbo_related');
        add_settings_field('turbo_per_page', 'Записей на страницу:',
            [$this, 'turbo_per_page'], 'turbo', 'turbo_related');
    }
    
    public function turbo_options_validate($input)
    {
        $fields = [ 'turbo_metrika_code', 'turbo_google_code', 'turbo_related_field', 'turbo_per_page' ];
        foreach ($fields as $field){
            if (isset($input[$field])) {
                $input[$field] = wp_kses_post($input[$field]);
            }
        }
        
        return $input;
    }
    
    public function turbo_related_html() {
        ?>
        Вы можете разместить ссылки на другие ресурсы или настроить отображение бесконечной ленты статей.
        Такие ссылки будут располагаться внизу Турбо‑страницы. Чтобы добавить ссылки в любом месте страницы, используйте
        <a href="https://yandex.ru/dev/turbo/doc/rss/elements/read-also.html" target="_blank">Блок ссылок на дополнительные материалы.</a>
        <h4>Блок со ссылками на другие страницы</h4>
        Содержит элемент link. Максимальное количество ссылок — 30.
        Чтобы добавить к статье изображение, используйте атрибут img с URL, по которому доступна иллюстрация.
        Оборачиваемый в элемент link текст не должен содержать HTML-элементы.
        
        <pre><code><?php echo htmlspecialchars('<link url="http://www.example.com/page.html" img="http://www.example.com/image.png">Текст ссылки</link>'); ?></code></pre>
        
        <?php
    }
    
    public function turbo_metrika_field() {
        $options = get_option('turbo_options');
        $metrika = $options['turbo_metrika_code'] ?? '';
        $metrika = esc_textarea($metrika);
        ?>
        <input type="text" id="turbo_metrika_code" name="turbo_options[turbo_metrika_code]" value="<?php echo $metrika; ?>" class="text">
        <?php
    }
    
    public function turbo_google_field() {
        $options = get_option('turbo_options');
        $google = $options['turbo_google_code'] ?? '';
        $google = esc_textarea($google);
        ?>
        <input type="text" id="turbo_google_code" name="turbo_options[turbo_google_code]" value="<?php echo $google; ?>" class="text">
        <?php
    }
    
    public function turbo_related_field() {
        $options = get_option('turbo_options');
        $related_html = $options['turbo_related_html'] ?? '';
        $related_html = esc_textarea($related_html);
        ?>
        <textarea id="turbo_related_html" name="turbo_options[turbo_related_html]" cols="50" rows="5" class="large-text"><?php echo $related_html; ?></textarea>
        <?php
    }
    
    public function turbo_per_page() {
        $value = esc_textarea(get_option('turbo_options', ['per_page' => '500'])['per_page']);
        ?>
        <input type="text" id="turbo_per_page" name="turbo_options[per_page]" value="<?php echo $value; ?>" class="text">
        Максимально 1000!
        <?php
    }
    
    public static function flush_rules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}

(new WP_Turbo_Plugin())->add_actions();