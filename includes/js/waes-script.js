jQuery(document).ready(function($) {
	var searchDelay;

    // Adding the span Field to show messages
    $('._sku_field').append('<span id="sku-message" style="display: none;"></span>');
    $('._sku_field').append('<div id="sku-list" style="display: none;"></div>');
    $('#sku-list')
        .append('<p>Referências correspondentes cadastradas:</p>')
        .append('<ul id="list-ul"></ul>');
    
    function fillList (sku_list) {
        $('#list-ul').html('');
        sku_list.forEach(function(entry) {
            $('#list-ul').append('<li>&#x2713; ' + entry + '</li>')
        });
        $('#sku-list').show();
    }

    function ajaxSearchSku() {
        var spanMessage = $('#sku-message');

        var data = {
	        action: 'waes_load_sku',
	        postID: $('#post_ID').val(),
	        newSKU: $('#_sku').val()
	    };

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function(response) {

                if (response['sku_list'].length > 0)
                    fillList(response['sku_list']);

                if ( response['valid'] == true ) {
                    spanMessage.addClass('success');
                    spanMessage.removeClass('error');
                }
                else {
                    spanMessage.removeClass('success');
                    spanMessage.addClass('error');
                }
                spanMessage.html('<strong>' + response['message'] + '</strong>');
                spanMessage.show();
            }
        });
    }

    $("#_sku").bind("paste keyup", function() {
        if ($(this).val().length > 2) {
            $('#sku-message').hide();
            $('#sku-list').hide();

            // If still waiting cancel
            if (searchDelay) {
                window.clearTimeout(searchDelay);
                delete searchDelay;
            }

            // Start the search after 300 miliseconds
            searchDelay = window.setTimeout(function (){
    		    // Call the Search
    	    	ajaxSearchSku();
            }, 300);
        }
    });
});