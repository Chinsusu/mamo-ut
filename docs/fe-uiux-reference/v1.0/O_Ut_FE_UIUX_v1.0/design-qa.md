# Design QA — O Út FE UI/UX v1.0

## Comparison target

- Source visual truth: `reference/O_Ut_Selected_UI_System.png`
- Combined comparison evidence: `qa/design-comparison-board.jpg`
- Browser implementation captures:
  - `qa/qa-home-desktop.png`
  - `qa/qa-product-desktop.png`
  - `qa/qa-home-mobile.png`
  - `qa/qa-product-mobile.png`
  - `qa/qa-admin-dashboard.png`
- Functional QA: `qa/functional-qa.json`

## Normalization

- Source design board: 1448 × 1086 px.
- Desktop implementation viewport: 1440 × 1000 CSS px, device scale factor 1; full-page capture 1440 × 4411 px.
- Mobile implementation viewport: 390 × 844 CSS px, device scale factor 1; viewport-only capture 390 × 844 px.
- Source desktop and mobile regions were cropped from the design board and placed with implementation captures on one 1800 × 1550 comparison canvas. Images were contained without distortion; surrounding design-board labels/device frames were treated as presentation context, not application UI.
- State compared: public home, mobile menu open, product detail, selected default product, light theme.

## Full-view comparison evidence

The implementation preserves the source system’s defining composition: cream paper surface, burgundy utility/header actions, two-column editorial hero, horizontal category access, product-led conversion area, serif display hierarchy, compact trust strip and mobile bottom navigation. The production page intentionally expands vertical spacing and content depth beyond the compressed design-board overview; this is consistent with a real scrollable storefront rather than a poster-style mockup.

The admin screens were not fully specified in the source board. They were evaluated against the approved color, type, radius, border and density system, while intentionally using a more neutral operational layout.

## Focused region comparison evidence

Focused comparisons were needed because the full board made product cards and mobile controls too small to judge. `qa/design-comparison-board.jpg` includes:

- Desktop header, hero, category strip and product row.
- Mobile home header/menu/bottom navigation.
- Mobile product image, gallery, sticky purchase bar and bottom navigation.

The product imagery, primary CTA treatment, typography contrast, cream/burgundy palette, rounded borders and compact icon treatment remain visibly aligned with the reference.

## Required fidelity surfaces

### Fonts and typography

- Display hierarchy uses `Playfair Display` with Georgia fallback; UI text uses `Inter` with system-sans fallback.
- Heading scale, dark ink color, bold optical weight and compact eyebrow labels match the approved editorial direction.
- Mobile wrapping was checked at 390 px; hero and product headings remain readable and do not overflow.
- Google Fonts are remote by design; the local fallback remains usable when offline.

### Spacing and layout rhythm

- Desktop container, hero split, product grid, section rhythm, cards, border radii and low-elevation shadows track the reference.
- The implementation uses more breathing room and longer page sections than the source board, an intentional production adaptation rather than layout drift.
- Responsive checks passed at 390, 768, 1024 and 1440-oriented rules. Persistent mobile buy and bottom bars remain available without horizontal overflow.

### Colors and visual tokens

- Primary burgundy `#5B1F2E`, dark burgundy `#421421`, rust `#A35A2C`, beige `#E8D8C1`, cream `#F7F2EC`, ink `#2E2A26`, gray `#7A7A7A` and olive `#66734A` map directly to the approved design system.
- Semantic success, warning, error and active states have adequate contrast and do not introduce a competing visual language.

### Image quality and asset fidelity

- Hero, product, story, blog and decorative imagery were derived from the approved O Út visual direction and stored as real raster assets.
- Product cards use clear 1:1 food crops; hero and editorial images use appropriate wide crops.
- The Tôm chua product detail was revised to lead with the branded jar image, matching the selected mobile reference more closely.
- Icons use a reusable SVG sprite rather than emoji or improvised CSS drawings.

### Copy and content

- Brand name, Hue-specialty categories, ordering language, trust messages and Vietnamese microcopy are specific to O Út.
- Product, checkout, success, tracking, empty, error and admin states have complete user-facing copy.
- Demo-only prices, policy, ingredients and bank data are explicitly marked for replacement before go-live.

## Comparison history

### Iteration 1

- [P1] Article cards hid body content because the media link remained inline and consumed the card height.
  - Fix: converted article cards to flex columns, made media a block with fixed aspect ratio and allowed the body to occupy normal flow.
  - Post-fix evidence: `qa/qa-home-desktop.png`; all three image, metadata, title, excerpt and link blocks are visible.
- [P0] Checkout could enter a loading state without completing because the total was recomputed after the submit button content removed the amount node.
  - Fix: totals are captured before the button is replaced by the loading state.
  - Post-fix evidence: `qa/qa-order-success.png`, `qa/qa-order-tracking.png`, and passing end-to-end checks in `qa/functional-qa.json`.
- [P2] Product-detail mobile opened on a bowl-only crop while the selected reference led with branded jar packaging.
  - Fix: reordered the approved Tôm chua gallery so the jar image is primary while retaining the food crop as the second image.
  - Post-fix evidence: `qa/qa-product-mobile.png` and `qa/design-comparison-board.jpg`.

### Final pass

No actionable P0, P1 or P2 differences remain. Core flow, responsive states and admin interactions pass without page errors or application console errors.

## Follow-up polish — non-blocking P3

- The mobile product screen uses a thumbnail rail instead of the source mock’s more minimal carousel controls. This is an intentional usability choice and can be simplified after real customer testing.
- Packaging photography should be replaced by final high-resolution product photography once physical labels and jar sizes are confirmed.
- Font files are not bundled; remote font loading can be replaced with licensed/self-hosted project fonts during Laravel integration.

## Primary interactions tested

- Search shell and product discovery.
- Quick add, variant selection, quantity and cart badge.
- Cart update/remove and coupon `OUUT10`.
- Checkout validation, bank transfer selection, order creation and QR demo.
- Order lookup and timeline.
- Admin login, dashboard, product status/edit, order status, post creation and store settings.
- Mobile menu, bottom navigation and sticky product-buy bar.

## Implementation checklist

- [x] Approved visual language reproduced.
- [x] Desktop and mobile storefront completed.
- [x] Core ordering journey completed.
- [x] Admin UI completed.
- [x] Empty/error/loading/success states completed.
- [x] Functional browser QA passed.
- [x] Internal assets and links validated.
- [x] Laravel handoff included.

final result: passed
