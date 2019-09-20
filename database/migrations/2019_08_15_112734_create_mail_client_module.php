<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Domain;


class CreateMailClientModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTable();
        $module = $this->createModule();
        $this->activateModuleOnDomains($module);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop table
        Schema::dropIfExists($this->tablePrefix . 'mail-clients');

        // Delete module
        Module::where('name', 'mail-client')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = '';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'mail-clients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createModule()
    {
        $module = new Module([
            'name' => 'mail-client',
            'icon' => 'mail',
            'model_class' => '',
            'data' => json_decode('{"package":"jerome-savin\/uccello-email-client","menu":"uccello.mail.list"}')
        ]);
        $module->save();
        return $module;
    }

    protected function activateModuleOnDomains($module)
    {
        $domains = Domain::all();
        foreach ($domains as $domain) {
            $domain->modules()->attach($module);
        }
    }
}