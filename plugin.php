<?php

/**
 * Plugin Name: Wicked Modal for Oxygen 6
 * Plugin URI: https://oxygenbuilder.com/
 * Description: Adds a fully accessible, customizable modal element for Oxygen 6/Breakdance via Element Studio.
 * Author: Wicked Modal
 * Author URI: https://oxygenbuilder.com/
 * License: GPLv2
 * Text Domain: wicked-modal
 * Domain Path: /languages/
 * Version: 0.1.0
 */

namespace WickedModal;

use function Breakdance\Util\getDirectoryPathRelativeToPluginFolder;

\add_action('breakdance_loaded', function () {
    \Breakdance\ElementStudio\registerSaveLocation(
        getDirectoryPathRelativeToPluginFolder(__DIR__) . '/elements',
        'WickedModal',
        'element',
        'Wicked Modal Elements',
        false
    );

    \Breakdance\ElementStudio\registerSaveLocation(
        getDirectoryPathRelativeToPluginFolder(__DIR__) . '/macros',
        'WickedModal',
        'macro',
        'Wicked Modal Macros',
        false,
    );

    \Breakdance\ElementStudio\registerSaveLocation(
        getDirectoryPathRelativeToPluginFolder(__DIR__) . '/presets',
        'WickedModal',
        'preset',
        'Wicked Modal Presets',
        false,
    );
},
    // register elements before loading them
    9
);

// Enqueue front-end assets for modal functionality and base styles
\add_action('wp_enqueue_scripts', function () {
    $ver = '0.1.0';
    $base = \plugin_dir_url(__FILE__);

    // Styles
    \wp_enqueue_style(
        'wicked-modal-styles',
        $base . 'assets/css/wicked-modal.css',
        [],
        $ver
    );

    // Script (vanilla JS for performance and no dependency issues)
    \wp_enqueue_script(
        'wicked-modal-script',
        $base . 'assets/js/wicked-modal.js',
        [],
        $ver,
        true
    );
});

