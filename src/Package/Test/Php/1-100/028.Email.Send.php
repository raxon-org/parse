<?php
/**
 * @author          Remco van der Velde
 * @since           2020-10-27
 * @version         1.0
 * @changeLog
 *     -    all
 */

use Raxon\App;
use Raxon\Config;
use Raxon\Exception\LocateException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Handler;
use Raxon\Parse\Module\Parse;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$dir = __DIR__;
$dir_vendor =
    DIRECTORY_SEPARATOR .
    'Application' .
    DIRECTORY_SEPARATOR .
    'vendor' .
    DIRECTORY_SEPARATOR;

$autoload = $dir_vendor . 'autoload.php';
$autoload = require $autoload;
try {
    $config = new Config(
        [
            'dir.vendor' => $dir_vendor,
            'time.start' => microtime(true),
        ]
    );
    $app = new App($autoload, $config);
    $result = App::run($app);
    $options = App::options($app);
    d($app->request());
    d($options);
    if(!property_exists($options, 'password')){
        throw new Exception('No -option password found');
    }
    $transport = Transport::fromDsn('smtp://system@workandtravel.world:' . $options->password. '@smtp.hostnet.nl:587');
    $mailer = new Mailer($transport);

    $email = (new Email())
        ->from('system@workandtravel.world')
        ->to('remco@workandtravel.world')
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject('Time for Symfony Mailer!')
        ->text('Sending emails is fun again!')
        ->html('<p>See Twig integration for better HTML integration!</p>');

    $mailer->send($email);
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}