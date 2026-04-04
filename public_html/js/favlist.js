
document.addEventListener('DOMContentLoaded', function () {

  document.querySelectorAll('.wishlist-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const elementId     = this.dataset.elementId;
      const elementSiteId = this.dataset.elementSiteId;
      const toggleUrl     = this.dataset.toggleUrl;
      const isSaved       = this.dataset.saved === 'true';
      const btnEl         = this;

      // Disable during request
      btnEl.disabled = true;

      fetch(toggleUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': '{{ craft.app.request.csrfToken }}'
        },
        body: new URLSearchParams({
          'elementId':     elementId,
          'elementSiteId': elementSiteId,
          '{{ craft.app.request.csrfParam }}': '{{ craft.app.request.csrfToken }}'
        })
      })
      .then(res => res.json())
      .then(data => {
        const nowSaved = data.items && data.items.action === 'added';

        btnEl.dataset.saved = nowSaved ? 'true' : 'false';
        btnEl.title         = nowSaved ? 'Remove from saved' : 'Save therapist';
        btnEl.setAttribute('aria-label', nowSaved ? 'Remove from saved' : 'Save therapist');

        // Swap the icon
        btnEl.innerHTML = nowSaved
          ? `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark-fill" viewBox="0 0 16 16">
              <path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
             </svg>`
          : `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark" viewBox="0 0 16 16">
              <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
             </svg>`;

        btnEl.disabled = false;
      })
      .catch(err => {
        console.error('Wishlist toggle failed:', err);
        btnEl.disabled = false;
      });
    });
  });

});
