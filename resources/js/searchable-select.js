/**
 * ═══════════════════════════════════════════════════════════════════════
 * STANDARDIZED SEARCHABLE DROPDOWN JAVASCRIPT ENGINE (PKBM PIKAT)
 * High-Performance, Mobile-Optimized, Vanilla JS Searchable Select
 * ═══════════════════════════════════════════════════════════════════════
 */

class SearchableSelect {
    constructor(selectEl, config = {}) {
        if (!selectEl || selectEl.tagName !== 'SELECT') return;
        if (selectEl._searchableSelect) {
            return selectEl._searchableSelect;
        }

        this.select = selectEl;
        this.select._searchableSelect = this;

        this.config = Object.assign({
            mobileBreakpoint: 640,
            searchPlaceholder: 'Ketik untuk mencari...',
            emptyText: 'Tidak ada data ditemukan',
            allowClear: !selectEl.required,
            zIndex: 99999
        }, config);

        this.isOpen = false;
        this.highlightedIndex = -1;
        this.filteredOptions = [];
        this.isMobile = window.innerWidth <= this.config.mobileBreakpoint;

        this.init();
    }

    init() {
        this.parseOptions();
        this.buildDOM();
        this.bindEvents();
        this.updateTrigger();
        this.observeDOM();
    }

    parseOptions() {
        this.options = [];
        const children = Array.from(this.select.children);

        let optionIndex = 0;
        children.forEach(child => {
            if (child.tagName === 'OPTGROUP') {
                const groupLabel = child.getAttribute('label') || 'Group';
                const subOptions = Array.from(child.children).filter(el => el.tagName === 'OPTION');
                subOptions.forEach(opt => {
                    this.options.push({
                        element: opt,
                        value: opt.value,
                        text: opt.textContent.trim(),
                        group: groupLabel,
                        disabled: opt.disabled,
                        selected: opt.selected,
                        index: optionIndex++
                    });
                });
            } else if (child.tagName === 'OPTION') {
                this.options.push({
                    element: child,
                    value: child.value,
                    text: child.textContent.trim(),
                    group: null,
                    disabled: child.disabled,
                    selected: child.selected,
                    index: optionIndex++
                });
            }
        });

        this.filteredOptions = [...this.options];
    }

    buildDOM() {
        // Create main wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'ss-wrapper';
        if (this.select.className) {
            this.wrapper.classList.add(...this.select.className.split(' ').filter(c => c && !['filterSelect', 'profileInput', 'formControl', 'select'].includes(c)));
        }
        if (this.select.disabled) {
            this.wrapper.classList.add('ss-disabled');
        }

        // Trigger Button
        this.trigger = document.createElement('button');
        this.trigger.type = 'button';
        this.trigger.className = 'ss-trigger';
        this.trigger.setAttribute('aria-haspopup', 'listbox');
        this.trigger.setAttribute('aria-expanded', 'false');
        if (this.select.disabled) this.trigger.disabled = true;

        this.labelSpan = document.createElement('span');
        this.labelSpan.className = 'ss-label';

        this.iconsSpan = document.createElement('span');
        this.iconsSpan.className = 'ss-icons';

        this.clearBtn = document.createElement('span');
        this.clearBtn.className = 'ss-clear-btn';
        this.clearBtn.innerHTML = '&times;';
        this.clearBtn.title = 'Hapus pilihan';
        this.clearBtn.style.display = 'none';

        this.arrowSpan = document.createElement('span');
        this.arrowSpan.className = 'ss-arrow';
        this.arrowSpan.innerHTML = `
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        `;

        this.iconsSpan.appendChild(this.clearBtn);
        this.iconsSpan.appendChild(this.arrowSpan);
        this.trigger.appendChild(this.labelSpan);
        this.trigger.appendChild(this.iconsSpan);
        this.wrapper.appendChild(this.trigger);

        // Desktop Dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'ss-dropdown ss-drop-down';

        this.searchWrapper = document.createElement('div');
        this.searchWrapper.className = 'ss-search-wrapper';

        this.searchIcon = document.createElement('span');
        this.searchIcon.className = 'ss-search-icon';
        this.searchIcon.innerHTML = `
            <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
        `;

        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'ss-search-input';
        this.searchInput.placeholder = this.config.searchPlaceholder;
        this.searchInput.autocomplete = 'off';
        this.searchInput.spellcheck = false;

        this.searchClear = document.createElement('button');
        this.searchClear.type = 'button';
        this.searchClear.className = 'ss-search-clear';
        this.searchClear.innerHTML = '&times;';

        this.searchWrapper.appendChild(this.searchIcon);
        this.searchWrapper.appendChild(this.searchInput);
        this.searchWrapper.appendChild(this.searchClear);

        this.optionsList = document.createElement('div');
        this.optionsList.className = 'ss-options-list';
        this.optionsList.setAttribute('role', 'listbox');

        this.dropdown.appendChild(this.searchWrapper);
        this.dropdown.appendChild(this.optionsList);
        this.wrapper.appendChild(this.dropdown);

        // Insert wrapper in place of select and hide select visually
        this.select.parentNode.insertBefore(this.wrapper, this.select);
        this.select.style.position = 'absolute';
        this.select.style.opacity = '0';
        this.select.style.pointerEvents = 'none';
        this.select.style.width = '1px';
        this.select.style.height = '1px';
        this.select.style.margin = '-1px';
        this.select.style.clip = 'rect(0,0,0,0)';
        this.select.tabIndex = -1;
        this.wrapper.appendChild(this.select);

        // Mobile Bottom Sheet Portal
        this.createMobileSheet();
        this.renderOptionsList();
    }

