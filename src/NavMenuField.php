<?php

namespace Accell\AcfNavMenu;

class NavMenuField extends \acf_field
{
    use Concerns\Asset;

    /**
     * The field name.
     *
     * @var string
     */
    public $name = 'nav_menu';

    /**
     * The field label.
     *
     * @var string
     */
    public $label = 'Nav Menu';

    /**
     * The field category.
     *
     * @var string
     */
    public $category = 'relational';

    /**
     * The field defaults.
     *
     * @var array
     */
    public $defaults = [
        'return_format' => 'id',
        'allow_null'    => 1,
        'container'     => 'div',
    ];

    /**
     * Create a new nav menu field instance.
     *
     * @param string $uri
     * @param string $path
     *
     * @return void
     */
    public function __construct($uri, $path)
    {
        $this->uri  = $uri;
        $this->path = $path;

        parent::__construct();
    }

    /**
     * Create the HTML interface for your field.
     *
     * @param array $field
     *
     * @return void
     */
    public function render_field($field)
    {
        $navMenus = $this->get_nav_menus($field['allow_null']);

        if (empty($navMenus)) {
            return;
        }

        echo sprintf(
            '<select class="%s" id="%s" name="%s">',
            $field['id'],
            $field['class'],
            $field['name']
        );

        foreach ($navMenus as $id => $name) {
            echo sprintf(
                '<option value="%s" %s>',
                esc_attr($id),
                selected($field['value'], $id)
            );

            echo esc_html($name);
            echo '</option>';
        }
    }

    /**
     * Create extra settings for your field. These are visible when editing a field.
     *
     * @param array $field
     *
     * @return void
     */
    public function render_field_settings($field)
    {
        acf_render_field_setting($field, [
            'label'        => 'Return Format',
            'instructions' => 'Specify the returned value on front end',
            'type'         => 'radio',
            'name'         => 'return_format',
            'layout'       => 'horizontal',
            'choices' => [
                'id'     => 'ID',
                'object' => 'Object',
                'menu'   => 'HTML',
            ],
        ]);

        acf_render_field_setting($field, [
            'label'        => 'Container',
            'instructions' => 'What to wrap the menu list with when returning HTML',
            'type'         => 'select',
            'name'         => 'container',
            'choices'      => $this->get_allowed_nav_container_tags(),
        ]);

        acf_render_field_setting($field, [
            'label' => 'Allow Null?',
            'type'  => 'true_false',
            'name'  => 'allow_null',
            'ui'    => 1,
            'choices' => [
                1  => 'Yes',
                0  => 'No',
            ],
        ]);
    }

    /**
     * This filter is applied to the $value after it is loaded from the database and
     * before it is returned to the template.
     *
     * @param mixed $value
     * @param mixed $post_id
     * @param array $field
     *
     * @return mixed
     */
    public function format_value($value, $post_id, $field)
    {
        if (empty($value)) {
            return false;
        }

        // Check save format
        if ($field['return_format'] === 'object') {
            $wpMenuObject = wp_get_nav_menu_object($value);

            if (empty($wpMenuObject)) {
                return false;
            }

            $menuObject = new \stdClass;

            $menuObject->ID    = $wpMenuObject->term_id;
            $menuObject->name  = $wpMenuObject->name;
            $menuObject->slug  = $wpMenuObject->slug;
            $menuObject->count = $wpMenuObject->count;

            return $menuObject;
        } elseif ($field['return_format'] === 'menu') {
            ob_start();

            wp_nav_menu([
                'menu'      => $value,
                'container' => $field['container'],
            ]);

            return ob_get_clean();
        }

        // Just return the ID
        return $value;
    }

    /**
     * This filter is used to perform validation on the value prior to saving.
     *
     * @param boolean $valid
     * @param mixed   $value
     * @param array   $field
     * @param array   $input
     *
     * @return boolean
     */
    public function validate_value($valid, $value, $field, $input)
    {
        return wp_get_nav_menu_object($value)
            ? $valid : 'The navigation menu selected is not valid.';
    }

    /**
     * The field value after loading from the database.
     *
     * @param  mixed $value
     * @param  int   $post_id
     * @param  array $field
     * @return mixed
     */
    public function load_value($value, $post_id, $field)
    {
        return $value;
    }

    /**
     * This filter is applied to the $value before it is saved in the database.
     *
     * @param mixed $value
     * @param mixed $post_id
     * @param array $field
     *
     * @return mixed
     */
    public function update_value($value, $post_id, $field)
    {
        if (!wp_get_nav_menu_object($value)) {
            return;
        }

        return $value;
    }

    /**
     * The field after loading from the database.
     *
     * @param  array $field
     * @return array
     */
    public function load_field($field)
    {
        return $field;
    }

    /**
     * The field before saving to the database.
     *
     * @param  array $field
     * @return array
     */
    public function update_field($field)
    {
        return $field;
    }

    /**
     * This action is called in the admin_enqueue_scripts action on the edit screen where
     * your field is created.
     *
     * @return void
     */
    public function input_admin_enqueue_scripts()
    {
        wp_enqueue_script('acf-' . $this->name, $this->asset('/js/field.js'), ['acf-input'], null);
        wp_enqueue_style('acf-' . $this->name, $this->asset('/css/field.css'), [], null);
    }

    /**
     * The assets enqueued when creating a field group.
     *
     * @return void
     */
    public function field_group_admin_enqueue_scripts()
    {
        $this->input_admin_enqueue_scripts();
    }

    /**
     * Get the allowed wrapper tags for use with wp_nav_menu().
     *
     * @return array An array of allowed wrapper tags.
     */
    public function get_allowed_nav_container_tags()
    {
        $tags    = apply_filters('wp_nav_menu_container_allowedtags', ['div', 'nav']);
        $allowed = ['0' => 'None'];

        foreach ($tags as $tag) {
            $allowed[$tag] = $tag;
        }

        return $allowed;
    }

    /**
     * Gets a list of Nav Menus indexed by their Nav Menu IDs.
     *
     * @param bool $allowNull If true, prepends the null option.
     *
     * @return array An array of Nav Menus indexed by their Nav Menu IDs.
     */
    public function get_nav_menus($allowNull = false)
    {
        $navs  = get_terms('nav_menu', ['hide_empty' => false]);
        $menus = [];

        if ($allowNull) {
            $menus[''] = ' - Select - ';
        }

        foreach ($navs as $nav) {
            $menus[$nav->term_id] = $nav->name;
        }

        return $menus;
    }
}
