# Localization Conventions

## Project Configuration

- Supported locales: English (`en`) and Indonesian (`id`); default and fallback locale: English (`en`), configurable through `APP_LOCALE` and `APP_FALLBACK_LOCALE`.
- Catalog location and format: `lang/{locale}/*.php`, with one PHP file per application domain. Current domains are `activity_log`, `general`, `users`, `vehicles`, and `work_orders`.
- Translation helper: Laravel's `__()` helper, using dot-notation keys.
- Key style: nested groups with descriptive snake_case keys, such as `work_orders.fields.status`.
- Shared catalog for cross-domain labels: `general.php`.
- Business-specific/configurable data lives in `config/business.php`.
- Use Carbon's locale-aware date formatting and Laravel's `Illuminate\Support\Number` for locale-aware numeric formatting.
- Established glossary: Invoice → Nota (`id`).

## Catalog Structure

- Keep one catalog file per application domain, such as `general`, `users`, `vehicles`, `work_orders`, or `activity_log`. Put a string in the domain that owns the concept; do not create a catalog per screen or component.
- Use nested, descriptive keys grouped by purpose, following the groups already used in the repo (`labels`, `fields`, `sections`, `actions`, `statuses`, `items`, `documents`, and `values`). Keys describe meaning, not wording.
- Reuse an existing key when the meaning is the same; put genuinely shared labels in the shared catalog. Do not add duplicate keys just to match a screen's layout.
- Keep the key structure identical across all supported locales. Add or remove a key in every locale in the same change, and translate the value for each language rather than copying the source text.
- Preserve established terminology; use Nota for the Indonesian invoice action and document title.
- Respect each language's plural rules. Use the framework's pluralization mechanism where plural forms differ. For languages without grammatical plural (for example Indonesian, Japanese, Chinese), the plural value is identical to the singular value; do not invent reduplicated or artificial plurals.
- Never build user-facing text by concatenation, because word order differs between languages. Use named placeholders instead, for example `__('documents.bank', ['bank' => $name])` or `t('documents.bank', { bank: name })` rather than appending `$name` to the translated string. Keep placeholders exactly as they appear in the source string.

## Scope and Boundaries

- Do not hardcode user-facing text in code, templates, routes, or UI definitions. This includes labels, group and menu names, enum/status display labels, validation and notification messages, empty or fallback display values, document and email copy, page titles, and accessible text such as alt text and ARIA labels.
- Before adding a key, check whether the framework, UI kit, or an installed package already provides the translation. Use the package's own key for package-owned UI; do not copy vendor strings into application catalogs or override package defaults without an explicit product requirement.
- Translate only human-readable presentation. Keep identifiers and data values as values, not translation keys: enum/database codes, route names, permissions, model attributes, currency codes, and business records.
- Keep business-specific facts (business name, contact details, addresses, bank accounts, and similar configurable data) in configuration, not in catalogs. Catalogs contain the labels and sentence templates used to present that data.
- Use locale-aware date, time, number, and currency formatting instead of hardcoding month names, separators, or currency symbols.
- Do not localize third-party brand names, URLs, or other proper names unless the product explicitly requires it.

## Review Checklist

- Search application-owned code and templates for remaining hardcoded user-facing text; distinguish UI copy from identifiers, business data, and package-owned text.
- Compare flattened keys across all supported locales and confirm they match for every changed domain.
- Confirm repeated meanings reuse one key and no application translation duplicates a vendor translation.
- Verify changed user-visible behavior in each supported locale where practical.
