<?php
namespace PunktDe\Gatling\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "PunktDe.Gatling".       *
 *                                                                        *
 *                                                                        */

use PunktDe\Gatling\Domain\Model\Simulation;
use PunktDe\Gatling\Processor\StatsProcessor;
use TYPO3\Flow\Annotations as Flow;
use \TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class GatlingCommandController extends CommandController {


	/**
	 * @var string
	 */
	protected $gatlingRoot;

	/**
	 * @var string
	 */
	protected $reportRootPath;


	/**
	 * @param string $gatlingBin The path and file name of the gatling.sh
	 * @param string $gatlingRoot The root path to the gatling test directory.
	 * @param string $reportRoot The path where the report should be written to.
	 * @param string $statsRoot The path where the stats should be written to
	 * @return string
	 */
	public function runCommand($gatlingBin, $gatlingRoot, $reportRoot, $statsRoot) {

		$this->gatlingRoot = $gatlingRoot;
		$this->reportRootPath = $reportRoot;

		$simulations = $this->buildAvailableSimulations();
		$errors = 0;

		foreach($simulations as $simulation) { /** @var $simulation Simulation */
			echo sprintf("=========== Running Simulation %s ===========\nCommand: %s\n",
				$simulation->getCombinedSimulationName(), $simulation->getRunCommand($gatlingBin));

			chdir($gatlingRoot);
			passthru($simulation->getRunCommand($gatlingBin));

			$this->renameReportFolder($simulation);

			$statsProcessor = new StatsProcessor();
			$statsProcessor->process($simulation, $statsRoot);
			$errors = $statsProcessor->getErrors($simulation);

			echo sprintf("=========== Finished Simulation %s ===========\n\n", $simulation->getCombinedSimulationName());
		}

		$this->quit($errors);
	}


	/**
	 * @internal param $gatlingRoot
	 * @return array
	 */
	protected function buildAvailableSimulations() {
		$simulationFilesPaths = Files::readDirectoryRecursively($this->gatlingRoot, '.scala');

		$simulations = array();

		foreach($simulationFilesPaths as $simulationFilePath) {
			$simulations[] = $this->buildSimulation($simulationFilePath);
		}

		return $simulations;
	}


	/**
	 * @param $simulationFilePath
	 * @return Simulation
	 */
	protected function buildSimulation($simulationFilePath) {
		$simulation = new Simulation();
		$simulation->setFilePath($simulationFilePath);

		$simulationCode = Files::getFileContents($simulationFilePath);

		preg_match("/package (.*)/", $simulationCode, $package);
		$simulation->setPackage($package[1]);

		preg_match("/class (.*) extends Simulation/", $simulationCode, $className);
		$simulation->setClassName($className[1]);

		$simulation->setDataPath('Data');
		$simulation->setSimulationPath('Simulations');
		$simulation->setReportRootPath($this->reportRootPath);

		return $simulation;
	}


	/**
	 * @param Simulation $simulation
	 * @throws \Exception
	 */
	protected function renameReportFolder(Simulation $simulation) {
		$dirEntries = scandir($simulation->getReportRootPath());
		$simulationName = $simulation->getCombinedSimulationName();
		$simulation->setReportPath(Files::concatenatePaths(array($simulation->getReportRootPath(), $simulationName)));

		if(is_dir($simulation->getReportPath())) {
			throw new \Exception('The report directory ' . $simulationName . ' already exists in directory ' . $simulation->getReportRootPath(), 1409137387);
		}

		foreach($dirEntries as $dirEntry) {
			$reportSourcePath = Files::concatenatePaths(array($simulation->getReportRootPath(), $dirEntry));

			if(is_dir($reportSourcePath)) {
				if(substr($dirEntry, 0, strlen($simulationName)) == $simulationName) {
					echo "Renaming " . $reportSourcePath . ' to ' . $simulation->getReportPath() . "\n";
					rename($reportSourcePath, $simulation->getReportPath());
				}
			}
		}
	}
}
