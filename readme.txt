=== Devgraphix Elementor Addons ===
Contributors: devgraphix
Tags: elementor, addons, widgets, page builder
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.12.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom-built Elementor widgets and elements by Devgraphix.

== Description ==

Devgraphix Elementor Addons adds a set of custom widgets to Elementor, grouped
under their own "Devgraphix" category in the editor panel.

Included widgets:

* Hover Cards — a card with a solid normal state (icon, number, heading,
  description, divider, footer label + arrow) that reveals a background image
  with a colored overlay on hover. Fully styleable in both Normal and Hover
  states, with four content layouts (Standard, Centered, Bottom, Reveal).
* Featured Box — a single card with two looks (set via the Type control):
  a Product look (full-bleed image + dark wash + chip/headline/price/CTA + a
  recessed cutout holding a product packshot pulled from a selected
  post/WooCommerce product or uploaded manually, with optional auto-link to the
  product) or a Content look (tonal background + number/label + heading +
  description + optional badge/chips/stats + positioned image). Drop several
  into your own Elementor columns/containers to build grids.
* Marquee Pills — a continuously-scrolling, seamless row of icon + text pills
  (add as many as you like). Smooth never-stopping loop with adjustable speed,
  direction, gap, pause-on-hover and edge fade; full pill + icon styling.
* Product Cards — a dynamic product card grid/carousel with two pixel-perfect
  looks (Vertical glass / Horizontal light). Pulls items from a post-type
  query (WooCommerce products by default); every text slot (name, family
  label, sub line, price, bullets, etc.) can be mapped to a Static value, an
  ACF field, the post title/excerpt or the WooCommerce price. Show as a grid
  or a carousel (arrows, dots, autoplay, loop). Vertical look includes the
  Rx pill + auto index counter; full styling for every element including
  image height.
* Swiss Heading — a heading block combining: an eyebrow row (optional icon +
  counter + divider + label, which can be wrapped in a rounded pill), a serif
  headline with an italic tinted accent, a subheading paragraph, and an
  optional large faint "watermark" heading behind it that you can position
  anywhere (fully responsive). The eyebrow divider can be a line, a dot or a
  custom character. Every part is optional and fully styleable.
* Photo Spotlight — a full-bleed photo card with freely-positionable overlay
  layers: a badge (dot + label), a serif stat ribbon with a caption, a
  "featured" chip (dot + label + arrow, optionally linked), and a solid stat
  card (label + large serif value + sub line). Each layer has a 9-point
  anchor + X/Y offset + rotation, and may bleed past the photo edge.
* Comparison Table — unlimited side-by-side comparison columns; highlight one
  into an elevated card with a ribbon badge. Each cell supports a value, a
  check/cross icon and a chip, with per-column background + accent colours.
  No wrapper background — drop it on your own section and add your own padding.
* Advanced Testimonials — a mixed-media testimonial carousel. Each slide is one
  of four card types: Text only (quote + stars + category), Video (YouTube,
  Vimeo or direct file — plays inline, thumbnail fetched automatically),
  Before & After (two photos with labels + a metric chip) or Single Photo (one
  photo + metric chip). Choose a card layout (media on top, media on bottom, or
  text-over-media overlay), set the metric chip's position, and give each card
  its own footer tag. Arrows, dots, autoplay, loop and pause-on-hover;
  responsive cards-per-view. No wrapper background.
* BMI Calculator — an interactive two-card Body-Mass-Index calculator (an Inputs
  card beside a Result card; drop it into your own section/background). Visitors
  drag the height and weight sliders (with an Imperial/Metric toggle) and the BMI
  number, category pill, scale marker, band colours and recommendation text
  update live. Every visible string is editable (card label, unit toggle labels,
  height/weight labels, disclaimer, result label, scale tick numbers and band
  labels, the four category names + recommendations, button text/link/icon), the
  slider ranges are configurable, and the two cards stack to one column
  responsively on tablet and mobile.
