<?php
namespace Ludo\Encryption;

use RuntimeException;
use Exception;
use Ludo\Support\Facades\Config;

/**
 * Class Encrypter
 * @package Ludo\Encrypter
 */
class Encrypter
{
    /**
     * The encryption key
     *
     * @var string
     */
    private static $key;

    /**
     * The algorithm used for encryption
     *
     * @var string
     */
    private static $cipher;

    /**
     * Encrypter constructor.
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        $config = Config::get('app');
        if (self::supported($config['key'], $config['cipher'])) {
            self::$key = $config['key'];
            self::$cipher = $config['cipher'];
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * Encrypt the given value.
     *
     * @param $value
     * @return string
     * @throws RuntimeException
     * @throws Exception
     */
    public function encrypt($value)
    {
        if (function_exists('random_bytes')) {
            $iv = random_bytes(16);
        } else {
            $iv = openssl_random_pseudo_bytes(16);
        }
        $value = openssl_encrypt($value, self::$cipher, self::$key, 0, $iv);

        if (false === $value) {
            throw new RuntimeException('Could not encrypt the data.');
        }
        $iv = base64_encode($iv);
        $mac = $this->hash($iv, $value);

        $json = json_encode(compact('iv', 'value', 'mac'));

        return base64_encode($json);
    }

    /**
     * Decrypt the given value.
     *
     * @param $payload
     * @return string
     */
    public function decrypt($payload)
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);
        $value = openssl_decrypt($payload['value'], self::$cipher, self::$key, 0, $iv);
        if (false === $value) {
            throw new RuntimeException('Could not decrypt the data.');
        }
        return $value;
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param $key
     * @param $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
            ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Create a MAC for the given value.
     *
     * @param $iv
     * @param $value
     * @return string
     */
    private function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, self::$key);
    }

    /**
     * Get the JSON array from the given payload.
     * 
     * @param $payload
     * @return mixed
     */
    private function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        if (!is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac'])) {
            throw new RuntimeException('The payload is invalid.');
        }

        $mac = $this->hash($payload['iv'], $payload['value']);
        if (!hash_equals($payload['mac'], $mac)) {
            throw new RuntimeException('The mac is invalid.');
        }

        return $payload;
    }
}