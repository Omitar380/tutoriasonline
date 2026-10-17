<?php
session_start();
require 'conexcion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

$nombreUsuario = $_SESSION['nombre'] ?? 'estudiante';
$apiKey = getenv('GEMINI_API_KEY');
$geminiConfigured = is_string($apiKey) && trim($apiKey) !== '';

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        ['sender' => 'bot', 'text' => "¡Hola $nombreUsuario! Soy tu asistente de estudio con Gemini. Puedes preguntarme sobre cualquier tema."]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensajeUsuario = trim($_POST['mensaje']);
    $caracteresMensaje = [];
    $cantidadCaracteres = preg_match_all('/./us', $mensajeUsuario, $caracteresMensaje);

    if ($mensajeUsuario === '') {
        $_SESSION['chat_error'] = 'Escribe una pregunta antes de enviarla.';
    } elseif ($cantidadCaracteres === false) {
        $_SESSION['chat_error'] = 'El mensaje contiene caracteres no válidos. Corrígelo e inténtalo de nuevo.';
    } elseif ($cantidadCaracteres > 4000) {
        $_SESSION['chat_error'] = 'Tu mensaje es demasiado largo. El límite es de 4000 caracteres.';
    } elseif (!$geminiConfigured) {
        $_SESSION['chat_error'] = 'El asistente no está disponible. Configura la clave de API de Gemini en el servidor.';
    } else {
        unset($_SESSION['chat_error']);
        $_SESSION['chat_history'][] = ['sender' => 'user', 'text' => $mensajeUsuario];
        $mensajeAgregado = true;

        $contents = [];
        foreach (array_slice($_SESSION['chat_history'], -12) as $message) {
            if (!isset($message['sender'], $message['text']) || !in_array($message['sender'], ['user', 'bot'], true)) {
                continue;
            }

            if (!empty($mensajeAgregado) && isset($_SESSION['chat_error'])) {
                array_pop($_SESSION['chat_history']);
            }

            $contents[] = [
                'role' => $message['sender'] === 'bot' ? 'model' : 'user',
                'parts' => [['text' => $message['text']]],
            ];
        }

        $payload = json_encode([
            'system_instruction' => [
                'parts' => [[
                    'text' => 'Eres TutorBot, un asistente educativo amable que responde en español. Contesta preguntas abiertas con explicaciones claras y útiles, adapta el nivel al estudiante y muestra los pasos cuando ayude. Si no sabes algo, dilo con honestidad. No afirmes haber visto archivos que no se han enviado. No inventes nombres, calificaciones ni disponibilidad de tutores; para encontrar tutores, indica que deben usar la sección Buscar tutores.',
                ]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($payload === false) {
            error_log('No se pudo preparar la solicitud de Gemini.');
            $_SESSION['chat_error'] = 'No se pudo preparar tu mensaje. Inténtalo de nuevo.';
        } else {
            $curl = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
            if ($curl === false) {
                error_log('No se pudo inicializar cURL para Gemini.');
                $_SESSION['chat_error'] = 'No se pudo conectar con el asistente. Inténtalo de nuevo más tarde.';
            } else {
                curl_setopt_array($curl, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'x-goog-api-key: ' . trim($apiKey),
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 45,
                ]);

                $responseBody = curl_exec($curl);
                $curlError = curl_error($curl);
                $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($responseBody === false) {
                    error_log('Error de transporte al llamar a Gemini: ' . $curlError);
                    $_SESSION['chat_error'] = 'No se pudo conectar con Gemini. Revisa tu conexión a internet e inténtalo otra vez.';
                } else {
                    $responseData = json_decode($responseBody, true);
                    $botResponse = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    if ($httpStatus < 200 || $httpStatus >= 300) {
                        $providerMessage = $responseData['error']['message'] ?? 'Respuesta HTTP ' . $httpStatus;
                        error_log('Gemini devolvió un error: ' . $providerMessage);
                        $_SESSION['chat_error'] = $httpStatus === 429
                            ? 'Gemini alcanzó el límite de solicitudes. Espera un momento e inténtalo otra vez.'
                            : 'Gemini no pudo responder. Revisa la configuración de la API e inténtalo de nuevo.';
                    } elseif (!is_string($botResponse) || trim($botResponse) === '') {
                        error_log('Gemini devolvió una respuesta vacía o con formato inesperado.');
                        $_SESSION['chat_error'] = 'Gemini no devolvió una respuesta. Inténtalo de nuevo.';
                    } else {
                        $_SESSION['chat_history'][] = ['sender' => 'bot', 'text' => trim($botResponse)];
                    }
                }
            }
        }
    }

    if (count($_SESSION['chat_history']) > 24) {
        $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -24);
    }

    header('Location: asistente_ia.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorBot IA - TutoresOnLine</title>
    <link rel="stylesheet" href="CSS/estilos.css?v=<?php echo filemtime(__DIR__ . '/CSS/estilos.css'); ?>">
    <style>
        .layout { display: flex; min-height: 100vh; background: #f4f6f9; }
        .sidebar { width: 240px; background: #fff; border-right: 1px solid #e0e0e0; padding: 20px; }
        .chat-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 30px; color: #26354a; font-size: 16px; font-weight: 700; text-decoration: none; }
        .chat-brand > span > span { color: #1a73e8; }
        .chat-brand .brand-logo-image { width: 34px; height: 34px; }
        .sidebar nav a { display: block; padding: 12px 15px; color: #555; text-decoration: none; border-radius: 6px; margin-bottom: 8px; font-weight: 500; }
        .sidebar nav a.active, .sidebar nav a:hover { background-color: #e8f0fe; color: #1a73e8; }
        .content { flex: 1; padding: 20px; max-width: 500px; margin: 0 auto; display: flex; flex-direction: column; }
        
        .chat-container { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); display: flex; flex-direction: column; height: 80vh; }
        .chat-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; }
        .chat-header h3 { color: #202124; font-size: 16px; margin: 0; }
        
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
        .message { max-width: 80%; padding: 12px 16px; border-radius: 12px; font-size: 13px; line-height: 1.4; }
        .message.bot { background: #f1f3f4; color: #202124; align-self: flex-start; border-bottom-left-radius: 2px; }
        .message.user { background: #1a73e8; color: #ffffff; align-self: flex-end; border-bottom-right-radius: 2px; }
        
        .chat-footer { padding: 15px; border-top: 1px solid #eee; }
        .input-box { display: flex; gap: 10px; align-items: center; }
        .input-box input[type="text"] { flex: 1; padding: 10px 14px; border: 1px solid #ccc; border-radius: 20px; font-size: 13px; outline: none; }
        .input-box button { background: #1a73e8; color: white; border: none; padding: 10px 18px; border-radius: 20px; font-weight: bold; cursor: pointer; }
        .input-box button:hover { background: #1557b0; }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <a class="chat-brand" href="dashboard.php"><img class="brand-logo-image" src="assets/logo-marca.png" alt=""><span>Tutores<span>OnLine</span></span></a>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="buscar.php">Buscar Tutores</a>
                <a href="mis_sesiones.php">Mis Sesiones</a>
                <a href="asistente_ia.php" class="active">Asistente IA</a>
                <a href="perfil.php">Mi Perfil y Archivos</a>
                <a href="logout.php" style="color: #d93025; margin-top: 40px;">Cerrar Sesión</a>
            </nav>
        </aside>

        <main class="content">
            <div class="chat-container">
                <div class="chat-header">
                    <h3>🤖 Tutor Bot IA</h3>
                    <span style="font-size: 12px; color: <?php echo $geminiConfigured ? '#34a853' : '#b06000'; ?>; font-weight: bold;">
                        <?php echo $geminiConfigured ? 'Gemini listo' : 'Falta configurar Gemini'; ?>
                    </span>
                </div>

                <div class="chat-messages">
                    <?php if (isset($_SESSION['chat_error'])): ?>
                        <div class="message bot" role="alert"><?php echo htmlspecialchars($_SESSION['chat_error'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php unset($_SESSION['chat_error']); ?>
                    <?php endif; ?>
                    <?php foreach ($_SESSION['chat_history'] as $msg): ?>
                        <div class="message <?php echo $msg['sender'] === 'user' ? 'user' : 'bot'; ?>">
                            <?php echo nl2br(htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chat-footer">
                    <form method="POST" action="asistente_ia.php" class="input-box">
                        <input type="text" name="mensaje" placeholder="Escribe tu pregunta..." maxlength="4000" required autocomplete="off">
                        <button type="submit">Enviar</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>