jQuery(document).ready(function ($) {
  
    //var form = $('form');
    // $("#terms").attr("checked", false);

  braintree.client.create({
    authorization: braintree_vars.clientToken
    }, function(err, clientInstance) {
      if (err) {
        console.error(err);
        return;
      }

    braintree.hostedFields.create({
      client: clientInstance,
      styles: {
         
        input: {
          // change input styles to match
          // bootstrap styles
          'font-size': '1rem',
          // color: '#495057'      
          },

        '.invalid': {
            // you can also use the object syntax alongside
            // the class name syntax
            color: "red"
          },

        '.valid': {
          // you can also use the object syntax alongside
          // the class name syntax
          "color": "green"
        }
      },

      fields: {
      //   cardholderName: {
      //     selector: '#cc-name',
      //     placeholder: 'Name as it appears on your card'
      //   },
        number: {
          container: '#uwa-card-number',
          placeholder: '1111 1111 1111 1111'
        },
        cvv: {
          container: '#uwa-card-cvc',
          placeholder: '123'
        },
        expirationDate: {
          container: '#uwa-card-expiry',
          placeholder: 'MM / YY'
        }
      }
    }, function(err, hostedFieldsInstance) {
      if (err) {
        console.error(err);
        return;
      }
      console.log(hostedFieldsInstance)
      function createInputChangeEventListener(element) {
        return function () {
          validateInput(element);
        }
      }

      function setValidityClasses(element, validity) {
        if (validity) {
          element.removeClass('is-invalid');
          element.addClass('is-valid');  
        } else {
          element.addClass('is-invalid');
          element.removeClass('is-valid');
        }
      }
      
      function validateInput(element) {
        // very basic validation, if the
        // fields are empty, mark them
        // as invalid, if not, mark them
        // as valid

        if (!element.val().trim()) {
          setValidityClasses(element, false);
          return false;
        }

        setValidityClasses(element, true);
        return true;
      }
      
      function validateEmail() {
        var baseValidity = validateInput(email);
        
        if (!baseValidity) {  
          return false;
        }

        if (email.val().indexOf('@') === -1) {
          setValidityClasses(email, false);
          return false;
        }
        
          setValidityClasses(email, true);
          return true;
      }

      //var ccName = $('#cc-name');
      var email = $('#reg_email');

     /* ccName.on('change', function () {
        validateInput(ccName);
      });*/

      email.on('change', validateEmail);

      hostedFieldsInstance.on('validityChange', function(event) {

        var field = event.fields[event.emittedBy];

        // Remove any previously applied error or warning classes
        $(field.container).removeClass('is-valid');
        $(field.container).removeClass('is-invalid');

        if (field.isValid) {
          $(field.container).addClass('is-valid');
        } else if (field.isPotentiallyValid) {

          // skip adding classes if the field is
          // not valid, but is potentially valid
        } else {
          $(field.container).addClass('is-invalid');
        }

      });

      hostedFieldsInstance.on('cardTypeChange', function(event) {
        var cardBrand = $('#card-brand');
        var cvvLabel = $('[for="uwa-card-cvc"]');

        if (event.cards.length === 1) {
          var card = event.cards[0];

          // change pay button to specify the type of card
          // being used
          cardBrand.text(card.niceType);
          // update the security code label
          cvvLabel.text(card.code.name);
        } else {
          // reset to defaults
          cardBrand.text('Card');
          cvvLabel.text('CVV');
        }

      });

      $("#terms").click(function(event) {
        console.log("l1");

        var formIsInvalid = false;
        var state = hostedFieldsInstance.getState();
        var errorElement = document.getElementById('uwa-card-errors');
        errorElement.textContent = "";
        // perform validations on the non-Hosted Fields
        // inputs
        if (!validateEmail()) {
          formIsInvalid = true;
        }

        // Loop through the Hosted Fields and check
        // for validity, apply the is-invalid class
        // to the field container if invalid
        Object.keys(state.fields).forEach(function(field) {
          if (!state.fields[field].isValid) {
            $(state.fields[field].container).addClass('is-invalid');
            formIsInvalid = true;
          }
        });

        if (formIsInvalid) {
          // skip tokenization request if any fields are invalid
          event.preventDefault();
          errorElement.textContent = "braintree_vars.invalid_card_details_msg";
          return;
        }

        hostedFieldsInstance.tokenize(function(err, payload) {
          console.log(err)
          console.log(payload)
          console.log("l2")

          if (err) {
            console.error(err);
            event.preventDefault();
            errorElement.textContent = "braintree_vars.invalid_card_details_msg";
            return;
          }

          var chkvalue = $(this).attr("checked");
          console.log(chkvalue)

          // if(chkvalue == "checked"){         
          //   $("#terms").attr("checked", false);
          // }

          var chkvalue = $(this).attr("checked");
          console.log("l1"+chkvalue);
          braintreeSourceHandler(payload.nonce);

          // This is where you would submit payload.nonce to your server
          // $('.toast').toast('show');

          // you can either send the form values with the payment
          // method nonce via an ajax request to your server,
          // or add the payment method nonce to a hidden inpiut
          // on your form and submit the form programatically
          // $('#payment-method-nonce').val(payload.nonce);
          // form.submit()
        });
      });
    });
  });

});

function braintreeSourceHandler(source) {
    // Insert the source ID into the form so it gets submitted to the server
    jQuery("#uwa_braintree_k_id").val(source);
      // jQuery("#terms").attr("checked", "checked");
      
}