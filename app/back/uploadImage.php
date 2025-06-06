<?php
require 'conection.php';

// Funciones específicas
function uploadUserPhoto(PDO $dbConection, $userId, $file)
{
    return uploadImage($dbConection, $file, 'src/uploads/users/', 'profile_pic', 'users', 'user_id', $userId);
}

function uploadCampaignPhoto(PDO $dbConection, $campaignId, $file)
{
    return uploadImage($dbConection, $file, 'src/uploads/campaigns/', 'campaign_pic', 'campaigns', 'campaign_id', $campaignId);
}

function uploadCharacterPhoto(PDO $dbConection, $characterId, $file)
{
    return uploadImage($dbConection, $file, 'src/uploads/characters/', 'character_pic', 'characters', 'character_id', $characterId);
}

// Función general
function uploadImage(PDO $dbConection, $file, $relativeFolder, $dbField, $table, $idField, $idValue)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }

    // Validamos el tipo de archivo para que no nos puedan subir cualquier cosa
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    // Y también validamos el tamaño del archivo para que no nos puedan subir archivos demasiado grandes
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large'];
    }

    // Creamos una carpeta si no existe, por si acaso
    $absoluteFolder = __DIR__ . '/../' . $relativeFolder;
    if (!is_dir($absoluteFolder)) {
        mkdir($absoluteFolder, 0777, true);
    }

    // Aquí comprobamos si ya había una imagen antigua
    $selectImg = $dbConection->prepare("SELECT $dbField FROM $table WHERE $idField = :id");
    $selectImg->execute([':id' => $idValue]);
    $oldPath = $selectImg->fetchColumn();

    // Borramos la imagen antigua si existe, para no llenar el servidor de imágenes antiguas
    if ($oldPath) {
        $absoluteOldPath = __DIR__ . '/../' . $oldPath;
        if (file_exists($absoluteOldPath)) {
            unlink($absoluteOldPath);
        }
    }


    // Después guardamos la nueva
    $imgExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); // Obtenemos la extensión del archivo
    $newName = uniqid('', true) . '.' . $imgExtension; // uniqid() genera un identificador único basado en el tiempo actual
    $relativePath = $relativeFolder . $newName;
    $destination = __DIR__ . '/../' . $relativePath; // Esto separa la ruta relativa (para guardar en la base de datos) de la ruta absoluta (para guardar el archivo).

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Error moving uploaded file'];
    }

    // Actualizar la base de datos
    $update = "UPDATE $table SET $dbField = :photo WHERE $idField = :id";
    $update = $dbConection->prepare($update);
    $success = $update->execute([
        ':photo' => $relativePath,
        ':id' => $idValue
    ]);

    if (!$success) {
        return ['success' => false, 'message' => 'Database update failed'];
    }

    return ['success' => true, 'message' => 'File uploaded successfully', 'path' => $destination];
}

// Aquí usamos un controlador para detectar qué se está subiendo y llamar a la función adecuada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_user_photo'])) {
        $result = uploadUserPhoto($dbConection, $_POST['user_id'], $_FILES['user_photo']);
    } elseif (isset($_POST['upload_campaign_photo'])) {
        $result = uploadCampaignPhoto($dbConection, $_POST['campaign_id'], $_FILES['campaign_photo']);
    } elseif (isset($_POST['upload_character_photo'])) {
        $result = uploadCharacterPhoto($dbConection, $_POST['character_id'], $_FILES['character_photo']);
    } else {
        $result = ['success' => false, 'message' => 'No valid upload type detected'];
    }

    if ($result['success']) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        echo "Error: " . htmlspecialchars($result['message']);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
