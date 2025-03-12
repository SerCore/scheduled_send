$(document).ready(function() {
    $('#scheduled_send_time').on('change', function() {
        const selectedTime = new Date($(this).val());
        const currentTime = new Date();

        if (selectedTime <= currentTime) {
            alert('Пожалуйста, выберите время в будущем.');
            $(this).val(''); // Сброс выбора
        } else {
            console.log('Время отправки выбрано: ' + $(this).val());
        }
    });

    // Отмена запланированного письма
    $('.cancel-scheduled-email').on('click', function() {
        const emailId = $(this).data('email-id');
        if (confirm('Вы уверены, что хотите отменить это письмо?')) {
            $.post('./?_task=mail&_action=plugin.scheduled_send_cancel', { email_id: emailId }, function(response) {
                location.reload(); // Обновляем страницу
            });
        }
    });
});