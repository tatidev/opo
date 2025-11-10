# Web Visibility Logic Flow Change

## Overview

This document outlines the updated logic for managing **web visibility** on both the **Product (Parent)** and **Colorline (Child Item)** levels, along with a UI/UX note regarding the “eye” icon used in the colorlines search list.

**NOTE:** The “eye” icon in the colorlines search list should reflect this new logic.

---

## Product (Parent) Edit Form

- The existing **web visibility checkbox** remains.
- This checkbox:
    - Is **only enabled** for manual use **after** a **“beauty shot”** image has been uploaded.
    - If no beauty shot image is uploaded:
        - `$product_webvis_checkbox` is **disabled** in the UI.
        - Its value is **forced to `FALSE`**.

---

## Colorline (Child Item) Behavior

- Each colorline retains its own **web visibility checkbox**.
- Its default value is `FALSE`.
- It is automatically set to `TRUE` *if all of the following are true*:
    1. The parent product’s **web visibility** checkbox is `TRUE`
    2. The colorline item’s `product_status` is one of:
        - `RUN`
        - `LTDQTY`
        - `RKFISH`
    3. The **manual override toggle** (see below) is `FALSE`

### New: Manual Override Toggle

- A new toggle should be added next to the web visibility checkbox in the **colorline edit form**.
- If enabled, this allows the user to **override the automatic logic** and set the checkbox manually.

---

## Logic Flow Summary

### Variables Used

- `$colorline_web_vis = FALSE;`  
    *Final computed web visibility status for the colorline*
- `$product_webvis_checkbox`  
    *Value from the parent product’s web visibility checkbox*
- `$product_colorline_webvis_checkbox`  
    *User-set value from the colorline edit form checkbox*
- `$product_status`  
    *The colorline item’s status string (e.g., `RUN`)*
- `$manual_override_toggle`  
    *Boolean toggle to allow manual control of the colorline visibility*

### Auto-Determined Visibility

    $colorline_web_vis = FALSE;

    if (
        $product_webvis_checkbox === TRUE &&
        in_array($product_status, [ 'RUN', 'LTDQTY', 'RKFISH' ]) &&
        $manual_override_toggle === FALSE
    ) {
        $colorline_web_vis = TRUE;
    }

### Manual Override Logic

    if (
        $product_webvis_checkbox === TRUE &&
        $manual_override_toggle === TRUE &&
        $product_colorline_webvis_checkbox !== NULL
    ) {
        $colorline_web_vis = $product_colorline_webvis_checkbox;
    }

---

## T_ITEM Table Update

- A new **`web_vis` boolean column** must be added to the `T_ITEM` table.
- This column stores the **final resolved visibility status** (`$colorline_web_vis`) as determined by the logic above.
- The value should be updated **whenever** the product or item is saved via the admin interface.
- This field is **used by the website** to determine whether the item is visible online.

### SQL Column Specification

    ALTER TABLE T_ITEM ADD COLUMN web_vis BOOLEAN NOT NULL DEFAULT FALSE;

---

## UI Impacts

- **“Eye” icon in colorline list view** must reflect `$colorline_web_vis` as calculated by the above logic.
- **Web visibility checkbox** in product form must be disabled if no beauty shot exists.
- **Colorline form** must include the new **manual override toggle**, clearly labeled.
- The checkbox and toggle UI should reflect and persist the visibility state stored in `T_ITEM.web_vis`.

---

## Testing Scenarios

| Case | Product WebVis | Beauty Shot | Colorline Status | Manual Override | Expected WebVis |
|------|----------------|-------------|------------------|------------------|------------------|
| A    | TRUE           | Uploaded    | RUN              | FALSE           | TRUE             |
| B    | TRUE           | Uploaded    | HOLD             | FALSE           | FALSE            |
| C    | FALSE          | Uploaded    | RUN              | FALSE           | FALSE            |
| D    | TRUE           | Uploaded    | RUN              | TRUE            | Checkbox Value   |
| E    | TRUE           | None        | RUN              | FALSE           | FALSE (Disabled) |

---