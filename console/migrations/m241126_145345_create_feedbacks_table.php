<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedbacks}}`.
 */
class m241126_145345_create_feedbacks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%feedback}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull()->comment('Email'),
            'phone' => $this->string()->notNull()->comment('Телефон'),
            'user_id' => $this->integer()->comment('Пользователь'),
            'text' => $this->text()->comment('Текст сообщения')->defaultValue(null),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-feedback-user_id',
            '{{%feedback}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-feedback-user_id',
            '{{%feedback}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-feedback-user_id', '{{%feedbacks}}');
        $this->dropIndex('idx-feedback-user_id', '{{%feedbacks}}');

        $this->dropTable('{{%feedback}}');
    }
}
