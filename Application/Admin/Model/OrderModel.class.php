<?php
/**
 * Created by PhpStorm.
 * User: dong
 * Date: 2018/7/19
 * Time: 0:58
 */
namespace Admin\Model;


use Think\Model\RelationModel;

class OrderModel extends RelationModel
{
    protected $tableName = 'order';

    protected $_link = array(
        'BlockLog' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'Blockedlog',
            'foreign_key' => 'orderid',
            'parent_key' => 'pay_orderid',
        )
    );
}