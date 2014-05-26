$(document).ready(function () {
    $('form').on('submit',function () {
        var form = $(this);
        var button = $(':submit', form);

        button.button('loading');


        $.post(form.attr('action'), form.serialize(), function (response) {

            form.find('.error').remove();
            form.find('.has-error').removeClass('has-error');

            if (response.error && response.error.length) {
                // Display errors
                for (var name in response.form) {
                    if (response.form.hasOwnProperty(name)) {
                        form.find('[name=' + name + ']').focus().parent().addClass('has-error')
                            .find('input')
                            .before('<small class="error text-danger">' + response.form[name] + '</small>');
                    }
                }

                // Re-Enables the button
                button.button('reset');
            }
            else {
                // Success
                button.replaceWith('<div class="alert alert-success">' + response.confirm + '</div>');

                if (form.data('success')) {
                    setTimeout(function () {
                        window.location = form.data('success');
                    }, 4000);
                }


                //form.find('fieldset').attr('disabled','disabled');
            }
        }, 'json');

        return false;
    }).on('change', 'input', function () {

        // Clears the error status

        var group = $(this).parents('.form-group:first');

        group.find('.error').remove();
        group.removeClass('has-error');
    })
});