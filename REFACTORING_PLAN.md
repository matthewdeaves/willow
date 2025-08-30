# Refactoring Plan for DefaultTheme

This document outlines a plan to refactor the `DefaultTheme` plugin to improve its structure, maintainability, and customization options, following CakePHP 5.x best practices.

## 1. Consolidate Repetitive Layout Code

**Problem:** The layout files (`default.php`, `article.php`, `article_index.php`, `page.php`) have a lot of duplicated code in the `<head>` and footer sections.

**Solution:**

1.  **Create a new base layout:** Create a new layout file, `templates/layout/site.php`, that will serve as the main layout for the theme. This layout will contain the common HTML structure, including the `<html>`, `<head>`, and `<body>` tags.
2.  **Create common elements:**
    *   Create `templates/element/layout/head.php` to contain the contents of the `<head>` section, including meta tags, CSS links, and JavaScript includes.
    *   Create `templates/element/layout/scripts.php` to contain the common JavaScript files included at the end of the `<body>`.
3.  **Use layout inheritance:** The existing layouts (`default.php`, `article.php`, etc.) will extend the new `site.php` layout and use blocks to override specific sections, such as the main content area.

**CakePHP Way:** This approach uses CakePHP's layout inheritance and elements to promote code reuse and a DRY (Don't Repeat Yourself) architecture.

## 2. Make "About" and "GitHub" Links Dynamic

**Problem:** The "About" text and the "GitHub" link are hardcoded in the templates.

**Solution:**

1.  **"About" text:**
    *   Create a new setting in the `SettingsManager` for the "About" text.
    *   Create a new UI in the admin panel to allow the administrator to edit this setting.
    *   In the `article_index.php` layout, read the "About" text from the `SettingsManager`.
2.  **"GitHub" link:**
    *   Create a new menu management system in the admin panel. This will allow the administrator to create and manage menu items, including their title, URL, and order.
    *   The `FrontEndSiteComponent` will fetch the menu items from the database and pass them to the view.
    *   The `main_menu.php` element will render the menu items dynamically.

**CakePHP Way:** This solution leverages CakePHP's helper system and a custom menu management system to make the content dynamic and manageable through the admin interface.

## 3. Refactor Sidebar Logic

**Problem:** The sidebar logic is duplicated and inconsistent across different layout files.

**Solution:**

1.  **Use CakePHP Cells:** Create separate "cells" for each sidebar widget (e.g., `FeaturedPostsCell`, `RecentPostsCell`, `ArchivesCell`). Each cell will be a self-contained component with its own logic and view.
2.  **Use Blocks:** In the main layout (`site.php`), define a "sidebar" block.
3.  **Render Cells in Views:** In the controller actions or view templates, render the desired cells into the "sidebar" block. This will allow for a flexible and customizable sidebar on a per-page basis.

**CakePHP Way:** Cells are the recommended way to create reusable, self-contained components in CakePHP. Using blocks for the sidebar allows for a flexible and powerful layout system.

## 4. Implement a Child Theme System

**Problem:** The theme is not easily customizable without directly modifying the plugin code.

**Solution:**

1.  **Theme Inheritance:** Modify the `AppView` class to support theme inheritance. The `AppView` will be configured with a "child" theme and a "parent" theme.
2.  **Template Overriding:** When rendering a template, the `AppView` will first look for the template in the child theme's directory. If the template is not found, it will fall back to the parent theme's directory.
3.  **Asset Overriding:** A similar mechanism will be implemented for webroot assets (CSS, JS, images).

**CakePHP Way:** This solution uses a custom `AppView` class to implement a powerful theme inheritance system, which is a common pattern in many CMSs and frameworks.

## 5. Enhance SEO with Rich Snippets

**Problem:** The theme has basic SEO features but lacks structured data for rich snippets.

**Solution:**

1.  **Create a Schema.org Helper:** Create a new `SchemaHelper` that can be used to generate Schema.org markup in JSON-LD format.
2.  **Implement Schemas:**
    *   **Article:** In `templates/Articles/view.php`, use the `SchemaHelper` to generate `Article` schema, including the headline, author, publication date, and other relevant information.
    *   **Breadcrumbs:** Create a `BreadcrumbsCell` that generates `BreadcrumbList` schema based on the current page.
    *   **Website:** In the main layout, generate `WebSite` schema, including the site name and search URL.
3.  **Use the Helper in Templates:** Call the `SchemaHelper` in the appropriate templates to output the JSON-LD markup in the `<head>` section.

**CakePHP Way:** This solution uses a custom helper to encapsulate the logic for generating Schema.org markup, making it easy to reuse and maintain.
