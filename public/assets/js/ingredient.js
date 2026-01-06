let selectedOrder = [];

$('#ing_allergen').select2({
  width: '100%',
  multiple: true
})
.on('select2:select', function (e) {
  const id = e.params.data.id;
  if (!selectedOrder.includes(id)) {
    selectedOrder.push(id);
  }
  reorderSelect2Tags(this, selectedOrder);
})
.on('select2:unselect', function (e) {
  const id = e.params.data.id;
  selectedOrder = selectedOrder.filter(v => v !== id);
  reorderSelect2Tags(this, selectedOrder);
});


function reorderSelect2Tags(select, order) {
  // Wait a tick for Select2 to render the new tag before reordering
  setTimeout(() => {
    const $container = $(select).next('.select2-container').find('ul.select2-selection__rendered');
    const $tags = $container.children('li.select2-selection__choice');

    // Build map of rendered tags by their text/value
    const tagMap = {};
    $tags.each(function () {
      const value = $(this).attr('title'); // tag label text (matches option text)
      tagMap[value] = $(this);
    });

    // Get ordered option text values
    const orderedTexts = order.map(v => $(select).find(`option[value="${v}"]`).text());

    // Append in correct order (no clearing to keep event bindings intact)
    orderedTexts.forEach(text => {
      if (tagMap[text]) {
        $container.append(tagMap[text]);
      }
    });
  }, 0);
}


