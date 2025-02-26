<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace Raxon\Parse\Module;

use Raxon\Module\Data;

use Exception;

class Variable {

    /**
     * @throws Exception
     */
    public static function count_assign(Build $build, Data $storage, $token=[], $is_result=false): string
    {
        $count = array_shift($token);
        $variable = array_shift($token);
        switch($count['type']){
            case Token::TYPE_IS_MINUS_MINUS :
                $assign = '$this->storage()->set(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->min_min_assign(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\')';
                $assign .= '))';
                return $assign;
            case Token::TYPE_IS_PLUS_PLUS :
                $assign = '$this->storage()->set(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->plus_plus_assign(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\')';
                $assign .= '))';
                return $assign;
            default:
                throw new Exception('unknown counter in assign (' . $count['type'] . ')');
        }        
    }

    /**
     * @throws Exception
     */
    private static function getArrayAttribute(Build $build, Data $storage, $variable=[]): string
    {
        $execute = [];
        if(array_key_exists('array', $variable['variable'])){
            foreach($variable['variable']['array'] as $nr => $list){
                if(
                    is_null($list) &&
                    array_key_exists('attribute', $variable['variable'])
                ) {
                    $execute[] = '[]';
                } else {
                    $list = $build->require('modifier', $list);
                    $list = $build->require('function', $list);
                    $value = Variable::getValue($build, $storage, $list);
                    if($value === 'null'){
                        if(!empty($execute)){
                            $add_quote = false;
                            $quote_add = false;
                            $attribute = '\'' . $variable['variable']['attribute'];
                            foreach($execute as $part_nr => $part_record){
                                if(substr($part_record, 0, 1) === '$'){
                                    if($part_nr === 0){
                                        $attribute .= '\' . \'.\' . ' . $part_record . ' . ';
                                    } else {
                                        if($add_quote === true){
                                            $attribute .= '.\' . ' . $part_record . ' . ';
                                            $add_quote = false;
                                        } else {
                                            $attribute .= ' \'.\' . ' . $part_record . ' . ';
                                        }
                                    }
                                    $quote_add = true;
                                } else {
                                    $add_quote = true;
                                    if($quote_add === true){
                                        $attribute .= '\'.' . $part_record;
                                        $quote_add = false;
                                    } else {
                                        $attribute .= '.' . $part_record;
                                    }
                                }
                            }
                            if(
                                !empty($part_record) &&
                                substr($part_record, 0, 1) === '$'
                            ){
                                $attribute = substr($attribute, 0, -3);
                            } else {
                                $attribute .= '\'';
                            }
                            $exec = '$this->storage()->index(' . $attribute  . ')';
                        } else {
                            $exec = '$this->storage()->index(\'' . $variable['variable']['attribute']  . '\')';
                        }
                        $execute[] = $exec;
                    } else {
                        if(
                            substr($value, 0, 1) === '\'' &&
                            substr($value, -1, 1) === '\''
                        ){
                            $value = substr($value, 1, -1);
                        }
                        //add compile on "
                        $execute[] = $value;
                    }
                }
            }
        }
        $result = '\'' . $variable['variable']['attribute'];
        $quote_add = false;
        $add_quote = false;
        foreach($execute as $nr => $record){
            if(substr($record, 0, 2) === '[]'){
                $result .= substr($record, 0, 2);
            }
            elseif(substr($record, 0, 1) === '$'){
                if($nr === 0){
                    $result .= '\' . \'.\' . ' . $record . ' . ';
                } else {
                    if($add_quote === true){
                        $result .= '.\' . ' . $record . ' . ';
                        $add_quote = false;
                    } else {
                        $result .= '\'.\' . ' . $record . ' . ';
                    }
                }
                $quote_add = true;
            } else {
                $add_quote = true;
                if($quote_add === true){
                    $result .= '\'.' . $record;
                    $quote_add = false;
                } else {
                    $result .= '.' . $record;
                }
            }
        }
        if(
            !empty($record) &&
            substr($record, 0, 1) === '$'
        ){
            $result = substr($result, 0, -3);
        } else {
            $result .= '\'';
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public static function assign(Build $build, Data $storage, $token=[], $is_result=false): string
    {
        $variable = array_shift($token);
        if(!array_key_exists('variable', $variable)){
            return '';
        }        
        $token = Variable::addAssign($token);
        if(
            array_key_exists('is_array', $variable['variable']) &&
            $variable['variable']['is_array'] === true &&
            $variable['variable']['operator'] === '=' &&
            array_key_exists('array', $variable['variable'])
        ){
            $attribute = Variable::getArrayAttribute($build, $storage, $variable);
            $assign = '$this->storage()->set(';
            $assign .= $attribute . ', ';
            $value = Variable::getValue($build, $storage, $token, $is_result);
            $assign .= $value . ')';
            return $assign;
        } else {
            switch($variable['variable']['operator']){
                case '=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . ')';
                    return $assign;
                case '+=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_plus_equal(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\'), ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . '))';
                    return $assign;
                case '-=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_min_equal(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\'), ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . '))';
                    return $assign;
                case '.=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_dot_equal(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\'), ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . '))';
                    return $assign;
                case '++' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_plus_plus(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\')';
                    $assign .= '))';
                    return $assign;
                case '--' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_min_min(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\')';
                    $assign .= '))';
                    return $assign;
                default:
                    throw new Exception('Variable operator not defined');

            }
        }
    }

    private static function addAssign($token=[]): array
    {
        foreach ($token as $nr => $record){
            $record['is_assign'] = true;
            $token[$nr] = $record;
        }
        return $token;
    }

    public static function is_count(Build $build, Data $storage, $token=[]): array
    {
        $count = null;
        foreach($token as $nr => $record){
            if($count === null){
                $count = $record;
            } else {
                if(array_key_exists('variable', $record)){
                   $token[$nr]['variable'] ['is_assign'] = true;
                   unset($token[$nr]['parse']);
                }                
            }
        }
        return $token;
    }

    /**
     * @throws Exception
     */
    public static function define(Build $build, Data $storage, $token=[]): string
    {
        $variable = array_shift($token);
        $object = $build->object();
        $is_variable = false;
        if(!array_key_exists('variable', $variable)){
            return '';
        }
        if(
            array_key_exists('is_array', $variable['variable']) &&
            $variable['variable']['is_array'] === true
        ){
            $variable['variable']['attribute'] .= '.\'';
            foreach($variable['variable']['array'] as $nr => $list) {
                $is_variable = false;
                $list = $build->require('modifier', $list);
                $list = $build->require('function', $list);
                $value = Variable::getValue($build, $storage, $list);
                if(
                    is_string($value) &&
                    substr($value, 0,1) === '\'' &&
                    substr($value, -1,1) === '\''
                ){
                    $variable['variable']['attribute'] .= ' . ' . substr($value, 0, -1) . '.\'';
                }
                elseif(is_string($value)){
                    if(substr($value, 0, 1) === '$'){
                        $variable['variable']['attribute'] .= ' . ' . $value . ' . \'.\'';
                        $is_variable = true;
                    } else {
                        $variable['variable']['attribute'] .= ' . ' . '\'' . $value . '.\'';
                    }

                } else {
                    $logger = $object->config('project.log.debug') ?? $object->config('project.log.app');
                    if($logger){
                        $object->logger($logger)->debug('Variable define error', [
                            'value' => $value,
                            'token' => $token,
                            'variable' => $variable,
                            'list' => $list,
                        ]);
                    }
                }
            }
            if($is_variable){
                $variable['variable']['attribute'] = substr($variable['variable']['attribute'], 0, -6);
            } else {
                $variable['variable']['attribute'] = substr($variable['variable']['attribute'], 0, -2) . '\'';
            }
            if(
                array_key_exists('is_literal', $variable) &&
                $variable['is_literal'] === true
            ){
                $define = '\'' . $variable['variable']['name'] . '\'';
            } else {
                $define = '$this->storage()->data(\'' . $variable['variable']['attribute'] . ')';
            }

        } else {
            if(
                array_key_exists('is_literal', $variable) &&
                $variable['is_literal'] === true
            ){
                $define = '\'' . $variable['variable']['name'] . '\'';
            } else {
                $define = '$this->storage()->data(\'' . $variable['variable']['attribute'] . '\')';
            }
        }
        $define_modifier = '';
        if(
            array_key_exists('has_modifier', $variable['variable']) &&
            $variable['variable']['has_modifier'] === true
        ){
            foreach($variable['variable']['modifier'] as $nr => $modifier_list){
                foreach($modifier_list as $modifier_nr => $modifier){
                    if(!array_key_exists('php_name', $modifier)){
                        continue;
                    }
                    $define_modifier .= '$this->' . $modifier['php_name'] . '($this->parse(), $this->storage(), ' . $define . ', ';
                    if(!empty($modifier['has_attribute'])){
                        foreach($modifier['attribute'] as $attribute_nr => $attribute_list){
                            $use_comma = true;
                            $set_max = 1024;
                            $set_counter = 0;
                            while(Set::has($attribute_list)) {
                                $set = Set::get($attribute_list);
                                $set = Operator::solve($build, $storage, $set);
                                $target = Set::target($attribute_list);
                                $attribute_list = Set::pre_remove($attribute_list);
                                $attribute_list = Set::replace($attribute_list, $set, $target);
                                $attribute_list = Set::remove($attribute_list);
                                $set_counter++;
                                if($set_counter > $set_max){
                                    break;
                                }
                            }
                            $attribute_list = Operator::solve($build, $storage, $attribute_list);
                            if(array_key_exists('type', $attribute_list)){
                                $attribute_list = [
                                    $attribute_list
                                ];
                            }
                            foreach($attribute_list as $token_nr => $attribute){
                                if(!is_array($attribute)){
                                    ddd($attribute_list);
                                }
                                elseif(!array_key_exists('type', $attribute)){
                                    ddd($attribute_list);
                                }
                                switch($attribute['type']){
                                    case Token::TYPE_CAST:
                                    case Token::TYPE_EXCLAMATION:
                                    case Token::TYPE_OPERATOR:
                                        $temp = [];
                                        $temp[] = $attribute;
                                        $define_modifier .= Value::get($build, $storage, $attribute);
                                        $use_comma = false;
                                        break;
                                    case Token::TYPE_METHOD :
                                        $tree = [];
                                        $tree[]= $attribute;
                                        $tree = $build->require('modifier', $tree);
                                        $tree = $build->require('function', $tree);
                                        $define_modifier .= Value::get($build, $storage, reset($tree));
                                        $use_comma = true;
                                        break;
                                    case Token::TYPE_VARIABLE:
                                        $temp = [];
                                        $temp[] = $attribute;
                                        $define_modifier .= Variable::define($build, $storage, $temp);
                                        $use_comma = true;
                                        break;
                                    default :
                                        $define_modifier .= Value::get($build, $storage, $attribute);
                                        $use_comma = true;
                                }
                            }
                            if($use_comma === true){
                                $define_modifier .= ', ';
                            } else {
                                $define_modifier .= ' ';
                            }
                        }
                    }
                    $define_modifier = substr($define_modifier, 0, -2) . ')';
                    $define = $define_modifier;
                    $define_modifier = '';
                }
            }
        }
        return $define;
    }

    /**
     * @throws Exception
     */
    public static function getValue(Build $build, Data $storage, $token=[], $is_result=false): mixed
    {
        $set_max = 1024;
        $set_counter = 0;
        while(Set::has($token)){
            $set = Set::get($token);
            $set = Operator::solve($build, $storage, $set);
            $target = Set::target($token);
            $token = Set::pre_remove($token);
            $token = Set::replace($token, $set, $target);
            $token = Set::remove($token);
            $set_counter++;
            if($set_counter > $set_max){
                break;
            }
        }
        $operator = Operator::solve($build, $storage, $token);
        $result = '';
        $in_array = false;
        $is_collect = false;
        $type = null;
        $selection = [];
        $operator_max = 1024;
        $operator_counter = 0;
        while(count($operator) >= 1){
            $record = array_shift($operator);
            if(is_bool($record) && $record === false){
                if(substr($result, -3) == ' . '){
                    $result = substr($result,0, -3);
                }
                return $result;
            }
            if(
                $is_collect === true &&
                $record['type'] !== Token::TYPE_CURLY_CLOSE
            ){
                if($type === null){
                    $type = Build::getType($build->object(), $record);
                }
                $selection[] = $record;
            }
            if(is_string($record)){
                trace();
                ddd($record);
            }
            if(!array_key_exists('type', $record)){
                if(is_array($record)){
                    $list = [];
                    $counter = 0;
                    $is_set = false;
                    foreach($record as $count => $set){
                        foreach($set as $nr => $item){
                            if(array_key_exists('type', $item)){
                                $is_set = true;
                                break;
                            }
                        }
                        if($is_set){
                            $set = Token::define($set);
                            foreach($set as $nr => $item){
                                $set[$nr] = Method::get($build, $storage, $item);
                            }
                        }
                        $list[] = Variable::getValue($build, $storage, $set);
                        $counter++;
                    }
                    if($counter === 1){
                        $result .= $list[0];
                    } else {
                        throw new Exception('Not implemented...');
                    }
                }
                elseif($is_collect === false){
                    $record = Method::get($build, $storage, $record);
                    $result .= Value::get($build, $storage, $record);
                    if(
                        !in_array(
                            $record['type'],
                            [
                                Token::TYPE_EXCLAMATION,
                                Token::TYPE_CAST
                            ],
                            true
                        )
                    ){
                        if(
                            $in_array === false &&
                            empty($record['is_foreach'])
                        ){
                            if(
                                in_array(
                                    $record['type'],
                                    [
                                        Token::TYPE_CODE
                                    ],
                                    true
                                ) &&
                                substr($record['value'], -1, 1) == '!'
                            ){
                                //nothing
                            }
                            elseif($in_array === true){
                                //nothing
                            }
                            elseif($record['type'] === Token::TYPE_PARENTHESE_OPEN) {
                                $result .= ' ';
                            }
                            elseif($record['type'] === Token::TYPE_PARENTHESE_CLOSE) {
                                $result = substr($result, 0, -3) . ')';
                            } else {
                                $add_dot = true;
                                if($record['type'] === 'code'){
                                    $rev = strrev($record['value']);
                                    if(substr($rev, 0, 6) === ' enolc'){
                                        //check for clone at the end
                                        $add_dot = false;
                                    } else {
                                        $explode = explode('(', $rev, 2);
                                        if (array_key_exists(1, $explode)) {
                                            $cast = strrev($explode[0]);
                                            $cast = explode(')', $cast, 2);
                                            if (array_key_exists(1, $cast)) {
                                                $cast = trim($cast[0]);
                                                if (
                                                    in_array(
                                                        $cast,
                                                        Value::TYPE_CAST,
                                                        true
                                                    )
                                                ) {
                                                    $add_dot = false;
                                                }
                                            }
                                        }
                                    }
                                }
                                //maybe need next...
                                if($add_dot){
                                    $result .= ' . ';
                                }
                            }
                        }
                    }
                }
            } else {
                if($record['type'] === Token::TYPE_CURLY_OPEN){
                    $selection = [];
                    $is_collect = true;
                    continue;
                }
                elseif($record['type'] === Token::TYPE_CURLY_CLOSE){
                    $result .= Code::result($build, $storage, $type, $selection);
                    $result .= ' . ';
                    $is_collect = false;
                    $type = null;
                    $selection = [];
                }
                elseif($record['type'] === Token::TYPE_BRACKET_SQUARE_OPEN){
                    $in_array = true;
                    if(substr($result, -3, 3) === ' . '){
                        $result = substr($result, 0, -3);
                    }
                    $result .= '[';
                }
                elseif(
                    $record['type'] === Token::TYPE_BRACKET_SQUARE_CLOSE &&
                    $in_array === true
                ){
                    $result .= ']';
                    if(
                        array_key_exists('array_depth', $record) &&
                        $record['array_depth'] === 0
                    ){
                        $in_array = false;
                    }
                }
                elseif($is_collect === false){
                    $record = Method::get($build, $storage, $record);
                    $result .= Value::get($build, $storage, $record);
                    if(
                        !in_array(
                            $record['type'],
                            [
                                Token::TYPE_EXCLAMATION,
                                Token::TYPE_CAST
                            ],
                            true
                        )
                    ){
                        if(
                            $in_array === false &&
                            empty($record['is_foreach'])
                        ){
                            if(
                                in_array(
                                    $record['type'],
                                    [
                                        Token::TYPE_CODE
                                    ],
                                    true
                                ) &&
                                substr($record['value'], -1, 1) == '!'
                            ){
                                //nothing
                            }
                            elseif($in_array === true){
                                //nothing
                            }
                            elseif($record['type'] === Token::TYPE_PARENTHESE_OPEN) {
                                $result .= ' ';
                            }
                            elseif($record['type'] === Token::TYPE_PARENTHESE_CLOSE) {
                                $result = substr($result, 0, -3) . ')';
                            } else {
                                $add_dot = true;
                                if($record['type'] === 'code'){
                                    $rev = strrev($record['value']);
                                    if(substr($rev, 0, 6) === ' enolc'){
                                        //check for clone at the end
                                        $add_dot = false;
                                    } else {
                                        $explode = explode('(', $rev, 2);
                                        if(array_key_exists(1, $explode)){
                                            $cast = strrev($explode[0]);
                                            $cast = explode(')', $cast, 2);
                                            if(array_key_exists(1, $cast)){
                                                $cast = trim($cast[0]);
                                                if(
                                                    in_array(
                                                        $cast,
                                                        Value::TYPE_CAST,
                                                        true
                                                    )
                                                ){
                                                    $add_dot = false;
                                                }
                                            }
                                        }
                                    }
                                }
                                //maybe need next...
                                if($add_dot){
                                    $result .= ' . ';
                                }
                            }
                        }
                    }
                    $operator_counter++;
                    if($operator_counter > $operator_max){
                        break;
                    }
                }
            }
        }
        if(substr($result, -3) === ' . '){
            $result = substr($result,0, -3);
        }
        return $result;
    }
}