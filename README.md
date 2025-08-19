# Wicked Modal for Oxygen 6

An accessible, customizable modal element for Oxygen 6/Breakdance built with Element Studio.

## Install

1) Zip this folder and install it as a WordPress plugin (Plugins > Add New > Upload).
2) Activate the plugin.
3) Open Oxygen/Breakdance > Settings > Element Studio. You should see the save locations and the element.

## Element

Element name: "Wicked Modal"

Features:
- Inner builder content slot (design your modal content inside the element)
- Multiple triggers: built-in button, external click (by selector), exit intent, time on page, delay, scroll depth, URL hash, URL param, inactivity, and manual API
- Accessible: focus trap, ESC to close (configurable), overlay click to close (configurable), aria-modal/aria-label
- Multiple modals per page supported
- No animations by default for performance; optional fade/scale/slide

## Using in the Builder

1) Add the "Wicked Modal" element to your page.
2) In Content tab:
	- Modal ID: keep unique per page.
	- Render Built-in Open Button: toggle if you want an automatically rendered button.
	- Include Close Button: adds an X button inside the modal.
3) Triggers tab:
	- Delay (ms)
	- Time On Page (ms)
	- Scroll Depth (%)
	- Exit Intent
	- External Trigger Selector (CSS) — e.g. `.open-modal` will open on clicks of elements matching this selector
	- Open on URL Hash (matches #<id> or #open-<id>)
	- Open on URL Param (if ?<key>=… exists)
	- Inactivity (ms)
	- Once Per Session (sessionStorage)
4) Layout tab: width, vertical alignment, scroll locking
5) Animation tab: none (default), fade, scale, slide-up, duration
6) Accessibility: aria-label, close on overlay/ESC
7) Place content inside the element — this is the modal body.

## Front-end API

Global: `window.WickedModal` with methods:

- `WickedModal.open(id)`
- `WickedModal.close(id)`
- `WickedModal.toggle(id)`

External data attributes:

- `[data-wm-open="<id>"]` — clicking opens
- `[data-wm-close="<id>"]` — clicking closes
- `[data-wm-toggle="<id>"]` — toggles

## Notes

- CSS in `assets/css/wicked-modal.css` contains minimal styling and optional animations.
- JS in `assets/js/wicked-modal.js` handles accessibility, focus trapping, triggers, and multiple instances.
- WordPress functions are called at runtime; static analysis may flag them in this repo but they work in WordPress.

## Next steps / ideas

- Add “once per N days” trigger (localStorage-based) and pageview count triggers
- Expose more design controls mapped to CSS variables
- Add presets for common modal sizes/styles