$(document).ready(function () {
    $('.js-example-basic-single, .select2-tags').select2({
        width: '100%'
    });

    /**
     * Allergen orders
     */
    const preselected = $('#ing_allergen').val() || [];
    if (preselected.length > 0) {
        // Initialize selectedOrder with the current selected values
        selectedOrder = preselected.slice(); // copy array
        // Ensure tags are displayed in the same order
        reorderSelect2Tags($('#ing_allergen')[0], selectedOrder);
    }

    // Function to format numbers with commas for display
    function formatWithCommas(value) {
        if (!value) return '';
        value = value.toString().replace(/,/g, ''); // Remove existing commas
        const parts = value.split('.');
        let integerPart = parts[0];
        const decimalPart = parts[1];

        // Add commas only to the integer part
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        return decimalPart ? `${integerPart}.${decimalPart}` : integerPart;
    }

    // Function to remove commas before using in calculations
    function removeCommas(value) {
        return value ? value.toString().replace(/,/g, '') : '0';
    }

    // Restrict non-numeric characters (except '.')
    function restrictNonNumeric(field) {
        let value = field.val();
        let sanitizedValue = value.replace(/[^0-9.]/g, ''); // Allow only numbers and .
        field.val(sanitizedValue);
    }

    // Apply validation and formatting while typing
    $('#ing_total_price, #ing_quantity').on('input', function () {
        restrictNonNumeric($(this));
    });

    // Format input when losing focus & calculate total price
    $('#ing_total_price, #ing_quantity').on('focusout', function () {
        let rawValue = removeCommas($(this).val()); // Get raw number
        let formattedValue = formatWithCommas(rawValue); // Format with commas
        $(this).val(formattedValue); // Display formatted value

        let total_price = parseFloat(removeCommas($('#ing_total_price').val())) || 0;
        let qty = parseFloat(removeCommas($('#ing_quantity').val())) || 0;
        let spec = parseFloat(removeCommas($('#ing_spec_gravity').val())) || 1;
        let qty_unit = $(`#ing_quantity_unit`).val()
        unitprice_calculation(total_price,qty,qty_unit,spec);
    });

    $(document).on('change','#ing_quantity_unit',function(){
        let total_price = parseFloat(removeCommas($('#ing_total_price').val())) || 0;
        let qty = parseFloat(removeCommas($('#ing_quantity').val())) || 0;
        let qty_unit = $(this).val()
        let spec = parseFloat(removeCommas($('#ing_spec_gravity').val())) || 1;
        unitprice_calculation(total_price,qty,qty_unit,spec);
    })

    $(document).on('focusout','#ing_spec_gravity',function(){
        let spec = parseFloat(removeCommas($(this).val())) || 1;
        let qty_unit = $(`#ing_quantity_unit`).val()
        if(qty_unit == "ml" || qty_unit == "l"){
            let total_price = parseFloat(removeCommas($('#ing_total_price').val())) || 0;
            let qty = parseFloat(removeCommas($('#ing_quantity').val())) || 0;
            unitprice_calculation(total_price,qty,qty_unit,spec);
        }
        return;
    })  
    
    function unitprice_calculation(total_price,qty,qty_unit,spec){
        let price_100_gram,cost_100g
        let msg
        switch (qty_unit) {
            case "g":
                price_100_gram = (total_price / qty ) * 100
                cost_100g = price_100_gram
                msg = "Price per 100g"
                break;
            case "kg":
                price_100_gram = (total_price / qty ) * 0.1  
                cost_100g = price_100_gram *10
                msg = "Price per kg"              
                break;
            case "ml":
                price_100_gram = (total_price/(qty * spec))*100 
                cost_100g = (total_price/qty)*100
                msg = "Price per 100mL"
                break;
            case "l":
                price_100_gram = (total_price/((qty * spec)))*0.1 
                cost_100g = total_price/qty 
                msg = "Price per L"
                break;
            default:
                break;
        }   

        $('#ing_unit_price').val(formatWithCommas(price_100_gram.toFixed(2))); // Show formatted total
        $('#ing_unit_kg_price').val(formatWithCommas((price_100_gram*10).toFixed(2))) // update kg
        $(`#raw-material-widget, #raw-material-head`).html(msg)
        $('#updated-price-detail, #updated-price-unit').html(formatWithCommas(cost_100g.toFixed(2))); // Show formatted total
       
    }

    // Nutritional Specification
    // Apply validation and formatting while typing
    $('#ing_energy, #ing_protein, #ing_total_fat, #ing_saturated_fat, #ing_avail_corb, #ing_total_sugar, #ing_sodium, #ing_spec_gravity, #ing_aus_per,#rm_supplied_shelf_life_num, #rm_inuse_shelf_life_num').on('input', function () {
        restrictNonNumeric($(this));
    });

    // Format input when losing focus
    $('#ing_energy, #ing_protein, #ing_total_fat, #ing_saturated_fat, #ing_avail_corb, #ing_total_sugar, #ing_sodium, #ing_spec_gravity, #ing_aus_per').on('focusout', function () {
        let rawValues = removeCommas($(this).val()); // Get raw number
        let formattedValues = formatWithCommas(rawValues); // Format with commas
        $(this).val(formattedValues); // Display formatted value
    });

    // Trigger focusout on these fields when the page loads to apply formatting
    $(window).on('load', function () {
        $('#ing_energy, #ing_protein, #ing_total_fat, #ing_saturated_fat, #ing_avail_corb, #ing_total_sugar, #ing_sodium, #ing_spec_gravity, #ing_aus_per').trigger('focusout');
        // $('#ing_quantity').trigger('focusout');
        
        let total_price = parseFloat(removeCommas($('#ing_total_price').val())) || 0;
        let qty = parseFloat(removeCommas($('#ing_quantity').val())) || 0;
        let qty_unit = $('#ing_quantity_unit').val();
        let spec = parseFloat(removeCommas($('#ing_spec_gravity').val())) || 1;
    
        unitprice_calculation(total_price, qty, qty_unit, spec);
    });

    function updateInputState() {
        var selectValue = $('select[name="ing_spec_unit"]').val();
        var gravityInput = $('input[name="ing_spec_gravity"]');
        var parentDiv = gravityInput.closest('div');

        if (selectValue === 'No') {
            gravityInput.val(0).attr('readonly', 'readonly');
            parentDiv.removeClass('table-active-input').addClass('table-active-readonly');
        } else if (selectValue === 'Yes') {
            gravityInput.removeAttr('readonly');
            parentDiv.removeClass('table-active-readonly').addClass('table-active-input');
        }
    }
    $('select[name="ing_spec_unit"]').on('change', updateInputState);

    updateInputState();
        // if ($(this).val() === 'g' || $(this).val() === 'kg') {
        //     $('input[name="ing_spec_gravity"]').val(0).attr('readonly', 'readonly');
        //     parentDiv.removeClass('table-active-input').addClass('table-active-readonly');
        // } else if ($(this).val() === 'ml' || $(this).val() === 'l') {
        //     $('input[name="ing_spec_gravity"]').removeAttr('readonly');
        //     parentDiv.removeClass('table-active-readonly').addClass('table-active-input');
        // }

});

