<?php
/**
 * Template Library Modal — rendered in both admin footer and frontend editor.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="amari-modal-backdrop" id="amari-templates-modal" style="display:none;" onclick="if(event.target===this)AmariTemplateLibrary.close()">
    <div class="amari-modal" style="width:860px;max-height:90vh;display:flex;flex-direction:column;">
        <div class="amari-modal-header">
            <h3>📚 Template Library</h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <input type="text" id="amari-tpl-search" class="amari-element-search" style="width:220px;" placeholder="🔍 Search templates...">
                <button class="amari-modal-close" onclick="AmariTemplateLibrary.close()">✕</button>
            </div>
        </div>

        <!-- Category Filters -->
        <div style="padding:12px 20px;border-bottom:1px solid var(--ab-border);display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0;">
            <button class="amari-tpl-cat-btn active" data-cat="all">All</button>
            <button class="amari-tpl-cat-btn" data-cat="Hero">Hero</button>
            <button class="amari-tpl-cat-btn" data-cat="Features">Features</button>
            <button class="amari-tpl-cat-btn" data-cat="About">About</button>
            <button class="amari-tpl-cat-btn" data-cat="Stats">Stats</button>
            <button class="amari-tpl-cat-btn" data-cat="Testimonials">Testimonials</button>
            <button class="amari-tpl-cat-btn" data-cat="Pricing">Pricing</button>
            <button class="amari-tpl-cat-btn" data-cat="FAQ">FAQ</button>
            <button class="amari-tpl-cat-btn" data-cat="CTA">CTA</button>
            <button class="amari-tpl-cat-btn" data-cat="Contact">Contact</button>
        </div>

        <!-- Template Grid -->
        <div class="amari-modal-body" style="flex:1;overflow-y:auto;" id="amari-templates-grid">
            <div style="padding:40px;text-align:center;color:var(--ab-text-muted);">Loading templates...</div>
        </div>
    </div>
</div>

<style>
.amari-tpl-cat-btn {
    padding: 5px 14px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--ab-border);
    border-radius: 20px;
    color: var(--ab-text-muted);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.18s;
}
.amari-tpl-cat-btn:hover { background: rgba(255,255,255,0.1); color: var(--ab-text); }
.amari-tpl-cat-btn.active { background: var(--ab-highlight); border-color: var(--ab-accent); color: var(--ab-accent); }

.amari-tpl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 14px;
}

.amari-tpl-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--ab-border);
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.2s;
    cursor: pointer;
}
.amari-tpl-card:hover {
    border-color: var(--ab-accent);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(233,69,96,0.15);
}

.amari-tpl-thumb {
    height: 120px;
    background: linear-gradient(135deg, var(--ab-panel-alt) 0%, var(--ab-panel) 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.amari-tpl-thumb-placeholder {
    font-size: 2rem;
    opacity: 0.4;
}
.amari-tpl-thumb-overlay {
    position: absolute;
    inset: 0;
    background: rgba(233,69,96,0.0);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.amari-tpl-card:hover .amari-tpl-thumb-overlay { background: rgba(233,69,96,0.12); }
.amari-tpl-insert-btn {
    opacity: 0;
    background: var(--ab-accent);
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    font-family: inherit;
    transition: opacity 0.2s;
}
.amari-tpl-card:hover .amari-tpl-insert-btn { opacity: 1; }

.amari-tpl-info { padding: 12px; }
.amari-tpl-name { font-size: 13px; font-weight: 700; color: var(--ab-text); margin-bottom: 4px; }
.amari-tpl-desc { font-size: 11px; color: var(--ab-text-muted); line-height: 1.4; }
.amari-tpl-cat-tag { display:inline-block;font-size:10px;background:var(--ab-highlight);color:var(--ab-accent);padding:2px 8px;border-radius:20px;margin-top:6px;font-weight:600; }

.amari-tpl-inserting {
    display: flex; align-items: center; justify-content: center;
    padding: 40px; text-align: center; color: var(--ab-text-muted);
}
</style>