    createMobileSheet() {
        this.mobileBackdrop = document.createElement('div');
        this.mobileBackdrop.className = 'ss-mobile-sheet-backdrop';

        this.mobileSheet = document.createElement('div');
        this.mobileSheet.className = 'ss-mobile-sheet';

        const handle = document.createElement('div');
        handle.className = 'ss-sheet-handle-bar';

        const header = document.createElement('div');
        header.className = 'ss-sheet-header';

        const title = document.createElement('h4');
        title.className = 'ss-sheet-title';
        // Get label from previous label or name
        const labelEl = this.wrapper.closest('.form-field-wrapper, .filterField, .mb-3, .mb-4')?.querySelector('label, .filterFieldLabel, .form-field-label');
        title.textContent = labelEl ? labelEl.textContent.replace('*', '').trim() : 'Pilih Opsi';

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'ss-sheet-close-btn';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = () => this.close();

        header.appendChild(title);
        header.appendChild(closeBtn);

        this.mobileSearchWrapper = this.searchWrapper.cloneNode(true);
        this.mobileSearchInput = this.mobileSearchWrapper.querySelector('.ss-search-input');
        this.mobileSearchClear = this.mobileSearchWrapper.querySelector('.ss-search-clear');

        this.mobileOptionsList = document.createElement('div');
        this.mobileOptionsList.className = 'ss-options-list';

        this.mobileSheet.appendChild(handle);
        this.mobileSheet.appendChild(header);
        this.mobileSheet.appendChild(this.mobileSearchWrapper);
        this.mobileSheet.appendChild(this.mobileOptionsList);

        this.mobileBackdrop.onclick = (e) => {
            if (e.target === this.mobileBackdrop) this.close();
        };

        document.body.appendChild(this.mobileBackdrop);
        document.body.appendChild(this.mobileSheet);
    }