$(document).ready(function () {
    $(".table.input-table input").on("focus", function () {
        // Remove the leading zero when the user focuses the input
        if ($(this).val() === "0") {
            $(this).val("");
        }
    }).on("input", function () {
        // Remove leading zeros while typing
        let val = $(this).val();
        $(this).val(val.replace(/^0+/, ''));
    }).on("blur", function () {
        // Ensure that an empty field becomes "0" when the user leaves it
        if ($(this).val().trim() === "") {
            $(this).val("0");
        }
    });
});



(function($) {
	'use strict';
	$(function() {
		if ($(".fa-basic-multiple").length) {
			$(".fa-basic-multiple").select2();
		}
	});
})(jQuery);

$(`#ing_name`).focusout(function(){
    let fval = $(this).val()
    if(fval){
        let randomNumber = '';
        for (let i = 0; i < 3; i++) {
            randomNumber +=Math.floor(Math.random() * 10) + 1; 
        }
        let final_val = `ing_${fval}_${randomNumber}`
        let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'final_val':final_val};	
        $.ajax({
            type: "POST",
            url: "/data/validate/ing_sku",
            dataType: 'json',
            data: data,
            beforeSend: function () {
            },
            success: function (response) {                
                if(response == false){
                    $(`#ing_sku`).val(final_val)
                }
            },
            complete: function(){}
        });
    }
})

function save_ingredient_source(_this){
    let source = $(_this).attr('data-source')
    let html, header = ``;
    if(source == "category"){
        html = `<div class="mb-3">
                    <label for="cat_name" class="col-form-label">Category Name:</label>
                    <input type="text" class="form-control" id="cat_name" name="cat_name">
                </div>`;
        header = `Add New Category`;
    }else if(source == "sub_category"){
        html = `<div class="mb-3">
                    <label for="subcat_name" class="col-form-label">Sub Category Name:</label>
                    <input type="text" class="form-control" id="subcat_name" name="subcat_name">
                </div>`;
        header = `Add New Sub Category`;
    }else if(source == "supplier"){
        html = `<div class="mb-3">
                    <label for="supplier_name" class="col-form-label">Supplier Name:</label>
                    <input type="text" class="form-control" id="supplier_name" name="supplier_name">
                </div>`;
        header = `Add New Supplier`;
    }else if(source == "country"){
        html = `<div class="mb-3">
                    <label for="country_orgin" class="col-form-label">Country Orgin:</label>
                    <input type="text" class="form-control" id="country_orgin" name="country_orgin" placeholder="Ex:AU">
                </div>
                <div class="mb-3">
                    <label for="country_name" class="col-form-label">Country Name:</label>
                    <input type="text" class="form-control" id="country_name" name="country_name" placeholder="Ex:Australia">
                </div>`;
        header = `Add New Country`;
    }else if(source == "allergen"){
        html = `<div class="mb-3">
                    <label for="allergen_name" class="col-form-label">Allergen Name:</label>
                    <input type="text" class="form-control" id="allergen_name" name="allergen_name">
                </div>`;
        header = `Add New Allergen`;
    }
    $(`#ingre_sour`).val(source)
    $(`#ingredientModalLabel`).text(header)
    $(`form#ing_source .modal-body`).html(html)
    $(`#ingredientModal`).modal('show')
}

function form_temp_submit(_this){
    let btn = $(_this);
    var cText = btn[0].innerText;
    let data;
    let formID = $(_this).closest("form").attr('id')
    if(formID == "ingredient_form"){
        data = get_Ingredientform_Data(formID);
        let form = $(`#ing_form`).val()
        let form_id = $(`#ing_form_id`).val()
        let url = $(_this).closest("form").attr('data-to')
        let updateRoute = $(_this).closest("form").data('update-route');
       
        $.ajax({
            type: "POST",
            url: url,
            processData: false,
            contentType: false,
            dataType: 'json',
            data: data,
            beforeSend: function () {
                if(btn){btn.text('loading...');btn.prop('disabled', true);}
            },
            success: function (response) {
                if(btn){btn.text(cText);btn.prop('disabled', false);}
                if(response.status == false){
                    if ('message_type' in response) {
                        show_swal(0, response.message, response.message_type);
                    } else {
                        show_swal(0, response.message);
                    }
                }else{
                    if(form =="add" && form_id == ''){
                        $(`#${formID}`).attr('data-to', updateRoute.replace(':id', response.id))
                        $(`#ing_form_id`).val(response.id)
                        window.location.href=response.edit_url;
                    }
                    files.length = 0
                    
                }
            },
            complete: function(){}
        });
    }

}

