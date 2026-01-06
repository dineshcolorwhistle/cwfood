// ---------------------------------------------
// INITIALIZE ALL DROPZONES ON THE PAGE
// ---------------------------------------------

// PRODUCT images (with default selector)
if (document.getElementById("dzProduct")) {
    initDropzone("dzProduct", "product");
}

// SPECIFICATION PDF (simple)
if (document.getElementById("dzSpec")) {
    initDropzone("dzSpec", "default");
}

// TICKET (if present, simple)
if (document.getElementById("dzTicket")) {
    initDropzone("dzTicket", "default");
}


// ----------------------------
// UPDATE HIDDEN DEFAULT FIELD
// ----------------------------
$(document).on('change', 'input[name="productDefault"]', function () {
    const default_image_val = parseInt($(this).attr('id').split('_').pop()) + 1;
    $('#default_image').val(default_image_val);
});
