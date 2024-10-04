<?php
$data = (object) [];
$data->set('time.duration',
    $this->value_plus(
        $this->value_multiply(
            $this->value_minus(
                (
                    (
                        $data->get('time.instance'), $data->get('time.start')
                    )
                ), 1000
            )
        ),
        ' ms'
    )
);