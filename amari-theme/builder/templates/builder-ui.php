<?php
/**
 * Builder UI — Inline JS templates used by amari-builder.js
 * Rendered in admin_footer via AmariBuilder::output_builder_templates()
 */
if ( ! defined( 'ABSPATH' ) ) exit;
global $post;
if ( ! $post ) return;
?>

<!-- AMARI BUILDER FULL-SCREEN OVERLAY -->
<div id="amari-builder-overlay" class="amari-builder-overlay" style="display:none;" data-post-id="<?php echo esc_attr($post->ID); ?>">

    <!-- TOP TOOLBAR -->
    <div class="amari-builder-toolbar">
        <div class="amari-builder-toolbar-left">
            <span class="amari-builder-logo">⚡ Amari Builder</span>
            <span class="amari-builder-page-title"><?php echo esc_html($post->post_title ?: 'Untitled'); ?></span>
        </div>

        <div class="amari-builder-toolbar-center">
            <button class="amari-tb-btn amari-view-btn active" data-view="desktop" title="Desktop">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </button>
            <button class="amari-tb-btn amari-view-btn" data-view="tablet" title="Tablet">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
            </button>
            <button class="amari-tb-btn amari-view-btn" data-view="mobile" title="Mobile">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            </button>
        </div>

        <div class="amari-builder-toolbar-right">
            <button class="amari-tb-btn amari-tb-btn-templates" id="amari-templates-btn" title="Template Library">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Templates
            </button>
            <button class="amari-tb-btn" id="amari-undo-btn" title="Undo (Ctrl+Z)" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.96"/></svg>
                Undo
            </button>
            <button class="amari-tb-btn" id="amari-redo-btn" title="Redo (Ctrl+Y)" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.49-4.96"/></svg>
                Redo
            </button>
            <button class="amari-tb-btn" id="amari-preview-btn" title="Preview page">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview
            </button>
            <button class="amari-tb-btn amari-save-btn" id="amari-save-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
            <button class="amari-tb-btn amari-tb-btn-close" id="amari-builder-close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Close
            </button>
        </div>
    </div>

    <!-- MAIN AREA -->
    <div class="amari-builder-main">

        <!-- LEFT PANEL: Element Palette + Sections Navigator -->
        <div class="amari-builder-panel-left">
            <div class="amari-panel-tabs">
                <button class="amari-panel-tab active" data-panel="elements">Elements</button>
                <button class="amari-panel-tab" data-panel="layout">Layout</button>
                <button class="amari-panel-tab" data-panel="settings">Page</button>
            </div>

            <!-- Elements Tab -->
            <div class="amari-panel-content" id="amari-panel-elements">
                <div class="amari-element-search-wrap">
                    <input type="text" class="amari-element-search" placeholder="🔍 Search elements..." id="amari-element-search">
                </div>
                <div id="amari-element-palette">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Layout / Sections Navigator Tab -->
            <div class="amari-panel-content" id="amari-panel-layout" style="display:none;">
                <div style="padding:12px;font-size:0.8rem;color:#999;">Click a section, row, or element to navigate to it.</div>
                <div id="amari-layout-tree">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Page Settings Tab -->
            <div class="amari-panel-content" id="amari-panel-settings" style="display:none;">
                <div class="amari-settings-group">
                    <label>Page Title</label>
                    <input type="text" id="amari-page-title" value="<?php echo esc_attr($post->post_title); ?>">
                </div>
                <div class="amari-settings-group">
                    <label>Header Style</label>
                    <select id="amari-header-style">
                        <option value="default">Default</option>
                        <option value="transparent">Transparent</option>
                        <option value="none">Hidden</option>
                    </select>
                </div>
                <div class="amari-settings-group">
                    <label>Footer</label>
                    <select id="amari-footer-style">
                        <option value="default">Default</option>
                        <option value="none">Hidden</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CANVAS -->
        <div class="amari-builder-canvas-wrap">
            <div class="amari-builder-canvas" id="amari-canvas" data-view="desktop">
                <div class="amari-canvas-inner" id="amari-canvas-inner">
                    <!-- Sections rendered here -->
                </div>
                <div class="amari-canvas-empty" id="amari-canvas-empty">
                    <div class="amari-canvas-empty-inner">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
                        <h3>Start Building</h3>
                        <p>Add a section to get started, or drag an element directly onto the canvas.</p>
                        <button class="amari-btn-add-section" id="amari-add-first-section">
                            + Add Section
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Element Settings -->
        <div class="amari-builder-panel-right" id="amari-settings-panel">
            <div class="amari-settings-panel-header">
                <span id="amari-settings-title">Select an element</span>
                <button class="amari-settings-close" id="amari-settings-close">✕</button>
            </div>
            <div class="amari-settings-panel-body" id="amari-settings-body">
                <div class="amari-settings-empty">
                    <p>Click any element on the canvas to edit its settings here.</p>
                </div>
            </div>
        </div>

    </div><!-- /.amari-builder-main -->

    <!-- SAVE STATUS BAR -->
    <div class="amari-builder-status" id="amari-status-bar" style="display:none;">
        <span id="amari-status-text"></span>
    </div>

</div><!-- /#amari-builder-overlay -->

<!-- ADD SECTION MODAL -->
<div class="amari-modal-backdrop" id="amari-add-section-modal" style="display:none;">
    <div class="amari-modal">
        <div class="amari-modal-header">
            <h3>Choose Layout</h3>
            <button class="amari-modal-close">✕</button>
        </div>
        <div class="amari-modal-body">
            <p style="color:#999;margin-bottom:16px;font-size:0.85rem;">Select a column layout for this section</p>
            <div class="amari-layout-grid" id="amari-layout-options">
                <!-- Layouts injected by JS -->
            </div>
        </div>
    </div>
</div>

<!-- COLUMN LAYOUT PICKER (inline) -->
<div class="amari-layout-picker-dropdown" id="amari-layout-picker" style="display:none;">
    <!-- Populated by JS -->
</div>
