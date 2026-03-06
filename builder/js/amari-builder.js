/**
 * Amari Builder — Core JavaScript Engine
 *
 * Architecture:
 *  AmariBuilder      — main controller, state management, init
 *  AmariCanvas       — renders builder data to DOM
 *  AmariDragDrop     — drag-and-drop engine (palette → canvas, reorder)
 *  AmariSettings     — right-panel settings rendering & binding
 *  AmariHistory      — undo/redo stack
 *  AmariModal        — section layout picker modal
 *  AmariStorage      — save/load via AJAX
 */

'use strict';

/* ==========================================================================
   UTILITIES
   ========================================================================== */

const uid = () => '_' + Math.random().toString(36).slice(2, 10);
const deepClone = obj => JSON.parse(JSON.stringify(obj));
const $ = (sel, ctx) => (ctx || document).querySelector(sel);
const $$ = (sel, ctx) => [...(ctx || document).querySelectorAll(sel)];
const on = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);

/* ==========================================================================
   HISTORY (Undo/Redo)
   ========================================================================== */

class AmariHistory {
    constructor(maxSteps = 50) {
        this.stack = [];
        this.cursor = -1;
        this.max = maxSteps;
    }
    push(state) {
        this.stack = this.stack.slice(0, this.cursor + 1);
        this.stack.push(deepClone(state));
        if (this.stack.length > this.max) this.stack.shift();
        this.cursor = this.stack.length - 1;
        this.updateButtons();
    }
    undo() {
        if (this.cursor <= 0) return null;
        this.cursor--;
        this.updateButtons();
        return deepClone(this.stack[this.cursor]);
    }
    redo() {
        if (this.cursor >= this.stack.length - 1) return null;
        this.cursor++;
        this.updateButtons();
        return deepClone(this.stack[this.cursor]);
    }
    canUndo() { return this.cursor > 0; }
    canRedo() { return this.cursor < this.stack.length - 1; }
    updateButtons() {
        const undoBtn = $('#amari-undo-btn');
        const redoBtn = $('#amari-redo-btn');
        if (undoBtn) undoBtn.disabled = !this.canUndo();
        if (redoBtn) redoBtn.disabled = !this.canRedo();
    }
}

/* ==========================================================================
   SETTINGS PANEL
   ========================================================================== */

class AmariSettings {
    constructor(builder) {
        this.builder = builder;
        this.currentTarget = null; // { type: 'element'|'row'|'section', id }
    }

    showElement(elementData) {
        this.currentTarget = { type: 'element', data: elementData };
        const config = AmariBuilderConfig.elements[elementData.type];
        if (!config) { this.showEmpty(); return; }

        $('#amari-settings-title').textContent = config.label + ' Settings';
        const body = $('#amari-settings-body');
        body.innerHTML = '';

        const settings = Object.assign({}, config.defaults, elementData.settings || {});

        config.controls.forEach(ctrl => {
            body.appendChild(this.renderControl(ctrl, settings[ctrl.id], elementData));
        });

        // Tabs for advanced settings
        const advSection = document.createElement('div');
        advSection.innerHTML = `
            <hr class="amari-settings-sep">
            <div class="amari-settings-section-title">Advanced</div>
        `;
        body.appendChild(advSection);

        this.bindControls(body, elementData);
    }

    showSection(sectionData) {
        this.currentTarget = { type: 'section', data: sectionData };
        $('#amari-settings-title').textContent = 'Section Settings';
        const body = $('#amari-settings-body');
        body.innerHTML = '';

        const s = sectionData.settings || {};
        const controls = [
            { id: 'bg_color',    type: 'color',    label: 'Background Color' },
            { id: 'bg_image',    type: 'image',    label: 'Background Image' },
            { id: 'padding',     type: 'text',     label: 'Padding', placeholder: '60px 0' },
            { id: 'min_height',  type: 'text',     label: 'Min Height', placeholder: 'e.g. 500px' },
            { id: 'full_width',  type: 'toggle',   label: 'Full Width (no container)' },
            { id: 'css_class',   type: 'text',     label: 'CSS Class' },
        ];

        controls.forEach(ctrl => {
            body.appendChild(this.renderControl(ctrl, s[ctrl.id] || '', sectionData));
        });

        this.bindControls(body, sectionData, 'section');
    }

    showEmpty() {
        $('#amari-settings-title').textContent = 'Settings';
        $('#amari-settings-body').innerHTML = `<div class="amari-settings-empty"><p>Click any element on the canvas to edit its settings.</p></div>`;
    }

