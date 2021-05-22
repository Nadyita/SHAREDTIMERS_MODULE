<?php declare(strict_types=1);

namespace Nadybot\User\Modules\SHAREDTIMERS_MODULE;

use Exception;
use Nadybot\Core\Registry;
use Nadybot\Modules\TIMERS_MODULE\TimerController;

/**
 * @author Nadyita (RK5)
 *
 * @Instance(overwrite=true, value="timercontroller")
 *
 *	@DefineCommand(
 *		command     = 'rtimer',
 *		accessLevel = 'guild',
 *		description = 'Adds a repeating timer',
 *		help        = 'timers.txt'
 *	)
 *	@DefineCommand(
 *		command     = 'timers',
 *		accessLevel = 'guild',
 *		description = 'Sets and shows timers',
 *		help        = 'timers.txt',
 *		alias       = 'timer'
 *	)
 */
class SharedTimerController extends TimerController {
	public const DB_TABLE = "timers";

	/** @Setup */
	public function setup(): void {
		$this->db->loadMigrations($this->moduleName, __DIR__ . "/Migrations");
		parent::setup();
		$this->loadFromDB();
	}

	/**
	 * @Event("timer(5sec)")
	 * @Description("Periodically reloads timers from database")
	 */
	public function loadFromDB(): void {
		$oldTimers = $this->timers;
		$this->timers = [];
		$data = $this->readAllTimers();
		foreach ($data as $row) {
			$timerKey = strtolower($row->name);
			// Keep timers that didn't change to keep alerts
			if (isset($oldTimers[$timerKey]) && $oldTimers[$timerKey]->settime == $row->settime) {
				$this->timers[$timerKey] = $oldTimers[$timerKey];
				continue;
			}

			// remove alerts that have already passed
			// leave 1 alert so that owner can be notified of timer finishing
			while (count($row->alerts) > 1 && $row->alerts[0]->time <= time()) {
				array_shift($row->alerts);
			}

			$this->timers[$timerKey] = $row;
		}
	}

	/**
	 * @Event("timer(1sec)")
	 * @Description("Checks timers and periodically updates chat with time left")
	 */
	public function checkTimers(): void {
		$time = time();

		foreach ($this->timers as $timer) {
			// Remove timers only from the database after every bot had the chance to fire it
			if (count($timer->alerts) == 0 && ($time - 10 > $timer->endtime)) {
				$this->remove($timer->name);
				continue;
			}

			foreach ($timer->alerts as $alert) {
				if ($alert->time > $time) {
					break;
				}

				array_shift($timer->alerts);

				[$name, $method] = explode(".", $timer->callback);
				$instance = Registry::getInstance($name);
				if ($instance === null) {
					$this->logger->log('ERROR', "Error calling callback method '$timer->callback' for timer '$timer->name': Could not find instance '$name'.");
					continue;
				}
				try {
					$instance->{$method}($timer, $alert);
				} catch (Exception $e) {
					$this->logger->log("ERROR", "Error calling callback method '$timer->callback' for timer '$timer->name': " . $e->getMessage(), $e);
				}
			}
		}
	}
}
