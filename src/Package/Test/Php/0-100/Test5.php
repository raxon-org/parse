<?php
$data = (object) [];
$data->set('framework.test', $this->framework());
$data->set('app', 1000);
$data->set(
    'time.duration',
    $this->value_minus(
        $data->get('app'),
        $this->value_plus(
            $this->value_multiply(
                $this->value_set(
                    $data->get('framework.test')::config('time.start'), 1000
                )
            ),
            'ms'
        )
    )
);