<?php

/**
 * Plugin Name: ACF: Nav Menu Field
 * Plugin URI: https://github.com/accell/acf-nav-menu
 * Description: ACF field that allows you to select a nav menu.
 * Version: 1.0.0
 * Author: accell
 * Author URI: https://github.com/accell
 */

namespace Accell\AcfNavMenu;

add_action('after_setup_theme', new class
{
    /**
     * The asset public path.
     *
     * @var string
     */
    protected $assetPath = 'public';

    /**
     * Invoke the plugin.
     *
     * @return void
     */
    public function __invoke()
    {
        if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
            require_once $composer;
        }

        $this->register();
    }

    /**
     * Register the Nav Menu field with ACF.
     *
     * @return void
     */
    protected function register()
    {
        foreach (['acf/include_field_types', 'acf/register_fields'] as $hook) {
            add_filter($hook, function () {
                return new NavMenuField(
                    plugin_dir_url(__FILE__) . $this->assetPath,
                    plugin_dir_path(__FILE__) . $this->assetPath
                );
            });
        }
    }
});
