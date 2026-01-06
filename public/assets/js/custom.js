function show_swal(status, message, message_type = '') {
    // Base SweetAlert configuration
    const swalConfig = {
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
    };

    // Check if it's a validation message
    if (status === 0 && message_type === 'Validation') {
        let errorHtml = '';
        
        // Handle multiple validation messages
        if (typeof message === 'string' && message.includes(',')) {
            const errorMessages = message.split(',').map(msg => msg.trim());
            errorMessages.forEach(msg => {
                if (msg) {
                    errorHtml += `<div class="validation-error-item">${msg}</div>`;
                }
            });
        } 
        // Handle single validation message
        else {
            errorHtml = `<div class="validation-error-item">${message}</div>`;
        }

        Swal.fire({
            title: 'Validation Error',
            html: errorHtml,
            icon: 'warning',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
                htmlContainer: 'validation-errors-container'
            },
            buttonsStyling: false
        });
    } 
    // Handle non-validation messages
    else {
        Swal.fire({
            text: message,
            icon: status === 0 ? 'warning' : 'success',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
    }
}

function get_workspace_based_client(_this){
    let client = $(_this).val()
    let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'client':client};	
    $.ajax({
        type: "POST",
        url: "/get/ws_based_clientID",
        dataType: 'json',
        data: data,
        beforeSend: function () {
        },
        success: function (response) {                
            if(response.status == true){
                $(`#client_Settings a`).attr('href', `/client/${client}/company-profile`)
                $('#ws_list').empty().append('<option disabled>Select Workspace</option>');
                if(response.ws_list.length > 0){
                    $.each(response.ws_list, function(key, value) {   
                        if(value.id == response.ws_id){
                            $('#ws_list').append($("<option></option>").attr("value", value.id).attr('selected',"").text(value.name));
                        }else{
                            $('#ws_list').append($("<option></option>").attr("value", value.id).text(value.name));
                        }
                    });
                    window.location.href = "/product-views";
                }         
            }else{
                show_swal(0, response.message);
            }
        },
        complete: function(){}
    });
}

function display_company_details(_this){
    let client = $(`#client_list`).val()
    let ws = $(_this).val()
    let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'client':client,'ws':ws};	
    $.ajax({
        type: "POST",
        url: "/session/update",
        dataType: 'json',
        data: data,
        beforeSend: function () {
        },
        success: function (response) {                
            if(response.status == true){
                window.location.href = "/products";
            }else{
                show_swal(0, response.message);
            }
        },
        complete: function(){}
    });
}

function make_favorite(_this) {
    let fav_val = $(_this).attr('data-favor')
    let id = $(_this).attr('id')
    let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'id':id,'favor':fav_val};	
    $.ajax({
        type: "POST",
        url: $(_this).attr('data-url'),
        dataType: 'json',
        data: data,
        beforeSend: function () {
        },
        success: function (response) {    
            if(response['status'] == true){
                show_swal(1, response.message);
                $(`#${id}`).attr('data-favor',response.val)
            }else{
                show_swal(0, response.message);
            }
        },
        complete: function(){}
    });

    //let msg = (fav_val == "0")? "Want to favorite this.!": "Want to Unfavorite this.!"
    // Swal.fire({
    // title: "Are you sure?",
    // text: msg,
    // icon: "warning",
    // showCancelButton: true,
    // confirmButtonColor: "#3085d6",
    // cancelButtonColor: "#d33",
    // confirmButtonText: "Yes, favorite it!"
    // }).then((result) => {
    // if (result.isConfirmed) {
        
    // }
    // });
}


function make_duplicate(_this){
    Swal.fire({
        title: "Are you sure?",
        text: "Want to duplicate this.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Duplicate it!"
      }).then((result) => {
        if (result.isConfirmed) {
            let type = $(_this).attr('data-type')
            let url = $(_this).attr('data-url')
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'type':type};	
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {    
                    if(response['status'] == true){
                        show_swal(1, response.message);
                        window.location.href = response.url
                    }else{
                        show_swal(0,response.message)
                    }
                },
                complete: function(){}
            });
        }
    });    
}

function confirm_popup() {
    show_swal(0,`This product will be saved as a sub-recipe once you complete all the pages and click the ‘Finish’ button.`)
    $(`#RM_SKU`).css('display','block')
    $(`#RM_SKU input[name="raw_material_sku"]`).val(`RM_${$('input[name="prod_sku"]').val()}`)
}

function remove_rm_popup(){
    $(`#RM_SKU`).css('display','none')
    $(`#RM_SKU input[name="raw_material_sku"]`).val(``)
}