    renderControl(ctrl, value, elementData) {
        const group = document.createElement('div');
        group.className = 'amari-settings-group';
        group.dataset.controlId = ctrl.id;

        const label = document.createElement('label');
        label.textContent = ctrl.label;
        group.appendChild(label);

        let input;

        switch (ctrl.type) {
            case 'text':
            case 'url':
            case 'number':
            case 'email':
                input = document.createElement('input');
                input.type = ctrl.type === 'url' ? 'text' : (ctrl.type === 'number' ? 'number' : 'text');
                input.value = value || '';
                input.placeholder = ctrl.placeholder || '';
                group.appendChild(input);
                break;

            case 'textarea':
                input = document.createElement('textarea');
                input.value = value || '';
                input.placeholder = ctrl.placeholder || '';
                input.rows = 4;
                group.appendChild(input);
                break;

            case 'richtext':
                // Simplified richtext (full TinyMCE integration needs WP media)
                input = document.createElement('textarea');
                input.value = value || '';
                input.placeholder = ctrl.placeholder || 'Enter rich text content...';
                input.rows = 6;
                input.className = 'amari-richtext-input';
                input.dataset.richtext = '1';
                group.appendChild(input);
                const rtNote = document.createElement('div');
                rtNote.className = 'amari-control-description';
                rtNote.textContent = 'HTML allowed (bold, italic, links, etc.)';
                group.appendChild(rtNote);
                break;

            case 'select':
                input = document.createElement('select');
                (ctrl.options || []).forEach(opt => {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.label;
                    if ((value || ctrl.default || '') == opt.value) o.selected = true;
                    input.appendChild(o);
                });
                group.appendChild(input);
                break;

            case 'color':
                const colorRow = document.createElement('div');
                colorRow.className = 'amari-color-row';
                const colorPicker = document.createElement('input');
                colorPicker.type = 'color';
                colorPicker.value = value || '#333333';
                colorPicker.dataset.colorPicker = '1';
                const colorText = document.createElement('input');
                colorText.type = 'text';
                colorText.value = value || '';
                colorText.placeholder = '#000000 or rgba(...)';
                colorText.dataset.colorText = '1';
                // Sync
                colorPicker.addEventListener('input', () => { colorText.value = colorPicker.value; });
                colorText.addEventListener('input', () => {
                    if (/^#[0-9a-f]{6}$/i.test(colorText.value)) colorPicker.value = colorText.value;
                });
                colorRow.appendChild(colorPicker);
                colorRow.appendChild(colorText);
                group.appendChild(colorRow);
                input = colorText; // track the text input
                break;

            case 'toggle':
                const toggleWrap = document.createElement('div');
                toggleWrap.className = 'amari-toggle-wrap';
                const toggleLabel = document.createElement('label');
                toggleLabel.className = 'amari-toggle';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = value === true || value === 'true' || value === 1;
                const slider = document.createElement('span');
                slider.className = 'amari-toggle-slider';
                toggleLabel.appendChild(checkbox);
                toggleLabel.appendChild(slider);
                toggleWrap.appendChild(toggleLabel);
                group.removeChild(label); // remove label, re-add inline
                const inlineLabel = document.createElement('span');
                inlineLabel.textContent = ctrl.label;
                inlineLabel.style.fontSize = '12px';
                inlineLabel.style.color = 'var(--ab-text)';
                toggleWrap.appendChild(inlineLabel);
                group.appendChild(toggleWrap);
                input = checkbox;
                break;

            case 'image':
                const imgPicker = document.createElement('div');
                imgPicker.className = 'amari-image-picker';
                const imgPreview = document.createElement('div');
                imgPreview.className = 'amari-image-preview';
                if (value) {
                    imgPreview.innerHTML = `<img src="${value}" alt="">`;
                } else {
                    imgPreview.innerHTML = `<div class="amari-image-preview-empty">📷 No image selected</div>`;
                }
                const imgInput = document.createElement('input');
                imgInput.type = 'hidden';
                imgInput.value = value || '';
                imgInput.dataset.imageUrl = '1';
                const imgBtn = document.createElement('button');
                imgBtn.type = 'button';
                imgBtn.className = 'amari-image-picker-btn';
                imgBtn.textContent = value ? '🔄 Change Image' : '📷 Select Image';
                imgBtn.addEventListener('click', () => {
                    this.openMediaPicker(imgInput, imgPreview, imgBtn);
                });
                imgPicker.appendChild(imgPreview);
                imgPicker.appendChild(imgInput);
                imgPicker.appendChild(imgBtn);
                group.appendChild(imgPicker);
                input = imgInput;
                break;

            default:
                input = document.createElement('input');
                input.type = 'text';
                input.value = value || '';
                group.appendChild(input);
        }

        if (ctrl.description) {
            const desc = document.createElement('div');
            desc.className = 'amari-control-description';
            desc.textContent = ctrl.description;
            group.appendChild(desc);
        }

        return group;
    }

    bindControls(container, targetData, targetType = 'element') {
        // Live update on any input change
        container.addEventListener('input', (e) => this.handleControlChange(e, targetData, targetType));
        container.addEventListener('change', (e) => this.handleControlChange(e, targetData, targetType));
    }

    handleControlChange(e, targetData, targetType) {
        const group = e.target.closest('[data-control-id]');
        if (!group) return;
        const ctrlId = group.dataset.controlId;

        let value;
        if (e.target.type === 'checkbox') {
            value = e.target.checked;
        } else if (e.target.dataset.imageUrl) {
            value = e.target.value;
        } else if (e.target.dataset.colorPicker) {
            return; // handled by colorText sync
        } else {
            value = e.target.value;
        }

        // Update the data model
        if (targetType === 'element') {
            if (!targetData.settings) targetData.settings = {};
            targetData.settings[ctrlId] = value;
            // Re-render element preview on canvas
            this.builder.canvas.updateElementPreview(targetData);
        } else if (targetType === 'section') {
            if (!targetData.settings) targetData.settings = {};
            targetData.settings[ctrlId] = value;
            this.builder.canvas.updateSectionStyle(targetData);
        }

        // Debounced history push
        clearTimeout(this._historyTimer);
        this._historyTimer = setTimeout(() => {
            this.builder.history.push(deepClone(this.builder.data));
        }, 600);
    }

    openMediaPicker(hiddenInput, preview, btn) {
        if (typeof wp === 'undefined' || !wp.media) {
            const url = prompt('Enter image URL:');
            if (url) {
                hiddenInput.value = url;
                preview.innerHTML = `<img src="${url}" alt="">`;
                btn.textContent = '🔄 Change Image';
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            return;
        }
        const frame = wp.media({
            title: 'Select Image',
            button: { text: 'Use Image' },
            multiple: false,
        });
        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            hiddenInput.value = attachment.url;
            preview.innerHTML = `<img src="${attachment.url}" alt="${attachment.alt || ''}">`;
            btn.textContent = '🔄 Change Image';
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
        frame.open();
    }
}

/* ==========================================================================
   CANVAS RENDERER
   ========================================================================== */

class AmariCanvas {
    constructor(builder) {
        this.builder = builder;
        this.container = $('#amari-canvas-inner');
        this.emptyState = $('#amari-canvas-empty');
    }

    render(data) {
        this.container.innerHTML = '';
        if (!data.sections || data.sections.length === 0) {
            this.showEmpty();
            return;
        }
        this.hideEmpty();
        data.sections.forEach(section => this.renderSection(section));
        this.renderAddSectionBar();
    }

    showEmpty() {
        if (this.emptyState) this.emptyState.classList.add('visible');
    }
    hideEmpty() {
        if (this.emptyState) this.emptyState.classList.remove('visible');
    }

    renderSection(section) {
        const el = document.createElement('div');
        el.className = 'ab-section';
        el.dataset.sectionId = section.id;

        // Apply background/padding styles
        const s = section.settings || {};
        if (s.bg_color)  el.style.backgroundColor = s.bg_color;
        if (s.padding)   el.style.padding = s.padding;
        if (s.min_height) el.style.minHeight = s.min_height;

        // Label & controls
        el.innerHTML = `
            <div class="ab-section-label" title="Drag to reorder">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px;"><circle cx="9" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                Section
            </div>
            <div class="ab-section-controls">
                <button class="ab-ctrl-btn ab-ctrl-settings" title="Section Settings" data-action="section-settings" data-id="${section.id}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </button>
                <button class="ab-ctrl-btn" title="Move Up" data-action="section-up" data-id="${section.id}">↑</button>
                <button class="ab-ctrl-btn" title="Move Down" data-action="section-down" data-id="${section.id}">↓</button>
                <button class="ab-ctrl-btn" title="Duplicate" data-action="section-duplicate" data-id="${section.id}">⧉</button>
                <button class="ab-ctrl-btn ab-ctrl-delete" title="Delete Section" data-action="section-delete" data-id="${section.id}">✕</button>
            </div>
        `;

        // Rows
        const rowsWrap = document.createElement('div');
        rowsWrap.className = 'ab-section-rows';
        (section.rows || []).forEach(row => rowsWrap.appendChild(this.renderRow(row, section)));
        el.appendChild(rowsWrap);

        // Add row button
        const addRowBtn = document.createElement('button');
        addRowBtn.className = 'ab-ctrl-btn';
        addRowBtn.style.cssText = 'display:block;margin:6px auto;font-size:11px;';
        addRowBtn.dataset.action = 'row-add';
        addRowBtn.dataset.sectionId = section.id;
        addRowBtn.textContent = '+ Add Row';
        el.appendChild(addRowBtn);

        this.container.appendChild(el);
        return el;
    }

    renderRow(row, section) {
        const el = document.createElement('div');
        el.className = 'ab-row';
        el.dataset.rowId = row.id;
        el.dataset.sectionId = section.id;

        el.innerHTML = `
            <div class="ab-row-controls" style="left:-36px;">
                <button class="ab-ctrl-btn" title="Row Layout" data-action="row-layout" data-id="${row.id}" style="font-size:9px;">⊞</button>
                <button class="ab-ctrl-btn" title="Duplicate Row" data-action="row-duplicate" data-id="${row.id}" style="font-size:9px;">⧉</button>
                <button class="ab-ctrl-btn ab-ctrl-delete" title="Delete Row" data-action="row-delete" data-id="${row.id}" style="font-size:9px;">✕</button>
            </div>
        `;

        (row.columns || []).forEach(col => el.appendChild(this.renderColumn(col, row, section)));
        return el;
    }

    renderColumn(col, row, section) {
        const el = document.createElement('div');
        el.className = 'ab-col';
        el.dataset.colId = col.id;
        el.dataset.rowId = row.id;
        el.dataset.sectionId = section.id;

        // Apply column size
        const sizeMap = {
            '1-1': '100%', '1-2': '50%', '1-3': '33.333%',
            '2-3': '66.667%', '1-4': '25%', '3-4': '75%',
            '2-5': '40%', '3-5': '60%',
        };
        el.style.flex = '0 0 ' + (sizeMap[col.size] || '100%');
        el.style.maxWidth = sizeMap[col.size] || '100%';

        // Empty hint
        if (!col.elements || col.elements.length === 0) {
            const hint = document.createElement('div');
            hint.className = 'ab-col-empty-hint';
            hint.textContent = 'Drag element here';
            el.appendChild(hint);
        }

        // Elements
        (col.elements || []).forEach(elemData => {
            el.appendChild(this.renderElement(elemData, col, row, section));
        });

        return el;
    }

    renderElement(elemData, col, row, section) {
        const el = document.createElement('div');
        el.className = 'ab-element';
        el.dataset.elementId = elemData.id;
        el.dataset.colId = col.id;
        el.dataset.rowId = row.id;
        el.dataset.sectionId = section.id;
        el.draggable = true;

        const config = AmariBuilderConfig.elements[elemData.type];
        const label = config ? config.label : elemData.type;

        // Controls bar
        el.innerHTML = `
            <div class="ab-element-controls">
                <button class="ab-ctrl-btn" title="Move Up" data-action="el-up" data-id="${elemData.id}">↑</button>
                <button class="ab-ctrl-btn" title="Move Down" data-action="el-down" data-id="${elemData.id}">↓</button>
                <button class="ab-ctrl-btn" title="Duplicate" data-action="el-duplicate" data-id="${elemData.id}">⧉</button>
                <button class="ab-ctrl-btn ab-ctrl-delete" title="Delete" data-action="el-delete" data-id="${elemData.id}">✕</button>
            </div>
        `;

        // Preview area
        const preview = document.createElement('div');
        preview.className = 'ab-element-preview';
        preview.style.cssText = 'pointer-events:none;padding:4px;';
        preview.innerHTML = this.getElementPreviewHTML(elemData);
        el.appendChild(preview);

        return el;
    }

    getElementPreviewHTML(elemData) {
        const config = AmariBuilderConfig.elements[elemData.type];
        const s = Object.assign({}, config ? config.defaults : {}, elemData.settings || {});
        const label = config ? config.label : elemData.type;
        const icon = config ? config.icon : '';

        // Simple previews per type
        switch (elemData.type) {
            case 'heading':
                const tag = s.tag || 'h2';
                const sizes = { h1:'2rem', h2:'1.6rem', h3:'1.3rem', h4:'1.1rem', h5:'1rem', h6:'0.9rem' };
                return `<${tag} style="font-size:${sizes[tag]};color:${s.color||'#1a1a2e'};text-align:${s.align||'left'};margin:0;padding:4px 0;">${s.text || 'Heading'}</${tag}>`;

            case 'text-block':
                return `<div style="color:${s.color||'#333'};font-size:0.9rem;max-height:80px;overflow:hidden;padding:4px 0;">${s.content || 'Text block content...'}</div>`;

            case 'button':
                return `<div style="text-align:${s.align||'left'};padding:4px 0;"><span style="display:inline-block;padding:8px 20px;background:${s.style==='secondary'?'transparent':'#e94560'};color:${s.style==='secondary'?'#1a1a2e':'#fff'};border:2px solid ${s.style==='secondary'?'#1a1a2e':'#e94560'};border-radius:6px;font-size:0.85rem;font-weight:600;">${s.label||'Button'}</span></div>`;

            case 'image':
                return s.url ? `<img src="${s.url}" style="max-height:120px;max-width:100%;display:block;border-radius:4px;" alt="">` :
                    `<div style="background:#f0f0f0;border:2px dashed #ddd;padding:20px;text-align:center;border-radius:6px;color:#aaa;font-size:0.8rem;">📷 Image</div>`;

            case 'video':
                return `<div style="background:#000;border-radius:6px;padding:20px;text-align:center;color:#fff;font-size:0.8rem;">▶ Video: ${(s.url||'').slice(0,40) || 'No URL'}</div>`;

            case 'spacer':
                return `<div style="background:repeating-linear-gradient(45deg,#f5f5f5,#f5f5f5 5px,#e0e0e0 5px,#e0e0e0 10px);height:${s.height||'40px'};border-radius:4px;display:flex;align-items:center;justify-content:center;"><span style="background:#fff;padding:2px 8px;border-radius:3px;font-size:11px;color:#999;">${s.height||'40px'} space</span></div>`;

            case 'divider':
                return `<hr style="border:none;border-top:${s.thickness||'1px'} ${s.style||'solid'} ${s.color||'#e5e7eb'};margin:8px 0;">`;

            case 'icon-box':
                return `<div style="text-align:${s.align||'center'};padding:8px 4px;"><div style="font-size:${s.icon_size||'2rem'};color:${s.icon_color||'#e94560'};">${s.icon||'⭐'}</div><h4 style="font-size:0.95rem;margin:4px 0 2px;">${s.title||'Icon Box'}</h4><p style="font-size:0.8rem;color:#666;margin:0;">${(s.content||'').slice(0,60)}</p></div>`;

            case 'contact-form':
                return `<div style="background:#f9f9f9;border:1px solid #eee;border-radius:8px;padding:12px;font-size:0.8rem;color:#666;"><div style="margin-bottom:6px;"><div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:6px 10px;margin-bottom:4px;">📧 Email</div><div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:6px 10px;">💬 Message</div></div><span style="background:#e94560;color:#fff;padding:4px 12px;border-radius:4px;font-size:0.75rem;">${s.submit_text||'Send'}</span></div>`;

            case 'portfolio-grid':
                return `<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;padding:4px;"><div style="background:#f0f0f0;border-radius:4px;aspect-ratio:1;"></div><div style="background:#f0f0f0;border-radius:4px;aspect-ratio:1;"></div><div style="background:#f0f0f0;border-radius:4px;aspect-ratio:1;"></div></div><p style="text-align:center;font-size:0.75rem;color:#999;margin:4px 0 0;">Portfolio Grid (${s.columns||3} cols)</p>`;

            case 'testimonial':
                return `<div style="background:#fff;border:1px solid #eee;border-radius:8px;padding:12px;"><p style="font-style:italic;font-size:0.8rem;color:#666;margin-bottom:8px;">"${(s.quote||'Testimonial quote...').slice(0,80)}"</p><strong style="font-size:0.8rem;">${s.name||'Name'}</strong> <span style="font-size:0.75rem;color:#999;">${s.role||'Role'}</span></div>`;

            case 'code-block':
                return `<div style="background:#1a1a2e;color:#7dd3fc;font-family:monospace;padding:10px;border-radius:6px;font-size:0.75rem;max-height:60px;overflow:hidden;">${'</>  Custom Code Block'}</div>`;

            default:
                return `<div style="background:#f0f0f0;border:1px solid #ddd;border-radius:6px;padding:12px;text-align:center;display:flex;align-items:center;gap:8px;justify-content:center;">${icon}<span style="font-size:0.85rem;font-weight:600;color:#666;">${label}</span></div>`;
        }
    }

    updateElementPreview(elemData) {
        const el = $(`.ab-element[data-element-id="${elemData.id}"] .ab-element-preview`);
        if (el) el.innerHTML = this.getElementPreviewHTML(elemData);
    }

    updateSectionStyle(sectionData) {
        const el = $(`.ab-section[data-section-id="${sectionData.id}"]`);
        if (!el) return;
        const s = sectionData.settings || {};
        el.style.backgroundColor = s.bg_color || '';
        el.style.padding = s.padding || '';
        el.style.minHeight = s.min_height || '';
    }

    renderAddSectionBar() {
        const bar = document.createElement('div');
        bar.className = 'ab-add-section-bar';
        bar.innerHTML = `<button class="ab-add-section-btn" data-action="add-section">+ Add Section</button>`;
        this.container.appendChild(bar);
    }
}

/* ==========================================================================
   DRAG AND DROP
   ========================================================================== */

class AmariDragDrop {
    constructor(builder) {
        this.builder = builder;
        this.dragging = null;       // current drag source info
        this.ghost = null;          // visual ghost element
        this.dropTarget = null;     // current drop target info
        this._bindPalette();
        this._bindCanvas();
    }

    _bindPalette() {
        on(document, 'mousedown', e => {
            const tile = e.target.closest('.amari-element-tile');
            if (!tile) return;
            this._startPaletteDrag(e, tile);
        });
    }

    _bindCanvas() {
        const canvas = $('#amari-canvas-inner');
        if (!canvas) return;
        on(canvas, 'mousemove', e => this._onMouseMove(e));
        on(canvas, 'mouseup', e => this._onMouseUp(e));
        on(document, 'mouseup', e => this._onMouseUp(e));
    }

    _startPaletteDrag(e, tile) {
        e.preventDefault();
        const type = tile.dataset.type;
        const label = tile.querySelector('.amari-element-tile-label')?.textContent || type;
        const icon  = tile.querySelector('svg')?.outerHTML || '';

        this.dragging = { source: 'palette', type, label };
        tile.classList.add('dragging');

        this._createGhost(e, icon, label);

        const onMove = ev => this._onMouseMove(ev);
        const onUp   = ev => { this._onMouseUp(ev); document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); tile.classList.remove('dragging'); };

        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }

    _createGhost(e, icon, label) {
        if (this.ghost) this.ghost.remove();
        this.ghost = document.createElement('div');
        this.ghost.className = 'amari-drag-ghost';
        this.ghost.innerHTML = `${icon}<span>${label}</span>`;
        document.body.appendChild(this.ghost);
        this._positionGhost(e);
    }

    _positionGhost(e) {
        if (!this.ghost) return;
        this.ghost.style.left = (e.clientX + 14) + 'px';
        this.ghost.style.top  = (e.clientY - 14) + 'px';
    }

    _onMouseMove(e) {
        if (!this.dragging) return;
        this._positionGhost(e);

        // Find drop target
        const col = e.target.closest('.ab-col');
        $$('.ab-col').forEach(c => c.classList.remove('ab-drag-over'));
        if (col) {
            col.classList.add('ab-drag-over');
            this.dropTarget = {
                colId:     col.dataset.colId,
                rowId:     col.dataset.rowId,
                sectionId: col.dataset.sectionId,
            };
        } else {
            this.dropTarget = null;
        }
    }

    _onMouseUp(e) {
        if (!this.dragging) return;

        if (this.ghost) { this.ghost.remove(); this.ghost = null; }
        $$('.ab-col').forEach(c => c.classList.remove('ab-drag-over'));

        if (this.dragging.source === 'palette' && this.dropTarget) {
            this.builder.actions.addElement(this.dragging.type, this.dropTarget);
        }

        this.dragging = null;
        this.dropTarget = null;
    }
}

/* ==========================================================================
   MAIN BUILDER CONTROLLER
   ========================================================================== */

class AmariBuilderController {
    constructor() {
        this.postId  = 0;
        this.data    = { sections: [] };
        this.history = new AmariHistory();
        this.canvas  = new AmariCanvas(this);
        this.settings = new AmariSettings(this);
        this.dragDrop = null; // init after DOM ready
        this.isDirty = false;
        this.selectedElement = null;
    }

