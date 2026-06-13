# Venbhas ProductLabels — Magento 2 Module

> Rule-based product labels and badges for Magento 2. Displays configurable text and discount badges on product images across every context with customer group targeting, REST API, and GraphQL support.

[![Magento 2.4.x](https://img.shields.io/badge/Magento-2.4.x-orange?logo=magento)](https://devdocs.magento.com/)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4-blue?logo=php)](https://www.php.net/)
[![License: OSL-3.0](https://img.shields.io/badge/License-OSL--3.0-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/packagist/v/venbhas/module-product-labels)](https://packagist.org/packages/venbhas/module-product-labels)

---

## Features

- **Two label types** — static text badges and auto-calculated discount badges (% or amount)
- **Rule-based conditions** — same condition engine as Magento catalog price rules (product attributes, category, stock status, special price, date range)
- **Customer group targeting** — restrict labels to specific customer groups or show to all
- **Universal coverage** — labels appear on PLP, PDP, related/upsell/cross-sell blocks, and all CMS widgets via a single plugin; no per-context configuration needed
- **Fully configurable appearance** — background colour, text colour, shape (rectangle, circle, ribbon), font size, position, and pixel offsets
- **REST API** — full CRUD for admin; product label lookup for storefront/headless
- **GraphQL API** — `productLabels` field on `ProductInterface` for PWA and headless frontends
- **FPC compatible** — cache tags for precise invalidation; Varnish separates entries per customer group automatically via `X-Magento-Vary`

> **Hyvä Theme:** Hyvä compatibility is provided by the separate `Venbhas_ProductLabelsHyva` module. Hyvä bypasses `Magento\Catalog\Block\Product\Image` entirely, so this module's plugin produces no output on a Hyvä storefront. Install `venbhas/module-product-labels-hyva` alongside this module on Hyvä stores.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.1 / 8.2 / 8.3 / 8.4 |
| Magento CE/EE | 2.4.x |
| magento/framework | ^103.0 |
| magento/module-catalog | ^104.0 |
| magento/module-catalog-rule | ^101.2 |
| magento/module-graph-ql | ^100.4 |
| magento/module-catalog-graph-ql | ^100.4 |

---

## Installation

### Via Composer (recommended)

```bash
composer require venbhas/module-product-labels
bin/magento module:enable Venbhas_ProductLabels
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

### Manual Installation

1. Copy the module into `app/code/Venbhas/ProductLabels/`.
2. Run from your Magento root:

```bash
bin/magento module:enable Venbhas_ProductLabels
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

---

## Configuration

Navigate to **Stores → Configuration → Venbhas → Product Labels**.

| Setting | Default | Description |
|---|---|---|
| Enable Module | Yes | Master on/off switch |
| Max Labels per Product | 2 | Maximum number of labels shown on any single product image |
| Label Z-Index | 10 | CSS z-index for label overlay |

---

## Admin Usage

Navigate to **Catalog → Product Labels** to create and manage labels.

### General Tab

| Field | Description |
|---|---|
| Name | Internal admin name for the label |
| Is Active | Enable or disable the label |
| Priority | Lower number = higher priority. When more labels match than the configured maximum, highest-priority labels are shown |
| From Date / To Date | Optional date range for the label to be active |
| Store Views | Restrict to specific store views (0 = all) |
| Customer Groups | Restrict to specific customer groups (empty = all groups) |

### Conditions Tab

Define which products the label applies to using the standard Magento rule condition interface — same as catalog price rules. Supports product attributes, category, stock status, special price, product type, and date range conditions.

### Label Settings Tab

| Field | Description |
|---|---|
| Label Type | `Text` — static text; `Discount` — auto-calculated discount |
| Text Content | The text to display (shown when Label Type = Text) |
| Discount Display | Show discount as `%` or `Amount` (shown when Label Type = Discount) |
| Background Colour | Hex colour for the badge background |
| Text Colour | Hex colour for the badge text |
| Shape | Rectangle, Circle, or Ribbon |
| Font Size (px) | Badge font size |
| Position | One of six positions: top-left, top-right, top-center, bottom-left, bottom-right, bottom-center |
| Offset X / Y (px) | Fine-tune label position within the chosen anchor point |

---

## REST API

| Method | Endpoint | Description |
|---|---|---|
| GET | `/V1/venbhas/product-labels` | List all labels |
| GET | `/V1/venbhas/product-labels/:id` | Get a label by ID |
| POST | `/V1/venbhas/product-labels` | Create a label |
| PUT | `/V1/venbhas/product-labels/:id` | Update a label |
| DELETE | `/V1/venbhas/product-labels/:id` | Delete a label |
| GET | `/V1/venbhas/products/:sku/labels` | Resolved labels for a product (storefront/guest) |

---

## GraphQL

The module adds a `productLabels` field to Magento's `ProductInterface`:

```graphql
{
  products(filter: { sku: { eq: "my-sku" } }) {
    items {
      sku
      productLabels {
        label_type
        display_text
        bg_color
        text_color
        shape
        position
      }
    }
  }
}
```

---

## License

This module is open-source software licensed under the [Open Software License 3.0 (OSL-3.0)](LICENSE).

---

## Support

Maintained by [Venbhas Softwares](https://github.com/Venbhas-Softwares).
For bugs or feature requests, please [open an issue](https://github.com/Venbhas-Softwares/magento-module-product-labels/issues).
