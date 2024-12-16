<?php

namespace app\modules\api\controllers;

use app\models\Categories;
use yii\rest\Controller;

class CategoryController extends Controller
{
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \kaabar\jwt\JwtHttpBearerAuth::class,
        ];
        return $behaviors;
    }

    public function actionIndex()
    {

        $categories = Categories::find()
            ->where(['parent_id' => null])
            ->with(['books', 'categories'])
            ->all();

        $result = [];
        foreach ($categories as $category) {
            // Формируем массив с данными категории
            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'books' => $this->formatBooks($category->books),
                'subcategories' => $this->formatSubcategories($category->categories),
            ];
            $result[] = $categoryData;
        }

        return $result;
    }

    public function actionSearch($title = null, $author = null, $status = null)
    {
        $categories = Categories::find()
            ->where(['parent_id' => null])
            ->with(['books'])
            ->all();

        $result = [];
        foreach ($categories as $category) {
            $booksQuery = $category->getBooks();

            if ($title) {
                $booksQuery->andWhere(['like', 'title', $title]);
            }

            if ($author) {
                $booksQuery->andWhere(['like', 'author', $author]);
            }

            if ($status) {
                $booksQuery->andWhere(['status' => $status]);
            }

            $books = $booksQuery->all();

            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'books' => $this->formatBooks($books),
            ];
            $result[] = $categoryData;
        }

        return $result;
    }

    /**
     * Форматируем данные книг
     *
     * @param \yii\db\ActiveQuery $books
     * @return array
     */
    private function formatBooks($books)
    {
        $bookData = [];
        foreach ($books as $book) {
            $bookData[] = [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'thumbnailUrl' => $book->thumbnailUrl,
                'pageCount' => $book->pageCount,
                'shortDescription' => $book->shortDescription,
                'status' => $book->status,
                'publishedDate' => $book->publishedDate,
                'link' => \Yii::$app->urlManager->createUrl(['api/book/view', 'id' => $book->id])
            ];
        }
        return $bookData;
    }

    /**
     * Форматируем данные подкатегорий
     *
     * @param \yii\db\ActiveQuery $subcategories
     * @return array
     */
    private function formatSubcategories($subcategories)
    {
        $subcategoryData = [];
        foreach ($subcategories as $subcategory) {
            $subcategoryData[] = [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
            ];
        }
        return $subcategoryData;
    }
}