document.addEventListener('DOMContentLoaded', function() {
    // Get the form element
    var form = document.querySelector('.vari-contact-form');
    
    // Ensure the form exists
    if (form) {
        form.addEventListener('submit', function(event) {
            // Prevent the default form submission
            event.preventDefault();
            
            // Create an object to store form data
            var formData = {};
            
            // Get all input fields within the form
            var inputs = form.querySelectorAll('input, select, textarea');
            
            // Loop through each input field and add it to the formData object
            inputs.forEach(function(input) {
                console.log(input);
                // if (input.name) {
                //     formData[input.name] = input.value;
                // }
            });
            
            // Push the formData object to the data layer
            // window.dataLayer = window.dataLayer || [];
            // window.dataLayer.push({
            //     'event': 'contactFormSubmit',
            //     'formData': formData
            // });

            // Optionally, submit the form after pushing data to data layer
            // form.submit();

            // Redirect after form submission
            // if (form.dataset.redirect) {
            //     window.location.href = form.dataset.redirect;
            // }
        });
    }
});
