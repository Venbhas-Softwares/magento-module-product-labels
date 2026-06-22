# Changelog

All notable changes to `Venbhas_ProductLabels` will be documented in this file.

## [1.0.0] - 2026-06-15

### Added
- Rule-based product labels with catalog price rule conditions
- Text and discount label types with configurable styling
- Customer group and store view targeting
- Admin CRUD grid and form under Catalog → Product Labels
- Frontend rendering via `Image::toHtml()` plugin across all product contexts
- REST API endpoints for label management and SKU lookup
- GraphQL `productLabels` field on `ProductInterface`
- Configuration: enable module, max labels, z-index, debug logging
- CLI command `venbhas:product-labels:debug`
