<script>
    jQuery(document).ready(function(){

        $("{{ $validator['selector'] }}").each(function() {
            $(this).validate({
                errorElement: 'span',
                errorClass: 'invalid-feedback',

                errorPlacement: function (error, element) {
                    if (element.parent('.input-group').length ||
                        element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                        error.insertAfter(element.parent());
                        // else just place the validation message immediately after the input
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid'); // add the Bootstrap error class to the control group
                },

                @if (isset($validator['ignore']) && is_string($validator['ignore']))
                    ignore: "{{ $validator['ignore'] }}",
                @endif


                unhighlight: function(element) {
                    $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
                },

                success: function (element) {
                    $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid'); // remove the Boostrap error class from the control group
                },

                focusInvalid: false, // do not focus the last invalid input
                @if (config('packages.js-validation.js-validation.focus_on_error'))
                    invalidHandler: function (form, validator) {

                        if (!validator.numberOfInvalids())
                            return;

                        $('html, body').animate({
                            scrollTop: $(validator.errorList[0].element).offset().top
                        }, {{  config('packages.js-validation.js-validation.duration_animate') }});
                        $(validator.errorList[0].element).focus();

                    },
                @endif

                rules: {!! json_encode($validator['rules']) !!}
            });
        });
    });
</script>
