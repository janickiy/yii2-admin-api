<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 *
 * @property Book[] $Book
 * @property Category[] $categories
 * @property Category $parent
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['parent_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'parent_id' => 'Parent ID',
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return ActiveQuery
     */
    public function getCategories(): ActiveQuery
    {
        return $this->hasMany(Category::class, ['parent_id' => 'id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'parent_id']);
    }

    /**
     * @param string $name
     * @param int $parent_id
     * @return array|self
     * @throws \yii\db\Exception
     */
    public static function create(string $name, int $parent_id )
    {
        $category = new self();
        $category->name = $name;
        $category->parent_id = $parent_id;

        if (!$category->save()) {
            return  $category->getErrors();
        }

        return $category;
    }
}
