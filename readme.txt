=== My Ads Manager Pro ===
Contributors: Custom
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 2.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional WordPress ad management with image uploads, expiry dates, groups, placements, rotations, and full tracking.

== Description ==

My Ads Manager Pro is a complete ad management plugin offering Advanced Ads Pro-level features:

**Ad Types:**
- Plain Text & Code
- Dummy (placeholder)
- Rich HTML
- Image Ads (with upload/selector)
- Ad Groups (rotation & weighted)
- Google Ad Manager
- AMP
- AdSense

**Core Features:**
- Image upload/selection for image ads
- Set expiry dates per ad
- Ad dimensions (width × height in px)
- Target URL with custom window & rel attributes
- Ad Groups with 4 rotation types:
  - Random rotation
  - Ordered/Sequential rotation
  - Grid layout
  - Ad Slider
- Visible ads control (number shown at once)
- Weighted rotation (A/B test style)
- Placements system for managing where ads display:
  - Position options (default, left, center, right)
  - Ad label control
  - Clearfix option
  - Inline CSS support
  - Display conditions (foundation for targeting)
- Ad/Group/Placement shortcodes: [mam_ad id="123"], [mam_group id="456"], [mam_placement id="789"]
- Full impression & click tracking with deduplication
- Admin dashboard with stats (impressions, clicks, CTR)
- Detailed ad list columns (type, impressions, clicks, CTR, expiry)
- Settings panel (toggle tracking, disable for admins)
- Disable ads for admin users option

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Go to My Ads Manager → Dashboard
4. Create ads, groups, and placements
5. Display via shortcode or PHP function

== Usage ==

**Create an Ad:**
1. Go to My Ads Manager → All Ads
2. Click "Add New"
3. Set title and ad type
4. Configure content/image, size, link, expiry
5. Publish

**Create an Ad Group:**
1. Go to My Ads Manager → Ad Groups
2. Click "Add New"
3. Set name and rotation type (Random, Ordered, Grid, Slider)
4. Set visible ads count
5. Add ads to the group with optional weights
6. Publish

**Create a Placement:**
1. Go to My Ads Manager → Placements
2. Click "Add New"
3. Select an ad or group
4. Set position, label, and CSS options
5. Publish
6. Use `[mam_placement id="123"]` in posts

**Shortcodes:**
- [mam_ad id="123"] — render a single ad
- [mam_group id="456"] — render an ad group with rotation
- [mam_placement id="789"] — render a placement with position/CSS

**Display Options:**
- Shortcode in posts/pages
- Direct PHP: echo MAM_Ad::render_id( 123 );
- Direct PHP: echo MAM_Group::render( 456 );
- Direct PHP: echo MAM_Placement::render( 789 );

== Features ==

✓ 8 ad types (plain, image, AdSense, rich, dummy, groups, AMP, Google Ad Manager)
✓ Image upload & media library integration
✓ Expiry date picker
✓ Ad dimensions (width, height)
✓ Target URL with link options
✓ Link attributes (nofollow, sponsored, target window)
✓ Tracking control (impressions, clicks, manual)
✓ Ad Groups with 4 rotation types
✓ Visible ads control (for grid/slider)
✓ Weighted rotation (A/B testing)
✓ Placements system for ad positioning
✓ Position options (default, left, center, right)
✓ Clearfix & inline CSS for placements
✓ Full tracking with deduplication
✓ Admin stats dashboard
✓ Detailed ad list columns with live stats
✓ Settings page for tracking control
✓ Disable ads for admins

== Changelog ==

= 2.0.1 =
* NEW: Placements CPT with position, label, and CSS options (matching Advanced Ads Pro)
* NEW: 4 Ad Group rotation types: Random, Ordered, Grid, Slider
* NEW: Visible ads dropdown (number of ads shown at once)
* NEW: Enhanced Group editor UI with weight controls
* NEW: [mam_placement] shortcode for placement rendering
* IMPROVED: Removed duplicate tracking options
* IMPROVED: Better admin menu organization
* IMPROVED: Group interface matches Advanced Ads Pro design

= 2.0.0 =
* NEW: 8 ad types (was 4)
* NEW: Image upload/selector for image ads
* NEW: Expiry date picker
* NEW: Ad dimensions (width, height)
* NEW: Link target, nofollow, sponsored options
* NEW: Tracking mode selector (default vs manual)
* NEW: Ad Groups with rotation types
* NEW: Ad list columns for impressions, clicks, CTR, expiry
* NEW: Settings page with tracking toggles
* NEW: Disable ads for admins option
* IMPROVED: Complete rewrite of admin UI
* IMPROVED: Enhanced tracking with IP hashing
* IMPROVED: Better meta management

= 1.0.1 =
* Fixed: Class loading and instantiation issues
* Fixed: Shortcode registration conflicts
* Fixed: Script tag stripping on save
* Added: Click tracking support
* Added: Settings page

= 1.0.0 =
* Initial release

