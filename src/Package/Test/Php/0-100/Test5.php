<?php
$data = (object) [];
$data->set(
    'time.duration',
    $this->value_plus(
        $this->value_minus(
            $data->get('app'),
            $this->value_set(
                $this->value_multiply(
                    $data->get('framework.test')->config('time.start'),
                    1000
                )
            )
        )
    ),
    'ms'
);