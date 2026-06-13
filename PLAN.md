# Venbhas_ProductLabels — Implementation Plan

**Magento Version:** 2.4.9 Community Edition  
**PHP Version:** 8.4  
**Target:** Magento Marketplace submission  
**Author:** Karthic Kannan N — Venbhas  
**Date:** 2026-06-11

---

## Overview

`Venbhas_ProductLabels` displays configurable visual labels/badges on product images across
all product-rendering contexts: catalog listing pages (PLP), product detail pages (PDP),
search results, and all product widgets (related, upsell, cross-sell, best seller, new
products, recently viewed, compared). Labels are driven by rule-based conditions identical
to Magento's catalog price rule engine, support customer group targeting, and expose full
REST and GraphQL APIs for headless and PWA integrations.

> **Hyvä Theme Compatibility** — Hyvä compat code must be built as a **separate module**
> (`Venbhas_ProductLabelsHyva`). Hyvä bypasses `Magento\Catalog\Block\Product\Image`
> entirely, so the `ImagePlugin` in this module produces no output on a Hyvä storefront.
> Including Hyvä-specific Alpine.js templates or a `Hyva_Theme` dependency inside this
> module would break installs without Hyvä. `Venbhas_ProductLabelsHyva` declares
> `Venbhas_ProductLabels` + `Hyva_Theme` as its dependencies and is installed only when
> Hyvä is present.

---

## Feature Scope

### Label Types

| Type       | Behaviour |
|------------|-----------|
| `text`     | Admin enters static text; rendered as a styled badge |
| `discount` | System auto-calculates `price − final_price`; admin chooses display as **%** or **Amount** |

Both types share: background colour, text colour, shape, font size, position, priority.

### Rule Conditions

Full `Magento\Rule` framework — same conditions as catalog price rules:
- Product attribute (any EAV attribute)
- Category
- Stock status / quantity
- Special price existence
- Product type
- Date range (from / to on the label itself)

### Customer Group Targeting

Each label can be restricted to one or more customer groups. If no groups are selected the
label displays for all groups (guests and all logged-in groups).

- Admin field: **Customer Groups** multiselect (General tab)
- Stored as comma-separated IDs in `customer_group_ids` (empty = all groups)
- Resolver filters candidate labels against the current session's customer group ID before
  running rule conditions
- FPC compatibility: Magento's `X-Magento-Vary` cookie already encodes customer group, so
  Varnish serves separate cache entries per group — no extra work required

### Display Locations

Labels display automatically on **all** product-rendering contexts. No per-label toggles.

| Context | Details |
|---------|---------|
| PLP | Category pages, search results, advanced search |
| PDP | Product detail page |
| Related | Related products block on PDP |
| Upsell | Upsell products block on PDP |
| Cross-sell | Cross-sell block on cart page |
| Widgets | CMS widgets: New Products, Best Seller, Recently Viewed, Compared |

### Admin Config (`Stores → Configuration → Venbhas → Product Labels`)

- Enable module (yes/no)
- Max labels per product (integer, default 2)
- Label z-index (integer, default 10)
- Enable debug/deprecation logging (yes/no) — applies to all Venbhas modules

---

## Module File Structure

