const easyMDE = new EasyMDE({
    element: document.getElementById('markdown-editor'),
    spellChecker: false,
    autosave: {
        enabled: true,
        uniqueId: "event_description",
    },
    placeholder: "Écrivez la description ici en Markdown...",
    status: ["lines", "words", "cursor"],
});