    /* ── Init ── */

    init() {
        const overlay = $('#amari-builder-overlay');
        if (!overlay) return;

        this.postId = parseInt(overlay.dataset.postId || '0');
        this.dragDrop = new AmariDragDrop(this);
        this.actions = new AmariActions(this);

        this._bindGlobalEvents();
        this._buildPalette();
    }

    open() {
        const overlay = $('#amari-builder-overlay');
        if (!overlay) return;

        overlay.style.display = 'flex';
        document.body.classList.add('amari-builder-active');

        // Load data
        this._loadData();
    }

    close(skipConfirm = false) {
        if (this.isDirty && !skipConfirm) {
            if (!confirm(AmariBuilderConfig.i18n.unsaved_changes)) return;
        }
        const overlay = $('#amari-builder-overlay');
        if (overlay) overlay.style.display = 'none';
        document.body.classList.remove('amari-builder-active');
    }

    /* ── Data Loading ── */

    _loadData() {
        this.canvas.container.innerHTML = '<div style="padding:60px;text-align:center;color:#aaa;">Loading...</div>';
        fetch(AmariBuilderConfig.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action:   'amari_get_builder_data',
                nonce:    AmariBuilderConfig.nonce,
                post_id:  this.postId,
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                this.data = res.data.data || { sections: [] };
                this.history.push(deepClone(this.data));
                this.canvas.render(this.data);
                this._rebuildLayoutTree();
                this.isDirty = false;
            } else {
                this.data = { sections: [] };
                this.canvas.render(this.data);
            }
        })
        .catch(() => {
            this.data = { sections: [] };
            this.canvas.render(this.data);
        });
    }

    /* ── Save ── */

    save() {
        const btn = $('#amari-save-btn');
        if (btn) { btn.classList.add('saving'); btn.textContent = '⏳ Saving...'; }

        fetch(AmariBuilderConfig.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action:       'amari_save_builder',
                nonce:        AmariBuilderConfig.nonce,
                post_id:      this.postId,
                builder_data: JSON.stringify(this.data),
            })
        })
        .then(r => r.json())
        .then(res => {
            if (btn) { btn.classList.remove('saving'); btn.innerHTML = '💾 Save'; }
            if (res.success) {
                this.isDirty = false;
                this._showStatus('✓ Saved!', 'success');
            } else {
                this._showStatus('✗ Save failed', 'error');
            }
        })
        .catch(() => {
            if (btn) { btn.classList.remove('saving'); btn.textContent = '💾 Save'; }
            this._showStatus('✗ Network error', 'error');
        });
    }

    /* ── Publish ── */

    publish() {
        const btn = $('#amari-publish-btn');
        if (btn) { btn.classList.add('publishing'); btn.textContent = '⏳ Publishing...'; }

        fetch(AmariBuilderConfig.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action:       'amari_publish_page',
                nonce:        AmariBuilderConfig.nonce,
                post_id:      this.postId,
                builder_data: JSON.stringify(this.data),
            })
        })
        .then(r => r.json())
        .then(res => {
            if (btn) {
                btn.classList.remove('publishing');
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Publish';
            }
            if (res.success) {
                this.isDirty = false;
                this._showStatus('✓ Published!', 'success');
                // Offer to view the live page
                if (res.data && res.data.permalink) {
                    setTimeout(() => {
                        if (confirm('Page published! View it now?')) {
                            window.open(res.data.permalink, '_blank');
                        }
                    }, 500);
                }
            } else {
                this._showStatus('✗ Publish failed: ' + (res.data?.message || 'Unknown error'), 'error');
            }
        })
        .catch(() => {
            if (btn) {
                btn.classList.remove('publishing');
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Publish';
            }
            this._showStatus('✗ Network error', 'error');
        });
    }

    /* ── Status ── */

    _showStatus(msg, type = '') {
        const bar = $('#amari-status-bar');
        if (!bar) return;
        bar.textContent = msg;
        bar.className = 'amari-builder-status' + (type ? ' ' + type : '');
        bar.style.display = 'block';
        clearTimeout(this._statusTimer);
        this._statusTimer = setTimeout(() => { bar.style.display = 'none'; }, 2500);
    }

    /* ── Palette ── */

    _buildPalette() {
        const palette = $('#amari-element-palette');
        if (!palette) return;

        const groups = {
            basic:       { label: 'Basic', elements: [] },
            media:       { label: 'Media', elements: [] },
            interactive: { label: 'Interactive', elements: [] },
            advanced:    { label: 'Advanced', elements: [] },
        };

        Object.values(AmariBuilderConfig.elements).forEach(cfg => {
            const g = groups[cfg.group] || groups.basic;
            g.elements.push(cfg);
        });

        palette.innerHTML = '';
        Object.entries(groups).forEach(([key, group]) => {
            if (!group.elements.length) return;
            const label = document.createElement('div');
            label.className = 'amari-element-group-label';
            label.textContent = group.label;
            palette.appendChild(label);

            const grid = document.createElement('div');
            grid.className = 'amari-element-grid';

            group.elements.forEach(cfg => {
                const tile = document.createElement('div');
                tile.className = 'amari-element-tile';
                tile.dataset.type = cfg.type;
                tile.title = `Drag to add ${cfg.label}`;
                tile.innerHTML = `${cfg.icon}<span class="amari-element-tile-label">${cfg.label}</span>`;
                tile.addEventListener('dblclick', () => this.actions.addElementToFirstAvailableCol(cfg.type));
                grid.appendChild(tile);
            });

            palette.appendChild(grid);
        });
    }

    /* ── Layout Tree ── */

    _rebuildLayoutTree() {
        const tree = $('#amari-layout-tree');
        if (!tree) return;
        tree.innerHTML = '';

        (this.data.sections || []).forEach((section, si) => {
            const sDiv = document.createElement('div');
            sDiv.className = 'ab-tree-section';

            const header = document.createElement('div');
            header.className = 'ab-tree-section-header';
            header.innerHTML = `<span class="ab-tree-icon">▸</span> Section ${si + 1}`;
            header.addEventListener('click', () => {
                const el = $(`.ab-section[data-section-id="${section.id}"]`);
                el && el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                this.settings.showSection(section);
            });
            sDiv.appendChild(header);

            const children = document.createElement('div');
            children.className = 'ab-tree-children';

            (section.rows || []).forEach((row, ri) => {
                const rowEl = document.createElement('div');
                rowEl.className = 'ab-tree-row';
                rowEl.textContent = `↳ Row ${ri + 1}`;
                children.appendChild(rowEl);

                (row.columns || []).forEach(col => {
                    (col.elements || []).forEach(elem => {
                        const cfg = AmariBuilderConfig.elements[elem.type];
                        const elemEl = document.createElement('div');
                        elemEl.className = 'ab-tree-element';
                        elemEl.textContent = `  • ${cfg ? cfg.label : elem.type}`;
                        elemEl.addEventListener('click', () => {
                            const el = $(`.ab-element[data-element-id="${elem.id}"]`);
                            el && el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            this.settings.showElement(elem);
                        });
                        children.appendChild(elemEl);
                    });
                });
            });

            sDiv.appendChild(children);
            tree.appendChild(sDiv);
        });
    }

    /* ── Global Event Bindings ── */

    _bindGlobalEvents() {
        // Open builder button (meta box)
        on(document, 'click', e => {
            if (e.target.closest('#amari-launch-builder')) {
                e.preventDefault();
                this.open();
            }
        });

        // Close
        on($('#amari-builder-close'), 'click', () => this.close());

        // Save draft
        on($('#amari-save-btn'), 'click', () => this.save());

        // Publish
        on($('#amari-publish-btn'), 'click', () => this.publish());

        // Keyboard shortcuts
        on(document, 'keydown', e => {
            if (!$('#amari-builder-overlay') || $('#amari-builder-overlay').style.display === 'none') return;
            if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); this.save(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); this._doUndo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); this._doRedo(); }
            if (e.key === 'Escape') this.close();
        });

        // Undo/Redo buttons
        on($('#amari-undo-btn'), 'click', () => this._doUndo());
        on($('#amari-redo-btn'), 'click', () => this._doRedo());

        // Canvas clicks (event delegation)
        on($('#amari-canvas-inner'), 'click', e => this._handleCanvasClick(e));

        // Preview
        on($('#amari-preview-btn'), 'click', () => {
            window.open(`/?p=${this.postId}&preview=true`, '_blank');
        });

        // Panel tabs
        on(document, 'click', e => {
            const tab = e.target.closest('.amari-panel-tab');
            if (!tab) return;
            $$('.amari-panel-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            $$('.amari-panel-content').forEach(p => p.style.display = 'none');
            const panel = $(`#amari-panel-${tab.dataset.panel}`);
            if (panel) panel.style.display = 'block';
        });

        // Responsive view
        on(document, 'click', e => {
            const btn = e.target.closest('.amari-view-btn');
            if (!btn) return;
            $$('.amari-view-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const canvas = $('#amari-canvas');
            if (canvas) canvas.dataset.view = btn.dataset.view;
        });

        // Add first section button
        on($('#amari-add-first-section'), 'click', () => {
            AmariModal.showLayoutPicker(layout => this.actions.addSection(layout));
        });

        // Settings close
        on($('#amari-settings-close'), 'click', () => this.settings.showEmpty());

        // Element search
        const searchInput = $('#amari-element-search');
        if (searchInput) {
            on(searchInput, 'input', e => {
                const q = e.target.value.toLowerCase();
                $$('.amari-element-tile').forEach(tile => {
                    const label = tile.querySelector('.amari-element-tile-label')?.textContent.toLowerCase() || '';
                    tile.style.display = label.includes(q) ? '' : 'none';
                });
                $$('.amari-element-group-label').forEach(label => {
                    const grid = label.nextElementSibling;
                    if (grid) {
                        const visible = $$('.amari-element-tile', grid).some(t => t.style.display !== 'none');
                        label.style.display = visible ? '' : 'none';
                        grid.style.display  = visible ? '' : 'none';
                    }
                });
            });
        }

        // Warn on page unload
        window.addEventListener('beforeunload', e => {
            if (this.isDirty) { e.preventDefault(); e.returnValue = ''; }
        });
    }

    _handleCanvasClick(e) {
        // Delegate clicks from canvas elements
        const btn = e.target.closest('[data-action]');
        if (btn) { e.stopPropagation(); this.actions.handle(btn.dataset.action, btn.dataset); return; }

        // Select element
        const abEl = e.target.closest('.ab-element');
        if (abEl) {
            $$('.ab-element').forEach(el => el.classList.remove('ab-selected'));
            abEl.classList.add('ab-selected');
            const elemData = this._findElement(abEl.dataset.elementId);
            if (elemData) this.settings.showElement(elemData);
            return;
        }

        // Select section
        const abSec = e.target.closest('.ab-section');
        if (abSec && !e.target.closest('.ab-element')) {
            $$('.ab-section').forEach(s => s.classList.remove('ab-selected'));
            abSec.classList.add('ab-selected');
        }
    }

    _findElement(id) {
        for (const sec of (this.data.sections || [])) {
            for (const row of (sec.rows || [])) {
                for (const col of (row.columns || [])) {
                    const elem = (col.elements || []).find(e => e.id === id);
                    if (elem) return elem;
                }
            }
        }
        return null;
    }

    _doUndo() {
        const state = this.history.undo();
        if (state) { this.data = state; this.canvas.render(this.data); this._rebuildLayoutTree(); this.isDirty = true; }
    }

    _doRedo() {
        const state = this.history.redo();
        if (state) { this.data = state; this.canvas.render(this.data); this._rebuildLayoutTree(); this.isDirty = true; }
    }

    markDirty() {
        this.isDirty = true;
    }
}

