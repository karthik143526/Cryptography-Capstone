<?php
// Note: In production, store this securely outside web root or in env vars
define('ENCRYPTION_KEY', 'secure_file_storage_secret_key_123!'); 
define('ENCRYPTION_CIPHER', 'AES-256-CBC');

function encryptFile($filePath, $destinationPath) {
    $key = hash('sha256', ENCRYPTION_KEY, true);
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_CIPHER);
    $iv = openssl_random_pseudo_bytes($ivLength);
    
    $fileData = file_get_contents($filePath);
    $encryptedData = openssl_encrypt($fileData, ENCRYPTION_CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    $finalData = $iv . $encryptedData;
    return file_put_contents($destinationPath, $finalData) !== false;
}

function decryptFileContent($filePath) {
    $key = hash('sha256', ENCRYPTION_KEY, true);
    $fileData = file_get_contents($filePath);
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_CIPHER);
    $iv = substr($fileData, 0, $ivLength);
    $encryptedData = substr($fileData, $ivLength);
    return openssl_decrypt($encryptedData, ENCRYPTION_CIPHER, $key, OPENSSL_RAW_DATA, $iv);
}
?>
