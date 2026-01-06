// -------------------------------------------------------
// GLOBAL BUCKET STORAGE (per Dropzone ID)
// -------------------------------------------------------
window.fileBuckets = window.fileBuckets || {}; // { dzProduct: [...], dzSpec: [...] }


// -------------------------------------------------------
// INIT A SINGLE DROPZONE BY ID
// -------------------------------------------------------
function initDropzone(dropzoneId, templateType = "default") {
    const zone = document.getElementById(dropzoneId);
    if (!zone) {
        console.error("Dropzone not found:", dropzoneId);
        return;
    }

    // ensure bucket
    if (!window.fileBuckets[dropzoneId]) {
        window.fileBuckets[dropzoneId] = [];
    }

    // find file input inside this dropzone
    const fileInput = zone.querySelector('input[type="file"]');
    if (!fileInput) {
        console.error(`File input not found in dropzone: ${dropzoneId}`);
        return;
    }

    // find preview list (either via data-preview or inside)
    let previewList = null;
    if (zone.dataset && zone.dataset.preview) {
        previewList = document.getElementById(zone.dataset.preview);
    } else {
        previewList = zone.querySelector("ul");
    }

    if (!previewList) {
        console.error(`Preview list not found for dropzone: ${dropzoneId}`);
        return;
    }

    // -------------------------
    // CLICK → OPEN FILE INPUT
    // -------------------------
    zone.addEventListener("click", function (e) {
        // avoid triggering when clicking on buttons inside li (which are outside dropzone anyway)
        if (e.target.closest("button")) return;
        fileInput.click();
    });

    // -------------------------
    // INPUT CHANGE
    // -------------------------
    fileInput.addEventListener("change", function (e) {
        if (!e.target.files || !e.target.files.length) return;
        handleFiles(dropzoneId, e.target.files, previewList, templateType);
        // allow selecting the same file again
        fileInput.value = "";
    });

    // -------------------------
    // DRAG & DROP
    // -------------------------
    zone.addEventListener("dragover", function (e) {
        e.preventDefault();
        zone.classList.add("drag-over");
    });

    zone.addEventListener("dragleave", function () {
        zone.classList.remove("drag-over");
    });

    zone.addEventListener("drop", function (e) {
        e.preventDefault();
        zone.classList.remove("drag-over");
        if (!e.dataTransfer.files || !e.dataTransfer.files.length) return;
        handleFiles(dropzoneId, e.dataTransfer.files, previewList, templateType);
    });
}


// -------------------------------------------------------
// HANDLE FILES FOR A GIVEN DROPZONE
// -------------------------------------------------------
function handleFiles(dropzoneId, fileList, previewList, templateType) {
    const bucket = window.fileBuckets[dropzoneId];

    Array.from(fileList).forEach((file) => {
        const id = Date.now() + Math.random();

        const item = {
            id,
            file,
            isPDF: false
        };

        bucket.push(item);

        // render <li>
        const li = renderTemplate(item, templateType, dropzoneId);
        previewList.appendChild(li);

        // if PDF, kick off thumbnail generation
        if (item.isPDF) {
            // slight delay to ensure canvas is in DOM
            setTimeout(() => {
                renderPDFThumb(item.file, `pdf-${item.id}`);
            }, 10);
        }
    });
}


// -------------------------------------------------------
// REMOVE FILE (CALLED FROM TEMPLATE BUTTON)
// -------------------------------------------------------
function dzRemove(dropzoneId, fileId) {
    const bucket = window.fileBuckets[dropzoneId] || [];

    // remove from bucket
    window.fileBuckets[dropzoneId] = bucket.filter(item => item.id !== fileId);

    // remove from DOM
    const zone = document.getElementById(dropzoneId);
    if (!zone) return;

    let previewList = null;
    if (zone.dataset && zone.dataset.preview) {
        previewList = document.getElementById(zone.dataset.preview);
    } else {
        previewList = zone.querySelector("ul");
    }
    if (!previewList) return;

    const li = previewList.querySelector(`li[data-id="${fileId}"]`);
    if (li) li.remove();
}


// -------------------------------------------------------
// RENAME FILE (CALLED FROM TEMPLATE BUTTON)
// (simple prompt version; can be swapped to SweetAlert)
// -------------------------------------------------------
function dzRename(dropzoneId, fileId) {
    const bucket = window.fileBuckets[dropzoneId] || [];
    const fileObj = bucket.find(item => item.id === fileId);
    if (!fileObj) return;

    const zone = document.getElementById(dropzoneId);
    if (!zone) return;

    let previewList = null;
    if (zone.dataset && zone.dataset.preview) {
        previewList = document.getElementById(zone.dataset.preview);
    } else {
        previewList = zone.querySelector("ul");
    }
    if (!previewList) return;

    const li = previewList.querySelector(`li[data-id="${fileId}"]`);
    if (!li) return;

    const nameEl = li.querySelector(".file-name");
    const currentName = nameEl ? nameEl.textContent : fileObj.file.name;

    const newName = prompt("Edit file name:", currentName);
    if (!newName || !newName.trim()) return;

    // Update UI
    if (nameEl) {
        nameEl.textContent = newName.trim();
    }

    // Update File object (creates a new File instance)
    const oldFile = fileObj.file;
    fileObj.file = new File([oldFile], newName.trim(), {
        type: oldFile.type,
        lastModified: oldFile.lastModified
    });
}


// -------------------------------------------------------
// RENDER PDF THUMBNAIL USING pdfjsLib
// -------------------------------------------------------
function renderPDFThumb(file, canvasId) {
    if (typeof pdfjsLib === "undefined") {
        console.warn("pdfjsLib not found. PDF thumbnail will not be rendered.");
        return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
        const typedArray = new Uint8Array(e.target.result);

        pdfjsLib.getDocument(typedArray).promise.then(function (pdf) {
            pdf.getPage(1).then(function (page) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;

                const context = canvas.getContext("2d");
                const viewport = page.getViewport({ scale: 0.3 });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                page.render({
                    canvasContext: context,
                    viewport: viewport
                });
            });
        });
    };

    reader.readAsArrayBuffer(file);
}
