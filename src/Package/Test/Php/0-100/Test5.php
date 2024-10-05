<?php
$data = (object) [];
$this->value_set(
    $this->value_multiply(
        $data->get('framework.test')->config('time.start'),
        1000
    )
);