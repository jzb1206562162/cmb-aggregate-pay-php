<?php

namespace Cmb\AggregatePay\Crypto;

use Guomi\Sm2;
use Guomi\Sm4;
use Cmb\AggregatePay\Exceptions\CmbPayException;

class CmbSmCrypto
{
    private Sm2 $sm2;
    private Sm4 $sm4;
    private string $cmbPublicKey;

    public function __construct(string $privateKeyHex, string $cmbPublicKeyBase64)
    {
        // SM2 初始化（招行规范：ID=1234567812345678）
        $this->sm2 = new Sm2();
        $this->sm2->setPrivateKey(hex2bin($privateKeyHex));
        $this->sm2->setUserId('1234567812345678');
        
        $this->cmbPublicKey = base64_decode($cmbPublicKeyBase64);
        $this->sm2->setPublicKey($this->cmbPublicKey);
        
        // SM4 初始化
        $this->sm4 = new Sm4();
    }

    /**
     * 生成签名（SM2withSM3）
     */
    public function sign(array $data): string
    {
        unset($data['sign']);
        ksort($data);
        
        $signStr = '';
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null) {
                $signStr .= $k . '=' . $v . '&';
            }
        }
        $signStr = rtrim($signStr, '&');
        
        return base64_encode($this->sm2->sign($signStr));
    }

    /**
     * 验证签名（招行返回）
     */
    public function verify(array $data, string $signatureBase64): bool
    {
        $sign = base64_decode($signatureBase64);
        unset($data['sign']);
        ksort($data);
        
        $signStr = '';
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null) {
                $signStr .= $k . '=' . urldecode($v) . '&';
            }
        }
        $signStr = rtrim($signStr, '&');
        
        return $this->sm2->verify($signStr, $sign);
    }

    /**
     * 敏感字段加密（SM4 + SM2）
     * @return array{value: string, key: string}
     */
    public function encryptSensitive(array $fieldData): array
    {
        $symKey = random_bytes(16);
        $iv = random_bytes(16);
        $plainText = json_encode($fieldData, JSON_UNESCAPED_UNICODE);
        
        // SM4-CBC 加密
        $cipherText = $this->sm4->encrypt($plainText, $symKey, $iv);
        
        return [
            'value' => base64_encode($iv . $cipherText),
            'key'   => base64_encode($this->sm2->encrypt($symKey))
        ];
    }
}