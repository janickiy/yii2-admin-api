<?php

use yii\db\Migration;

/**
 * Class m241216_000112_user_refresh_tokens
 */
class m241216_000112_user_refresh_tokens extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_refresh_tokens}}', [
            'user_refresh_tokenID' => $this->primaryKey(),
            'urf_userID' => $this->integer()->notNull(),
            'urf_token' => $this->string(1000)->notNull(),
            'urf_ip' => $this->string(50)->notNull(),
            'urf_user_agent' => $this->string(1000)->notNull(),
            'urf_created' => $this->dateTime()->notNull()->comment('UTC'),
            'urf_expires_at' => $this->integer()->notNull()->comment('Timestamp'),
        ]);

        $this->addForeignKey(
            'fk-user_refresh_tokens-user',
            '{{%user_refresh_tokens}}',
            'urf_userID',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addCommentOnTable('{{%user_refresh_tokens}}', 'For JWT authentication process');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user_refresh_tokens-user', '{{%user_refresh_tokens}}');

        $this->dropTable('{{%user_refresh_tokens}}');
    }
}