/* ==========================================================================
   ACTIONS
   ========================================================================== */

class AmariActions {
    constructor(builder) {
        this.b = builder;
    }

    handle(action, dataset) {
        switch (action) {
            case 'add-section':         AmariModal.showLayoutPicker(layout => this.addSection(layout)); break;
            case 'section-settings':    const sec = this._findSection(dataset.id); if (sec) this.b.settings.showSection(sec); break;
            case 'section-delete':      if (confirm(AmariBuilderConfig.i18n.confirm_section)) this.deleteSection(dataset.id); break;
            case 'section-up':          this.moveSectionUp(dataset.id); break;
            case 'section-down':        this.moveSectionDown(dataset.id); break;
            case 'section-duplicate':   this.duplicateSection(dataset.id); break;
            case 'row-add':             AmariModal.showLayoutPicker(layout => this.addRow(dataset.sectionId, layout)); break;
            case 'row-delete':          this.deleteRow(dataset.id); break;
            case 'row-duplicate':       this.duplicateRow(dataset.id); break;
            case 'el-delete':           if (confirm(AmariBuilderConfig.i18n.confirm_delete)) this.deleteElement(dataset.id); break;
            case 'el-duplicate':        this.duplicateElement(dataset.id); break;
            case 'el-up':               this.moveElementUp(dataset.id); break;
            case 'el-down':             this.moveElementDown(dataset.id); break;
        }
    }

