<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\services\url\rewrite;

use fecshop\models\mongodb\url\UrlRewrite;
use Yii;
use yii\base\InvalidValueException;

/**
 * Url Rewrite RewriteMongodb service
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class RewriteMongodb implements RewriteInterface
{
    public $numPerPage = 20;
    /**
     * @property $urlKey | string 
     * 通过重写后的urlkey字符串，去url_rewrite表中查询，找到重写前的url字符串。
     */
    public function getOriginUrl($urlKey)
    {
        $UrlData = UrlRewrite::find()->where([
            'custom_url_key' => $urlKey,
        ])->asArray()->one();
        if ($UrlData['custom_url_key']) {
            return $UrlData['origin_url'];
        }
    }

    public function getPrimaryKey()
    {
        return '_id';
    }

    public function getByPrimaryKey($primaryKey)
    {
        if ($primaryKey) {
            return UrlRewrite::findOne($primaryKey);
        } else {
            return new UrlRewrite();
        }
    }

    /*
     * example filter:
     * [
     * 		'numPerPage' 	=> 20,
     * 		'pageNum'		=> 1,
     * 		'orderBy'	=> ['_id' => SORT_DESC, 'sku' => SORT_ASC ],
            where'			=> [
                ['>','price',1],
                ['<=','price',10]
     * 			['sku' => 'uk10001'],
     * 		],
     * 	'asArray' => true,
     * ]
     */
    public function coll($filter = '')
    {
        $query = UrlRewrite::find();
        $query = Yii::$service->helper->ar->getCollByFilter($query, $filter);

        return [
            'coll' => $query->all(),
            'count'=> $query->limit(null)->offset(null)->count(),
        ];
    }

    /**
     * @property $one|array
     * save $data to cms model,then,add url rewrite info to system service urlrewrite.
     */
    public function save($one)
    {
        $primaryVal = isset($one[$this->getPrimaryKey()]) ? $one[$this->getPrimaryKey()] : '';
        if ($primaryVal) {
            $model = UrlRewrite::findOne($primaryVal);
            if (!$model) {
                Yii::$service->helper->errors->add('UrlRewrite '.$this->getPrimaryKey().' is not exist');

                return;
            }
        } else {
            $model = new UrlRewrite();
        }
        unset($one['_id']);
        $saveStatus = Yii::$service->helper->ar->save($model, $one);

        return true;
    }
    /**
     * @property $ids | Array or String 
     * 删除相应的url rewrite 记录
     */
    public function remove($ids)
    {
        if (!$ids) {
            Yii::$service->helper->errors->add('remove id is empty');

            return false;
        }
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $model = UrlRewrite::findOne($id);
                if (isset($model[$this->getPrimaryKey()]) && !empty($model[$this->getPrimaryKey()])) {
                    $url_key = $model['url_key'];
                    $model->delete();
                } else {
                    //throw new InvalidValueException("ID:$id is not exist.");
                    Yii::$service->helper->errors->add("UrlRewrite Remove Errors:ID $id is not exist.");

                    return false;
                }
            }
        } else {
            $id = $ids;
            $model = UrlRewrite::findOne($id);
            if (isset($model[$this->getPrimaryKey()]) && !empty($model[$this->getPrimaryKey()])) {
                $url_key = $model['url_key'];
                $model->delete();
            } else {
                Yii::$service->helper->errors->add("UrlRewrite Remove Errors:ID:$id is not exist.");

                return false;
            }
        }

        return true;
    }
    /**
     * @property $time | Int
     * 根据updated_at 更新时间，删除相应的url rewrite 记录
     */
    public function removeByUpdatedAt($time)
    {
        if ($time) {
            UrlRewrite::deleteAll([
                '$or' => [
                    [
                        'updated_at' => [
                            '$lt' => (int) $time,
                        ],
                    ],
                    [
                        'updated_at' => [
                            '$exists' => false,
                        ],
                    ],
                ],

            ]);
            echo "delete complete \n";
        }
    }
    /**
     * 返回url rewrite model 对应的query
     */
    public function find()
    {
        return UrlRewrite::find();
    }
    /**
     * 返回url rewrite 查询结果
     */
    public function findOne($where)
    {
        return UrlRewrite::findOne($where);
    }
    /**
     * 返回url rewrite model
     */
    public function newModel()
    {
        return new UrlRewrite();
    }
}
