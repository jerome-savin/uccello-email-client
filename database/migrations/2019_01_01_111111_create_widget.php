<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Uccello\Core\Models\Widget;

class CreateWidget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Widget::create([
            'label' => 'widget.emails_related',
            'type' => 'summary',
            'class' => 'JeromeSavin\UccelloEmailClient\Widgets\EmailsRelated',
            'data' => json_decode('{"package":"jerome-savin\/uccello-email-client"}')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Widget::where('label', 'widget.emails_related')
            ->where('type', 'summary')
            ->where('class', 'JeromeSavin\UccelloEmailClient\Widgets\EmailsRelated')
            ->delete();
    }
}
