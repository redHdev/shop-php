$(document).ready(function() {
    $("#signupForm").on("submit", function(e) {
        e.preventDefault(); // Prevent default form submission
        
        var userName = $('#yourName').val();
        var userEmail = $('#yourEmail').val();
        var userNickName = $('#yourUsername').val();
        var acceptTerms = $('#acceptTerms')[0].checked;
        var password = $('#yourPassword').val();
        var passwordConfirm = $('#passwordConfirm').val();

        if(!userName || !userNickName || !acceptTerms || !userEmail){
            console.log("field is not invalid");
            return;
        }

        var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if(!regex.test(userEmail)){
            console.log("Email is not validated");
            return;
        }

        if (password !== passwordConfirm) {
            // If passwords do not match, prevent form submission and show error
            e.preventDefault();
            $('#passwordConfirm').addClass('is-invalid');
            return;
        } else {
            $('#passwordConfirm').removeClass('is-invalid');
        }

        $.ajax({
            url: '/shop-php/signup',
            type: 'POST',
            data: $(this).serialize(), // Serialize form data
            dataType: 'json',
            success: function(response) {
                if (response.status == true) {
                    window.location.href = 'http://localhost/shop-php/verify';
                    alert("Successfully signed up! " + response.message);
                    // You can also redirect the user to another page or update the UI accordingly
                } else {
                    alert("Sign-up failed: " + response.message);
                }
            }
        });
    });

});