    renderOptionsList(searchTerm = '') {
        const term = searchTerm.toLowerCase().trim();
        const activeList = this.isMobile ? this.mobileOptionsList : this.optionsList;
        activeList.innerHTML = '';

        this.filteredOptions = this.options.filter(opt => {
            if (!term) return true;
            return opt.text.toLowerCase().includes(term) || (opt.group && opt.group.toLowerCase().includes(term));
        });

        if (this.filteredOptions.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'ss-empty-state';
            empty.innerHTML = `
                <div style="font-size:22px; margin-bottom:4px; opacity:0.6;">🔍</div>
                <div>${this.config.emptyText}</div>
            `;
            activeList.appendChild(empty);
            return;
        }

        let currentGroup = null;

        this.filteredOptions.forEach((opt, idx) => {
            if (opt.group && opt.group !== currentGroup) {
                currentGroup = opt.group;
                const groupEl = document.createElement('div');
                groupEl.className = 'ss-optgroup-label';
                groupEl.textContent = currentGroup;
                activeList.appendChild(groupEl);
            }

            const optionEl = document.createElement('div');
            optionEl.className = 'ss-option';
            if (opt.value === this.select.value) optionEl.classList.add('ss-selected');
            if (opt.disabled) optionEl.classList.add('ss-disabled');
            if (idx === this.highlightedIndex) optionEl.classList.add('ss-highlighted');
            optionEl.setAttribute('data-value', opt.value);
            optionEl.setAttribute('data-index', opt.index);

            const textSpan = document.createElement('span');
            textSpan.className = 'ss-option-text';

            if (term && opt.text.toLowerCase().includes(term)) {
                const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                textSpan.innerHTML = opt.text.replace(regex, '<span class="ss-highlight">$1</span>');
            } else {
                textSpan.textContent = opt.text;
            }

            const checkSpan = document.createElement('span');
            checkSpan.className = 'ss-option-check';
            checkSpan.innerHTML = '✓';

            optionEl.appendChild(textSpan);
            optionEl.appendChild(checkSpan);

            optionEl.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectOption(opt);
            });

            activeList.appendChild(optionEl);
        });
    }

    bindEvents() {
        // Trigger click
        this.trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (this.select.disabled) return;
            this.toggle();
        });

        // Clear button click
        this.clearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.clearSelection();
        });

        // Search inputs
        const handleSearch = (val) => {
            const clearBtn = this.isMobile ? this.mobileSearchClear : this.searchClear;
            clearBtn.style.display = val ? 'flex' : 'none';
            this.highlightedIndex = -1;
            this.renderOptionsList(val);
        };

        this.searchInput.addEventListener('input', (e) => handleSearch(e.target.value));
        this.mobileSearchInput.addEventListener('input', (e) => handleSearch(e.target.value));

        this.searchClear.addEventListener('click', () => {
            this.searchInput.value = '';
            handleSearch('');
            this.searchInput.focus();
        });

        this.mobileSearchClear.addEventListener('click', () => {
            this.mobileSearchInput.value = '';
            handleSearch('');
            this.mobileSearchInput.focus();
        });

        // Keyboard navigation
        this.wrapper.addEventListener('keydown', (e) => {
            if (!this.isOpen) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.open();
                }
                return;
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                this.close();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.moveHighlight(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.moveHighlight(-1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                    this.selectOption(this.filteredOptions[this.highlightedIndex]);
                }
            }
        });

        // Native select sync
        this.select.addEventListener('change', () => {
            this.updateTrigger();
            this.wrapper.classList.remove('ss-invalid');
        });

        this.select.addEventListener('invalid', () => {
            this.wrapper.classList.add('ss-invalid');
        });

        // Resize detection
        window.addEventListener('resize', () => {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth <= this.config.mobileBreakpoint;
            if (wasMobile !== this.isMobile && this.isOpen) {
                this.close();
            }
        });

        // Click outside desktop dropdown
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.wrapper.contains(e.target) && !this.mobileSheet.contains(e.target)) {
                this.close();
            }
        });
    }

    moveHighlight(direction) {
        if (this.filteredOptions.length === 0) return;
        this.highlightedIndex += direction;
        if (this.highlightedIndex < 0) this.highlightedIndex = this.filteredOptions.length - 1;
        if (this.highlightedIndex >= this.filteredOptions.length) this.highlightedIndex = 0;

        const activeList = this.isMobile ? this.mobileOptionsList : this.optionsList;
        const optionEls = activeList.querySelectorAll('.ss-option');
        optionEls.forEach((el, idx) => {
            if (idx === this.highlightedIndex) {
                el.classList.add('ss-highlighted');
                el.scrollIntoView({ block: 'nearest' });
            } else {
                el.classList.remove('ss-highlighted');
            }
        });
    }

    updateTrigger() {
        const selectedOpt = this.select.options[this.select.selectedIndex];
        if (selectedOpt && selectedOpt.value !== '') {
            this.labelSpan.textContent = selectedOpt.textContent.trim();
            this.labelSpan.classList.remove('ss-placeholder');
            if (this.config.allowClear && !this.select.required) {
                this.clearBtn.style.display = 'inline-flex';
            }
        } else {
            const placeholder = selectedOpt ? selectedOpt.textContent.trim() : (this.select.getAttribute('data-placeholder') || 'Pilih salah satu...');
            this.labelSpan.textContent = placeholder;
            this.labelSpan.classList.add('ss-placeholder');
            this.clearBtn.style.display = 'none';
        }
    }

    selectOption(opt) {
        if (opt.disabled) return;
        this.select.value = opt.value;
        this.updateTrigger();
        this.close();

        // Dispatch events so form validation, AJAX and onchange listeners fire
        this.select.dispatchEvent(new Event('input', { bubbles: true }));
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    clearSelection() {
        const emptyOpt = this.options.find(o => o.value === '');
        this.select.value = emptyOpt ? emptyOpt.value : '';
        this.updateTrigger();
        this.select.dispatchEvent(new Event('input', { bubbles: true }));
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        // Close other open instances first
        document.querySelectorAll('.ss-wrapper.ss-open').forEach(el => {
            if (el !== this.wrapper && el._searchableSelectInstance) {
                el._searchableSelectInstance.close();
            }
        });

        this.isOpen = true;
        this.wrapper.classList.add('ss-open');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.highlightedIndex = -1;

        if (this.isMobile) {
            this.mobileSearchInput.value = '';
            this.mobileSearchClear.style.display = 'none';
            this.renderOptionsList('');
            this.mobileBackdrop.classList.add('ss-active');
            this.mobileSheet.classList.add('ss-active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => this.mobileSearchInput.focus(), 150);
        } else {
            this.searchInput.value = '';
            this.searchClear.style.display = 'none';
            this.renderOptionsList('');
            this.positionDropdown();
            setTimeout(() => this.searchInput.focus(), 80);
        }
    }

    positionDropdown() {
        const rect = this.wrapper.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const dropdownHeight = 320;

        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
            this.dropdown.classList.remove('ss-drop-down');
            this.dropdown.classList.add('ss-drop-up');
        } else {
            this.dropdown.classList.remove('ss-drop-up');
            this.dropdown.classList.add('ss-drop-down');
        }
    }

    close() {
        if (!this.isOpen) return;
        this.isOpen = false;
        this.wrapper.classList.remove('ss-open');
        this.trigger.setAttribute('aria-expanded', 'false');

        if (this.isMobile) {
            this.mobileBackdrop.classList.remove('ss-active');
            this.mobileSheet.classList.remove('ss-active');
            document.body.style.overflow = '';
        }
    }

    refresh() {
        this.parseOptions();
        this.updateTrigger();
        this.renderOptionsList();
    }

    observeDOM() {
        const observer = new MutationObserver(() => {
            this.refresh();
        });
        observer.observe(this.select, { childList: true, attributes: true, attributeFilter: ['disabled'] });
    }

    destroy() {
        if (this.mobileBackdrop && this.mobileBackdrop.parentNode) {
            this.mobileBackdrop.parentNode.removeChild(this.mobileBackdrop);
        }
        if (this.mobileSheet && this.mobileSheet.parentNode) {
            this.mobileSheet.parentNode.removeChild(this.mobileSheet);
        }
        if (this.wrapper && this.wrapper.parentNode) {
            this.select.style.position = '';
            this.select.style.opacity = '';
            this.select.style.pointerEvents = '';
            this.select.style.width = '';
            this.select.style.height = '';
            this.select.style.margin = '';
            this.select.style.clip = '';
            this.select.tabIndex = 0;
            this.wrapper.parentNode.insertBefore(this.select, this.wrapper);
            this.wrapper.parentNode.removeChild(this.wrapper);
        }
        delete this.select._searchableSelect;
    }
}

/**
 * Global initialization helper that scans DOM and applies SearchableSelect
 */
function initSearchableSelect(root = document) {
    const selector = [
        'select.searchable-select',
        'select[data-searchable="true"]',
        'select.filterSelect',
        'select.profileInput',
        'select.filter-select',
        'select.select',
        'select.formControl'
    ].join(', ');

    const selects = root.querySelectorAll(selector);
    selects.forEach(select => {
        if (select.hasAttribute('data-searchable-ignore') || select.classList.contains('no-searchable')) {
            return;
        }
        // If it's a small 2-choice yes/no without explicit searchable attribute, we can still enhance it or keep it clean
        new SearchableSelect(select);
    });
}

// Auto-run on DOM ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initSearchableSelect());
    } else {
        initSearchableSelect();
    }
}

// Expose globally to window
if (typeof window !== 'undefined') {
    window.SearchableSelect = SearchableSelect;
    window.initSearchableSelect = initSearchableSelect;
    window.refreshSearchableSelect = (selectEl) => {
        if (selectEl && selectEl._searchableSelect) {
            selectEl._searchableSelect.refresh();
        } else if (selectEl) {
            new SearchableSelect(selectEl);
        }
    };
}

export default SearchableSelect;
