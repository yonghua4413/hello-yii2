<?php

use app\models\Article;
use app\models\Music;
use app\models\Setting;
use app\models\User;
use app\models\UserDetail;
use yii\db\Migration;

class m160523_015948_init extends Migration
{
    public function up()
    {
        $tableInnoDBOptions = 'ENGINE=InnoDB CHARACTER SET utf8';
        $tableMyISAMOptions = 'ENGINE=MyISAM CHARACTER SET utf8';

        $this->createTable(UserDetail::tableName(), [
            'id' => $this->primaryKey(10)->unsigned(),
            'user_id' => $this->integer(10)->notNull()->unique()->unsigned()->comment('用户ID'),
            'avatar_file' => $this->string(100)->defaultValue(null)->comment('头像文件'),
            'gender' => 'enum(\'0\',\'1\',\'2\') DEFAULT \'0\' COMMENT \'性别\'',
            'birthday' => $this->integer(11)->unsigned()->comment('生日'),
            'phone' => $this->string(11)->unique()->comment('电话'),
            'resume' => $this->string(100)->comment('简介'),
            'updated_at' => $this->integer(11)->unsigned()->comment('修改时间'),
        ], $tableInnoDBOptions);

        $this->createTable(Article::tableName(), [
            'id' => $this->primaryKey(10)->unsigned(),
            'title' => $this->string(20)->notNull()->comment('标题'),
            'created_by' => $this->integer(10)->notNull()->unsigned()->comment('作者'),
            'published_at' => $this->integer(11)->notNull()->unsigned()->comment('发布时间'),
            'content' => $this->text()->notNull()->comment('内容'),
            'visible' => 'enum(\'Y\',\'N\') DEFAULT \'Y\' COMMENT \'可见性\'',
            'type' => 'enum(\'H\',\'M\') NOT NULL COMMENT \'文章类型\'',
            'status' => 'enum(\'Y\',\'N\') DEFAULT \'Y\' COMMENT \'状态\'',
            'created_at' => $this->integer(11)->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->unsigned()->comment('修改时间'),
        ]);

        $this->createTable(Music::tableName(), [
            'id' => $this->primaryKey(10)->unsigned(),
            'track_title' => $this->string(50)->notNull()->comment('标题'),
            'music_file' => $this->string(100)->notNull()->comment('音乐文件'),
            'user_id' => $this->integer(10)->unsigned()->comment('用户ID'),
            'visible' => 'enum(\'Y\',\'N\') DEFAULT \'Y\' COMMENT \'可见性\'',
            'status' => 'enum(\'Y\',\'N\') DEFAULT \'Y\' COMMENT \'状态\'',
            'created_at' => $this->integer(11)->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->unsigned()->comment('修改时间'),
        ], $tableInnoDBOptions);

        $this->createTable(Setting::tableName(), [
            'id' => $this->primaryKey(10)->unsigned(),
            'key' => $this->string(20)->notNull()->unique()->comment('键'),
            'value' => $this->text()->notNull()->comment('值'),
            'status' => 'enum(\'Y\',\'N\') DEFAULT \'Y\' COMMENT \'状态\'',
            'description' => $this->string(200)->comment('描述'),
            'tag' => $this->string(20)->comment('标记'),
            'created_by' => $this->integer(10)->unsigned()->comment('创建者'),
            'updated_by' => $this->integer(10)->unsigned()->comment('最后操作者'),
            'created_at' => $this->integer(11)->unsigned()->comment('创建时间'),
            'updated_at' => $this->integer(11)->unsigned()->comment('修改时间'),
        ], $tableMyISAMOptions . ' COMMENT=\'网站配置\'');

        //插入数据
        $time = time();
        
        $this->batchInsert(User::tableName(), [
            'username',
            'auth_key',
            'password_hash',
            'email',
            'created_at',
            'updated_at',
        ], [
            [
                'hu',
                Yii::$app->security->generateRandomString(),
                '$2y$13$GseYzG9z1Q87wVsGJ9DdleP/QHSPoPbAdLr3y4D8gDCFq2BRuIYQu',
                'hubeiwei1234@qq.com',
                $time,
                $time,
            ],
            [
                'test',
                Yii::$app->security->generateRandomString(),
                '$2y$13$bkTA/HShzVF8P2uZDRZgbe5jPftXwO3xIJnF5gjph63xtFKs9bEpS',
                'hubeiwei@hotmail.com',
                $time,
                $time,
            ],
        ]);

        $this->batchInsert(UserDetail::tableName(), ['user_id', 'updated_at'], [[1, $time], [2, $time]]);

        //以下是插入rbac相关的数据
        $this->batchInsert('auth_item', ['name', 'type', 'created_at', 'updated_at'], [
            ['/*', 2, $time, $time],
            ['/admin/*', 2, $time, $time],
            ['/admin/default/index', 2, $time, $time],
            ['/gii/*', 2, $time, $time],
            ['/gii/default/index', 2, $time, $time],
            ['/manage/article/*', 2, $time, $time],
            ['/manage/article/index', 2, $time, $time],
            ['/manage/default/*', 2, $time, $time],
            ['/manage/default/index', 2, $time, $time],
            ['/manage/music/*', 2, $time, $time],
            ['/manage/music/index', 2, $time, $time],
            ['/manage/setting/*', 2, $time, $time],
            ['/manage/setting/index', 2, $time, $time],
            ['/manage/user-detail/*', 2, $time, $time],
            ['/manage/user-detail/index', 2, $time, $time],
            ['/manage/user/*', 2, $time, $time],
            ['/manage/user/index', 2, $time, $time],
        ]);

        $this->batchInsert('auth_item', ['name', 'type', 'description', 'created_at', 'updated_at'], [
            ['Guest', 1, '访客', $time, $time],
            ['SuperAdmin', 1, '超管', $time, $time],
        ]);

        $this->insert('auth_item_child', ['parent' => 'SuperAdmin', 'child' => '/*']);

        $this->insert('auth_assignment', [
            'item_name' => 'SuperAdmin',
            'user_id' => '1',
            'created_at' => $time,
        ]);

        $this->batchInsert('menu', ['name', 'parent', 'route', 'order'], [
            ['首页', null, '/manage/default/index', 1],
            ['前台', null, null, 2],
            ['文章管理', 2, '/manage/article/index', 1],
            ['音乐管理', 2, '/manage/article/index', 2],
            ['用户', null, null, 3],
            ['用户管理', 5, '/manage/user/index', 1],
            ['用户资料', 5, '/manage/user-detail/index', 2],
            ['系统', null, null, 4],
            ['网站配置', 8, '/manage/setting/index', 1],
            ['权限管理', 8, '/admin/default/index', 2],
            ['代码生成', 8, '/gii/default/index', 3],
        ]);
    }

    public function down()
    {
        $this->dropTable(User::tableName());
        $this->dropTable(UserDetail::tableName());
        $this->dropTable(Article::tableName());
        $this->dropTable(Music::tableName());
        $this->dropTable(Setting::tableName());

        //其实都可以用truncateTable的，但是auth_item这个表居然不能用，反正没有自增id，用delete就好
        $this->delete('menu');
        $this->delete('auth_assignment');
        $this->delete('auth_item_child');
        $this->delete('auth_item');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