function form_submit(_this){
    let btn = $(_this);
    var cText = btn[0].innerText;
    let formState = $(_this).attr('title');
    let data;
    let formID = $(_this).closest("form").attr('id')
    let url = $(_this).closest("form").attr('data-to')
    if(formID == "ingredient_form"){
        data = get_Ingredientform_Data(formID);
    }
    sendAjaxReq(url,data,btn,cText,formID,formState);
}

function get_Ingredientform_Data(formID) {
    var data = new FormData();
    var form_data = $(`#${formID}`).serializeArray();
    $.each(form_data, function (key, input) {
        if (input.name !== 'ing_allergen[]') {
            data.append(input.name, input.value);
        }
    });

    console.log(selectedOrder);
    
    // ✅ Append allergens in the correct user-selected order
    if (typeof selectedOrder !== 'undefined' && selectedOrder.length > 0) {
        selectedOrder.forEach(val => {
            data.append('ing_allergen[]', val);
        });
    } else {
        // fallback if user didn’t select anything or selectedOrder not set
        const selectedVals = $('#ing_allergen').val() || [];
        selectedVals.forEach(val => data.append('ing_allergen[]', val));
    }

    selectedFiles.forEach((item, index) => {
        data.append("image_file[]", item.file); // Append each file to FormData
    });
   return data;
}

function getFormData(formID) {
    var data = new FormData();
    var form_data = $(`#${formID}`).serializeArray();
    $.each(form_data, function (key, input) {
        data.append(input.name, input.value);
    });
   return data;
}

function sendAjaxReq(url, data, btn, cText, formID, formState='') {
    $.ajax({
        type: "POST",
        url: url,
        processData: false,
        contentType: false,
        dataType: 'json',
        data: data,
        beforeSend: function () {
            if(btn) {
                btn.text('loading...');
                btn.prop('disabled', true);
            }
        },
        success: function (response) {
            if(btn) {
                btn.text(cText);
                btn.prop('disabled', false);
            }
            if (!response.status) {
                if ('message_type' in response) {
                    show_swal(0, response.message, response.message_type);
                } else {
                    show_swal(0, response.message);
                }
            } else {
                show_swal(1, response.message);
                if(formID == "ing_source") {
                    ingredient_source_update(response);
                } else if(formID == "ingredient_form") {
                    // if(formState == "Finish"){
                       
                    // }
                     setTimeout(() => {
                            window.location.href = response.url;
                        }, 3000);
                }
            }
        },
        error: function(xhr) {
            if(btn) {
                btn.text(cText);
                btn.prop('disabled', false);
            }
            show_swal(0, 'An error occurred. Please try again.');
        }
    });
}

// function show_swal(status, message, message_type = '') {
//     // Base SweetAlert configuration
//     const swalConfig = {
//         confirmButtonClass: 'btn btn-success',
//         cancelButtonClass: 'btn btn-danger',
//         buttonsStyling: false
//     };

//     // Check if it's a validation message
//     if (status === 0 && message_type === 'Validation') {
//         let errorHtml = '';
        
//         // Handle multiple validation messages
//         if (typeof message === 'string' && message.includes(',')) {
//             const errorMessages = message.split(',').map(msg => msg.trim());
//             errorMessages.forEach(msg => {
//                 if (msg) {
//                     errorHtml += `<div class="validation-error-item">${msg}</div>`;
//                 }
//             });
//         } 
//         // Handle single validation message
//         else {
//             errorHtml = `<div class="validation-error-item">${message}</div>`;
//         }

