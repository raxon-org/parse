<?php
namespace Plugin;

use Exception;
use ErrorException;

trait Password_Hash {


    protected function password_hash(string $password, int $cost=13, array $options=null): array
    {
        $result = '';
        if(is_int($cost)){
            try {
                $result = password_hash($password, PASSWORD_BCRYPT, [
                    'cost' => $cost
                ]);
            } catch (Exception | ErrorException $exception){
                return $exception;
            }
        } else {
            if(is_string($cost)){
                $algorithm = constant($cost);
                if(is_array($options)){
                    $result = password_hash($password, $algorithm, $options);
                }
                elseif(is_int($options)){
                    $result = password_hash($password, $algorithm, [
                        'cost' => $options
                    ]);
                }
                else {
                    $result = password_hash($password, $algorithm);
                }
            }
        }
        return $result;
    }
}