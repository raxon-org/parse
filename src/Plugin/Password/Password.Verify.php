<?php
namespace Plugin;

trait Password_Verify {


    protected function password_verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}