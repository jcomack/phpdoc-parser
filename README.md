# PHPDoc Parser

PHPDoc Parser is the parser based off of the WP-Parser used for creating the new code reference at [developer.wordpress.org](https://developer.wordpress.org/reference). This version parses the inline documentation for the various Yoast plugins and produces custom post type entries in WordPress.

## Requirements
* PHP 7+ 
* [Composer](https://getcomposer.org/)
* [WP CLI](https://wp-cli.org/)

Clone the repository into your WordPress plugins directory:

```bash
git clone https://github.com/jcomack/phpdoc-parser.git
```

After that install the dependencies using Composer in the parser directory:

```bash
composer install
```

## Running

The parser should be run via [WP-CLI](https://wp-cli.org/). If you have a WordPress environment configured with WordPress and WP-CLI, ensure that you navigate to the proper directory on your machine containing your environment. Usually WP-CLI is run over SSH.

Once you're in a compatible CLI environment, navigate via the `cd` command (or similar) to your WordPress environment's directory. Usually navigating to your `wp-content` directory should suffice.


Before you can begin parsing, ensure that you have activated the parser by running:

    wp plugin activate phpdoc-parser

Then, when you're ready to parse your first plugin, run the following command:

    wp parser create /<directory_where_plugin_is_located>/ --user=admin

If you're running a multisite environment, please ensure you add the following parameter to the command to ensure all parsed documentation is placed in the correct database tables / sub-site:

    --url="http://sub.site.test"

