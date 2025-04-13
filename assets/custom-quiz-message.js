document.addEventListener("DOMContentLoaded", function() {
    // Selecciona el botón "Start Quiz"
    var startQuizButton = document.querySelector('.wpProQuiz_button[name="startQuiz"]');

    if (startQuizButton && typeof quizData !== 'undefined') {
        // Crea el contenedor del mensaje
        var messageDiv = document.createElement('div');
        messageDiv.className = 'custom-quiz-message';
        messageDiv.id = 'quiz-start-message';  // Asignar ID al contenedor del mensaje
        messageDiv.innerHTML = `
            <a id="back-to-course-link" href="${document.referrer}" class="back-to-course-link">Volver al curso</a>
            <p id="quiz-start-paragraph">Estás a punto de iniciar la evaluación del curso <strong>${quizData.courseName}</strong>, que consta de 30 preguntas. Esta evaluación es contrarreloj, tendrás 45 segundos para responder cada pregunta. Al finalizar, te entregaremos tu resultado, y podrás decidir si quieres publicarlo en el ranking público o mantenerlo privado. Recuerda que solo puedes rendir esta evaluación 3 veces, por lo que te sugerimos prestar mucha atención y responder conscientemente. Cuando estés listo, haz clic en <strong>Comenzar</strong>. ¡Éxito!</p>
        `;

        // Inserta el mensaje antes del botón "Start Quiz"
        startQuizButton.parentNode.insertBefore(messageDiv, startQuizButton);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    function ajustarFlotadoBotonesQuiz() {
        const botones = document.querySelectorAll('.wpProQuiz_QuestionButton');

        botones.forEach(btn => {
            const nombre = btn.getAttribute('name');
            if (nombre === 'back') {
                btn.style.float = 'left';
                btn.style.marginLeft = '0';
                btn.style.marginRight = '10px';
            } else if (nombre === 'next' || nombre === 'check') {
                btn.style.float = 'right';
                btn.style.marginRight = '0';
                btn.style.marginLeft = '10px';
            }
        });
    }

    // Ejecutar al cargar
    ajustarFlotadoBotonesQuiz();

    // Observar si aparecen nuevos botones
    const observer = new MutationObserver(() => {
        ajustarFlotadoBotonesQuiz();
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