    addSection(layout = '1-1') {
        const layouts = {
            '1-1':   [{ size:'1-1' }],
            '1-2_1-2': [{ size:'1-2' }, { size:'1-2' }],
            '1-3_1-3_1-3': [{ size:'1-3' }, { size:'1-3' }, { size:'1-3' }],
            '1-4_1-4_1-4_1-4': [{ size:'1-4' }, { size:'1-4' }, { size:'1-4' }, { size:'1-4' }],
            '2-3_1-3': [{ size:'2-3' }, { size:'1-3' }],
            '1-3_2-3': [{ size:'1-3' }, { size:'2-3' }],
        };

        const cols = (layouts[layout] || layouts['1-1']).map(c => ({ id: uid(), size: c.size, elements: [] }));

        const section = {
            id: uid(),
            settings: { bg_color: '', padding: '60px 0', full_width: false },
            rows: [{
                id: uid(),
                layout: layout,
                settings: {},
                columns: cols,
            }],
        };

        if (!this.b.data.sections) this.b.data.sections = [];
        this.b.data.sections.push(section);
        this._commit();
    }

    addRow(sectionId, layout = '1-1') {
        const section = this._findSection(sectionId);
        if (!section) return;

        const layouts = {
            '1-1':   [{ size:'1-1' }],
            '1-2_1-2': [{ size:'1-2' }, { size:'1-2' }],
            '1-3_1-3_1-3': [{ size:'1-3' }, { size:'1-3' }, { size:'1-3' }],
        };
        const cols = (layouts[layout] || layouts['1-1']).map(c => ({ id: uid(), size: c.size, elements: [] }));

        section.rows.push({
            id: uid(),
            layout,
            settings: {},
            columns: cols,
        });
        this._commit();
    }

