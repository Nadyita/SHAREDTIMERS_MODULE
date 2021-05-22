<?php declare(strict_types=1);

namespace Nadybot\User\Modules\SHAREDTIMERS_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\DB;
use Nadybot\Core\LoggerWrapper;
use Nadybot\Core\SchemaMigration;

class CreateTimersTable implements SchemaMigration {
	public function migrate(LoggerWrapper $logger, DB $db): void {
		$table = "timers";
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, function(Blueprint $table) {
			$table->string("name", 255)->nullable();
			$table->string("owner", 25)->nullable();
			$table->string("mode", 50)->nullable();
			$table->integer("endtime")->nullable();
			$table->integer("settime")->nullable();
			$table->string("callback", 255)->nullable();
			$table->string("data", 255)->nullable();
			$table->text("alerts")->nullable();
		});
	}
}
