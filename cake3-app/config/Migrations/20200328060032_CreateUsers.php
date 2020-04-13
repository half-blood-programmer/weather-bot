<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsers extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('created', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('available', 'boolean', [
            'default' => true,
            'null' => false,
        ]);

        $table->addColumn('first_name', 'text', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('username', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('language_code', 'string', [
            'default' => null,
            'limit' => 2,
            'null' => false,
        ]);
        $table->addColumn('is_bot', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('chat_id', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('city_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('forecast_message_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('weather_message_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('weather_updated_message_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('last_updated_weather', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('last_updated_forecast', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('tz', 'smallint', [
            'default' => null,
            'null' => true,
        ]);

        $table->create();
    }

    public function down()
    {

    }
}
