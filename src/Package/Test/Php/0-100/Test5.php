<?php
$data = (object) [];
$a = (object) [];
$data->set('time.instance', $a->config('time.start'));
$data->set('time.duration',
    $this->value_minus(
        $data->get('time.instance'),
        $this->value_plus(
            $this->value_multiply(
                $this->value_set(
                    $data->get('time.start'), 1000
                )
            ),
            'ms'
        )
    )
);