    addElement(type, { colId, rowId, sectionId }) {
        const config = AmariBuilderConfig.elements[type];
        if (!config) return;

        const col = this._findCol(colId);
        if (!col) return;

        const element = {
            id:       uid(),
            type:     type,
            settings: deepClone(config.defaults || {}),
        };

        if (!col.elements) col.elements = [];
        col.elements.push(element);
        this._commit();

        // Select & show settings
        this.b.settings.showElement(element);
    }

    addElementToFirstAvailableCol(type) {
        const sections = this.b.data.sections || [];
        if (!sections.length) {
            this.addSection('1-1');
        }
        const section = sections[sections.length - 1];
        const row = section.rows[section.rows.length - 1];
        const col = row.columns[0];
        if (col) {
            this.addElement(type, { colId: col.id, rowId: row.id, sectionId: section.id });
        }
    }

    deleteSection(id) {
        this.b.data.sections = (this.b.data.sections || []).filter(s => s.id !== id);
        this._commit();
        this.b.settings.showEmpty();
    }

    moveSectionUp(id) {
        const arr = this.b.data.sections;
        const i = arr.findIndex(s => s.id === id);
        if (i > 0) { [arr[i-1], arr[i]] = [arr[i], arr[i-1]]; this._commit(); }
    }

    moveSectionDown(id) {
        const arr = this.b.data.sections;
        const i = arr.findIndex(s => s.id === id);
        if (i < arr.length - 1) { [arr[i], arr[i+1]] = [arr[i+1], arr[i]]; this._commit(); }
    }

