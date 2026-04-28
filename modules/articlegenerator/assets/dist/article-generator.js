(function () {
    const generateAllBtn = document.getElementById('ag-generate-all-btn');
    const enrichBtn      = document.getElementById('ag-enrich-btn');
    const status         = document.getElementById('ag-status');
    const perspectiveInput = document.getElementById('ag-perspective');
    const briefInput       = document.getElementById('ag-brief');

    if (!generateAllBtn && !enrichBtn) return;

    function setLoading(activeBtn, otherBtn, label) {
        activeBtn.disabled = true;
        activeBtn.textContent = label;
        if (otherBtn) otherBtn.disabled = true;
        status.textContent = '';
        status.style.color = '';
    }

    function setIdle(activeBtn, otherBtn, originalLabel) {
        activeBtn.disabled = false;
        activeBtn.textContent = originalLabel;
        if (otherBtn) otherBtn.disabled = false;
    }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            ?? window.Craft?.csrfTokenValue
            ?? '';
    }

    async function callAction(btn, loadingLabel, originalLabel, otherBtn) {
        const entryId     = btn.dataset.entryId;
        const action      = btn.dataset.action;
        const perspective = perspectiveInput?.value.trim() ?? '';
        const brief       = briefInput?.value.trim() ?? '';
        const csrf        = getCsrf();

        setLoading(btn, otherBtn, loadingLabel);

        try {
            const response = await fetch(action, {
                method: 'POST',
                headers: {
                    'Accept':       'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrf,
                },
                body: new URLSearchParams({
                    entryId,
                    perspective,
                    brief,
                    [window.Craft?.csrfTokenName ?? 'CRAFT_CSRF_TOKEN']: csrf,
                }),
            });

            const data = await response.json();

            if (data.success) {
                const msg = data.mode === 'create'
                    ? `Full article generated — ${data.faqCount} FAQs created. Reload the page to review.`
                    : `${data.faqCount} FAQs and takeaways generated. Reload the page to review.`;
                status.style.color = '#27ae60';
                status.textContent = msg;
            } else {
                status.style.color = '#e74c3c';
                status.textContent = 'Error: ' + (data.error ?? 'Unknown error');
            }
        } catch (err) {
            status.style.color = '#e74c3c';
            status.textContent = 'Request failed: ' + err.message;
        } finally {
            setIdle(btn, otherBtn, originalLabel);
        }
    }

    if (generateAllBtn) {
        generateAllBtn.addEventListener('click', () => {
            callAction(generateAllBtn, 'Generating…', 'Generate Full Article', enrichBtn);
        });
    }

    if (enrichBtn) {
        enrichBtn.addEventListener('click', () => {
            callAction(enrichBtn, 'Generating…', 'Generate Takeaways & FAQs only', generateAllBtn);
        });
    }
})();