* Savings Calculator — an interactive cost-comparison calculator. Visitors drag
  a "what are you paying now?" slider and the yearly-savings headline, the
  struck-through current-cost figure and the savings breakdown update live.
  Every visible string is editable (eyebrow, headline parts, slider label,
  comparison-strip labels, breakdown heading + an unlimited rows repeater where
  any row can show the live monthly savings, button, footnote), the currency
  symbol, your price and the slider range/step/default are configurable, and the
  layout stacks to a single column on tablet/mobile.
* Marquee Text — a large, continuously-scrolling text marquee (the big rotating
  footer headline). Enter one phrase and it loops seamlessly forever with an
  optional separator (dot or character) between repeats. Choose direction,
  speed, spacing and pause-on-hover; fill the text with a solid colour or a
  gradient; and softly fade the left/right edges (adjustable width). Fully
  responsive — the type scales down per device.

== Requirements ==

* WordPress 5.9+
* PHP 7.4+
* Elementor 3.5.0+

== Installation ==

1. Upload the `devgraphix-elementor-addons` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Make sure Elementor is installed and active.
4. Edit any page with Elementor and find the widgets under the "Devgraphix"
   category.

== Changelog ==

= 1.12.0 =
* New widget: Marquee Text — a large, continuously-scrolling text marquee (the
  big rotating footer headline from the design). One editable phrase loops
  seamlessly forever (repeated + mirrored for a gap-free, never-resetting loop
  at a constant pixel speed), with an optional dot/character separator.
* Options: direction, speed, spacing, pause-on-hover; a solid-colour OR gradient
  text fill (with angle); and a soft edge fade with adjustable width. Responsive
  typography scales the text down on tablet/mobile.

= 1.11.3 =
* Removed all brand/medical references ("AHC", "GLP-1", and mockup section
  names) from the plugin — they only appeared in code comments and one editor
  search keyword; no visitor-facing output was affected. The widgets remain
  fully generic and multipurpose.

