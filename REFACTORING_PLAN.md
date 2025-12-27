# Willow CMS - Top 5 Refactoring Priorities

Based on comprehensive code review of controllers, models, views, helpers, services, jobs, and CI workflows.

**Important Context:** The project has custom bake templates at `plugins/AdminTheme/templates/bake/` that define standard patterns for index actions, search results, forms, and AJAX handling. The `SearchHandler.init()` JavaScript utility already exists. Many existing controllers/templates predate these standards or have custom logic.

---

## 1. Abstract Translation Jobs into Base Class (HIGH PRIORITY)

**Problem:** 3 translation job classes are 95% identical with ~450 total lines that could become ~150.

**Affected Files:**
- `src/Job/TranslateArticleJob.php` (~150 lines)
- `src/Job/TranslateTagJob.php` (~150 lines)
- `src/Job/TranslateImageGalleryJob.php` (~150 lines)

**Identical Code:**
- Argument validation (lines 46-58)
- Entity fetching and locale iteration
- Empty SEO fields check with re-queue logic (lines 121-156)
- Translation application and save

**Refactoring:**
- Create `src/Job/AbstractTranslateJob.php` extending `AbstractJob`
- Move common logic: validation, locale iteration, empty fields re-queueing, save
- Child classes implement: `getTableName()`, `getApiMethodName()`, `getFieldsList()`

**Impact:** Single point for translation bug fixes, ~300 lines removed

---

## 2. Consolidate GoogleApiService Translation Methods (HIGH PRIORITY)

**Problem:** 3 translation methods in GoogleApiService are 95% identical, differing only by field names.

**Affected File:** `src/Service/Api/Google/GoogleApiService.php` (334 lines)

**Duplicated Methods:**
- `translateArticle()` (lines 95-150) - 55 lines
- `translateTag()` (lines 167-211) - 44 lines
- `translateImageGallery()` (lines 228-272) - 44 lines

**Refactoring:**
- Create generic `translateContent(EntityInterface $entity, array $fieldMappings): array`
- Each current method becomes a 5-line wrapper defining field mappings
- Or use configuration arrays passed to a single method

**Impact:** Reduce from 334 lines to ~200 lines, easier to add new translatable entities

---

## 3. Align Existing Admin Controllers with Bake Patterns (MEDIUM PRIORITY)

**Problem:** Existing admin controllers have custom implementations that predate the standardized bake templates. The bake templates at `plugins/AdminTheme/templates/bake/` already define good patterns for index actions with AJAX search, but existing controllers don't follow them consistently.

**Key Bake Patterns Available:**
- `SearchHandler.init()` JavaScript utility (`plugins/AdminTheme/webroot/js/utils/search-handler.js`)
- Standardized index.twig with debounced search, status filters, AJAX rendering
- Reusable elements: `search_form`, `status_filter`, `evd_dropdown`, `pagination`

**Refactoring Approach:**
- Audit existing controllers against bake-generated patterns
- Identify controllers with custom logic that justifies deviation
- Refactor controllers that simply predate the standards to use standard patterns
- Consider creating a `SearchableControllerTrait` that matches bake output for runtime use

**Affected Controllers to Audit:**
- `src/Controller/Admin/ArticlesController.php` - Has custom featured image, author logic
- `src/Controller/Admin/UsersController.php` - Has custom admin/active badges
- `src/Controller/Admin/CommentsController.php` - Has custom moderation logic
- Others may be candidates for regeneration via bake

**Impact:** Consistent patterns, easier maintenance, leverage existing bake infrastructure

---

## 4. Create Reusable Form Field Element (MEDIUM PRIORITY)

**Problem:** While bake templates generate consistent form patterns, existing hand-written templates repeat the Bootstrap form field pattern 70+ times. The bake template (`plugins/AdminTheme/templates/bake/element/form.twig`) generates this code - we need a runtime equivalent.

**Affected Files:**
- `plugins/AdminTheme/templates/element/form/seo.php` - 8 repetitions
- `plugins/AdminTheme/templates/element/form/article.php` - 12 repetitions
- `plugins/AdminTheme/templates/Admin/Users/add.php` - 8 repetitions
- Plus 15+ more templates

**Refactoring:**
- Create `plugins/AdminTheme/templates/element/form/field.php` element
- Mirror the pattern from bake's `form.twig` for consistency
- Accept parameters: field name, type, options, wrapper class
- Update bake template to also use this element for future consistency

**Impact:** ~500 lines consolidated, bake and runtime patterns aligned

---

## 5. Improve Bake Templates with Form Field Element (MEDIUM PRIORITY)

**Problem:** The bake template `plugins/AdminTheme/templates/bake/element/form.twig` generates repetitive form field code. Once the runtime `form/field.php` element exists (Priority 4), update bake to use it.

**Current Bake Pattern (generates repetitive code):**
```twig
{% for field in fields %}
<div class="mb-3">
    <?php echo $this->Form->control('{{ field }}', ['class' => ...]); ?>
    <?php if ($this->Form->isFieldError('{{ field }}')): ?>
        <div class="invalid-feedback">...</div>
    <?php endif; ?>
</div>
{% endfor %}
```

**Improved Bake Pattern:**
```twig
{% for field in fields %}
    <?= $this->element('form/field', ['name' => '{{ field }}', 'type' => '{{ fieldType }}']) ?>
{% endfor %}
```

**Impact:** Future baked code is DRY, consistent with hand-written templates

---

## Summary Table

| Priority | Refactoring | Files Affected | Lines Saved | Complexity |
|----------|-------------|----------------|-------------|------------|
| 1 | AbstractTranslateJob | 3 job classes | ~300 | Medium |
| 2 | GoogleApiService consolidation | 1 service | ~130 | Low |
| 3 | Align controllers with bake patterns | 13 controllers | ~400 | Medium |
| 4 | Reusable form field element | 20+ templates | ~500 | Low |
| 5 | Update bake templates to use element | 1 bake template | Future DRY | Low |

**Total Estimated Impact:** ~1,300 lines of duplication removed + improved future code generation

---

## Recommended Implementation Order

1. **GoogleApiService** (Lowest risk, isolated change, immediate impact)
2. **AbstractTranslateJob** (Medium risk, requires testing translation flows)
3. **Form field element** (Low risk, additive change, enables #5)
4. **Update bake templates** (Low risk, builds on #3)
5. **Align controllers** (Medium risk, audit-first approach, may just use bake to regenerate)

---

## Key Bake Template Files

The project already has mature bake infrastructure:
- `plugins/AdminTheme/templates/bake/Controller/controller.twig`
- `plugins/AdminTheme/templates/bake/Template/index.twig` - AJAX search pattern
- `plugins/AdminTheme/templates/bake/Template/search_results.twig`
- `plugins/AdminTheme/templates/bake/Template/add.twig` / `edit.twig`
- `plugins/AdminTheme/templates/bake/element/form.twig` - Form generation
- `plugins/AdminTheme/webroot/js/utils/search-handler.js` - Debounced search

---

## Additional Findings (Lower Priority)

- **SEO Update Jobs** (ArticleSeoUpdateJob, TagSeoUpdateJob, ImageGallerySeoUpdateJob) - 85% duplication, similar pattern to translation jobs
- **MetaTagsHelper** - 247 lines, could split into OpenGraph/Social helpers
- **CI workflow** - Quality checks run 4x (once per PHP version) instead of once
- **Cache clearing** - Duplicated `clearContentCache()` in 3 controllers
- **Add/edit templates** - Already handled by bake (they call `element('form')`) - existing duplication is in pre-bake code
