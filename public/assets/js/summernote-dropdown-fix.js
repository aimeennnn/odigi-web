// Summernote Dropdown Overlap Fix
// Script ini mengatasi masalah dropdown yang tumpang tindih

(function() {
    'use strict';
    
    console.log('Summernote Dropdown Fix loaded');
    
    // Function to close all dropdowns
    function closeAllDropdowns() {
        // Close Bootstrap dropdowns
        const openDropdowns = document.querySelectorAll('.note-toolbar .btn-group.open');
        openDropdowns.forEach(dropdown => {
            dropdown.classList.remove('open');
        });
        
        // Close Summernote color palettes
        const colorPalettes = document.querySelectorAll('.note-toolbar .note-color-palette');
        colorPalettes.forEach(palette => {
            palette.classList.remove('show');
            palette.style.display = 'none';
            palette.style.visibility = 'hidden';
            palette.style.opacity = '0';
        });
        
        // Close Summernote table pickers
        const tablePickers = document.querySelectorAll('.note-toolbar .note-table');
        tablePickers.forEach(table => {
            table.classList.remove('show');
            table.style.display = 'none';
            table.style.visibility = 'hidden';
            table.style.opacity = '0';
        });
        
        // Close Summernote popovers
        const popovers = document.querySelectorAll('.note-toolbar .note-popover');
        popovers.forEach(popover => {
            popover.classList.remove('show');
            popover.style.display = 'none';
            popover.style.visibility = 'hidden';
            popover.style.opacity = '0';
        });
    }
    
    // Function to setup dropdown event handlers
    function setupDropdownHandlers() {
        const toolbar = document.querySelector('.note-toolbar');
        if (!toolbar) return;
        
        // Handle Bootstrap dropdown toggles
        const dropdownToggles = toolbar.querySelectorAll('[data-toggle="dropdown"]');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns first
                closeAllDropdowns();
                
                // Toggle current dropdown
                const btnGroup = this.closest('.btn-group');
                if (btnGroup) {
                    btnGroup.classList.toggle('open');
                }
            });
        });
        
        // Handle Summernote color buttons
        const colorButtons = toolbar.querySelectorAll('.note-color-btn');
        colorButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns first
                closeAllDropdowns();
                
                // Show color palette
                const palette = this.closest('.note-color-palette');
                if (palette) {
                    palette.classList.add('show');
                    palette.style.display = 'block';
                    palette.style.visibility = 'visible';
                    palette.style.opacity = '1';
                }
            });
        });
        
        // Handle Summernote table buttons
        const tableButtons = toolbar.querySelectorAll('.note-table-btn');
        tableButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns first
                closeAllDropdowns();
                
                // Show table picker
                const tablePicker = this.closest('.note-table');
                if (tablePicker) {
                    tablePicker.classList.add('show');
                    tablePicker.style.display = 'block';
                    tablePicker.style.visibility = 'visible';
                    tablePicker.style.opacity = '1';
                }
            });
        });
        
        // Handle clicks outside to close dropdowns
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.note-toolbar')) {
                closeAllDropdowns();
            }
        });
        
        // Handle escape key to close dropdowns
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    }
    
    // Function to initialize the fix
    function initDropdownFix() {
        // Wait for Summernote to be initialized
        const checkInterval = setInterval(() => {
            const toolbar = document.querySelector('.note-toolbar');
            if (toolbar) {
                clearInterval(checkInterval);
                setupDropdownHandlers();
                console.log('Summernote dropdown fix initialized');
            }
        }, 100);
        
        // Timeout after 5 seconds
        setTimeout(() => {
            clearInterval(checkInterval);
        }, 5000);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdownFix);
    } else {
        initDropdownFix();
    }
    
    // Re-initialize when Summernote is recreated
    const originalSummernote = window.$ && window.$.fn.summernote;
    if (originalSummernote) {
        const originalInit = originalSummernote;
        window.$.fn.summernote = function(options) {
            const result = originalInit.apply(this, arguments);
            
            // Setup dropdown handlers after Summernote initialization
            setTimeout(() => {
                setupDropdownHandlers();
            }, 200);
            
            return result;
        };
    }
    
    // Expose functions globally for debugging
    window.closeAllSummernoteDropdowns = closeAllDropdowns;
    window.setupSummernoteDropdownHandlers = setupDropdownHandlers;
    
})();
