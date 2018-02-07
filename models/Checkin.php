<?php

namespace app\models;

/**
 * This is the model class for table "checkin".
 *
 * @property integer $id
 * @property string $text
 * @property string $rating
 * @property integer $user_id
 * @property integer $branch_id
 * @property string $created
 * @property string $updated
 */
class Checkin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'checkin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'branch_id'], 'required'],
            [['user_id', 'branch_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['text'], 'string', 'max' => 1023],
            [['rating'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Text',
            'rating' => 'Rating',
            'user_id' => 'User ID',
            'branch_id' => 'Branch ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }
}
