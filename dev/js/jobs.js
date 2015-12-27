// Add job
$('.btn-add-job').on('click', function(e){
    e.preventDefault();
    $(this).prop('disabled', true);

    // Get form variables
    var form = $('form.add-job');

    // Loop through fields
    var data = new Object();
    var collection = $('input[name="collection"]').val();

    $('.tab-pane.active', form).each(function(){
        var pane = $(this);

        var object = new Object();
        object.type = pane.data('type');

        $('input, textarea, select', pane).each(function(){
            if($(this).attr('name')){
                // Regular fields
                if($(this).attr('type') == 'checkbox'){
                    object[$(this).attr('name')] = $(this).prop('checked') ? 1 : 0;
                }else{
                    object[$(this).attr('name')] = $(this).val();
                }
            }
        });

        data[pane.data('part')] = object;
    });

    // Get the schedule
    data['schedule'] = $('#schedule option:checked').val();

    // Ajax call
    $.ajax({
        url: baseURL + 'api/input/' + collection,
        data: JSON.stringify(data),
        method: 'PUT',
        headers: {
            'Accept' : 'application/json',
            'Authorization': authHeader
        },
        success: function(e){
            // Done, redirect to jobs page
            window.location = baseURL + 'api/admin/jobs';
        },
        error: function(e){
            $('.btn-add-job').prop('disabled', false);
            if(e.status != 405){
                var error = JSON.parse(e.responseText);
                if(error.error && error.error.message){
                    $('.error .text').html(error.error.message)
                    $('.error').removeClass('hide').show().focus();
                }
            }else{
                // Ajax followed location header -> ignore
                window.location = baseURL + 'api/admin/jobs';
            }
        }
    })

});


// Edit job
$('.btn-edit-job').on('click', function(e){
    e.preventDefault();

    $(this).prop('disabled', true);

    // Get form variables
    var form = $('form.edit-job');

    // Loop through fields
    var data = new Object();
    var collection = $('input[name="collection"]').val();

    $('.tab-pane', form).each(function(){

        var pane = $(this);
        var sequence = pane.attr('id');

        var object = new Object();
        object.type = pane.data('type');

        $('input, textarea, select', pane).each(function(){
            if($(this).attr('name')){
                // Regular fields
                if($(this).attr('type') == 'checkbox'){
                    object[$(this).attr('name')] = $(this).prop('checked') ? 1 : 0;
                }else{
                    object[$(this).attr('name')] = $(this).val();
                }
            }
        });

        data[sequence] = object;
    });

    // Get the schedule
    data['schedule'] = $('#schedule option:checked').val();

    // Ajax call
    $.ajax({
        url: baseURL + 'api/input/' + collection,
        data: JSON.stringify(data),
        method: 'POST',
        headers: {
            'Accept' : 'application/json',
            'Authorization': authHeader
        },
        success: function(e){
            // Done, redirect to jobs page
            window.location = baseURL + 'api/admin/jobs';
        },
        error: function(e){
            $('.btn-edit-job').prop('disabled', false);
            if(e.status != 405){
                var error = JSON.parse(e.responseText);
                if(error.error && error.error.message){
                    $('.error .text').html(error.error.message)
                    $('.error').removeClass('hide').show().focus();
                }
            }else{
                // Ajax followed location header -> ignore
                window.location = baseURL + 'api/admin/jobs';
            }
        }
    })

});