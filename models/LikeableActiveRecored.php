<?php

namespace app\models;

/**
 * This is the abstract model class that support reactions (like and dislike).
 *
 */
class LikeableActiveRecored extends \yii\db\ActiveRecord
{
    public function getLikes()
    {
        return $this->hasMany(Reaction::className(), ['object_id' => 'id'])
            ->where(['object_type' => static::tableName(), 'type' => 'like']);
    }

    public function getDislikes()
    {
        return $this->hasMany(Reaction::className(), ['object_id' => 'id'])
            ->where(['object_type' => static::tableName(), 'type' => 'dislike']);
    }
}
