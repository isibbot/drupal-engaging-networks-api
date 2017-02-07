(function($) {
  Drupal.behaviors.tcs_en_api = {
    attach: function(context, settings) {
      $(document).ready(function() {
        $("#Email_Address").blur(function() {
          // The value from the email address field
          var value = $('#Email_Address').val();
          $.ajax({
            type: 'GET',
            dataType: 'json',
            url: "https://secondary.uat.childrenssociety.org.uk/page/json/" + value, // Update URL
            success: function(data) {
              console.log(data);
              if (data.new_supporter) {
                // If new_supporter uncheck the radio buttons
                $('#positive_opt_in_emailDiv input, #positive_opt_in_phoneDiv input, #positive_opt_in_smsDiv input').attr('checked', false);
              }
              if (data.opted_in) {
                // If opted_in uncheck the radio buttons
                $.each(data, function(index, value) {
                  switch (index) {
                    case 'email':
                      if (value) {
                        $('#positive_opt_in_email1').attr('checked', true);
                      } else {
                        $('#positive_opt_in_email0').attr('checked', true);
                      }
                      break;
                    case 'telephone':
                      if (value) {
                        $('#positive_opt_in_phone1').attr('checked', true);
                      } else {
                        $('#positive_opt_in_phone0').attr('checked', true);
                      }
                      break;
                    case 'sms':
                      if (value) {
                        $('#positive_opt_in_sms1').attr('checked', true);
                      } else {
                        $('#positive_opt_in_sms0').attr('checked', true);
                      }
                      break;
                  }
                });
              }
              else if (data.opted_out) {
                // If opted_out uncheck the radio buttons
                $('#positive_opt_in_emailDiv input, #positive_opt_in_phoneDiv input, #positive_opt_in_smsDiv input').attr('checked', false);
              }
            },
            error: function(data) {
              console.log(data);
            }
          });
        });
      });
    }
  };
}(jQuery));
