// --------------------------------------------------------
// SHARED TEMPLATE RENDERER FOR ALL DROPZONES
// type: "product" → has default radio
// type: anything else → simple preview
// --------------------------------------------------------
function renderTemplate(item, type, dropzoneId) {

    const f   = item.file;
    const ext = f.name.split(".").pop().toLowerCase();

    const isImage = f.type.startsWith("image/");
    const isPDF   = ext === "pdf";
    const isDoc   = ["doc", "docx"].includes(ext);

    // -------------------------------
    // THUMBNAIL
    // -------------------------------
    let thumb = "";
    if (isImage) {
        thumb = `<img src="${URL.createObjectURL(f)}" class="thumb me-3" style="width:60px;height:60px;object-fit:cover;">`;
    } else if (isPDF) {
        thumb = `<canvas id="pdf-${item.id}" width="60" height="70" class="me-3"></canvas>`;
        item.isPDF = true;
    } else if (isDoc) {
        thumb = `<i class="bi bi-file-earmark-word me-3" style="font-size:48px;"></i>`;
    } else {
        thumb = `<i class="bi bi-file-earmark me-3" style="font-size:48px;"></i>`;
    }

    // -------------------------------
    // FIND PREVIEW LIST & COUNT ITEMS
    // -------------------------------
    let previewList = null;
    const zone = document.getElementById(dropzoneId);

    if (zone && zone.dataset && zone.dataset.preview) {
        previewList = document.getElementById(zone.dataset.preview);
    } else if (zone) {
        previewList = zone.querySelector("ul");
    }

    const existingCount = previewList ? previewList.querySelectorAll("li").length : 0;

    // -------------------------------
    // DEFAULT RADIO (PRODUCT ONLY)
    // -------------------------------
    let defaultRadio = "";
    if (type === "product") {
        const checkedAttr = existingCount === 0 ? "checked" : "";
        defaultRadio = `
            <div class="form-check me-3">
                <input type="radio"
                       class="form-check-input"
                       name="productDefault"
                       id="new_file_${existingCount}" 
                       value="${item.id}"
                       ${checkedAttr}>
                       <label class="form-check-label" for="new_file_${existingCount}">Make as Default</label>
            </div>
        `;

        // If this is the first product file, also update hidden default_image field
        if (existingCount === 0) {
            const hiddenDefault = document.getElementById("default_image");
            if (hiddenDefault) {
                hiddenDefault.value = item.id;
            }
        }
    }

    // -------------------------------
    // BUILD LI
    // -------------------------------
    const li = document.createElement("li");
    li.classList.add("list-group-item", "mb-2");
    li.setAttribute("data-id", item.id);

    li.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                ${thumb}
                <div>
                    <strong class="file-name">${f.name}</strong><br>
                    <small>${(f.size / 1024).toFixed(2)} KB</small> |
                    <small>${f.type}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                ${defaultRadio}
                <button type="button" class="btn p-0" onclick="dzRename('${dropzoneId}', ${item.id})">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button type="button" class="btn p-0" onclick="dzRemove('${dropzoneId}', ${item.id})">
                    <span class="material-symbols-outlined text-danger">delete</span>
                </button>
            </div>
        </div>
    `;

    return li;
}

