<?php

class TranslationException extends Exception {}

class Translator {
    private static $apiKey = null;
    private static $jsonFiles = [
        'es' => __DIR__ . '/../i18n/es.json',
        'eu' => __DIR__ . '/../i18n/eu.json',
        'en' => __DIR__ . '/../i18n/en.json'
    ];

    /**
     * Inicializa el traductor con la API key
     */
    public static function init($apiKey) {
        if (empty($apiKey)) {
            throw new TranslationException("La API key no puede estar vacía");
        }
        self::$apiKey = $apiKey;
    }

    /**
     * Traduce texto usando la API de Google Translate
     */
    private static function translate($text, $targetLang, $sourceLang = 'es') {
        if (self::$apiKey === null) {
            throw new TranslationException("El traductor no ha sido inicializado. Llama a Translator::init() primero.");
        }

        if (empty($text)) {
            return '';
        }

        $url = "https://translation.googleapis.com/language/translate/v2";
        $postData = [
            'q' => $text,
            'source' => $sourceLang,
            'target' => $targetLang,
            'key' => self::$apiKey
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new TranslationException("Error de cURL: " . $error);
        }

        if ($httpCode !== 200) {
            throw new TranslationException("Error HTTP: " . $httpCode . ". Respuesta: " . $response);
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TranslationException("Error al decodificar JSON: " . json_last_error_msg());
        }

        if (!isset($result['data']['translations'][0]['translatedText'])) {
            throw new TranslationException("Formato de respuesta inválido: " . print_r($result, true));
        }

        return $result['data']['translations'][0]['translatedText'];
    }

    /**
     * Guarda las traducciones en los archivos JSON
     */
    private static function saveToJson($lang, $jsonKey, $text) {
        if (!isset(self::$jsonFiles[$lang])) {
            throw new TranslationException("Idioma no soportado: " . $lang);
        }

        $filePath = self::$jsonFiles[$lang];
        
        // Verificar si el directorio existe
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new TranslationException("No se pudo crear el directorio: " . $dir);
            }
        }

        // Verificar si el archivo existe y es escribible
        if (file_exists($filePath) && !is_writable($filePath)) {
            throw new TranslationException("El archivo no tiene permisos de escritura: " . $filePath);
        }

        // Leer o crear el contenido JSON
        if (file_exists($filePath)) {
            $jsonContent = json_decode(file_get_contents($filePath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new TranslationException("Error al leer el archivo JSON: " . json_last_error_msg());
            }
        } else {
            $jsonContent = [];
        }

        // Actualizar el contenido
        $jsonContent[$jsonKey] = $text;

        // Guardar el archivo
        if (file_put_contents($filePath, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            throw new TranslationException("Error al guardar el archivo: " . $filePath);
        }
    }

    /**
     * Traduce y guarda el texto en todos los idiomas
     */
    public static function translateAndSaveToJson($key, $text, $misionId) {
        try {
            // Validar parámetros
            if (empty($key) || empty($misionId)) {
                throw new TranslationException("La clave y el ID de misión son requeridos");
            }

            $jsonKey = "mission." . $misionId . "." . $key;

            // Guardar texto original en español
            self::saveToJson('es', $jsonKey, $text);

            // Traducir y guardar para otros idiomas
            foreach (array_keys(self::$jsonFiles) as $lang) {
                if ($lang === 'es') continue;

                try {
                    $translatedText = self::translate($text, $lang);
                    self::saveToJson($lang, $jsonKey, $translatedText);
                } catch (Exception $e) {
                    error_log("Error al traducir al " . $lang . ": " . $e->getMessage());
                    // Continuar con otros idiomas si uno falla
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error en traducción: " . $e->getMessage());
            throw new TranslationException("Error en el proceso de traducción: " . $e->getMessage());
        }
    }
}