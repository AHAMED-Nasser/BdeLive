!function () {
  let e = document.getElementById("markdown-editor");
  if (!e || "undefined" == typeof EasyMDE) return;
  let t = e.getAttribute("data-autosave-id") || "markdown_content";
  let editor = new EasyMDE({
    element: e,
    spellChecker: !1,
    forceSync: !0,
    autosave: { enabled: !0, uniqueId: t },
    placeholder: e.getAttribute("placeholder") || "Écrivez en Markdown...",
    status: ["lines", "words", "cursor"]
  });
  let form = e.closest("form");
  if (form && editor && editor.codemirror) {
    function syncToTextarea() {
      try {
        editor.codemirror.save();
      } catch (err) {}
    }
    form.addEventListener("submit", syncToTextarea, !0);
    let submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.addEventListener("click", syncToTextarea, !0);
    }
  }
}();