    duplicateSection(id) {
        const arr = this.b.data.sections;
        const i = arr.findIndex(s => s.id === id);
        if (i === -1) return;
        const clone = deepClone(arr[i]);
        clone.id = uid();
        clone.rows.forEach(r => { r.id = uid(); r.columns.forEach(c => { c.id = uid(); c.elements.forEach(e => e.id = uid()); }); });
        arr.splice(i + 1, 0, clone);
        this._commit();
    }

    deleteRow(id) {
        for (const sec of (this.b.data.sections || [])) {
            const i = sec.rows.findIndex(r => r.id === id);
            if (i !== -1) { sec.rows.splice(i, 1); this._commit(); return; }
        }
    }

    duplicateRow(id) {
        for (const sec of (this.b.data.sections || [])) {
            const i = sec.rows.findIndex(r => r.id === id);
            if (i !== -1) {
                const clone = deepClone(sec.rows[i]);
                clone.id = uid();
                clone.columns.forEach(c => { c.id = uid(); c.elements.forEach(e => e.id = uid()); });
                sec.rows.splice(i + 1, 0, clone);
                this._commit();
                return;
            }
        }
    }

    deleteElement(id) {
        for (const sec of (this.b.data.sections || [])) {
            for (const row of (sec.rows || [])) {
                for (const col of (row.columns || [])) {
                    const i = (col.elements || []).findIndex(e => e.id === id);
                    if (i !== -1) { col.elements.splice(i, 1); this._commit(); this.b.settings.showEmpty(); return; }
                }
            }
        }
    }

    duplicateElement(id) {
        for (const sec of (this.b.data.sections || [])) {
            for (const row of (sec.rows || [])) {
                for (const col of (row.columns || [])) {
                    const i = (col.elements || []).findIndex(e => e.id === id);
                    if (i !== -1) {
                        const clone = deepClone(col.elements[i]);
                        clone.id = uid();
                        col.elements.splice(i + 1, 0, clone);
                        this._commit();
                        return;
                    }
                }
            }
        }
    }

    moveElementUp(id) {
        for (const sec of (this.b.data.sections || [])) {
            for (const row of (sec.rows || [])) {
                for (const col of (row.columns || [])) {
                    const i = (col.elements || []).findIndex(e => e.id === id);
                    if (i > 0) { [col.elements[i-1], col.elements[i]] = [col.elements[i], col.elements[i-1]]; this._commit(); return; }
                }
            }
        }
    }

    moveElementDown(id) {
        for (const sec of (this.b.data.sections || [])) {
            for (const row of (sec.rows || [])) {
                for (const col of (row.columns || [])) {
                    const i = (col.elements || []).findIndex(e => e.id === id);
                    if (i !== -1 && i < col.elements.length - 1) { [col.elements[i], col.elements[i+1]] = [col.elements[i+1], col.elements[i]]; this._commit(); return; }
                }
            }
        }
    }

    _findSection(id) {
        return (this.b.data.sections || []).find(s => s.id === id);
    }
    _findCol(id) {
        for (const sec of (this.b.data.sections || [])) {
            for (const row of (sec.rows || [])) {
                const c = row.columns.find(c => c.id === id);
                if (c) return c;
            }
        }
        return null;
    }

    _commit() {
        this.b.canvas.render(this.b.data);
        this.b.history.push(deepClone(this.b.data));
        this.b._rebuildLayoutTree();
        this.b.markDirty();
    }
}

/* ==========================================================================
   MODAL (Layout Picker)
   ========================================================================== */

const AmariModal = {
    showLayoutPicker(callback) {
        const modal = $('#amari-add-section-modal');
        if (!modal) return;

        const grid = $('#amari-layout-options');
        if (!grid._built) {
            grid._built = true;
            const layouts = [
                { key: '1-1',              label: '1 Column',    preview: [100] },
                { key: '1-2_1-2',          label: '2 Columns',   preview: [50,50] },
                { key: '1-3_1-3_1-3',      label: '3 Columns',   preview: [33,33,33] },
                { key: '1-4_1-4_1-4_1-4', label: '4 Columns',   preview: [25,25,25,25] },
                { key: '2-3_1-3',          label: '2/3 + 1/3',  preview: [66,33] },
                { key: '1-3_2-3',          label: '1/3 + 2/3',  preview: [33,66] },
                { key: '1-4_3-4',          label: '1/4 + 3/4',  preview: [25,75] },
            ];

            layouts.forEach(layout => {
                const opt = document.createElement('div');
                opt.className = 'amari-layout-option';
                opt.dataset.layout = layout.key;

                const prev = document.createElement('div');
                prev.className = 'amari-layout-option-preview';
                layout.preview.forEach(pct => {
                    const col = document.createElement('div');
                    col.style.flex = pct;
                    prev.appendChild(col);
                });

                const lbl = document.createElement('div');
                lbl.className = 'amari-layout-option-label';
                lbl.textContent = layout.label;

                opt.appendChild(prev);
                opt.appendChild(lbl);

                opt.addEventListener('click', () => {
                    modal.style.display = 'none';
                    callback(layout.key);
                });
                grid.appendChild(opt);
            });
        }

        modal.style.display = 'flex';
        const closeBtn = modal.querySelector('.amari-modal-close');
        const close = () => modal.style.display = 'none';
        if (closeBtn) closeBtn.onclick = close;
        modal.onclick = e => { if (e.target === modal) close(); };
    }
};

