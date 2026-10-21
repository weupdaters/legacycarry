<!-- Global Image Preview Lightbox Modal -->
<div class="modal fade" id="globalImagePreviewModal" tabindex="-1" aria-labelledby="globalImagePreviewModalLabel" aria-hidden="true" style="z-index: 9999;">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 900px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(17, 24, 39, 0.97); backdrop-filter: blur(12px);">
      <!-- Modal Header -->
      <div class="modal-header border-0 py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate me-2">
          <i class="bi bi-image text-info font-18"></i>
          <h6 class="modal-title text-white text-truncate fw-semibold mb-0" id="globalImagePreviewTitle" style="font-size: 15px;">
            Image Preview
          </h6>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <a href="#" id="globalImagePreviewDownload" download class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 font-12" title="Download Full Image">
            <i class="bi bi-download me-1"></i> Download
          </a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-2 p-md-4 text-center d-flex align-items-center justify-content-center" style="min-height: 300px; max-height: 80vh; background: transparent;">
        <div class="position-relative w-100 d-flex align-items-center justify-content-center">
          <img id="globalImagePreviewSrc" src="" alt="Preview" class="img-fluid rounded-3 shadow-lg" 
               style="max-height: 75vh; max-width: 100%; object-fit: contain; transition: transform 0.25s ease;">
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-0 py-2 px-4 d-flex justify-content-between align-items-center">
        <small class="text-white-50 font-12" id="globalImagePreviewCaption">Click anywhere outside to close</small>
        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Global Image Hover & Click Indicators */
  .img-popup-clickable,
  table td img:not([width="20"]):not([width="24"]):not(.no-popup) {
    cursor: pointer !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
  }
  .img-popup-clickable:hover,
  table td img:not([width="20"]):not([width="24"]):not(.no-popup):hover {
    transform: scale(1.05);
    filter: brightness(1.08);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
  }
  .image-popup-hover {
    position: relative;
    cursor: pointer;
    display: inline-block;
  }
  .image-popup-hover .image-zoom-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    opacity: 0;
    transition: opacity 0.2s ease;
    border-radius: inherit;
    pointer-events: none;
  }
  .image-popup-hover:hover .image-zoom-overlay {
    opacity: 1;
  }
</style>

<script>
  // Universal Image Popup Lightbox Handler
  (function() {
    function openImagePopup(src, title) {
      if (!src) return;
      const modalEl = document.getElementById('globalImagePreviewModal');
      if (!modalEl) return;

      const imgEl = document.getElementById('globalImagePreviewSrc');
      const titleEl = document.getElementById('globalImagePreviewTitle');
      const downloadEl = document.getElementById('globalImagePreviewDownload');
      const captionEl = document.getElementById('globalImagePreviewCaption');

      if (imgEl) imgEl.src = src;
      if (titleEl) titleEl.textContent = title || 'Image Preview';
      if (downloadEl) downloadEl.href = src;
      if (captionEl) captionEl.textContent = title ? title : 'Image Preview';

      // Bootstrap 5 modal show
      if (window.bootstrap && bootstrap.Modal) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      } else if (window.$ && $(modalEl).modal) {
        $(modalEl).modal('show');
      }
    }

    // Intercept clicks on any image or image link across the entire page
    document.addEventListener('click', function(e) {
      // 1. Check if clicked on a trigger with data-preview-image
      const trigger = e.target.closest('[data-preview-image]');
      if (trigger) {
        e.preventDefault();
        e.stopPropagation();
        const src = trigger.getAttribute('data-preview-image');
        const title = trigger.getAttribute('data-preview-title') || trigger.getAttribute('title') || '';
        openImagePopup(src, title);
        return;
      }

      // 2. Check if clicked on an anchor linking to an image file (e.g. target="_blank")
      const anchor = e.target.closest('a');
      if (anchor) {
        const href = anchor.getAttribute('href') || '';
        // Check if href is an image URL
        if (href.match(/\.(jpeg|jpg|png|webp|gif|svg)($|\?)/i) || (href.includes('/storage/') && !href.includes('.pdf') && !href.includes('.doc'))) {
          e.preventDefault();
          e.stopPropagation();
          const title = anchor.getAttribute('data-preview-title') || anchor.getAttribute('title') || anchor.textContent.trim() || 'Image Preview';
          openImagePopup(href, title);
          return;
        }
      }

      // 3. Check if clicked directly on an <img> tag in table, card, or document preview
      if (e.target.tagName === 'IMG' && !e.target.classList.contains('no-popup')) {
        // Exclude system logos or tiny avatar icons if they are smaller than 30px
        const rect = e.target.getBoundingClientRect();
        if (rect.width >= 30 && rect.height >= 30) {
          e.preventDefault();
          e.stopPropagation();
          const src = e.target.getAttribute('data-preview-image') || e.target.getAttribute('src');
          // Find title from alt, data-title, or closest row
          let title = e.target.getAttribute('data-preview-title') || e.target.getAttribute('alt') || '';
          if (!title || title === 'Preview') {
            const tr = e.target.closest('tr');
            if (tr) {
              const secondCol = tr.querySelector('td:nth-child(2)');
              if (secondCol) title = secondCol.innerText.trim().split('\n')[0];
            }
          }
          openImagePopup(src, title || 'Image Preview');
          return;
        }
      }
    }, true); // Use capture phase so target="_blank" never triggers a new tab
  })();
</script>
