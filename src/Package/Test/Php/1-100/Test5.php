<?php
/**
 * @package Package\Raxon\Parse
 * @license MIT
 * @version 2024.09.18
 * @author Remco van der Velde (remco@universeorange.com)
 * @compile-date 2024-10-06 19:23:44
 * @compile-time 179.632 ms
 * @note compiled by raxon/framework 2024.09.18
 * @url https://raxon.org
 * @source /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/0-100/Test97.tpl
 */

namespace Package\Raxon\Parse;

use Raxon\App;
use Raxon\Module\Data;
use Raxon\Module\Core;
use Package\Raxon\Parse\Service\Parse;
use Plugin;
use Exception;
use Error;
use ErrorException;
use ReflectionClass;
use Raxon\Exception\TemplateException;
use Raxon\Exception\LocateException;

class mnt_Vps3_Mount_Package_Raxon_Parse_Test_Tpl_0_100_Test97_tpl_6b2df4fb4cb7dac5cefffbb4430216dc9ffad987f56716591c8560530a8729cd {

    use Plugin\Basic;
    use Plugin\Parse;
    use Plugin\Value;
    use Plugin\Plugin_Object;

    public function __construct(App $object, Parse $parse, Data $data, $flags, $options){
        $this->object($object);
        $this->parse($parse);
        $this->data($data);
        $this->flags($flags);
        $this->options($options);
    }

    /**
     * @throws Exception
     */
    public function run(): mixed
    {
        ob_start();
        $object = $this->object();
        $parse = $this->parse();
        $data = $this->data();
        $flags = $this->flags();
        $options = $this->options();
        $options->debug = true;
        if (!($object instanceof App)) {
            throw new Exception('$object is not an instance of Raxon\App');
        }
        if (!($parse instanceof Parse)) {
            throw new Exception('$parse is not an instance of Package\Raxon\Parse\Service\Parse');
        }
        if (!($data instanceof Data)) {
            throw new Exception('$data is not an instance of Raxon\Module\Data');
        }
        if (!is_object($flags)) {
            throw new Exception('$flags is not an object');
        }
        if (!is_object($options)) {
            throw new Exception('$options is not an object');
        }
        $object->config('package.raxon/parse.build.state.tag', Core::object('{"source":"\/mnt\/Vps3\/Mount\/Package\/Raxon\/Parse\/Test\/Tpl\/0-100\/Test97.tpl","tag":"{{$array1 = [\n1,\n4,\n]}}","line":{"start":1,"end":4},"column":{"1":{"start":1,"end":14},"4":{"start":1,"end":4}}}', Core::FINALIZE));
        try {
            $data->set('array1', [
                1,
                4,
            ]);
        } catch(ErrorException | Error | Exception $exception){
            throw new TemplateException('{{$array1 = [
1,
4,
]}}
On line: 1, column: 1 in source: /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/0-100/Test97.tpl', 0, $exception);
        }
        $object->config('package.raxon/parse.build.state.tag', Core::object('{"source":"\/mnt\/Vps3\/Mount\/Package\/Raxon\/Parse\/Test\/Tpl\/0-100\/Test97.tpl","tag":"{{$array2 = [\n3,\n2,\n]}}","line":{"start":5,"end":8},"column":{"5":{"start":1,"end":14},"8":{"start":1,"end":4}}}', Core::FINALIZE));
        try {
            $data->set('array2', [
                3,
                2,
            ]);
        } catch(ErrorException | Error | Exception $exception){
            throw new TemplateException('{{$array2 = [
3,
2,
]}}
On line: 5, column: 1 in source: /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/0-100/Test97.tpl', 0, $exception);
        }
        $object->config('package.raxon/parse.build.state.tag', Core::object('{"source":"\/mnt\/Vps3\/Mount\/Package\/Raxon\/Parse\/Test\/Tpl\/0-100\/Test97.tpl","tag":"{{$array = $array1 + $array2}}","line":9,"column":{"start":1,"end":31}}', Core::FINALIZE));
        try {
            $data->set('array', $this->value_plus($data->get('array1'), $data->get('array2')));
        } catch(ErrorException | Error | Exception $exception){
            throw new TemplateException('{{$array = $array1 + $array2}}
On line: 9, column: 1 in source: /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/0-100/Test97.tpl', 0, $exception);
        }
        $object->config('package.raxon/parse.build.state.tag', Core::object('{"source":"\/mnt\/Vps3\/Mount\/Package\/Raxon\/Parse\/Test\/Tpl\/0-100\/Test97.tpl","tag":"{{$array|object:\'json\'}}","line":10,"column":{"start":1,"end":25}}', Core::FINALIZE));
        try {
            $llddlnho_mael_kmoe_ofle_paigjbinnkgn = $this->plugin_object(
                $data->get('array'),
                'json'
            );
            if($llddlnho_mael_kmoe_ofle_paigjbinnkgn === null){
                throw new TemplateException('Null-pointer exception: "$array" on line: 10, column: 1 in source: /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/0-100/Test97.tpl. You can use modifier "default" to surpress it ');
            }
            if(!is_scalar($llddlnho_mael_kmoe_ofle_paigjbinnkgn)){
                //array or object
                ob_get_clean();
                return $llddlnho_mael_kmoe_ofle_paigjbinnkgn;
            }
            elseif(is_bool($llddlnho_mael_kmoe_ofle_paigjbinnkgn)){
                return $llddlnho_mael_kmoe_ofle_paigjbinnkgn;
            } else {
                echo $llddlnho_mael_kmoe_ofle_paigjbinnkgn;
            }
        } catch (Exception $exception) {
            if ($llddlnho_mael_kmoe_ofle_paigjbinnkgn === null) {
                throw new TemplateException('Exception: "$array" on line: 10, column: 1 in source: /mnt/Vps3/Mount/Package/Raxon/Parse/Test/Tpl/0-100/Test97.tpl. You can use modifier "default" to surpress it ');
            }
        }
            echo '
';
            return ob_get_clean();
        }
    }