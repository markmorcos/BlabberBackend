<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BusinessSearch represents the model behind the search form about `app\models\Business`.
 */
class BusinessSearch extends Business
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'admin_id'], 'integer'],
            [['name', 'nameAr', 'phone', 'main_image', 'rating', 'price', 'website', 'fb_page', 'description', 'descriptionAr', 'featured', 'verified', 'show_in_home', 'created', 'updated'], 'safe'],
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
        $query = Business::find();

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
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'category_id' => $this->category_id,
            'admin_id' => $this->admin_id,
            'created' => $this->created,
            'updated' => $this->updated,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'nameAr', $this->nameAr])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'addressAr', $this->addressAr])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'operation_hours', $this->operation_hours])
            ->andFilterWhere(['like', 'lat', $this->lat])
            ->andFilterWhere(['like', 'lng', $this->lng])
            ->andFilterWhere(['like', 'main_image', $this->main_image])
            ->andFilterWhere(['like', 'rating', $this->rating])
            ->andFilterWhere(['like', 'price', $this->price])
            ->andFilterWhere(['like', 'website', $this->website])
            ->andFilterWhere(['like', 'fb_page', $this->fb_page])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'descriptionAr', $this->descriptionAr])
            ->andFilterWhere(['like', 'featured', $this->featured])
            ->andFilterWhere(['like', 'verified', $this->verified])
            ->andFilterWhere(['like', 'show_in_home', $this->show_in_home]);

        return $dataProvider;
    }
}