/* ==========================================================================
   TEMPLATE LIBRARY
   ========================================================================== */

const AmariTemplateLibrary = {
    _templates: null,     // cached index
    _activeCat: 'all',
    _search: '',

    /* Open the modal, load templates if needed */
    open() {
        const modal = $('#amari-templates-modal');
        if (!modal) return;
        modal.style.display = 'flex';

        if (this._templates) {
            this._render();
        } else {
            this._fetch();
        }

        // Bind category buttons (once)
        if (!modal._libBound) {
            modal._libBound = true;

            on(modal, 'click', e => {
                const catBtn = e.target.closest('.amari-tpl-cat-btn');
                if (catBtn) {
                    $$('.amari-tpl-cat-btn').forEach(b => b.classList.remove('active'));
                    catBtn.classList.add('active');
                    this._activeCat = catBtn.dataset.cat || 'all';
                    this._render();
                }

                const insertBtn = e.target.closest('.amari-tpl-insert-btn');
                if (insertBtn) {
                    e.stopPropagation();
                    this._insert(insertBtn.dataset.id);
                }

                const card = e.target.closest('.amari-tpl-card');
                if (card && !e.target.closest('.amari-tpl-insert-btn')) {
                    this._insert(card.dataset.id);
                }
            });

            const searchEl = $('#amari-tpl-search');
            if (searchEl) {
                on(searchEl, 'input', e => {
                    this._search = e.target.value.toLowerCase();
                    this._render();
                });
            }
        }
    },

    close() {
        const modal = $('#amari-templates-modal');
        if (modal) modal.style.display = 'none';
    },

    _fetch() {
        const grid = $('#amari-templates-grid');
        if (grid) grid.innerHTML = '<div style="padding:40px;text-align:center;color:var(--ab-text-muted);">Loading templates…</div>';

        const fd = new FormData();
        fd.append('action', 'amari_get_templates');
        fd.append('nonce', AmariBuilderConfig.nonce);

        fetch(AmariBuilderConfig.ajaxUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data && res.data.templates) {
                    this._templates = res.data.templates;
                    this._render();
                } else {
                    if (grid) grid.innerHTML = '<div style="padding:40px;text-align:center;color:#e94560;">Failed to load templates.</div>';
                }
            })
            .catch(() => {
                if (grid) grid.innerHTML = '<div style="padding:40px;text-align:center;color:#e94560;">Network error loading templates.</div>';
            });
    },

    _render() {
        const grid = $('#amari-templates-grid');
        if (!grid || !this._templates) return;

        let visible = this._templates;

        if (this._activeCat && this._activeCat !== 'all') {
            visible = visible.filter(t => t.category === this._activeCat);
        }

        if (this._search) {
            visible = visible.filter(t =>
                (t.name || '').toLowerCase().includes(this._search) ||
                (t.description || '').toLowerCase().includes(this._search)
            );
        }

        if (!visible.length) {
            grid.innerHTML = '<div style="padding:40px;text-align:center;color:var(--ab-text-muted);">No templates found.</div>';
            return;
        }

        const categoryEmojis = {
            'Hero': '🦸', 'Features': '⭐', 'About': '👤', 'Stats': '📊',
            'Testimonials': '💬', 'Pricing': '💰', 'FAQ': '❓', 'CTA': '🎯', 'Contact': '📬',
        };

        grid.innerHTML = `<div class="amari-tpl-grid">` + visible.map(t => `
            <div class="amari-tpl-card" data-id="${t.id}">
                <div class="amari-tpl-thumb">
                    <div class="amari-tpl-thumb-placeholder">${categoryEmojis[t.category] || '📐'}</div>
                    <div class="amari-tpl-thumb-overlay">
                        <button class="amari-tpl-insert-btn" data-id="${t.id}">+ Insert</button>
                    </div>
                </div>
                <div class="amari-tpl-info">
                    <div class="amari-tpl-name">${this._esc(t.name)}</div>
                    <div class="amari-tpl-desc">${this._esc(t.description || '')}</div>
                    <span class="amari-tpl-cat-tag">${this._esc(t.category)}</span>
                </div>
            </div>
        `).join('') + `</div>`;
    },

    _insert(id) {
        const grid = $('#amari-templates-grid');
        if (grid) grid.innerHTML = '<div class="amari-tpl-inserting"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:amari-spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>&nbsp;Inserting template…</div>';

        const fd = new FormData();
        fd.append('action', 'amari_get_template');
        fd.append('nonce', AmariBuilderConfig.nonce);
        fd.append('template_id', id);

        fetch(AmariBuilderConfig.ajaxUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data && res.data.section) {
                    const builder = window.AmariBuilder;
                    if (builder) {
                        if (!builder.data.sections) builder.data.sections = [];
                        builder.data.sections.push(res.data.section);
                        builder.canvas.render(builder.data);
                        builder._rebuildLayoutTree();
                        builder.history.push(builder.data);
                        builder.markDirty();
                    }
                    this.close();
                } else {
                    this._render(); // restore grid on error
                    alert('Could not insert template. Please try again.');
                }
            })
            .catch(() => {
                this._render();
                alert('Network error inserting template.');
            });
    },

    _esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    },
};

/* ==========================================================================
   BOOT
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    // Only init on edit pages with the builder overlay present
    const overlay = document.getElementById('amari-builder-overlay');
    if (!overlay) return;

    window.AmariBuilder = new AmariBuilderController();
    window.AmariBuilder.init();

    // Wire Templates button
    on($('#amari-templates-btn'), 'click', () => AmariTemplateLibrary.open());

    // Auto-open if URL param is present
    if (new URLSearchParams(window.location.search).has('amari_builder')) {
        window.AmariBuilder.open();
    }

    // Hook for "Enable Builder" checkbox — reload meta box to show button
    const cb = document.getElementById('amari_builder_enabled_cb');
    if (cb) {
        cb.addEventListener('change', () => {
            if (cb.checked) window.AmariBuilder.open();
        });
    }
});
