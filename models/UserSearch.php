<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UserSearch represents the model behind the search form about `app\models\User`.
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'password', 'role', 'email', 'username', 'mobile', 'gender', 'birthdate', 'auth_key', 'profile_photo', 'cover_photo', 'facebook_id', 'firebase_token', 'approved', 'blocked', 'created', 'updated'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'birthdate' => $this->birthdate,
            'approved' => $this->approved,
            'blocked' => $this->blocked,
            'created' => $this->created,
            'updated' => $this->updated,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'role', $this->role])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'profile_photo', $this->profile_photo])
            ->andFilterWhere(['like', 'cover_photo', $this->cover_photo])
            ->andFilterWhere(['like', 'facebook_id', $this->facebook_id])
            ->andFilterWhere(['like', 'firebase_token', $this->firebase_token]);

        return $dataProvider;
    }
}
