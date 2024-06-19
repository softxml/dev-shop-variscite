(function($){
    $(document).ready(function(){
        $('.vari-contact-form input').on('focusout', function() {
            var inputData = $(this).val();
            if(inputData.length) {
                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: myAjax.ajaxurl,
                    data: {
                        action: "o_sendPhoneCode",
                        input_data: inputData
                    },
                    success: function (response) {
                        if (response.data.errors) {
                            alert("הייתה בעיה בשליחת הסיסמה");

                        }
                        if (response.success) {
                            console.log('success');
                        }

                    }
                })
            }
        })


    })
})(jQuery); 