<?php
/**
 * SSR for Wicked Modal
 * Renders overlay + modal container with slots for inner content.
 * Uses data attributes consumed by wicked-modal.js
 */

$props = $propertiesData['content']['content'] ?? [];
$design = $propertiesData['design']['design'] ?? [];

$modal_id = $props['modal_id'] ?? ('wicked-modal-' . (\rand(1000,9999)));
$include_close = (bool)($props['include_close'] ?? true);
$render_open_button = (bool)($props['render_open_button'] ?? true);
$open_label = $props['open_label'] ?? 'Open Modal';

$delay = (int)($props['delay'] ?? 0);
$time_on_page = (int)($props['time_on_page'] ?? 0);
$scroll_depth = (int)($props['scroll_depth'] ?? 0);
$exit_intent = !empty($props['exit_intent']);
$trigger_selector = trim($props['trigger_selector'] ?? '');
$open_on_hash = !empty($props['open_on_hash']);
$open_on_param = trim($props['open_on_param'] ?? '');
$inactivity = (int)($props['inactivity'] ?? 0);
$once_per_session = !empty($props['once_per_session']);

$width = trim($props['width'] ?? '');
$size_preset = $props['size_preset'] ?? 'medium';
$vertical_align = $props['vertical_align'] ?? 'center';
$lock_scroll = !empty($props['lock_scroll']);

$animation = $props['animation'] ?? 'none';
$anim_duration = (int)($props['anim_duration'] ?? 200);
$aria_label = $props['aria_label'] ?? 'Dialog';
$close_on_overlay = array_key_exists('close_on_overlay', $props) ? (bool)$props['close_on_overlay'] : true;
$close_on_esc = array_key_exists('close_on_esc', $props) ? (bool)$props['close_on_esc'] : true;
$frequency_mode = $props['frequency_mode'] ?? 'always';
$frequency_days = (int)($props['frequency_days'] ?? 7);

$animClass = '';
if ($animation === 'fade') $animClass = 'wm-anim-fade';
elseif ($animation === 'scale') $animClass = 'wm-anim-scale';
elseif ($animation === 'slide-up') $animClass = 'wm-anim-slide-up';

$overlayStyle = '';
if (!empty($design['overlay_bg'])) {
  $overlayBg = is_string($design['overlay_bg']) ? $design['overlay_bg'] : '';
  if ($overlayBg) { $overlayStyle .= "--wicked-modal-overlay-bg: {$overlayBg};"; }
}

$modalStyle = '';
// size preset mapping (custom wins if provided)
if ($size_preset !== 'custom') {
  $presetWidth = [
    'small' => '480px',
    'medium' => '640px',
    'large' => '800px',
    'xl' => '960px',
    'full' => '100vw'
  ][$size_preset] ?? '640px';
  $modalStyle .= "--wm-width: {$presetWidth};";
}
if ($width) { $modalStyle = "--wm-width: {$width};"; }
if ($anim_duration) { $modalStyle .= "--wm-anim-dur: {$anim_duration}ms;"; }

// Map design controls to CSS variables / inline styles
if (!empty($design['modal_bg']) && is_string($design['modal_bg'])) {
  $modalStyle .= "--wicked-modal-bg: {$design['modal_bg']};";
}
if (!empty($design['modal_radius']) && is_string($design['modal_radius'])) {
  $modalStyle .= "--wicked-modal-radius: {$design['modal_radius']};";
}
if (!empty($design['modal_shadow']) && is_string($design['modal_shadow'])) {
  $modalStyle .= "--wicked-modal-shadow: {$design['modal_shadow']};";
}
// Padding applied directly (accepts CSS shorthand)
$modalInlineProps = '';
if (!empty($design['modal_padding']) && is_string($design['modal_padding'])) {
  $modalInlineProps .= "padding: {$design['modal_padding']};";
}

// Close button color
$closeStyle = '';
if (!empty($design['close_color']) && is_string($design['close_color'])) {
  $closeStyle .= "color: {$design['close_color']};";
}

$overlayAlign = '';
if ($vertical_align === 'top') $overlayAlign = 'wm-align-top';
elseif ($vertical_align === 'bottom') $overlayAlign = 'wm-align-bottom';
else $overlayAlign = 'wm-align-center';

?>
<?php if ($render_open_button): ?>
<button class="wm-open-btn" data-wm-open="<?php echo \esc_attr($modal_id); ?>"><?php echo \esc_html($open_label); ?></button>
<?php endif; ?>

<div class="wm-host <?php echo \esc_attr($animClass); ?>" data-wm-modal id="<?php echo \esc_attr($modal_id); ?>"
     <?php if ($delay>0): ?> data-wm-delay="<?php echo (int)$delay; ?>"<?php endif; ?>
     <?php if ($time_on_page>0): ?> data-wm-time-on-page="<?php echo (int)$time_on_page; ?>"<?php endif; ?>
     <?php if ($scroll_depth>0): ?> data-wm-scroll-depth="<?php echo (int)$scroll_depth; ?>"<?php endif; ?>
     <?php if ($exit_intent): ?> data-wm-exit-intent="true"<?php endif; ?>
  <?php if ($trigger_selector): ?> data-wm-trigger-selector="<?php echo \esc_attr($trigger_selector); ?>"<?php endif; ?>
  <?php if ($open_on_hash): ?> data-wm-open-on-hash="true"<?php endif; ?>
  <?php if ($open_on_param): ?> data-wm-open-on-param="<?php echo \esc_attr($open_on_param); ?>"<?php endif; ?>
  <?php if ($inactivity>0): ?> data-wm-inactivity="<?php echo (int)$inactivity; ?>"<?php endif; ?>
  <?php if ($once_per_session): ?> data-wm-once-per-session="true"<?php endif; ?>
  data-wm-close-on-overlay="<?php echo $close_on_overlay ? 'true' : 'false'; ?>"
  data-wm-close-on-esc="<?php echo $close_on_esc ? 'true' : 'false'; ?>"
  data-wm-lock-scroll="<?php echo $lock_scroll ? 'true' : 'false'; ?>"
     <?php if ($frequency_mode === 'session'): ?> data-wm-frequency="session"<?php endif; ?>
     <?php if ($frequency_mode === 'days'): ?> data-wm-frequency="days" data-wm-frequency-days="<?php echo (int)$frequency_days; ?>"<?php endif; ?>
>
  <div class="wm-overlay wm-hidden <?php echo \esc_attr($overlayAlign); ?>" data-wm-overlay aria-hidden="true" style="<?php echo \esc_attr($overlayStyle); ?>">
    <div class="wm-modal" data-wm-dialog style="<?php echo \esc_attr($modalStyle . $modalInlineProps); ?>" aria-label="<?php echo \esc_attr($aria_label); ?>">
      <?php if ($include_close): ?>
        <!-- Default close button (can be visually hidden if a user places a custom close element with data-wm-close) -->
        <button class="wm-close" data-wm-close aria-label="Close" type="button" style="<?php echo \esc_attr($closeStyle); ?>">×</button>
      <?php endif; ?>

      <!-- Inner content -->
      %%SSR%%

    </div>
  </div>
</div>
