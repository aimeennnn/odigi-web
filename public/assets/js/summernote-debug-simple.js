// Simple Summernote Debug Script
console.log('Summernote Debug Script loaded');

// Check if Summernote is working
function checkSummernote() {
    console.log('=== Summernote Status Check ===');
    
    // Check jQuery
    if (typeof $ !== 'undefined') {
        console.log('✅ jQuery loaded:', $.fn.jquery);
    } else {
        console.log('❌ jQuery not loaded');
        return;
    }
    
    // Check Summernote
    if ($.fn.summernote) {
        console.log('✅ Summernote loaded');
    } else {
        console.log('❌ Summernote not loaded');
        return;
    }
    
    // Check textarea
    const textarea = document.getElementById('keterangan');
    if (textarea) {
        console.log('✅ Textarea found');
        console.log('  - ID:', textarea.id);
        console.log('  - Class:', textarea.className);
        console.log('  - Has Summernote data:', !!$('#keterangan').data('summernote'));
    } else {
        console.log('❌ Textarea not found');
        return;
    }
    
    // Check modal
    const modal = document.getElementById('modalTambahKomite');
    if (modal) {
        console.log('✅ Modal found');
        console.log('  - Modal visible:', modal.classList.contains('show'));
    } else {
        console.log('❌ Modal not found');
    }
}

// Run check after page loads
setTimeout(checkSummernote, 1000);

// Manual initialization function
window.fixSummernote = function() {
    console.log('🔧 Manually fixing Summernote...');
    
    if (typeof $ === 'undefined' || !$.fn.summernote) {
        console.log('❌ Dependencies not ready');
        return;
    }
    
    const textarea = document.getElementById('keterangan');
    if (!textarea) {
        console.log('❌ Textarea not found');
        return;
    }
    
    // Destroy existing
    if ($('#keterangan').data('summernote')) {
        $('#keterangan').summernote('destroy');
        console.log('🗑️ Destroyed existing Summernote');
    }
    
    // Initialize new
    try {
        $('#keterangan').summernote({
            height: 300,
            minHeight: 200,
            maxHeight: 500,
            width: '100%',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            placeholder: 'Masukkan keterangan lengkap mengenai keputusan komite...',
            dialogsInBody: true,
            disableDragAndDrop: false,
            disableResizeEditor: false,
            focus: false,
            callbacks: {
                onInit: function() {
                    console.log('✅ Summernote initialized successfully (manual)');
                }
            }
        });
    } catch (error) {
        console.log('❌ Error:', error);
    }
};

console.log('💡 Run fixSummernote() in console to manually initialize Summernote');
