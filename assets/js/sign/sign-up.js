$(document).ready(function() {
    $("#signupForm").on("submit", function(e) {
        e.preventDefault(); // Prevent default form submission
        
        var userName = $('#yourName').val();
        var userEmail = $('#yourEmail').val();
        var userNickName = $('#yourUsername').val();
        var acceptTerms = $('#acceptTerms').val();
        var password = $('#yourPassword').val();
        var passwordConfirm = $('#passwordConfirm').val();

        console.log(acceptTerms);
        
        // if (password !== passwordConfirm) {
        //     // If passwords do not match, prevent form submission and show error
        //     e.preventDefault();
        //     $('#passwordConfirm').addClass('is-invalid');
        // } else {
        //     $('#passwordConfirm').removeClass('is-invalid');
        // }



        // $.ajax({
        //     url: '/shop-php/signup',
        //     type: 'POST',
        //     data: $(this).serialize(), // Serialize form data
        //     dataType: 'json',
        //     success: function(response) {
        //         if (response.status == "success") {
        //             alert("Successfully signed up! " + response.message);
        //             // You can also redirect the user to another page or update the UI accordingly
        //         } else {
        //             alert("Sign-up failed: " + response.message);
        //         }
        //     },
        //     error: function(xhr, status, error) {
        //         alert("An error occurred: " + error);
        //     }
        // });
    });

});