```
Venbhas/ProductLabels/
├── Api/
│   ├── Data/
│   │   └── LabelInterface.php
│   └── LabelRepositoryInterface.php
├── Block/
│   └── Product/
│       └── Labels.php
├── Console/
│   └── Command/
│       └── DebugProductLabels.php          (dev helper: show labels for a SKU)
├── Controller/
│   └── Adminhtml/
│       └── Label/
│           ├── Index.php
│           ├── NewAction.php
│           ├── Edit.php
│           ├── Save.php
│           ├── Delete.php
│           ├── MassDelete.php
│           └── MassStatus.php
├── etc/
│   ├── acl.xml
│   ├── adminhtml/
│   │   ├── menu.xml
│   │   ├── routes.xml
│   │   └── system.xml
│   ├── db_schema.xml
│   ├── db_schema_whitelist.json            (auto-generated via bin/magento)
│   ├── di.xml
│   ├── frontend/
│   │   └── di.xml                          (frontend pool if needed)
│   ├── module.xml
│   ├── schema.graphqls                     (GraphQL type + field definitions)
│   └── webapi.xml                          (REST endpoint declarations)
├── Logger/
│   ├── Handler.php
│   └── Logger.php
├── Model/
│   ├── Config.php                          (config reader helper)
│   ├── Label.php                           (extends AbstractModel + AbstractRule)
│   ├── LabelRepository.php
│   ├── ResourceModel/
│   │   ├── Label.php
│   │   └── Label/
│   │       └── Collection.php
│   ├── Label/
│   │   ├── Condition/
│   │   │   ├── Combine.php
│   │   │   └── Product.php
│   │   ├── Resolver.php                    (matches labels to a product)
│   │   └── DiscountCalculator.php          (price − final_price logic)
│   └── Resolver/
│       ├── ProductLabels.php               (GraphQL resolver — productLabels field on ProductInterface)
│       └── DataProvider/
│           └── LabelData.php               (shared DTO formatter used by both REST and GraphQL)
├── Plugin/
│   └── Block/
│       └── Product/
│           └── ImagePlugin.php             (after-plugin on Image::toHtml — wraps all product images with labels across every context)
├── Setup/
│   └── Patch/
│       └── Data/
│           └── (empty — no seed data needed at launch)
├── Ui/
│   └── Component/
│       └── Listing/
│           └── Column/
│               ├── Actions.php
│               ├── LabelType.php
│               └── IsActive.php
├── view/
│   ├── adminhtml/
│   │   ├── layout/
│   │   │   ├── productlabels_label_index.xml
│   │   │   ├── productlabels_label_new.xml
│   │   │   └── productlabels_label_edit.xml
│   │   ├── templates/
│   │   │   └── label/
│   │   │       └── conditions.phtml        (rule condition renderer)
│   │   ├── ui_component/
│   │   │   ├── productlabels_label_listing.xml
│   │   │   └── productlabels_label_form.xml
│   │   └── web/
│   │       ├── css/
│   │       │   └── source/
│   │       │       └── _labels.less        (admin preview styles)
│   │       └── js/
│   │           └── label-form.js           (conditional field visibility)
│   └── frontend/
│       ├── layout/
│       │   └── catalog_product_view.xml           (PDP media container position:relative only; plugin covers all other contexts)
│       ├── templates/
│       │   └── product/
│       │       └── labels.phtml
│       └── web/
│           └── css/
│               └── source/
│                   └── _labels.less        (badge styles: shapes, positioning)
├── CHANGELOG.md
├── PLAN.md                                 (this file)
├── composer.json
└── registration.php
```

---

## Database Schema

### Table: `venbhas_product_labels`

| Column                  | Type           | Notes |
|-------------------------|----------------|-------|
| `label_id`              | int unsigned   | PK, auto-increment |
| `name`                  | varchar(255)   | Admin-facing name |
| `label_type`            | varchar(20)    | `text` or `discount` |
| `text_content`          | varchar(255)   | Used when `label_type = text` |
| `discount_display`      | varchar(10)    | `percent` or `amount`; used when `label_type = discount` |
| `bg_color`              | varchar(7)     | Hex colour, e.g. `#FF0000` |
| `text_color`            | varchar(7)     | Hex colour |
| `shape`                 | varchar(20)    | `rectangle`, `circle`, `ribbon` |
| `font_size`             | smallint       | px, default 12 |
| `position`              | varchar(20)    | `top-left`, `top-right`, `bottom-left`, `bottom-right`, `top-center`, `bottom-center` |
| `position_x`            | smallint       | Horizontal offset px (default 0) |
| `position_y`            | smallint       | Vertical offset px (default 0) |
| `priority`              | smallint       | Lower = higher priority (default 0) |
| `customer_group_ids`    | varchar(255)   | Comma-separated group IDs; empty = all groups |
| `is_active`             | smallint       | 0/1 |
| `from_date`             | date           | Nullable |
| `to_date`               | date           | Nullable |
| `conditions_serialized` | text           | JSON — rule conditions |
| `created_at`            | timestamp      | Auto |
| `updated_at`            | timestamp      | Auto on update |