//         Swal.fire({
//             title: 'Validation Error',
//             html: errorHtml,
//             icon: 'warning',
//             confirmButtonText: 'OK',
//             customClass: {
//                 confirmButton: 'btn btn-success',
//                 cancelButton: 'btn btn-danger',
//                 htmlContainer: 'validation-errors-container'
//             },
//             buttonsStyling: false
//         });
//     } 
//     // Handle non-validation messages
//     else {
//         Swal.fire({
//             text: message,
//             icon: status === 0 ? 'warning' : 'success',
//             confirmButtonText: 'OK',
//             customClass: {
//                 confirmButton: 'btn btn-success',
//                 cancelButton: 'btn btn-danger'
//             },
//             buttonsStyling: false
//         });
//     }
// }

function ingredient_source_update(response){
    if(response.source == "category"){
        var selectOption = $('#ing_category');
        selectOption.append(
            $('<option></option>').val(response.id).html(response.name)
        );
    }else if(response.source == "sub_category"){
        var selectOption = $('#ing_subcategory');
        selectOption.append(
            $('<option></option>').val(response.id).html(response.name)
        );
    }else if(response.source == "supplier"){
        var selectOption = $('#ing_supplier');
        selectOption.append(
            $('<option></option>').val(response.id).html(response.name)
        );
    }else if(response.source == "country"){
        var selectOption = $('#ing_country');
        selectOption.append(
            $('<option></option>').val(response.id).html(response.name)
        );
    }else if(response.source == "allergen"){
        var selectOption = $('#ing_allergen');
        selectOption.append(
            $('<option></option>').val(response.id).html(response.name)
        );
    }
    $(`#ingredientModal`).modal('hide')
    return;
}

function commonDelete(_this){
    const archive = $(_this).data('archive');
    Swal.fire({
        title: "Are you sure?",
        text: (archive == 0) ? 'You want to move this record to archive status.': 'You won\'t be able to revert this!',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: (archive == 0)? 'Yes, archive it!': 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
            let data = {'_token':$('meta[name="csrf-token"]').attr('content')};	
            $.ajax({
                type: "POST",
                url: $(_this).attr('data-url'),
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {       
                    if(response.status == true){
                        $(_this).closest("tr").remove();
                        Swal.fire({
                            title: (archive == 0)? "Archived!": "Deleted!",
                            text: response.message,
                            icon: "success"
                        });
                    }else{
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning',
                            html: response.message
                        });
                    }
                },
                complete: function(){}
            });
        }
      });


}

// $(`#ing_unit_price,#ing_quantity`).focusout(function(){
//     let unit_price = $(`#ing_unit_price`).val()
//     let qty = $(`#ing_quantity`).val()
//     if(unit_price && qty){
//         let total = unit_price * qty
//         $(`#ing_total_price`).val(total)
//     }
// })

// let selectedFiles = [];

// document.addEventListener("DOMContentLoaded", function () {
//     const dropzone = document.getElementById("dropzone");
//     const fileInput = document.getElementById("fileInput");
//     const fileList = document.getElementById("fileList");

//     dropzone.addEventListener("click", () => fileInput.click());
//     fileInput.addEventListener("change", () => addFiles(fileInput.files));

//     dropzone.addEventListener("dragover", e => {
//         e.preventDefault();
//         dropzone.classList.add("dragover");
//     });

//     dropzone.addEventListener("dragleave", () => dropzone.classList.remove("dragover"));

//     dropzone.addEventListener("drop", e => {
//         e.preventDefault();
//         dropzone.classList.remove("dragover");
//         addFiles(e.dataTransfer.files);
//     });

//     function addFiles(files) {
//         [...files].forEach(file => {
//             selectedFiles.push(file);
//             displayFile(file);
//         });
//         syncInputFiles();
//     }

//     function syncInputFiles() {
//         const dataTransfer = new DataTransfer();
//         selectedFiles.forEach(f => dataTransfer.items.add(f));
//         fileInput.files = dataTransfer.files;
//     }

//     function displayFile(file) {
//         const index = selectedFiles.length - 1;

//         // let div = document.createElement("div");
//         // div.classList.add("file-item");
//         // div.setAttribute("data-index", index);

