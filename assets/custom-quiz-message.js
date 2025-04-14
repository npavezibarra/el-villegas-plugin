document.addEventListener("DOMContentLoaded", function () {
    var startQuizButton = document.querySelector('.wpProQuiz_button[name="startQuiz"]');

    if (startQuizButton && typeof quizData !== 'undefined') {
        // Crear contenedor
        var messageDiv = document.createElement('div');
        messageDiv.className = 'custom-quiz-message';
        messageDiv.id = 'quiz-start-message';

        // Texto condicional según tipo de prueba
        var messageContent = '';
        if (quizData.type === 'first') {
            messageContent = `
                <a id="back-to-course-link" href="${document.referrer}" class="back-to-course-link">Volver al curso</a>
                <p id="quiz-start-paragraph">
                    Estás a punto de realizar la <strong>Prueba Inicial</strong> del curso <strong>${quizData.courseName}</strong>. 
                    Esta evaluación tiene como objetivo medir tus conocimientos antes de comenzar. Consta de 30 preguntas contrarreloj, 
                    con 45 segundos para cada una. Recuerda que solo puedes rendirla 3 veces. 
                    <br><br>
                    Una vez finalices todas las lecciones del curso, podrás acceder a la Prueba Final para comparar tu progreso. 
                    ¡Te deseamos lo mejor!
                </p>
            `;
        } else if (quizData.type === 'final') {
            messageContent = `
                <a id="back-to-course-link" href="${document.referrer}" class="back-to-course-link">Volver al curso</a>
                <p id="quiz-start-paragraph">
                    Estás a punto de rendir la <strong>Prueba Final</strong> del curso <strong>${quizData.courseName}</strong>. 
                    Esta evaluación final te permitirá conocer cuánto has avanzado desde que comenzaste. 
                    Al completarla, recibirás una tabla comparativa entre esta prueba y la inicial, para que puedas visualizar tu progreso.
                    <br><br>
                    Consta de 30 preguntas contrarreloj, con un límite de 45 segundos por pregunta. 
                    Tienes un máximo de 3 intentos. ¡Mucho éxito!
                </p>
            `;
        }

        // Agregar contenido e insertar
        messageDiv.innerHTML = messageContent;
        startQuizButton.parentNode.insertBefore(messageDiv, startQuizButton);
    }
});