### Table: `venbhas_product_labels_store`

| Column     | Type         | Notes |
|------------|--------------|-------|
| `label_id` | int unsigned | FK → `venbhas_product_labels.label_id` ON DELETE CASCADE |
| `store_id` | smallint     | 0 = all stores |

---

## Key Model Relationships

```
Label (Model\Label)
  extends  Magento\Rule\Model\AbstractModel
  uses     Model\Label\Condition\Combine  (getConditionsInstance)
  uses     Model\Label\Condition\Product  (getActionsInstance — unused, required by interface)

Label\Condition\Combine
  extends  Magento\Rule\Model\Condition\Combine
  uses     Label\Condition\Product

Label\Condition\Product
  extends  Magento\CatalogRule\Model\Rule\Condition\Product
  (inherits all catalogue attribute conditions out of the box)
```

This gives us the full catalog price rule condition set (product attributes, category,
stock, special price, etc.) with zero custom condition code needed.

---

## Admin Menu

```
Catalog
└── Product Labels          (Venbhas_ProductLabels::label_manage)
    └── → productlabels/label/index
```

---

## Admin UI Component — Form Fields

### Tab: General

| Field            | Type            | Notes |
|------------------|-----------------|-------|
| Name             | text            | Required |
| Is Active        | toggle (yes/no) | Default yes |
| Priority         | number          | Default 0; lower = higher priority |
| From Date        | date            | Optional activation start |
| To Date          | date            | Optional activation end |
| Store Views      | multiselect     | 0 = All Store Views |
| Customer Groups  | multiselect     | Empty = all groups (guest, general, wholesale, etc.) |

### Tab: Conditions

Standard Magento rule condition component (`Magento_Rule/conditions`).
Reuses the same renderer as catalog price rules.

### Tab: Label Settings

| Field              | Type       | Visible when |
|--------------------|------------|--------------|
| Label Type         | select     | Always (`text` / `discount`) |
| Text Content       | text       | `label_type = text` |
| Discount Display   | select     | `label_type = discount` (`%` / `Amount`) |
| Background Colour  | color      | Always |
| Text Colour        | color      | Always |
| Shape              | select     | Always (`Rectangle` / `Circle` / `Ribbon`) |
| Font Size (px)     | number     | Always |
| Position           | select     | Always (6 positions) |
| Offset X (px)      | number     | Always (default 0) |
| Offset Y (px)      | number     | Always (default 0) |

Conditional field visibility (`label-form.js`) hides/shows **Text Content** and
**Discount Display** based on the selected Label Type.

---

## Discount Calculation Logic (`Model/Label/DiscountCalculator.php`)

```php
// Both values come from the product's price index (already tax/rule applied)
$price      = (float) $product->getPrice();
$finalPrice = (float) $product->getFinalPrice();

if ($price <= 0 || $finalPrice >= $price) {
    return null; // no discount — label should not render
}

$discountPercent = round((($price - $finalPrice) / $price) * 100);
$discountAmount  = $price - $finalPrice;

// Output based on admin's "Discount Display" choice:
// percent → "-{$discountPercent}%"
// amount  → formatted currency string
```

Labels of type `discount` are silently skipped if no active discount exists on the product.

---

## Label Resolver (`Model/Label/Resolver.php`)

```
1. Load all active labels for current store (date-filtered, store-filtered)
   → sorted by priority ASC
2. Filter by customer group: discard labels whose customer_group_ids does not contain
   the current session's customer group ID (skip check if customer_group_ids is empty)
3. For each remaining label, call $label->validate($product)
   → uses the Magento\Rule engine against conditions_serialized
4. Collect matching labels
5. If label_type = discount: run DiscountCalculator; skip if no discount
6. Return top N labels (N = config max_labels_per_product)
```