//         // div.innerHTML = `
//         //     <span>${file.name}</span>
//         //     <span class="material-symbols-outlined remove-file">delete</span>
//         // `;

//         // div.querySelector(".remove-file").addEventListener("click", function () {
//         //     removeFile(index);
//         // });

//         let div = document.createElement("div");
//             div.classList.add("file-item", "d-flex", "justify-content-between", "align-items-center", "px-3", "py-2", "mb-2", "border", "rounded");
//             div.setAttribute("data-index", index);
//             div.innerHTML = `
//                 <span class="file-name">${file.name}</span>

//                 <div class="d-flex align-items-center gap-4">
//                     <div class="form-check m-0">
//                         <input class="form-check-input" type="radio" name="ingredientDefault" id="file_default_${index}" value="${index}">
//                         <label class="form-check-label" for="file_default_${index}">Make as Default</label>
//                     </div>

//                     <button class="btn p-0 remove-file" data-index="${index}">
//                         <span class="material-symbols-outlined text-danger">delete</span>
//                     </button>
//                 </div>
//             `;
//             // Delete button event
//             div.querySelector(".remove-file").addEventListener("click", function () {
//             removeFile(index);
//         });
//         fileList.appendChild(div);
//     }

//     function removeFile(idx) {
//         // Remove from array
//         selectedFiles.splice(idx, 1);

//         // Re-render UI
//         renderList();

//         // Sync input
//         syncInputFiles();
//     }

//     function renderList() {
//         fileList.innerHTML = "";
//         selectedFiles.forEach((file, index) => {
//             let div = document.createElement("div");
//             div.classList.add("file-item", "d-flex", "justify-content-between", "align-items-center", "px-3", "py-2", "mb-2", "border", "rounded");
//             div.setAttribute("data-index", index);
//             div.innerHTML = `
//                 <span class="file-name">${file.name}</span>
//                 <div class="d-flex align-items-center gap-4">
//                     <div class="form-check m-0">
//                         <input class="form-check-input" type="radio" name="ingredientDefault" id="file_default_${index}" value="${index}">
//                         <label class="form-check-label" for="file_default_${index}">Make as Default</label>
//                     </div>
//                     <button class="btn p-0 remove-file" data-index="${index}">
//                         <span class="material-symbols-outlined text-danger">delete</span>
//                     </button>
//                 </div>
//             `;

//             // Delete button event
//             div.querySelector(".remove-file").addEventListener("click", () => {
//                 removeFile(index);
//             });

//             fileList.appendChild(div);
//         });
//     }

// });



// let files = []; // Array to hold the files
// $('#uploadimage').on('change', function() {
//     var selectedFiles = this.files; // Get the selected files array
//     let filelength = $('#fileList li').length;
//     if (selectedFiles.length > 0) {
//         for (var i = 0; i < 10; i++) {
//             var file = selectedFiles[i];
//             files.push(file); // Append each file to the array
//             // Add the file name to the UI
//             var fileName = file.name;
//             var listItem = `<li class="list-group-item d-flex justify-content-between align-items-center text-primary-dark-mud">
//                                 ${fileName}`;
//                                 if(filelength == 0 && i == 0){
//                                     listItem += `<div class="d-flex justify-content-between align-items-center gap-5"><div class="form-check">
//                                                 <input class="form-check-input" type="radio" name="ingredientDefault" id="ingredient_img_${i}" checked>
//                                                 <label class="form-check-label text-primary-dark-mud" for="ingredient_img_${i}">Make as Default</label>
//                                             </div>`;
//                                             $(`#default_image`).val(0)
//                                 }else if(filelength > 0){
//                                     listItem += `<div class="d-flex justify-content-between align-items-center gap-5"><div class="form-check">
//                                         <input class="form-check-input" type="radio" name="ingredientDefault" id="ingredient_img_${filelength + i}">
//                                         <label class="form-check-label text-primary-dark-mud" for="ingredient_img_${filelength + i}">Make as Default</label>
//                                     </div>`;
//                                 }else{
//                                         listItem += `<div class="d-flex justify-content-between align-items-center gap-5"><div class="form-check">
//                                             <input class="form-check-input" type="radio" name="ingredientDefault" id="ingredient_img_${i}">
//                                             <label class="form-check-label text-primary-dark-mud" for="ingredient_img_${i}">Make as Default</label>
//                                         </div>`;
//                                 }
//                                listItem += ` <button class="btn icon-primary-orange deleteBtn" data-index="${files.length - 1}"><span class="material-symbols-outlined">delete</span></button></div>
//                             </li>`;
//                 $('#fileList').append(listItem);
//         }
//     }
//     $(this).val(''); // Clear the input after adding the file
// });

