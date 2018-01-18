<?php

namespace app\models;

/**
 * This is the model class for table "branch_flag".
 *
 * @property integer $id
 * @property integer $branch_id
 * @property integer $flag_id
 * @property string $created
 * @property string $updated
 */
class BranchFlag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'branch_flag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['branch_id', 'flag_id'], 'required'],
            [['branch_id', 'flag_id'], 'integer'],
            [['created', 'updated'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_id' => 'Branch ID',
            'flag_id' => 'Flag ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getFlag()
    {
        return $this->hasOne(Flag::className(), ['id' => 'flag_id']);
    }
}