Results are cached per `product_id + store_id + customer_group_id` using Magento's cache
framework with cache tags `[venbhas_product_labels, venbhas_label_{id}]` for precise
invalidation. Varnish separates cache entries per customer group automatically via the
`X-Magento-Vary` cookie — no custom vary logic needed.

---

## Frontend Rendering

### Rendering Strategy

Labels must appear on product images across every context without duplicating logic per
layout handle. The mechanism is a single **after-plugin on
`Magento\Catalog\Block\Product\Image::toHtml()`** (`Plugin/Block/Product/ImagePlugin.php`).

Every product image in Magento — on PLP, PDP, related/upsell/cross-sell blocks, and all
CMS widgets — is rendered through this one block class. The plugin:

1. Gets the `$product` object from the Image block (`$subject->getProduct()`)
2. Calls `Resolver::getLabelsForProduct($product)` (cached per product + store)
3. If labels exist, wraps the original image HTML in a container `<div class="vp-label-wrapper">` and appends rendered label markup
4. Returns the wrapped HTML

This means **no layout XML modifications are required** for widgets or any new context added
in future — coverage is automatic everywhere `Image::toHtml()` is called.

### Layout XML (PDP only — position inside media area)

`catalog_product_view.xml` is the only layout override needed, and only to ensure the PDP
media container has `position: relative` — the plugin handles the actual label injection.

### Context Coverage

| Context | Block class | Covered by plugin? |
|---------|-------------|--------------------|
| PLP (category) | `Magento\Catalog\Block\Product\ListProduct` | Yes |
| Search results | `Magento\CatalogSearch\Block\Result` | Yes |
| Advanced search | `Magento\CatalogSearch\Block\Advanced\Result` | Yes |
| PDP | `Magento\Catalog\Block\Product\View\Gallery` | Yes |
| Related products | `Magento\Catalog\Block\Product\ProductList\Related` | Yes |
| Upsell products | `Magento\Catalog\Block\Product\ProductList\Upsell` | Yes |
| Cross-sell (cart) | `Magento\Catalog\Block\Product\ProductList\Crosssell` | Yes |
| New Products widget | `Magento\Catalog\Block\Product\Widget\NewWidget` | Yes |
| Best Seller widget | `Magento\Sales\Block\Widget\Guest\Form` / custom | Yes |
| Recently Viewed widget | `Magento\Reports\Block\Product\Widget\Viewed` | Yes |
| Compared widget | `Magento\Reports\Block\Product\Widget\Compared` | Yes |

### Block (`Block/Product/Labels.php`)

- Accepts a `$product` object
- Calls `Resolver::getLabelsForProduct($product)`
- Returns rendered HTML string of all matched labels for use by the plugin

### Template (`labels.phtml`)

- Iterates resolved label DTOs
- Applies inline styles (bg colour, text colour, font-size)
- Applies CSS class for shape (`vp-label--rectangle`, `vp-label--circle`, `vp-label--ribbon`)
- Positions via absolute CSS + configurable offset (position_x, position_y)
- Wrapper `<div class="vp-label-wrapper">` uses `position: relative` and `display: inline-block`
  so labels overlay the image correctly in any container

---

## REST API (`etc/webapi.xml`)

All admin endpoints require `Venbhas_ProductLabels::label_manage` ACL.  
The product labels lookup endpoint is available to logged-in customers and guests.

| Method   | Endpoint                                    | ACL / Auth              | Description |
|----------|---------------------------------------------|-------------------------|-------------|
| `GET`    | `/V1/venbhas/product-labels`                | Admin token             | List all labels (paginated) |
| `GET`    | `/V1/venbhas/product-labels/:id`            | Admin token             | Get a single label by ID |
| `POST`   | `/V1/venbhas/product-labels`                | Admin token             | Create a new label |
| `PUT`    | `/V1/venbhas/product-labels/:id`            | Admin token             | Update an existing label |
| `DELETE` | `/V1/venbhas/product-labels/:id`            | Admin token             | Delete a label |
| `GET`    | `/V1/venbhas/products/:sku/labels`          | Customer / Guest token  | Resolved labels for a product SKU |