// Handle image delete button click
// $(document).on('click', '.deleteBtn', function() {
//     var index = $(this).data('index');
//     files.splice(index, 1); // Remove the file from the array
//     $(this).closest('li').remove(); // Remove the list item from the UI
// });

// $(document).on('change', 'input[name="ingredientDefault"]', function() {  
//     $(`#default_image`).val($(this).attr('id').split('_').pop())
// })

function remove_images(_this) {
    let imgID = $(_this).attr('data-id')
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
            let data = {'_token':$('meta[name="csrf-token"]').attr('content')};	
            $.ajax({
                type: "POST",
                url: `/remove/images/${imgID}`,
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {    
                    if(response['status'] == false){
                        if ('message_type' in response) {
                            show_swal(0, response.message, response.message_type);
                        } else {
                            show_swal(0, response.message);
                        }
                    }else{
                        $(_this).closest('li').remove(); // Remove the list item from the UI
                        show_swal(1,"Image has been deleted.")
                    }
                },
                complete: function(){}
            });
        }
      });
}


function download_images(_this) {
    var checkedCheckboxes = $('#img_lib').find('input[name="img_ckeck"]:checked');
    if(checkedCheckboxes.length == 0){
        show_swal(0,"Check any one checkbox")
    }else{
        var myarray = [];
        checkedCheckboxes.each(function() {
            myarray.push({
                module: $(this).attr('data-module'),
                moduleid: $(this).attr('data-moduleid')
            });
        });
        let selectArray = JSON.stringify(myarray)
        let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'details':selectArray};	
        $.ajax({
            type: "POST",
            url: `/admin/download/images`,
            dataType: 'json',
            data: data,
            beforeSend: function () {
            },
            success: function (response) {    
                if(response['status'] == false){
                    if ('message_type' in response) {
                        show_swal(0, response.message, response.message_type);
                    } else {
                        show_swal(0, response.message);
                    }
                }else{
                    $(_this).closest('li').remove(); // Remove the list item from the UI
                    show_swal(1,"Image has been deleted.")
                }
            },
            complete: function(){}
        });
    }
}


$(document).on('click','#rawmaterialDefault',function() {
    var _this = this;
    $('input.raw_material_check').each(function() {
      if ($(_this).is(':checked')) {
        $(this).prop('checked', true);
      } else {
        if($(this).attr('id') == "SKU" || $(this).attr('id') == "name_by_kitchen"){
        }else{
            $(this).prop('checked', false);
        }
      }
    });
});

function export_details(_this) {
    let url = $(_this).attr('data-url')
    $('#exampleModal').modal('show')
    $('#exportable_url').val(url)         
}

function save_export_column(params) {
    let url = $('#exportable_url').val()         
    let selectedLabels = [];
    $('#export_column').find('div.form-check-temp input').each(function() {
        if ($(this).is(':checked')) {
            let label = $(this).next('label').text().trim(); // Get the label text
            if (label) {
                selectedLabels.push(label);
            }
        }
    });
    let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'ex_xol':selectedLabels};	
    $.ajax({
        type: "POST",
        url: $(`#export_url`).val(),
        dataType: 'json',
        data: data,
        beforeSend: function () {
        },
        success: function (response) {    
            if(response['status'] == true){
                $('#exampleModal').modal('hide')
            }
        },
        complete: function(){
             window.open(url,'_blank');
        }
    });
}

$(document).on('change','select.suitable_select',function(){
    let selectVal = $(this).val()
    if(selectVal == "No"){
        let selectName = $(this).attr('name')
        let splitArray = selectName.split('_')
        $(`select[name="rm_${splitArray[1]}_validated"]`).val('na')
        $(`select[name="rm_${splitArray[1]}_certification_yn"]`).val('No')        
    }
})






