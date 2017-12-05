<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "option".
 *
 * @property integer $id
 * @property integer $poll_id
 * @property string $option
 * @property string $optionAr
 * @property string $correct
 * @property string $created
 * @property string $updated
 */
class Option extends LikeableActiveRecored
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'option';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['poll_id', 'option', 'optionAr', 'correct'], 'required'],
            [['poll_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['option', 'optionAr'], 'string', 'max' => 255],
            [['correct'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'poll_id' => 'Poll',
            'option' => 'Option',
            'optionAr' => 'Option Ar',
            'correct' => 'Correct',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getPoll()
    {
        return $this->hasOne(Poll::className(), ['id' => 'poll_id']);
    }
}
