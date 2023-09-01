$(document).ready(function() {
    console.log("verification-digit");

    // Focus on the first input when the document is ready
    $('#first').focus();

    $(".verification-digital").on('input', function(e) {
        var val = $(this).val();

        // Remove any non-digit characters
        val = val.replace(/[^0-9]/g, '');

        $(this).val(val);

        // If the value is more than one character, trim it.
        if (val.length > 1) {
            $(this).val(val.slice(0, 1));
        }

        // If there's a value, move to next input. If it's the last input, don't focus out.
        if (val.length === 1) {
            var next = $(this).closest('div.col-2').next('.col-2').find('.verification-digital');
            if (next.length) {
                next.focus();
            } else {
                $(this).blur();
            }
        }

         // Check if all fields are filled to focus the submit button
         if ($(".verification-digital").filter(function() { return $(this).val() == ""; }).length === 0) {
            $("#submitBtn").focus();
        }
    });

    // Handle backspace and delete keydown events
    $(".verification-digital").on('keydown', function(e) {
        if (e.key === 'Backspace' || e.key === 'Delete') {
            if ($(this).val() === '') {
                var prev = $(this).closest('div.col-2').prev('.col-2').find('.verification-digital');
                if (prev.length) {
                    prev.focus();
                }
            }
        } else if (e.key.length === 1 && !/^[0-9]$/.test(e.key)) {  // Check if it's a single character and not a digit
            e.preventDefault();  // If not a digit, prevent input
        }
    });

    $("#verificationForm").on("submit", function(e) {
        e.preventDefault(); // Prevent default form submission
        
        var first = $('#first').val();
        var second = $('#second').val();
        var third = $('#third').val();
        var fourth = $('#fourth').val();
        var fifth = $('#fifth').val();
        var sixth = $('#sixth').val();

        if(!first || !second || !third || !fourth || !fifth || !sixth){
            console.log("field is not invalid");
            return;
        }

        setLoader(true);

        $.ajax({
            url: '/shop-php/verification',
            type: 'POST',
            data: $(this).serialize(), // Serialize form data
            dataType: 'json',
            success: function(response) {
                setLoader(false);
                console.log(response);
                if (response.status == true) {
                    window.location.href = 'http://localhost/shop-php';
                    // You can also redirect the user to another page or update the UI accordingly
                } else {
                    alert("Sign-up failed: " + response.message);
                }
            }
        });
    });
});