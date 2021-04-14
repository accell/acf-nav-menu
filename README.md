# ACF Nav Menu Field

A navigation menu field for ACF.

## Requirements

- [ACF](https://www.advancedcustomfields.com/pro/) >= 5.0
- [PHP](http://php.net/manual/en/install.php) >= 7.2
- [Composer](https://getcomposer.org/download/)

## Installation

### Bedrock

Install via Composer:

```bash
$ composer require accell/acf-nav-menu
```

### Manual

Download the release `.zip` and install into `wp-content/plugins`.

## Usage

Calling the field will return an ID, object, or raw HTML depending on how you've configured the field.

### ACF Composer

If you are on Sage 10 and using [Log1x's ACF Composer](https://github.com/log1x/acf-composer) package:

```php
$field
  ->addField('my_nav_field', 'nav_menu')
    ->setConfig('return_format' => 'id')
    ->setConfig('allow_null' => 0)
    ->setConfig('container' => 'div');
```

## Bug Reports

If you discover a bug in ACF Nav Menu, please [open an issue](https://github.com/accell/acf-nav-menu/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Nav Menu is provided under the [MIT License](LICENSE.md).
