$(document).ready(function() {
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        // Валидация на клиентской стороне
        let valid = true;
        $('#message').empty();

        if ($('#companyName').val() === '') {
            valid = false;
            $('#message').append('<p>Название компании обязательно.</p>');
        }
        if (!/^\d{10}$|^\d{12}$/.test($('#inn').val())) {
            valid = false;
            $('#message').append('<p>ИНН должен быть 10 или 12 цифр.</p>');
        }
        if (!/^11\d{10}$/.test($('#phone').val())) {
            valid = false;
            $('#message').append('<p>Телефон должен начинаться с 11 и содержать 11 цифр.</p>');
        }
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test($('#email').val())) {
            valid = false;
            $('#message').append('<p>Неправильный формат email.</p>');
        }

        if (valid) {
            $.ajax({
                type: 'POST',
                url: 'process.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#message').append('<p>' + response.message + '</p>');
                        loadRegisteredEntities();
                    } else {
                        response.errors.forEach(function(error) {
                            $('#message').append('<p>' + error + '</p>');
                        });
                    }
                }
            });
        }
    });

    function loadRegisteredEntities() {
        $.ajax({
            url: 'process.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '<table><tr><th>Название компании</th><th>ИНН</th><th>Телефон</th><th>Email</th></tr>';
                data.forEach(function(entity) {
                    html += '<tr><td>' + entity.company_name + '</td><td>' + entity.inn + '</td><td>' + entity.phone + '</td><td>' + entity.email + '</td></tr>';
                });
                html += '</table>';
                $('#registeredEntities').html(html);
            }
        });
    }

    loadRegisteredEntities(); // Загрузить зарегистрированные юридические лица при загрузке страницы
});
