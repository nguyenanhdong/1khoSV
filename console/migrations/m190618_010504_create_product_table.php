<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product}}`.
 */
class m190618_010504_create_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'productname' => $this->string()->notNull()->unique(),
            'image'=>$this->string()->notNull(),
            'avatar'=>$this->string()->notNull(),
            'path'=>$this->string(),
            'video'=>$this->string(),
            'des'=>$this->string(4000),
            'status' => $this->smallInteger()->notNull()->defaultValue(1)->comment('0:notshow;1:show;2:notbuy'),
            'price' => $this->string()->notNull(),
            'pricesale' => $this->string(),
            'size' => $this->string(),
            'color' => $this->string(),
            'brand' => $this->string(),
            'timesale' => $this->string(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product}}');
    }
}
