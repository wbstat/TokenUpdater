$(document).ready(function() {

    var $form = $('#refresh_form');

    $form.on('submit', function(e)
    {
        e.preventDefault();
		
		formData = objectifyForm($form);

        sendAjax(formData, function(response) {
            $('.result').html(response.html);
        });
    });



	function objectifyForm(form) {
	
		var formArray = form.serializeArray();
		
	    var returnArray = {};
	    for (var i = 0; i < formArray.length; i++){
	        returnArray[formArray[i]['name']] = formArray[i]['value'];
	    }
	    return returnArray;
	}

	function sendAjax(data, callback, async) {
	    $.ajax({
	        url: '/action.php',
	        method: 'POST',
	        dataType: 'json',
	        data: data,
	        cache: false,
	        async: true,
	        success: function(response) {
	            if(typeof callback === 'function') callback(response);
	        }
	    });
	}

});
