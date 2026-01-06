let selectedFiles = [];

const dropArea = document.getElementById("dropzone");
const fileInput = document.getElementById("fileInput");
const fileList = document.getElementById("fileList");

// ----------------------------
// DRAG DROP HANDLERS
// ----------------------------
dropArea.addEventListener("dragover", e => {
    e.preventDefault();
    dropArea.classList.add("drag-over");
});
dropArea.addEventListener("dragleave", () => dropArea.classList.remove("drag-over"));
dropArea.addEventListener("drop", e => {
    e.preventDefault();
    dropArea.classList.remove("drag-over");
    addFiles(e.dataTransfer.files);
});
dropArea.addEventListener("click", () => fileInput.click());
fileInput.addEventListener("change", e => addFiles(e.target.files));


// ----------------------------
// ADD NEW FILES
// ----------------------------
function addFiles(files) {
    [...files].forEach((file) => {
        selectedFiles.push({ file });
    });
    appendNewFiles();
}


// ----------------------------
// APPEND NEW FILE PREVIEWS
// ----------------------------
function appendNewFiles() {
    // existing <li> count
    const existingCount = fileList.querySelectorAll("li").length;

    selectedFiles.forEach((item, index) => {
        const file = item.file;
        const isImage = file.type.startsWith("image/");
        const thumbURL = isImage ? URL.createObjectURL(file) : "";

        let checked = (existingCount == 0 && index == 0)?'checked': '';
        
        // Global index (existing + new)
        const globalIndex = existingCount + index;

        let li = document.createElement("li");
        li.classList.add("list-group-item", "mb-2");
        li.setAttribute("data-new-index", index);

        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                
                <div class="d-flex align-items-center">
                    ${
                        isImage
                        ? `<img src="${thumbURL}" class="thumb me-3">`
                        : `<i class="bi bi-file-earmark me-3" style="font-size:40px"></i>`
                    }
                    <div>
                        <strong id="file_name_${index}">${file.name}</strong><br>
                        <small>${(file.size / 1024).toFixed(2)} KB</small> |
                        <small>${file.type}</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                
                    <div class="form-check m-0">
                        <input class="form-check-input" 
                               type="radio" 
                               name="productDefault" 
                               id="new_file_${globalIndex}" 
                               value="new_${index}" ${checked}>
                        <label class="form-check-label" for="new_file_${globalIndex}">Make as Default</label>
                    </div>
                    <div>
                        <button type="button"
                                class="btn p-0"
                                onclick="editNewFileName(${index})">
                            <span class="material-symbols-outlined">edit</span>
                        </button>

                        <button type="button" 
                                class="btn p-0" 
                                onclick="removeNewFile(${index})">
                            <span class="material-symbols-outlined text-danger">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        fileList.appendChild(li);
    });
}


// ----------------------------
// DELETE ONLY NEWLY ADDED FILES
// ----------------------------
window.removeNewFile = function(index) {
    selectedFiles.splice(index, 1);

    // Remove one <li> with matching "data-new-index"
    const newLis = fileList.querySelectorAll(`li[data-new-index="${index}"]`);
    newLis.forEach(li => li.remove());

    // re-render ONLY new files (avoid duplication)
    rebuildNewList();
};


window.editNewFileName = function(index) {
    let currentName = selectedFiles[index].file.name;

    Swal.fire({
        title: "Edit File Name",
        input: "text",
        inputValue: currentName,
        inputValidator: (value) => {
            if (!value.trim()) {
                return "File name cannot be empty";
            }
        },
        showCancelButton: true,
        confirmButtonText: "Save",
    }).then(result => {
        if (result.isConfirmed) {

            // Update UI
            document.getElementById(`file_name_${index}`).textContent = result.value;

            // Update File object name (create a new File instance)
            let oldFile = selectedFiles[index].file;
            let newFile = new File([oldFile], result.value, { type: oldFile.type });

            selectedFiles[index].file = newFile;
        }
    });
};


// ----------------------------
// REBUILD NEW PREVIEW LIST AFTER DELETE
// ----------------------------
function rebuildNewList() {
    // Remove all dynamic (new) items
    fileList.querySelectorAll("li[data-new-index]").forEach(li => li.remove());

    // Re-append updated items
    appendNewFiles();
}




// ----------------------------
// UPDATE HIDDEN DEFAULT FIELD
// ----------------------------
$(document).on('change', 'input[name="productDefault"]', function () {
    const default_image_val = parseInt($(this).attr('id').split('_').pop()) + 1;
    $('#default_image').val(default_image_val);
});

