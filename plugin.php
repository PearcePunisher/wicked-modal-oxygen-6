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

/**
 * Register Element Studio save locations for Breakdance and Oxygen 6 (Beta).
 * Works when either builder is active. Safe to call multiple times.
 */
function register_element_studio_locations_for_breakdance(): void
{
    if (!\function_exists('Breakdance\\ElementStudio\\registerSaveLocation')) {
        return;
    }

    // Prefer helper to compute relative path if available
    $baseRel = \function_exists('Breakdance\\Util\\getDirectoryPathRelativeToPluginFolder')
        ? \Breakdance\Util\getDirectoryPathRelativeToPluginFolder(__DIR__)
        : null;

    $elements = $baseRel ? $baseRel . '/elements' : __DIR__ . '/elements';
    $macros   = $baseRel ? $baseRel . '/macros'   : __DIR__ . '/macros';
    $presets  = $baseRel ? $baseRel . '/presets'  : __DIR__ . '/presets';

    \Breakdance\ElementStudio\registerSaveLocation($elements, 'WickedModal', 'element', 'Wicked Modal Elements', false);
    \Breakdance\ElementStudio\registerSaveLocation($macros,   'WickedModal', 'macro',   'Wicked Modal Macros',   false);
    \Breakdance\ElementStudio\registerSaveLocation($presets,  'WickedModal', 'preset',  'Wicked Modal Presets',  false);
}

function register_element_studio_locations_for_oxygen(): void
{
    if (!\function_exists('Oxygen\\ElementStudio\\registerSaveLocation')) {
        return;
    }

    // Oxygen 6 Beta likely ships a similar Util helper; fall back to absolute paths if not present
    $baseRel = \function_exists('Oxygen\\Util\\getDirectoryPathRelativeToPluginFolder')
        ? \Oxygen\Util\getDirectoryPathRelativeToPluginFolder(__DIR__)
        : null;

    $elements = $baseRel ? $baseRel . '/elements' : __DIR__ . '/elements';
    $macros   = $baseRel ? $baseRel . '/macros'   : __DIR__ . '/macros';
    $presets  = $baseRel ? $baseRel . '/presets'  : __DIR__ . '/presets';

    \Oxygen\ElementStudio\registerSaveLocation($elements, 'WickedModal', 'element', 'Wicked Modal Elements', false);
    \Oxygen\ElementStudio\registerSaveLocation($macros,   'WickedModal', 'macro',   'Wicked Modal Macros',   false);
    \Oxygen\ElementStudio\registerSaveLocation($presets,  'WickedModal', 'preset',  'Wicked Modal Presets',  false);
}

// Register at the appropriate time for each builder, plus a conservative fallback
\add_action('breakdance_loaded', __NAMESPACE__ . '\\register_element_studio_locations_for_breakdance', 9);
\add_action('oxygen_loaded',     __NAMESPACE__ . '\\register_element_studio_locations_for_oxygen',     9);
\add_action('plugins_loaded', function () {
    // Fallback if specific hooks didn't fire
    register_element_studio_locations_for_breakdance();
    register_element_studio_locations_for_oxygen();
}, 11);

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