= 1.11.2 =
* Savings Calculator: default demo numbers aligned to a clear worked example
  (price $129/mo, slider default $499/mo, range $150–$1,200) so the yearly
  figures ($1,548 vs $5,988 = save $4,440) and the −$370/mo breakdown line are
  immediately legible. All numbers, the currency symbol and every label remain
  editable — the calculation works for any values you enter (your monthly price
  ×12 vs the customer's selected monthly cost ×12).

= 1.11.1 =
* BMI Calculator: scoped down to just the two interactive cards (Inputs +
  Result). Removed the outer sand-gradient wrapper, the header (eyebrow,
  "Calculate your BMI" headline, intro), the stat card and the "BMI" watermark,
  so the widget drops cleanly into your own section/background.
* BMI Calculator: added the UNDER / NORMAL / OVER / OBESE band-label row beneath
  the scale (editable), and corrected the slider min/max label colour to match
  the design.

= 1.11.0 =
* New widget: Savings Calculator — an interactive cost-comparison calculator
  built from the homepage design. Drag the "what are you paying now?" slider and
  the yearly-savings headline, the struck-through current-cost figure and the
  breakdown rows all update live in the browser.
* Every text field is editable in Elementor (eyebrow + icon, the three headline
  parts, slider label, currency symbol, per-month/per-year suffixes, comparison
  strip labels and "vs" text, breakdown heading, an unlimited breakdown rows
  repeater, zero-savings text, button text/link/icon, and the footnote).
* Configurable price, slider min/max/step/default; any breakdown row can be set
  to display the live monthly savings. Fully responsive — fluid headline and a
  layout that stacks to one column on tablet/mobile.

= 1.10.0 =
* New widget: BMI Calculator — an interactive Body-Mass-Index calculator built
  from the homepage design. Drag the height/weight sliders (Imperial or Metric)
  and the BMI number, category pill, scale marker, band colours and
  recommendation text all update live in the browser.
* Every text field is editable in Elementor (eyebrow, headline + italic accent,
  intro, stat card, measurement/slider labels, unit toggle labels, disclaimer,
  result label, scale ticks, the four BMI category names + their recommendation
  texts, recommendation label, and the button text/link/icon).
* Configurable slider ranges and defaults, optional unit toggle, stat card,
  scale bar, watermark and button. Fully responsive — fluid display type and a
  layout that stacks to a single column on tablet/mobile.

= 1.9.3 =
* Advanced Testimonials: the carousel now shows exactly the chosen number of
  cards per view — the next card no longer peeks in at the edge after the
  carousel scrolls. Card snapping respects the shadow breathing-room padding.

= 1.9.2 =
* Advanced Testimonials: added an Image Height control (Photo / Before & After
  section) so you can set the height of the before/after and single-photo
  images. Leave it empty to keep the natural photo proportions.

= 1.9.1 =
* Advanced Testimonials: the footer tag is now purely per-card — the global
  "Default Footer Tag" was removed, and each testimonial has its own Footer Tag
  field (leave empty to hide it on that card).
* Advanced Testimonials: roomier, cleaner footer — larger avatar, name and
  location, plus a new Footer Spacing control to dial it compact or spacious.

= 1.9.0 =
* Renamed the "Testimonials" widget to "Advanced Testimonials".
* Video cards now support YouTube and Vimeo links (and direct .mp4/.webm
  files). The video plays inline on click, and the thumbnail is pulled
  automatically from YouTube/Vimeo (upload one to override).
* Added a Card Layout option: media on top, media on bottom, or a text-over-
  media overlay.
* The footer tag is now set per testimonial card (with a widget-wide default
  for any card that doesn't set its own).
* The metric chip position is now adjustable (corner + X/Y offset).
* Roomier card footer spacing.

= 1.8.8 =
* Featured Box: the product packshot is now correctly sized the moment you add
  an image — it no longer overflows the card on themes that force
  `img { height: auto }`. (You can still fine-tune it with Packshot Height.)

= 1.8.7 =
* Testimonials: card shadows are no longer clipped by the carousel's scroll
  area — the track now has breathing room so the soft shadow renders in full
  on every side. Dots/arrows stay aligned with the card edges.

= 1.8.6 =
* Testimonials (Before & After): the metric chip now sits above the Before /
  After label bars instead of overlapping them. The label bars also get a
  consistent height so the chip always clears them.

= 1.8.5 =
* Testimonials: added a responsive Dot Size control (Arrows & Dots section).
  The active dot's pill width scales with it automatically.

= 1.8.4 =
* Testimonials: fixed oversized navigation dots on themes that apply a global
  min-width / min-height to buttons — the dots are now reset and locked to the
  intended small size (active dot is a short pill), regardless of theme styles.

= 1.8.3 =
* Featured Box (Content look): the stats row now always sticks to the bottom of
  the card, regardless of how much (or how little) content is above it — the
  content column fills the full card height and the stats pin to the bottom.

= 1.8.2 =
* Comparison Table: rows are now added one at a time in a proper repeater (like
  the Marquee Pills). Each row has its own Caption, Value, Chip, a real icon
  picker (any icon) and an icon colour. Each row chooses which column it
  belongs to, so you keep unlimited columns. The old per-column text box is
  kept as a silent fallback for existing tables.

= 1.8.1 =
* Swiss Heading: fixed the background heading "Place In Front" toggle — it now
  reliably brings the watermark over the heading text.

= 1.8.0 =
* Swiss Heading: the eyebrow can now be wrapped in a rounded pill and can show
  an icon at the start (with colour, size, background, padding and radius).
* Swiss Heading: the eyebrow divider can be a line, a dot, or a custom
  character (e.g. ·  /  —), each with its own size controls.
* Swiss Heading: added an optional large "background heading" watermark behind
  the main heading — enable/disable, any text, full responsive positioning
  (horizontal, vertical, rotation), typography, colour, and a front/back
  toggle.

= 1.7.2 =
* Featured Box: lots more styling control. The packshot cutout now has its own
  Background (solid or gradient), border, corner radius and padding controls.
  The number/index can be recoloured and restyled (typography, and on the
  product look its pill background, border, padding and radius). New controls
  for the divider (colour, length, thickness), category-label typography,
  heading + accent typography and colours (both looks), and full CTA button
  styling (background, colours, typography, padding, radius, border, shadow).

= 1.7.1 =
* Comparison Table: rows are easier to write — type one thing per line and it
  becomes the row's main text (no more stray empty space when you only add
  one part). Captions and chips are still available via pipes.
* Comparison Table: rows can now use many icons (star, arrow, shield, bolt,
  heart, clock, lock, info, dollar, gift, truck, dot, plus, minus) in addition
  to check/cross — written as [name] tokens at the start of a value.
* Comparison Table: added colour controls for the row icons (check, cross,
  other, plus background, and the highlighted-column icon colours).
* Comparison Table: the per-column background now supports gradients (and
  solids) and cleanly overrides the default cream gradient.

= 1.7.0 =
* Added the Testimonials widget: a mixed-media carousel with four card types —
  Text only, Video (play button + duration + optional link), Before & After
  (two labelled photos + metric chip) and Single Photo (one photo + metric
  chip). Arrows, dots, autoplay, loop, pause-on-hover and responsive
  cards-per-view; fully styleable per card type. No wrapper background.

= 1.6.2 =
* Photo Spotlight: added a new Stat Card layer — a solid floating card with a
  small label, a large serif value and a muted sub line (like a metric /
  progress callout). Freely positionable like the other layers (9-point
  anchor + X/Y offset + rotation) and fully styleable.

= 1.6.1 =
* Photo Spotlight: the photo is now painted as a background layer, so Cover /
  Contain reliably fill the widget at any height (theme `img` rules can no
  longer break it). Added an Image Position control.

= 1.6.0 =
* Added the Comparison Table widget: unlimited columns, highlight one as an
  elevated card with a ribbon, per-cell check/cross icons + chips, per-column
  colours, and JS row-height matching so columns line up.

= 1.5.0 =
* Every widget now has a distinct dual-tone (green + dark grey) panel icon, so
  Devgraphix elements are easy to spot among stock Elementor widgets.
* Renamed "Section Heading" to "Swiss Heading" and "Image Showcase" to
  "Photo Spotlight".
* All size, spacing and dimension controls across every widget are now
  responsive (separate desktop / tablet / mobile values).

= 1.4.1 =
* Image Showcase: each overlay layer (badge, stat, caption, chip) can now be
  freely positioned — a 9-point anchor plus X/Y offset and rotation — and may
  bleed past the photo edge (the photo keeps its rounded clip).

= 1.4.0 =
* Added the Image Showcase widget: full-bleed photo card with a top-left badge,
  a tilted serif stat ribbon + caption, and a bottom-left featured chip —
  every layer optional and fully styleable.

= 1.3.0 =
* Added the Section Heading widget: eyebrow row (counter pill + divider +
  label) + serif headline with an italic accent + subheading, each part
  optional and fully styleable.

= 1.2.3 =
* Product Cards: fixed a fatal error (500 / widget would not load or save) when
  rendering WooCommerce Subscriptions products — the subscription class call was
  not namespaced correctly.
* Product Cards & Featured Box: the manual product picker now lists every public
  post type (Products, custom post types, etc.), not just Posts and Pages.

= 1.2.2 =
* Featured Box: corner-anchored content images now sit perfectly flush with the
  card edge (no 1px gap) — the card border is drawn as an on-top overlay so it
  no longer pushes images off the corner. Added a 4-way Image Offset (margin)
  control (positive insets, negative bleeds) for the content image.

= 1.2.1 =
* Marquee Pills: rebuilt the loop to be truly seamless for any number of pills —
  the row now fills the screen and wraps with no gap or visible reset, and the
  scroll speed stays constant as you add pills.
* Product Cards: WooCommerce Subscriptions billing period (e.g. "/ month") now
  shows after the price; added a manual price-suffix fallback for non-
  subscription products.
* Product Cards: the manual product picker is now filtered by the chosen post
  type, and the vertical card has a solid background by default.

= 1.2.0 =
* Added the Product Cards widget: dynamic product grid/carousel with Vertical
  (glass) and Horizontal (light) looks, ACF / WooCommerce field mapping, and
  full per-element styling.
* All widget defaults and demo content now use neutral placeholder copy.
* Removed the Hello World reference widget.

= 1.1.0 =
* Added Hover Cards, Featured Box (product + content looks, WooCommerce-aware)
  and Marquee Pills widgets.
* Assets are now versioned by file mtime for reliable cache-busting.

= 1.0.0 =
* Initial release: plugin scaffold, custom "Devgraphix" widget category, and a
  Hello World reference widget.
