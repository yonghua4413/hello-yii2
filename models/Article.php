<?php
/**
 * Created by PhpStorm.
 * User: HBW
 * Date: 2016/6/8
 * Time: 0:51
 * To change this template use File | Settings | File Templates.
 */

namespace app\models;

use app\models\base\ArticleBase;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Markdown;

class Article extends ArticleBase
{
    const TYPE_HTML = 'H';
    const TYPE_MARKDOWN = 'M';
    public static $type_array = [
        self::TYPE_HTML,
        self::TYPE_MARKDOWN,
    ];
    public static $type_map = [
        self::TYPE_HTML => 'Html',
        self::TYPE_MARKDOWN => 'Markdown',
    ];

    const VISIBLE_YES = 'Y';
    const VISIBLE_NO = 'N';
    public static $visible_array = [
        self::VISIBLE_YES,
        self::VISIBLE_NO,
    ];
    public static $visible_map = [
        self::VISIBLE_YES => '显示',
        self::VISIBLE_NO => '隐藏',
    ];

    const STATUS_DISABLE = 'N';
    const STATUS_ENABLE = 'Y';
    public static $status_array = [
        self::STATUS_ENABLE,
        self::STATUS_DISABLE,
    ];
    public static $status_map = [
        self::STATUS_ENABLE => '启用',
        self::STATUS_DISABLE => '禁用',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['type', 'in', 'range' => self::$type_array],
            ['visible', 'in', 'range' => self::$visible_array],
            ['status', 'in', 'range' => self::$status_array],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'user.username',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'user.username' => '作者',
        ]);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * 解析文章
     * 
     * @return string
     */
    public function processArticle()
    {
        $content = '<article class="article-body">';
        if ($this->type == self::TYPE_MARKDOWN) {
            $content .= Markdown::process($this->content, 'gfm');
        } else if ($this->type == self::TYPE_HTML) {
            $content .= $this->content;
        }
        $content .= '</article>';
        return $content;
    }
}
