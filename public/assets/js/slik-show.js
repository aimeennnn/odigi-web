// SLIK Show JavaScript - Minimal JS untuk halaman show slik

document.addEventListener('DOMContentLoaded', function() {
    // Initialize file preview functionality if needed
    initFilePreview();
});

// File Preview Functionality (minimal for show page)
function initFilePreview() {
    // Handle modal preview for images if any
    const previewButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    previewButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Preview modal opened for:', this.getAttribute('data-bs-target'));
        });
    });
    
    // Handle download links
    const downloadLinks = document.querySelectorAll('a[download]');
    
    downloadLinks.forEach(link => {
        link.addEventListener('click', function() {
            console.log('File download initiated:', this.href);
        });
    });
    
    // Handle external links (PDFs)
    const externalLinks = document.querySelectorAll('a[target="_blank"]');
    
    externalLinks.forEach(link => {
        link.addEventListener('click', function() {
            console.log('External file opened:', this.href);
        });
    });
}