The product SKU endpoint runs the full Resolver pipeline (conditions + customer group +
discount calculation) and returns the computed display text alongside all styling data.
Response format mirrors `LabelInterface` with an additional `display_text` field.

---

## GraphQL API (`etc/schema.graphqls`)

Adds a `productLabels` field to Magento's `ProductInterface`.

```graphql
type ProductLabelData {
    label_id:     Int
    label_type:   String    # "text" | "discount"
    display_text: String    # computed: static text or "-20%" / "Save $12.00"
    bg_color:     String    # hex colour
    text_color:   String    # hex colour
    shape:        String    # "rectangle" | "circle" | "ribbon"
    font_size:    Int       # px
    position:     String    # "top-left" | "top-right" | etc.
    position_x:   Int       # offset px
    position_y:   Int       # offset px
}

interface ProductInterface {
    productLabels: [ProductLabelData] @resolver(class: "Venbhas\\ProductLabels\\Model\\Resolver\\ProductLabels")
}
```

`Model/Resolver/ProductLabels.php` delegates to `Resolver::getLabelsForProduct()`.  
`Model/Resolver/DataProvider/LabelData.php` converts resolved label DTOs to the array
format expected by both the GraphQL resolver and the REST response.

Module dependencies added to `module.xml`:
- `Magento_GraphQl`
- `Magento_CatalogGraphQl`

---

## Config (`Stores → Configuration → Venbhas → Product Labels`)

```
Section:  venbhas_productlabels
Group:    general
  - enabled            (yes/no)
  - max_labels         (integer, default 2)
  - z_index            (integer, default 10)

Group:    developer
  - enable_logging     (yes/no, default no)
  - log_level          (info / warning / debug)
```

`Model/Config.php` exposes typed getters consumed by Resolver, Block, and Logger.

---

## Logging (`Logger/`)

Custom `Monolog` logger virtual type wired in `di.xml`:

```xml
<virtualType name="VenbhasProductLabelsLogger" type="Magento\Framework\Logger\Monolog">
    <arguments>
        <argument name="name" xsi:type="string">VenbhasProductLabels</argument>
        <argument name="handlers" xsi:type="array">
            <item name="system" xsi:type="object">Venbhas\ProductLabels\Logger\Handler</item>
        </argument>
    </arguments>
</virtualType>
```

Handler writes to `var/log/venbhas_product_labels.log`.  
All log calls throughout the module gate on `Config::isLoggingEnabled()` before writing.  
Other Venbhas modules should follow the same pattern with their own handler/config.

---

## ACL Resources

```
Magento_Backend::admin
└── Venbhas_ProductLabels::root
    ├── Venbhas_ProductLabels::label_manage   (view grid + REST GET)
    ├── Venbhas_ProductLabels::label_save     (create / edit + REST POST/PUT)
    ├── Venbhas_ProductLabels::label_delete   (delete / mass delete + REST DELETE)
    └── Venbhas_ProductLabels::config         (Stores → Config access)
```

---

## Marketplace Compliance Checklist

| Requirement                      | Approach |
|----------------------------------|----------|
| PHP 8.4 compatibility            | Typed properties, no deprecated APIs |
| Magento 2.4.x compatibility      | No removed APIs, use service contracts |
| Coding standards                 | `bin/phpcs` clean — Magento2 ruleset |
| PHPStan                          | Level 5 clean via `bin/analyse` |
| Unit tests                       | PHPUnit — Resolver, DiscountCalculator, Config |
| Integration tests                | Admin CRUD controllers, Repository |
| `composer.json`                  | `type: magento2-module`, semantic version, MIT license |
| `CHANGELOG.md`                   | Required for Marketplace submission |
| No hard dependencies on Hyvä     | Hyvä compat ships as `Venbhas_ProductLabelsHyva` — separate module, separate Marketplace listing |
| REST API                         | `etc/webapi.xml` + `LabelRepositoryInterface` service contract |
| GraphQL API                      | `etc/schema.graphqls` + `Model/Resolver/ProductLabels.php` |
| Full Page Cache compatible       | IdentityInterface + cache tags on block |
| No inline JS in templates        | All JS in `web/js/` with RequireJS |

