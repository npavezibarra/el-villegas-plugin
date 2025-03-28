<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultado del Quiz</title>
  <style>
    body {
      font-family: Georgia, serif;
      background-color: #f4f4f4;
      color: #222;
      margin: 0;
      padding: 0;
    }

    .email-container {
      max-width: 500px;
      margin: 40px auto;
      background: #ffffff;
      padding: 30px;
      border-radius: 8px;
    }

    .header {
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .sub-header {
      text-align: center;
      font-size: 16px;
      margin-bottom: 30px;
      color: #666;
    }

    .content {
      text-align: center;
      margin-bottom: 30px;
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
      margin-top: 20px;
    }

    .content p {
      margin: 5px 0;
      font-size: 16px;
    }

    .quiz-title {
      font-size: 20px;
      font-weight: bold;
      margin: 10px 0 5px 0;
    }

    .quiz-subtitle {
      font-size: 16px;
      margin-bottom: 20px;
    }

    .progress-container {
      width: 70%;
      background-color: #eee;
      border-radius: 20px;
      overflow: hidden;
      margin: 0 auto 10px auto;
      height: 20px;
    }

    .progress-bar {
      height: 100%;
      background-color: #e88f8f;
      width: {{quiz_percentage}}%;
    }

    .progress-label {
      font-size: 22px;
      font-weight: bold;
      text-align: center;
      margin-top: 4px;
    }

    .next-steps {
      background-color: #f9f9f9;
      padding: 40px;
      border-radius: 8px;
      text-align: center;
      margin-top: 20px;
    }

    .next-steps h3 {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .next-steps p {
      font-size: 16px;
      margin-bottom: 10px;
      line-height: 1.6;
    }

    .cta-button {
      background-color: #000;
      color: #fff !important;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
      display: inline-block;
      margin-top: 15px;
      font-size: 16px;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #999;
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">UNIVERSIDAD VILLEGAS</div>
    <div class="sub-header">e l v i l l e g a s . c l</div>

    <div class="content next-steps">
      <p>Estimado <strong>{{user_name}}</strong>, has finalizado:</p>

      <div class="quiz-title">{{quiz_name}}</div>
      <div class="quiz-subtitle">y has obtenido {{quiz_percentage}}% de respuestas correctas.</div>

      <div class="progress-container">
        <div class="progress-bar"></div>
      </div>
      <div class="progress-label">{{quiz_percentage}}%</div>
    </div>

    <div class="next-steps">
      <h3>¿Qué pasos seguir ahora?</h3>
      <p>Ahora puedes proceder a completar todas las lecciones incluidas en este curso sobre <strong>La República Romana</strong>.</p>
      <p>Una vez finalizadas, estarás listo para realizar la evaluación final, que reflejará el progreso alcanzado durante el curso.</p>
      <p>Recuerda que puedes avanzar a tu propio ritmo: algunos estudiantes lo completan en un día, mientras que otros pueden tardar más.</p>

      <a href="{{course_url}}" class="cta-button">Ir al Curso</a>
    </div>

    <div class="footer">
      Este mensaje fue generado automáticamente por el sistema de cursos de elvillegas.cl
    </div>
  </div>
</body>
</html>
