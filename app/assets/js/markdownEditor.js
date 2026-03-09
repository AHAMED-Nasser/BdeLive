/**
 * Initialise EasyMDE pour les champs Markdown (événements, articles).
 * Cherche #markdown-editor et utilise data-autosave-id pour l'autosave.
 */
(function () {
    const el = document.getElementById('markdown-editor');
    if (!el || typeof EasyMDE === 'undefined') return;
    const autosaveId = el.getAttribute('data-autosave-id') || 'markdown_content';
    new EasyMDE({
        element: el,
        spellChecker: false,
        autosave: {
            enabled: true,
            uniqueId: autosaveId,
        },
        placeholder: el.getAttribute('placeholder') || "Écrivez en Markdown...",
        status: ["lines", "words", "cursor"],
    });
})();
