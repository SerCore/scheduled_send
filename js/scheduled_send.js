$(document).ready(function() {
    $('#scheduled_send_time').on('change', function() {
        // Обработка выбора времени
        console.log('Время отправки выбрано: ' + $(this).val());
    });
});