---

## Implementation Phases

### Phase 1 — Scaffold & Database
- `registration.php`, `composer.json`, `etc/module.xml`
- `etc/db_schema.xml` (both tables)
- Enable module, run `setup:upgrade`

### Phase 2 — Model Layer
- `Api/Data/LabelInterface.php` (include `customer_group_ids` getter/setter)
- `Api/LabelRepositoryInterface.php`
- `Model/Label.php` (extends AbstractModel + rule integration)
- `Model/ResourceModel/Label.php`
- `Model/ResourceModel/Label/Collection.php`
- `Model/LabelRepository.php`

### Phase 3 — Rule Conditions
- `Model/Label/Condition/Combine.php`
- `Model/Label/Condition/Product.php`
- Wire via `getConditionsInstance()` in Label model

### Phase 4 — Admin Controllers & Routing
- `etc/adminhtml/routes.xml`, `etc/acl.xml`, `etc/adminhtml/menu.xml`
- Controllers: Index, NewAction, Edit, Save, Delete, MassDelete, MassStatus

### Phase 5 — Admin UI Components
- `ui_component/productlabels_label_listing.xml`
- `ui_component/productlabels_label_form.xml` (all tabs + conditional fields)
- `Ui/Component/Listing/Column/Actions.php`, `LabelType.php`, `IsActive.php`
- `web/js/label-form.js` — conditional field visibility

### Phase 6 — Config & Logging
- `etc/adminhtml/system.xml`
- `Model/Config.php`
- `Logger/Handler.php`, `Logger/Logger.php`
- `etc/di.xml` virtual type wiring

### Phase 7 — Discount Calculator & Resolver
- `Model/Label/DiscountCalculator.php`
- `Model/Label/Resolver.php` (condition evaluation + customer group filter + caching)
- Cache key includes `customer_group_id`

### Phase 8 — Frontend Rendering
- `Plugin/Block/Product/ImagePlugin.php` (after-plugin on `Image::toHtml` — covers all contexts)
- `Block/Product/Labels.php` (label HTML renderer, called by plugin)
- `view/frontend/templates/product/labels.phtml`
- `view/frontend/web/css/source/_labels.less` (shapes, positioning, wrapper)
- `view/frontend/layout/catalog_product_view.xml` (PDP media container `position: relative` only)
- Wire plugin in `etc/di.xml`

### Phase 9 — REST API & GraphQL
- `etc/webapi.xml` (6 endpoints)
- `etc/schema.graphqls` (`ProductLabelData` type + `productLabels` field on `ProductInterface`)
- `Model/Resolver/ProductLabels.php` (GraphQL resolver)
- `Model/Resolver/DataProvider/LabelData.php` (shared DTO formatter)
- Add `Magento_GraphQl` + `Magento_CatalogGraphQl` to `etc/module.xml` dependencies

### Phase 10 — QA
- Unit tests for Resolver (including customer group filter), DiscountCalculator, Config
- Integration tests for Repository, admin Save controller, REST endpoints
- GraphQL query test for `productLabels` field
- `bin/phpcs` clean
- `bin/analyse` PHPStan Level 5

---

## Out of Scope (deferred)

- Image label type (upload PNG/SVG badge)
- Dynamic text tokens (`{attr:color}`, etc.)
- Label stacking per position (max N per product is in scope; per-position stacking is not)
- Live WYSIWYG preview in admin form
- Import / Export (CSV)
- **Hyvä compat — `Venbhas_ProductLabelsHyva` (separate module, separate Marketplace listing)**
  - Must be a standalone module; cannot be merged into this module
  - Depends on `Venbhas_ProductLabels` + `Hyva_Theme`
  - Replaces the `ImagePlugin` approach with Alpine.js templates that hook into Hyvä's
    own product image rendering pipeline
  - Includes Tailwind CSS safelist entries for label shape/position classes
