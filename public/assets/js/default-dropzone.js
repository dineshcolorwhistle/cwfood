// ---------------------------------------------
// FILE BUCKETS FOR MULTIPLE DROPZONES
// ---------------------------------------------
let fileBuckets = {
    0: [],   // For dropzone #1 → selectedFiles
    1: []    // For dropzone #2 → commentFiles
};

// ---------------------------------------------
// GET MULTIPLE ELEMENT GROUPS
// ---------------------------------------------
const dropAreas = document.querySelectorAll("[id^='dropzone']");
const fileInputs = document.querySelectorAll("[id^='fileInput']");
const fileLists  = document.querySelectorAll("[id^='fileList']");


// =============================================
// INITIALIZE EACH DROPZONE
// =============================================
dropAreas.forEach((dropArea, boxIndex) => {

    // --- drag over
    dropArea.addEventListener("dragover", e => {
        e.preventDefault();
        dropArea.classList.add("drag-over");
    });

    // --- drag leave
    dropArea.addEventListener("dragleave", () => {
        dropArea.classList.remove("drag-over");
    });

    // --- on drop
    dropArea.addEventListener("drop", e => {
        e.preventDefault();
        dropArea.classList.remove("drag-over");
        handleNewFiles(e.dataTransfer.files, boxIndex);
    });

    // --- click to open fileInput
    dropArea.addEventListener("click", () => fileInputs[boxIndex].click());

    // --- fileInput change
    fileInputs[boxIndex].addEventListener("change", e => {
        handleNewFiles(e.target.files, boxIndex);
    });
});


// =============================================
// ADD NEW FILES TO CORRECT BUCKET
// =============================================
function handleNewFiles(files, boxIndex) {
    [...files].forEach(file => {
        fileBuckets[boxIndex].push({
            file,
            id: Date.now() + Math.random() // unique dynamic id
        });
    });

    rebuildList(boxIndex);
}


// =============================================
// REBUILD LIST: keep existing + append new
// =============================================
function rebuildList(boxIndex) {

    const list = fileLists[boxIndex];

    // STEP 1: Remove ALL previously added NEW items
    list.querySelectorAll("li[data-new-id]").forEach(li => li.remove());

    // STEP 2: Append fresh previews for ALL new files
    fileBuckets[boxIndex].forEach((item) => {
        appendNewPreview(item, boxIndex);
    });
}


// =============================================
// APPEND NEW FILE PREVIEW
// =============================================
function appendNewPreview(item, boxIndex) {

    const list = fileLists[boxIndex];
    const f = item.file;

    const ext = f.name.split('.').pop().toLowerCase();
    const isImage = f.type.startsWith("image/");
    const isPDF = ext === "pdf";
    const isDoc = ["doc", "docx"].includes(ext);

    let li = document.createElement("li");
    li.classList.add("list-group-item", "mb-2");
    li.setAttribute("data-new-id", item.id);

    let thumbHTML = "";

    if (isImage) {
        // IMAGE thumbnail
        const thumbURL = URL.createObjectURL(f);
        thumbHTML = `<img src="${thumbURL}" class="thumb me-3">`;

    } else if (isPDF) {
        // placeholder until thumbnail is generated
        thumbHTML = `<canvas id="pdf-thumb-${item.id}" class="thumb me-3" width="60" height="70"></canvas>`;

        // generate PDF thumbnail
        generatePdfThumbnail(f, `pdf-thumb-${item.id}`);

    } else if (isDoc) {
        // Word documents cannot be rendered → show icon
        thumbHTML = `<i class="bi bi-file-earmark-word me-3" style="font-size:48px;"></i>`;
    } else {
        thumbHTML = `<i class="bi bi-file-earmark me-3" style="font-size:48px;"></i>`;
    }

    li.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                ${thumbHTML}
                <div>
                     <strong class="file-name">${f.name}</strong><br>
                    <small>${(f.size / 1024).toFixed(2)} KB</small> |
                    <small>${f.type}</small>
                </div>
            </div>
            <div>
                <button type="button" class="btn p-0" onclick="editNewFileName(${boxIndex}, ${item.id})">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button class="btn p-0" onclick="removeNewFile(${boxIndex}, ${item.id})">
                    <span class="material-symbols-outlined text-danger">delete</span>
                </button>
            </div>
        </div>
    `;

    list.appendChild(li);
}


function generatePdfThumbnail(file, canvasId) {
    const fileReader = new FileReader();

    fileReader.onload = function() {
        const typedarray = new Uint8Array(this.result);

        pdfjsLib.getDocument(typedarray).promise.then(pdf => {
            pdf.getPage(1).then(page => {

                const canvas = document.getElementById(canvasId);
                const ctx = canvas.getContext("2d");

                const viewport = page.getViewport({ scale: 0.2 }); // thumbnail size

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });
            });
        });
    };

    fileReader.readAsArrayBuffer(file);
}



// =============================================
// DELETE NEW FILE FROM BUCKET AND UI
// =============================================
window.removeNewFile = function(boxIndex, id) {

    // Remove from array
    fileBuckets[boxIndex] = fileBuckets[boxIndex].filter(item => item.id !== id);

    // Remove from UI
    const list = fileLists[boxIndex];
    const target = list.querySelector(`li[data-new-id="${id}"]`);
    if (target) target.remove();
};


window.editNewFileName = function (boxIndex, id) {

    // Find file object by ID
    let fileObj = fileBuckets[boxIndex].find(f => f.id === id);
    if (!fileObj) return;

    let currentName = fileObj.file.name;

    Swal.fire({
        title: "Edit File Name",
        input: "text",
        inputValue: currentName,
        showCancelButton: true,
        confirmButtonText: "Save",
        inputValidator: value => {
            if (!value.trim()) return "File name cannot be empty";
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        let newName = result.value.trim();

        // 1️⃣ UPDATE UI
        const li = fileLists[boxIndex].querySelector(`li[data-new-id="${id}"]`);
        if (li) {
            li.querySelector(".file-name").textContent = newName;
        }

        // 2️⃣ RENAME FILE OBJECT (must recreate File object)
        let oldFile = fileObj.file;

        fileObj.file = new File([oldFile], newName, {
            type: oldFile.type,
            lastModified: oldFile.lastModified
        });
    });
};

